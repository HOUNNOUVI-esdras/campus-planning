<?php

use App\Enums\CourseChange;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

/**
 * Build a valid course form submission.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function courseFormData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Algorithmique avancée',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '10:00',
        'ends_at' => '12:00',
    ], $overrides);
}

test('a professor can schedule a course for a group', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData(['group_id' => $group->id]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', 'Cours programmé. Les participants ont été notifiés par email.');

    $course = Course::query()->sole();

    expect($course->title)->toBe('Algorithmique avancée')
        ->and($course->room)->toBe('Amphi B')
        ->and($course->day_of_week)->toBe(Weekday::Wednesday)
        ->and($course->formattedStartTime())->toBe('10:00')
        ->and($course->formattedEndTime())->toBe('12:00')
        ->and($course->professor->is($professor))->toBeTrue()
        ->and($course->group->is($group))->toBeTrue();
});

test('scheduling a course emails the professor and every student of the group', function () {
    $professor = User::factory()->professor()->create();
    $alice = User::factory()->student()->create();
    $bob = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach([$alice->id, $bob->id]);

    Notification::fake();

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData(['group_id' => $group->id]))
        ->assertSessionHasNoErrors();

    $course = Course::query()->sole();

    Notification::assertSentTo(
        [$professor, $alice, $bob],
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->course->is($course)
            && $notification->change === CourseChange::Created,
    );
    Notification::assertCount(3);
});

test('a course is saved when email delivery fails', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $this->mock(ChannelManager::class)
        ->shouldReceive('send')
        ->andThrow(new RuntimeException('Mail is down.'));

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData(['group_id' => $group->id]))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('warning', "Cours programmé, mais les emails n'ont pas pu être envoyés.");

    expect(Course::count())->toBe(1);
});

test('students cannot schedule courses', function () {
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();

    $this->actingAs($student)
        ->post(route('courses.store'), courseFormData(['group_id' => $group->id]))
        ->assertForbidden();

    expect(Course::count())->toBe(0);
});

test('a guest is redirected from the course form', function () {
    $this->get(route('courses.create'))
        ->assertRedirect(route('login'));
});

test('a course requires a title, a room, a day, a time slot and a group', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor)
        ->post(route('courses.store'), [])
        ->assertSessionHasErrors([
            'title' => 'Le titre du cours est obligatoire.',
            'room' => 'La salle est obligatoire.',
            'day_of_week' => 'Choisissez un jour.',
            'starts_at' => "L'heure de début est obligatoire.",
            'ends_at' => "L'heure de fin est obligatoire.",
            'group_id' => 'Choisissez le groupe qui suit ce cours.',
        ]);

    expect(Course::count())->toBe(0);
});

test('a course rejects a group that does not exist', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData(['group_id' => 999]))
        ->assertSessionHasErrors(['group_id' => 'Le groupe sélectionné est invalide.']);

    expect(Course::count())->toBe(0);
});

test('a course must use one of the available rooms', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData(['room' => 'Salle inconnue', 'group_id' => $group->id]))
        ->assertSessionHasErrors(['room' => 'Choisissez une salle valide.']);

    expect(Course::count())->toBe(0);
});

test('a course must end after it starts', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData([
            'starts_at' => '12:00',
            'ends_at' => '11:00',
            'group_id' => $group->id,
        ]))
        ->assertSessionHasErrors(['ends_at' => "L'heure de fin doit être postérieure à l'heure de début."]);

    expect(Course::count())->toBe(0);
});

test('a professor cannot schedule two courses at the same time', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    Course::factory()->for($professor, 'professor')->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $this->actingAs($professor)
        ->post(route('courses.store'), courseFormData([
            'day_of_week' => Weekday::Monday->value,
            'starts_at' => '09:00',
            'ends_at' => '11:00',
            'group_id' => $group->id,
        ]))
        ->assertSessionHasErrors(['starts_at' => 'Ce créneau chevauche un autre cours que vous enseignez déjà.']);

    expect(Course::count())->toBe(1);
});

test('a professor sees their course in the edit form', function () {
    $professor = User::factory()->professor()->create();
    $course = Course::factory()->for($professor, 'professor')->create(['title' => 'Algorithmique avancée']);

    $this->actingAs($professor)
        ->get(route('courses.edit', $course))
        ->assertOk()
        ->assertSee('Algorithmique avancée');
});

test('a professor can update their course and participants are emailed', function () {
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($student);

    $course = Course::factory()->for($professor, 'professor')->for($group)->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    Notification::fake();

    $this->actingAs($professor)
        ->put(route('courses.update', $course), courseFormData([
            'title' => 'Algorithmique et complexité',
            'room' => 'Amphi C',
            'day_of_week' => Weekday::Monday->value,
            'starts_at' => '08:00',
            'ends_at' => '10:00',
            'group_id' => $group->id,
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect($course->refresh()->title)->toBe('Algorithmique et complexité')
        ->and($course->room)->toBe('Amphi C');

    Notification::assertSentTo(
        [$professor, $student],
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->course->is($course)
            && $notification->change === CourseChange::Updated,
    );
});

test('a professor can move their course to another group', function () {
    $professor = User::factory()->professor()->create();
    $secondGroup = Group::factory()->create();
    $course = Course::factory()->for($professor, 'professor')->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $this->actingAs($professor)
        ->put(route('courses.update', $course), courseFormData([
            'day_of_week' => Weekday::Monday->value,
            'starts_at' => '08:00',
            'ends_at' => '10:00',
            'group_id' => $secondGroup->id,
        ]))
        ->assertSessionHasNoErrors();

    expect($course->refresh()->group->is($secondGroup))->toBeTrue();
});

test('a professor cannot edit a course taught by another professor', function () {
    $intruder = User::factory()->professor()->create();
    $course = Course::factory()->create(['title' => 'Algorithmique avancée']);

    $this->actingAs($intruder)
        ->put(route('courses.update', $course), courseFormData([
            'title' => 'Cours détourné',
            'group_id' => $course->group_id,
        ]))
        ->assertForbidden();

    expect($course->refresh()->title)->toBe('Algorithmique avancée');
});

test('a professor can cancel their course and its students are emailed', function () {
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($student);

    $course = Course::factory()->for($professor, 'professor')->for($group)->create();

    Notification::fake();

    $this->actingAs($professor)
        ->delete(route('courses.destroy', $course))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', 'Cours annulé. Les étudiants ont été notifiés par email.');

    expect($course->fresh())->toBeNull();

    Notification::assertSentTo(
        $student,
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->change === CourseChange::Cancelled,
    );
    Notification::assertNotSentTo($professor, CourseScheduleNotification::class);
});

test('students cannot delete a course', function () {
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($student);
    $course = Course::factory()->for($group)->create();

    $this->actingAs($student)
        ->delete(route('courses.destroy', $course))
        ->assertForbidden();

    expect($course->fresh())->not->toBeNull();
});
