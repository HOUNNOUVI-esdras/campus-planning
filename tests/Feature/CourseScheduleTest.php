<?php

use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Illuminate\Support\Facades\Notification;

test('a guest is redirected to the login page', function () {
    $response = $this->get(route('courses.create'));

    $response->assertRedirect(route('login'));
});

test('a professor can update their course', function () {
    $professor = User::factory()->professor()->create();
    $course = Course::factory()->for($professor, 'professor')->create();

    $response = $this->actingAs($professor)->put(route('courses.update', $course), [
        'title' => 'Algorithmique et complexité',
        'room' => 'Amphi C',
        'day_of_week' => Weekday::Tuesday->value,
        'starts_at' => '14:00',
        'ends_at' => '16:00',
        'group_id' => $course->group_id,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('courses', [
        'id' => $course->id,
        'title' => 'Algorithmique et complexité',
        'room' => 'Amphi C',
    ]);
});

test('a professor cannot update a course taught by another professor', function () {
    $intruder = User::factory()->professor()->create();
    $course = Course::factory()->create(['title' => 'Algorithmique']);

    $response = $this->actingAs($intruder)->put(route('courses.update', $course), [
        'title' => 'Cours détourné',
        'room' => 'Amphi C',
        'day_of_week' => Weekday::Tuesday->value,
        'starts_at' => '14:00',
        'ends_at' => '16:00',
        'group_id' => $course->group_id,
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Algorithmique']);
});

test('a professor can cancel their course and the students are emailed', function () {
    Notification::fake();

    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($student);
    $course = Course::factory()->for($professor, 'professor')->for($group)->create();

    $response = $this->actingAs($professor)->delete(route('courses.destroy', $course));

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    Notification::assertSentTo($student, CourseScheduleNotification::class);
});

test('a student cannot cancel a course', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();

    $response = $this->actingAs($student)->delete(route('courses.destroy', $course));

    $response->assertForbidden();
    $this->assertDatabaseHas('courses', ['id' => $course->id]);
});
