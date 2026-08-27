<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\GroupTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupTeam>
 */
class GroupTeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'name' => 'Group '.fake()->unique()->numberBetween(1, 1000),
            'position' => fake()->numberBetween(1, 10),
        ];
    }
}
