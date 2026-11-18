<?php

use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Illuminate\Support\Facades\Notification;

test('un professeur crée un cours pour un groupe', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('courses', [
        'title' => 'Algorithmique',
        'professor_id' => $professor->id,
        'group_id' => $group->id,
    ]);
});

test('le professeur et les étudiants du groupe reçoivent un email', function () {
    Notification::fake();

    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($student);

    $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    Notification::assertSentTo($professor, CourseScheduleNotification::class);
    Notification::assertSentTo($student, CourseScheduleNotification::class);
});

test('un étudiant ne peut pas créer un cours', function () {
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($student)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('courses', 0);
});

test('tous les champs sont obligatoires', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), []);

    $response->assertSessionHasErrors(['title', 'room', 'day_of_week', 'starts_at', 'ends_at', 'group_id']);
    $this->assertDatabaseCount('courses', 0);
});

test('un cours doit finir après avoir commencé', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '12:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasErrors('ends_at');
    $this->assertDatabaseCount('courses', 0);
});

test('un professeur ne peut pas avoir deux cours qui se chevauchent', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();
    Course::factory()->for($professor, 'professor')->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Réseaux',
        'room' => 'Amphi A',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '09:00',
        'ends_at' => '11:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasErrors('starts_at');
    $this->assertDatabaseCount('courses', 1);
});

test('deux cours qui se touchent ne sont pas en conflit', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();
    Course::factory()->for($professor, 'professor')->create([
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Réseaux',
        'room' => 'Amphi A',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '10:00',
        'ends_at' => '12:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 2);
});

test("le cours est enregistré même si l'email échoue", function () {
    Notification::shouldReceive('send')->andThrow(new Exception('SMTP indisponible'));

    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $this->assertDatabaseCount('courses', 1);
});
