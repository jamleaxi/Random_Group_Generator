<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
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
            'group_team_id' => GroupTeam::factory(),
            'name' => fake()->unique()->name(),
        ];
    }
}
