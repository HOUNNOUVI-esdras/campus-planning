<?php

use App\Models\SpecialDay;
use App\Models\User;

test('un professeur crée un jour spécial', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->post(route('special-days.store'), [
        'libelle' => 'Fête nationale',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('special_days', [
        'date' => '2026-08-01',
        'libelle' => 'Fête nationale',
        'status' => 'actif',
    ]);
});

test('un événement sur plusieurs jours crée un jour spécial par date', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor)->post(route('special-days.store'), [
        'libelle' => 'Vacances',
        'date_debut' => '2026-12-24',
        'date_fin' => '2026-12-26',
    ]);

    $this->assertDatabaseCount('special_days', 3);
    $this->assertDatabaseHas('special_days', ['date' => '2026-12-25', 'libelle' => 'Vacances']);
});

test('tous les champs obligatoires doivent être remplis', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->post(route('special-days.store'), []);

    $response->assertSessionHasErrors(['libelle', 'date_debut']);
    $this->assertDatabaseCount('special_days', 0);
});

test('la date de fin ne peut pas précéder la date de début', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->post(route('special-days.store'), [
        'libelle' => 'Vacances',
        'date_debut' => '2026-12-26',
        'date_fin' => '2026-12-24',
    ]);

    $response->assertSessionHasErrors('date_fin');
    $this->assertDatabaseCount('special_days', 0);
});

test('une date ne peut pas être enregistrée deux fois', function () {
    $professor = User::factory()->professor()->create();
    SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Existant']);

    $response = $this->actingAs($professor)->post(route('special-days.store'), [
        'libelle' => 'Doublon',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertSessionHasErrors('date_debut');
    $this->assertDatabaseCount('special_days', 1);
    $this->assertDatabaseMissing('special_days', ['libelle' => 'Doublon']);
});

test('un jour spécial peut être annulé', function () {
    $professor = User::factory()->professor()->create();
    $jour = SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Fête']);

    $this->actingAs($professor)->patch(route('special-days.cancel', $jour));

    $this->assertDatabaseHas('special_days', ['id' => $jour->id, 'status' => 'annule']);
});

test('le libellé d\'un jour spécial peut être modifié', function () {
    $professor = User::factory()->professor()->create();
    $jour = SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Ancien']);

    $this->actingAs($professor)->patch(route('special-days.update', $jour), [
        'libelle' => 'Nouveau',
    ]);

    $this->assertDatabaseHas('special_days', ['id' => $jour->id, 'libelle' => 'Nouveau']);
});
test('un étudiant ne peut pas créer un jour spécial', function () {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->post(route('special-days.store'), [
        'libelle' => 'Fête nationale',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('special_days', 0);
});