<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class BffStandingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_enriches_standings_with_team_names(): void
    {
        Passport::actingAs(User::factory()->create());

        Http::fake([
            '*standings-service:8003/standings' => Http::response([
                ['team_id' => 't1', 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3],
            ], 200),
            '*team-service:8001/teams*' => Http::response([
                'data' => [['id' => 't1', 'name' => 'Tigers']],
            ], 200),
        ]);

        $this->getJson('/api/standings')
            ->assertOk()
            ->assertJsonPath('0.team_id', 't1')
            ->assertJsonPath('0.team_name', 'Tigers');
    }

    public function test_legacy_classification_alias_is_read_only(): void
    {
        Passport::actingAs(User::factory()->create());

        Http::fake([
            '*standings-service:8003/standings' => Http::response([], 200),
            '*team-service:8001/teams*' => Http::response(['data' => []], 200),
        ]);

        $this->getJson('/api/teamClassification')->assertOk();
        $this->postJson('/api/teamClassification', [])->assertStatus(405);
        $this->putJson('/api/teamClassification/t1', [])->assertStatus(405);
        $this->deleteJson('/api/teamClassification/t1')->assertStatus(405);
    }
}
