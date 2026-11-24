<?php

use App\Models\SpecialDay;
use App\Models\User;

test('un admin déclare un jour spécial', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('special-days.store'), [
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
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('special-days.store'), [
        'libelle' => 'Vacances',
        'date_debut' => '2026-12-24',
        'date_fin' => '2026-12-26',
    ]);

    $this->assertDatabaseCount('special_days', 3);
    $this->assertDatabaseHas('special_days', ['date' => '2026-12-25', 'libelle' => 'Vacances']);
});

test('tous les champs obligatoires doivent être remplis', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('special-days.store'), []);

    $response->assertSessionHasErrors(['libelle', 'date_debut']);
    $this->assertDatabaseCount('special_days', 0);
});

test('la date de fin ne peut pas précéder la date de début', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('special-days.store'), [
        'libelle' => 'Vacances',
        'date_debut' => '2026-12-26',
        'date_fin' => '2026-12-24',
    ]);

    $response->assertSessionHasErrors('date_fin');
    $this->assertDatabaseCount('special_days', 0);
});

test('une date ne peut pas être enregistrée deux fois', function () {
    $admin = User::factory()->admin()->create();
    SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Existant']);

    $response = $this->actingAs($admin)->post(route('special-days.store'), [
        'libelle' => 'Doublon',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertSessionHasErrors('date_debut');
    $this->assertDatabaseCount('special_days', 1);
    $this->assertDatabaseMissing('special_days', ['libelle' => 'Doublon']);
});

test('un admin peut annuler un jour spécial', function () {
    $admin = User::factory()->admin()->create();
    $jour = SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Fête']);

    $this->actingAs($admin)->patch(route('special-days.cancel', $jour));

    $this->assertDatabaseHas('special_days', ['id' => $jour->id, 'status' => 'annule']);
});

test('un admin peut modifier le libellé d\'un jour spécial', function () {
    $admin = User::factory()->admin()->create();
    $jour = SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Ancien']);

    $this->actingAs($admin)->patch(route('special-days.update', $jour), [
        'libelle' => 'Nouveau',
    ]);

    $this->assertDatabaseHas('special_days', ['id' => $jour->id, 'libelle' => 'Nouveau']);
});

test('un professeur ne peut pas déclarer un jour spécial', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->post(route('special-days.store'), [
        'libelle' => 'Fête nationale',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('special_days', 0);
});

test('un étudiant ne peut pas déclarer un jour spécial', function () {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->post(route('special-days.store'), [
        'libelle' => 'Fête nationale',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('special_days', 0);
});