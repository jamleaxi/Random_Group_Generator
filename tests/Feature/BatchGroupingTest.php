<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use App\Models\User;
use App\Support\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class BatchGroupingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

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

    public function test_teams_can_be_exported_as_an_excel_workbook_with_an_all_sheet_and_one_sheet_per_team(): void
    {
        $batch = Batch::factory()->create(['name' => 'Test Batch']);
        $teamA = GroupTeam::factory()->for($batch)->create(['name' => 'Alpha', 'position' => 1]);
        $teamB = GroupTeam::factory()->for($batch)->create(['name' => 'Beta', 'position' => 2]);
        Participant::factory()->for($batch)->for($teamA)->create(['name' => 'Zoe', 'gender' => Gender::FEMALE]);
        Participant::factory()->for($batch)->for($teamA)->create(['name' => 'Amy', 'gender' => Gender::FEMALE]);
        Participant::factory()->for($batch)->for($teamB)->create(['name' => 'Bob', 'gender' => Gender::MALE]);

        $response = $this->get(route('batches.export.teams', $batch));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tempFile, $response->streamedContent());
        $spreadsheet = IOFactory::load($tempFile);
        unlink($tempFile);

        $this->assertSame(['All', 'Alpha', 'Beta'], $spreadsheet->getSheetNames());

        $all = $spreadsheet->getSheetByName('All');
        $this->assertSame('Amy', $all->getCell('A2')->getValue());
        $this->assertSame('Bob', $all->getCell('A3')->getValue());
        $this->assertSame('Zoe', $all->getCell('A4')->getValue());
        $this->assertSame('Alpha', $all->getCell('C2')->getValue());

        $alpha = $spreadsheet->getSheetByName('Alpha');
        $this->assertSame('Amy', $alpha->getCell('A2')->getValue());
        $this->assertSame('Zoe', $alpha->getCell('A3')->getValue());
    }

    public function test_all_participants_can_be_cleared_from_a_batch(): void
    {
        $batch = Batch::factory()->create(['locked' => false]);
        $teamA = GroupTeam::factory()->for($batch)->create();
        $teamB = GroupTeam::factory()->for($batch)->create();
        Participant::factory()->for($batch)->for($teamA)->create();
        Participant::factory()->for($batch)->for($teamB)->create();

        $response = $this->delete(route('batches.participants.clear', $batch));

        $response->assertRedirect(route('batches.show', $batch));
        $this->assertSame(0, $batch->participants()->count());
        $this->assertDatabaseHas('group_teams', ['id' => $teamA->id]);
        $this->assertDatabaseHas('group_teams', ['id' => $teamB->id]);
    }

    public function test_a_locked_batchs_participants_cannot_be_cleared(): void
    {
        $batch = Batch::factory()->create(['locked' => true]);
        $team = GroupTeam::factory()->for($batch)->create();
        Participant::factory()->for($batch)->for($team)->create();

        $response = $this->delete(route('batches.participants.clear', $batch));

        $response->assertRedirect(route('batches.show', $batch));
        $response->assertSessionHas('error');
        $this->assertSame(1, $batch->participants()->count());
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
