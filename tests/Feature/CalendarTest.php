<?php

use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;

test('professors see the courses they teach', function () {
    $professor = User::factory()->professor()->create();
    $course = Course::factory()->for($professor, 'professor')->create([
        'title' => 'Algorithmique avancée',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday,
    ]);

    $this->actingAs($professor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($course->title)
        ->assertSee($course->room);
});

test('professors do not see courses taught by another professor', function () {
    $professor = User::factory()->professor()->create();
    $otherProfessor = User::factory()->professor()->create();

    Course::factory()->for($professor, 'professor')->create([
        'title' => 'Algorithmique avancée',
        'day_of_week' => Weekday::Monday,
    ]);

    Course::factory()->for($otherProfessor, 'professor')->create([
        'title' => 'Réseaux informatiques',
        'day_of_week' => Weekday::Monday,
    ]);

    $this->actingAs($professor)
        ->get(route('dashboard'))
        ->assertSee('Algorithmique avancée')
        ->assertDontSee('Réseaux informatiques');
});

test('students see the courses of their groups', function () {
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $course = Course::factory()->for($group)->create([
        'title' => 'Bases de données',
        'day_of_week' => Weekday::Tuesday,
    ]);

    $group->students()->attach($student);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($course->title)
        ->assertSee($course->professor->name);
});

test('students do not see courses of groups they do not belong to', function () {
    $student = User::factory()->student()->create();

    Course::factory()->create([
        'title' => 'Bases de données',
        'day_of_week' => Weekday::Tuesday,
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertDontSee('Bases de données');
});
