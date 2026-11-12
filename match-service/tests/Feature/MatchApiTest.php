<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MatchApiTest extends TestCase
{
    use RefreshDatabase;

    private string $homeId;

    private string $awayId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->homeId = (string) Str::uuid();
        $this->awayId = (string) Str::uuid();

        DB::table('known_teams')->insert([
            ['id' => $this->homeId, 'name' => 'Home', 'active' => true, 'players_count' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $this->awayId, 'name' => 'Away', 'active' => true, 'players_count' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_creates_scheduled_match(): void
    {
        $payload = [
            'home_team_id' => $this->homeId,
            'away_team_id' => $this->awayId,
            'scheduled_at' => '2026-11-12T20:00:00+00:00',
        ];
        $body = json_encode($payload);

        $response = $this->postJson('/matches', $payload, $this->internalHeaders('POST', '/matches', $body));

        $response->assertCreated()->assertJsonPath('status', 'scheduled');

        $matchId = $response->json('id');

        $this->assertDatabaseHas('matches', ['id' => $matchId, 'status' => 'scheduled']);
        $this->assertDatabaseHas('domain_events', [
            'aggregate_type' => 'match',
            'aggregate_id' => $matchId,
            'event_type' => 'match.scheduled',
        ]);
        $this->assertDatabaseHas('outbox_events', ['status' => 'pending']);
    }

    public function test_rejects_unknown_team(): void
    {
        $payload = [
            'home_team_id' => $this->homeId,
            'away_team_id' => (string) Str::uuid(),
            'scheduled_at' => '2026-11-12T20:00:00+00:00',
        ];
        $body = json_encode($payload);

        $this->postJson('/matches', $payload, $this->internalHeaders('POST', '/matches', $body))
            ->assertStatus(422);
    }

    public function test_starts_scores_and_finishes_match(): void
    {
        $body = json_encode([
            'home_team_id' => $this->homeId,
            'away_team_id' => $this->awayId,
            'scheduled_at' => '2026-11-12T20:00:00+00:00',
        ]);

        $matchId = $this->postJson('/matches', json_decode($body, true), $this->internalHeaders('POST', '/matches', $body))
            ->json('id');

        $this->postJson("/matches/{$matchId}/start", [], $this->internalHeaders('POST', "/matches/{$matchId}/start", json_encode([])))
            ->assertOk()
            ->assertJsonPath('status', 'in_progress');

        $playerId = (string) Str::uuid();
        DB::table('known_players')->insert([
            'id' => $playerId,
            'team_id' => $this->homeId,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $goal = ['team_id' => $this->homeId, 'player_id' => $playerId, 'minute' => 7, 'own_goal' => false];
        $this->postJson("/matches/{$matchId}/goals", $goal, $this->internalHeaders('POST', "/matches/{$matchId}/goals", json_encode($goal)))
            ->assertOk()
            ->assertJsonPath('home_score', 1);

        $this->postJson("/matches/{$matchId}/finish", [], $this->internalHeaders('POST', "/matches/{$matchId}/finish", json_encode([])))
            ->assertOk()
            ->assertJsonPath('status', 'finished');

        $this->assertDatabaseHas('match_goals', ['match_id' => $matchId, 'team_id' => $this->homeId, 'minute' => 7]);
        $this->assertDatabaseHas('domain_events', ['aggregate_id' => $matchId, 'event_type' => 'match.finished']);
    }

    public function test_cancels_scheduled_match(): void
    {
        $body = json_encode([
            'home_team_id' => $this->homeId,
            'away_team_id' => $this->awayId,
            'scheduled_at' => '2026-11-12T20:00:00+00:00',
        ]);

        $matchId = $this->postJson('/matches', json_decode($body, true), $this->internalHeaders('POST', '/matches', $body))
            ->json('id');

        $cancel = ['reason' => 'Court unavailable'];
        $this->postJson("/matches/{$matchId}/cancel", $cancel, $this->internalHeaders('POST', "/matches/{$matchId}/cancel", json_encode($cancel)))
            ->assertOk()
            ->assertJsonPath('status', 'cancelled');
    }
}
