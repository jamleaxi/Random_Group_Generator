<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use App\Models\User;
use App\Support\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_importing_a_csv_loads_entries_for_review_without_assigning_them(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);

        $csv = "name,gender\nmaria clara,Female\njose rizal,Male\nalex morgan,LGBTQ+\nsam lee,\n";
        $file = UploadedFile::fake()->createWithContent('participants.csv', $csv);

        $response = $this->post(route('batches.participants.import', $batch), ['csv' => $file]);

        $response->assertRedirect(route('batches.show', $batch));
        $response->assertSessionHas('importedEntries', [
            ['name' => 'Maria Clara', 'gender' => Gender::FEMALE],
            ['name' => 'Jose Rizal', 'gender' => Gender::MALE],
            ['name' => 'Alex Morgan', 'gender' => Gender::LGBTQ],
            ['name' => 'Sam Lee', 'gender' => Gender::UNSPECIFIED],
        ]);
        $this->assertSame(0, $batch->participants()->count());
    }

    public function test_windows_1252_encoded_csv_files_preserve_accented_characters(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);

        $csv = mb_convert_encoding(
            "name,gender\n\"Cañeda, Jopy D.\",Male\n\"Acuña, Kerubina R.\",Female\n",
            'Windows-1252',
            'UTF-8'
        );
        $file = UploadedFile::fake()->createWithContent('participants.csv', $csv);

        $response = $this->post(route('batches.participants.import', $batch), ['csv' => $file]);

        $response->assertSessionHas('importedEntries', [
            ['name' => 'Cañeda, Jopy D.', 'gender' => Gender::MALE],
            ['name' => 'Acuña, Kerubina R.', 'gender' => Gender::FEMALE],
        ]);
    }

    public function test_importing_a_csv_flags_names_duplicated_within_the_file(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);

        $csv = "name,gender\nmaria clara,Female\nMARIA CLARA,Female\njose rizal,Male\n";
        $file = UploadedFile::fake()->createWithContent('participants.csv', $csv);

        $response = $this->post(route('batches.participants.import', $batch), ['csv' => $file]);

        $response->assertSessionHas('importedDuplicateNameKeys', ['maria clara']);
        $response->assertSessionHas('importDuplicates', ['Maria Clara']);
    }

    public function test_importing_a_csv_flags_names_that_already_exist_in_the_batch(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        $team = GroupTeam::factory()->for($batch)->create();
        GroupTeam::factory()->for($batch)->create();
        Participant::factory()->for($batch)->for($team)->create(['name' => 'Maria Clara']);

        $csv = "name,gender\nmaria clara,Female\njose rizal,Male\n";
        $file = UploadedFile::fake()->createWithContent('participants.csv', $csv);

        $response = $this->post(route('batches.participants.import', $batch), ['csv' => $file]);

        $response->assertSessionHas('importedDuplicateNameKeys', ['maria clara']);
    }

    public function test_imported_entries_are_only_assigned_once_the_manual_form_is_submitted(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);

        $csv = "name,gender\nmaria clara,Female\njose rizal,Male\n";
        $file = UploadedFile::fake()->createWithContent('participants.csv', $csv);

        $this->post(route('batches.participants.import', $batch), ['csv' => $file]);

        $response = $this->post(route('batches.participants.store', $batch), [
            'entries' => [
                ['name' => 'Maria Clara', 'gender' => Gender::FEMALE],
                ['name' => 'Jose Rizal', 'gender' => Gender::MALE],
            ],
        ]);

        $response->assertRedirect(route('batches.show', $batch));
        $this->assertSame(2, $batch->participants()->count());
        $this->assertDatabaseHas('participants', ['name' => 'Maria Clara', 'gender' => Gender::FEMALE]);
        $this->assertDatabaseHas('participants', ['name' => 'Jose Rizal', 'gender' => Gender::MALE]);
    }
}
