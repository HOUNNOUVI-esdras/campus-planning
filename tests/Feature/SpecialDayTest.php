<?php

namespace Tests\Feature;

use App\Models\SpecialDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecialDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_d_un_jour_special(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/special-days', [
            'libelle'    => 'Fête nationale',
            'date_debut' => '2026-08-01',
        ]);

        $this->assertDatabaseHas('special_days', [
            'date'    => '2026-08-01',
            'libelle' => 'Fête nationale',
            'status'  => 'actif',
        ]);
    }

    public function test_evenement_sur_plusieurs_jours(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/special-days', [
            'libelle'    => 'Vacances',
            'date_debut' => '2026-12-24',
            'date_fin'   => '2026-12-26',
        ]);

        $this->assertDatabaseCount('special_days', 3);
    }

    public function test_une_date_ne_peut_pas_etre_dupliquee(): void
{
    $user = User::factory()->create();
    SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Existant']);

    $response = $this->actingAs($user)->post('/special-days', [
        'libelle'    => 'Doublon',
        'date_debut' => '2026-08-01',
    ]);

    $response->assertSessionHasErrors('date_debut');
    $this->assertDatabaseMissing('special_days', ['libelle' => 'Doublon']);
}

    public function test_annulation_d_un_jour_special(): void
    {
        $user = User::factory()->create();
        $jour = SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Fête']);

        $this->actingAs($user)->patch("/special-days/{$jour->id}/cancel");

        $this->assertDatabaseHas('special_days', ['id' => $jour->id, 'status' => 'annule']);
    }

   public function test_modification_du_libelle(): void
{
    $user = User::factory()->create();
    $jour = SpecialDay::create(['date' => '2026-08-01', 'libelle' => 'Ancien']);

    $this->actingAs($user)->patch("/special-days/{$jour->id}", ['libelle' => 'Nouveau']);

    $this->assertDatabaseHas('special_days', ['id' => $jour->id, 'libelle' => 'Nouveau']);
}
}