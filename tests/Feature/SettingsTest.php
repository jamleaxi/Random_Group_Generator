<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_institution_name_can_be_set(): void
    {
        $response = $this->post(route('settings.update'), [
            'institution_name' => 'Acme Corporation',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertSame('Acme Corporation', Setting::current()->institution_name);
    }

    public function test_a_logo_can_be_uploaded_and_replaces_the_previous_one(): void
    {
        Storage::fake('public');

        $this->post(route('settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $firstPath = Setting::current()->logo_path;
        Storage::disk('public')->assertExists($firstPath);

        $this->post(route('settings.update'), [
            'logo' => UploadedFile::fake()->image('logo2.png'),
        ]);

        $secondPath = Setting::current()->logo_path;
        Storage::disk('public')->assertExists($secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        $this->assertNotSame($firstPath, $secondPath);
    }

    public function test_a_logo_can_be_removed(): void
    {
        Storage::fake('public');

        $this->post(route('settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $path = Setting::current()->logo_path;

        $this->post(route('settings.update'), [
            'remove_logo' => '1',
        ]);

        $this->assertNull(Setting::current()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }
}
