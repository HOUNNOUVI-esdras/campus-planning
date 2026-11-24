<?php

use App\Enums\Weekday;
use App\Models\Group;
use App\Models\SpecialDay;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

// Semaine de référence : lundi 9, mardi 10, mercredi 11, jeudi 12, vendredi 13, samedi 14 novembre 2026.

test('un cours est enregistré quand aucun jour spécial n\'existe', function () {
    $this->travelTo('2026-11-09 09:00');
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 1);
});

test('un cours est refusé un jour spécial', function () {
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Jour férié']);
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasErrors('day_of_week');
    $this->assertDatabaseCount('courses', 0);
});

test('un cours est enregistré un autre jour que le jour spécial', function () {
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Jour férié']);
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Thursday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 1);
});

test('un cours est enregistré quand le jour spécial est annulé', function () {
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Jour férié', 'status' => 'annule']);
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 1);
});

test('un cours est refusé au milieu d\'un événement sur plusieurs jours', function () {
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Vacances']);
    SpecialDay::create(['date' => '2026-11-12', 'libelle' => 'Vacances']);
    SpecialDay::create(['date' => '2026-11-13', 'libelle' => 'Vacances']);
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Thursday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasErrors('day_of_week');
    $this->assertDatabaseCount('courses', 0);
});

test('un cours est refusé le dernier jour de l\'événement', function () {
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Vacances']);
    SpecialDay::create(['date' => '2026-11-12', 'libelle' => 'Vacances']);
    SpecialDay::create(['date' => '2026-11-13', 'libelle' => 'Vacances']);
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Friday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasErrors('day_of_week');
    $this->assertDatabaseCount('courses', 0);
});

test('un cours est enregistré le lendemain de la fin de l\'événement', function () {
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Vacances']);
    SpecialDay::create(['date' => '2026-11-12', 'libelle' => 'Vacances']);
    SpecialDay::create(['date' => '2026-11-13', 'libelle' => 'Vacances']);
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Saturday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 1);
});

test('aucun email n\'est envoyé quand le cours est refusé', function () {
    Notification::fake();
    $this->travelTo('2026-11-09 09:00');
    SpecialDay::create(['date' => '2026-11-11', 'libelle' => 'Jour férié']);
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($student);

    $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    Notification::assertNothingSent();
});