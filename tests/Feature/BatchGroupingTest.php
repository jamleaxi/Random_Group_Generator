<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_batch_creates_named_and_auto_named_groups(): void
    {
        $response = $this->post(route('batches.store'), [
            'name' => 'Test Batch',
            'group_count' => 3,
            'group_names' => "Red\nBlue",
        ]);

        $batch = Batch::first();
        $response->assertRedirect(route('batches.show', $batch));

        $this->assertSame(['Red', 'Blue', 'Group 3'], $batch->groupTeams()->orderBy('position')->pluck('name')->all());
    }

    public function test_names_are_evenly_distributed_across_groups(): void
    {
        $batch = Batch::factory()->create(['group_count' => 3]);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);
        GroupTeam::factory()->for($batch)->create(['name' => 'C', 'position' => 3]);

        $names = collect(range(1, 10))->map(fn (int $i) => "Person {$i}")->all();

        $this->post(route('batches.participants.store', $batch), [
            'names' => implode("\n", $names),
        ]);

        $counts = $batch->groupTeams()->withCount('participants')->get()->pluck('participants_count');

        $this->assertSame(10, $counts->sum());
        $this->assertSame(4, $counts->max());
        $this->assertSame(3, $counts->min());
    }

    public function test_duplicate_names_are_not_assigned_twice(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        $team = GroupTeam::factory()->for($batch)->create();
        Participant::factory()->for($batch)->for($team)->create(['name' => 'Alice']);

        $response = $this->post(route('batches.participants.store', $batch), [
            'names' => "Alice\nBob",
        ]);

        $response->assertSessionHas('duplicates', ['Alice']);
        $this->assertSame(1, $batch->participants()->where('name', 'Alice')->count());
        $this->assertSame(1, $batch->participants()->where('name', 'Bob')->count());
    }

    public function test_a_batch_can_be_locked_and_unlocked(): void
    {
        $batch = Batch::factory()->create(['locked' => false]);

        $this->patch(route('batches.lock', $batch));
        $this->assertTrue($batch->fresh()->locked);

        $this->patch(route('batches.lock', $batch));
        $this->assertFalse($batch->fresh()->locked);
    }

    public function test_an_unlocked_batch_can_be_deleted(): void
    {
        $batch = Batch::factory()->create(['locked' => false]);
        $team = GroupTeam::factory()->for($batch)->create();
        Participant::factory()->for($batch)->for($team)->create();

        $response = $this->delete(route('batches.destroy', $batch));

        $response->assertRedirect(route('batches.index'));
        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
        $this->assertDatabaseMissing('group_teams', ['id' => $team->id]);
    }

    public function test_a_locked_batch_cannot_be_deleted(): void
    {
        $batch = Batch::factory()->create(['locked' => true]);

        $response = $this->delete(route('batches.destroy', $batch));

        $response->assertRedirect(route('batches.show', $batch));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('batches', ['id' => $batch->id]);
    }
}
