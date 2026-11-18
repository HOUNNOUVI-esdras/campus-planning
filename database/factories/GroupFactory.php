<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => sprintf(
                '%s · Groupe %d',
                fake()->randomElement(['L1 Informatique', 'L2 Informatique', 'L3 Informatique', 'M1 Génie logiciel']),
                fake()->unique()->numberBetween(1, 9999),
            ),
        ];
    }
}
