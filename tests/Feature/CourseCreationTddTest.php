<?php

use App\Enums\CourseChange;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->professor = User::factory()->professor()->create();
    $this->student = User::factory()->student()->create();
    $this->group = Group::factory()->create();
    $this->group->students()->attach($this->student);

    $this->payload = [
        'title' => 'Algorithmique avancée',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $this->group->id,
    ];
});

test('un professeur crée un cours pour un groupe', function () {
    Notification::fake();

    $this->actingAs($this->professor)
        ->post(route('courses.store'), $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $course = Course::query()->sole();

    expect($course->professor_id)->toBe($this->professor->id)
        ->and($course->title)->toBe('Algorithmique avancée')
        ->and($course->room)->toBe('Amphi B')
        ->and($course->day_of_week)->toBe(Weekday::Monday)
        ->and($course->starts_at)->toBe('08:00:00')
        ->and($course->ends_at)->toBe('10:00:00')
        ->and($course->group_id)->toBe($this->group->id);

    Notification::assertSentTo(
        $this->professor,
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->change === CourseChange::Created,
    );

    Notification::assertSentTo(
        $this->student,
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->change === CourseChange::Created,
    );
});

test('un étudiant ne peut pas ouvrir le formulaire de création', function () {
    $this->actingAs($this->student)
        ->get(route('courses.create'))
        ->assertForbidden();
});

test('les données invalides sont rejetées', function () {
    $this->actingAs($this->professor)
        ->post(route('courses.store'), [
            'title' => '',
            'room' => 'Salle inconnue',
            'day_of_week' => Weekday::Sunday->value,
            'starts_at' => '07:00',
            'ends_at' => '06:00',
            'group_id' => $this->group->id,
        ])
        ->assertSessionHasErrors(['title', 'room', 'day_of_week', 'starts_at', 'ends_at']);

    expect(Course::count())->toBe(0);
});

test('un professeur ne peut pas avoir deux cours qui se chevauchent', function () {
    Course::factory()->for($this->professor, 'professor')->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $this->actingAs($this->professor)
        ->post(route('courses.store'), $this->payload)
        ->assertSessionHasErrors([
            'starts_at' => 'Ce créneau chevauche un autre cours que vous enseignez déjà.',
        ]);

    expect(Course::count())->toBe(1);
});

test('deux cours qui se touchent ne sont pas en conflit', function () {
    Course::factory()->for($this->professor, 'professor')->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $this->actingAs($this->professor)
        ->post(route('courses.store'), [...$this->payload, 'starts_at' => '10:00', 'ends_at' => '12:00'])
        ->assertSessionHasNoErrors();

    expect(Course::count())->toBe(2);
});

test("le cours est conservé si l'email échoue", function () {
    Notification::shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('SMTP indisponible'));

    $this->actingAs($this->professor)
        ->post(route('courses.store'), $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(Course::count())->toBe(1);
});
