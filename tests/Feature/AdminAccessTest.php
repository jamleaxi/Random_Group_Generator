<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every route guarded by the "auth" middleware must reject a guest with
     * a redirect, never render the page or perform the action.
     */
    public function test_guests_are_redirected_away_from_every_admin_route(): void
    {
        $batch = Batch::factory()->create();
        $team = GroupTeam::factory()->for($batch)->create();
        $participant = Participant::factory()->for($batch)->for($team)->create();

        $bindings = [
            '{batch}' => (string) $batch->id,
            '{groupTeam}' => (string) $team->id,
            '{participant}' => (string) $participant->id,
        ];

        $adminRoutes = collect(Route::getRoutes())
            ->filter(fn (RoutingRoute $route) => in_array('auth', $route->gatherMiddleware(), true));

        $this->assertNotEmpty($adminRoutes, 'Expected at least one route guarded by the auth middleware.');

        foreach ($adminRoutes as $route) {
            $uri = strtr('/'.ltrim($route->uri(), '/'), $bindings);
            $method = collect($route->methods())->first(fn (string $m) => $m !== 'HEAD');

            $response = $this->call($method, $uri);

            $response->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }
}
