<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GroupTeam;
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

    public function test_participants_can_be_imported_from_a_csv_with_name_and_gender_columns(): void
    {
        $batch = Batch::factory()->create(['group_count' => 2]);
        GroupTeam::factory()->for($batch)->create(['name' => 'A', 'position' => 1]);
        GroupTeam::factory()->for($batch)->create(['name' => 'B', 'position' => 2]);

        $csv = "name,gender\nmaria clara,Female\njose rizal,Male\nalex morgan,LGBTQ+\nsam lee,\n";
        $file = UploadedFile::fake()->createWithContent('participants.csv', $csv);

        $response = $this->post(route('batches.participants.import', $batch), ['csv' => $file]);

        $response->assertRedirect(route('batches.show', $batch));
        $this->assertSame(4, $batch->participants()->count());
        $this->assertDatabaseHas('participants', ['name' => 'Maria Clara', 'gender' => Gender::FEMALE]);
        $this->assertDatabaseHas('participants', ['name' => 'Jose Rizal', 'gender' => Gender::MALE]);
        $this->assertDatabaseHas('participants', ['name' => 'Alex Morgan', 'gender' => Gender::LGBTQ]);
        $this->assertDatabaseHas('participants', ['name' => 'Sam Lee', 'gender' => Gender::UNSPECIFIED]);
    }
}
