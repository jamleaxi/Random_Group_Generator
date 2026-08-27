<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\User;
use App\Support\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJoinTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication(): void
    {
        $response = $this->get(route('batches.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_open_a_batch_for_public_submissions(): void
    {
        $this->actingAs(User::factory()->create());
        $batch = Batch::factory()->create(['locked' => false, 'public_token' => null]);

        $this->post(route('batches.link.open', $batch));

        $this->assertNotNull($batch->fresh()->public_token);
    }

    public function test_a_user_can_join_an_open_batch_and_gets_assigned_to_a_team(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2, 'public_token' => 'test-token']);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);

        $response = $this->post(route('join.store', $batch), [
            'last_name' => 'doe',
            'first_name' => 'jane',
            'middle_initial' => 'q',
            'gender' => Gender::FEMALE,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('participants', [
            'batch_id' => $batch->id,
            'name' => 'Jane Q. Doe',
            'gender' => Gender::FEMALE,
        ]);
    }

    public function test_joining_a_closed_batch_is_not_found(): void
    {
        $batch = Batch::factory()->create(['public_token' => null]);

        $response = $this->get(route('join.show', ['batch' => 999999]));

        $response->assertNotFound();
    }
}
