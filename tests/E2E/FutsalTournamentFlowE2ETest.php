<?php

namespace Tests\E2E;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FutsalTournamentFlowE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Passport::actingAs(User::factory()->create());
    }

    public function test_complete_futsal_tournament_e2e_lifecycle(): void
    {
        // 1. Create a Team Match
        $matchResponse = $this->postJson('/api/teamMatch', [
            'date_team_match' => '2026-11-10',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'scoreboard' => 0,
        ]);
        $matchResponse->assertStatus(201)->assertJsonPath('status', 201);
        $matchId = $matchResponse->json('data.id');
        $this->assertNotEmpty($matchId);

        // 2. Create two Teams
        $teamAResponse = $this->postJson('/api/team', ['name' => 'Tigers Futsal']);
        $teamAResponse->assertStatus(201);
        $teamAId = $teamAResponse->json('data.id');

        $teamBResponse = $this->postJson('/api/team', ['name' => 'Eagles Futsal']);
        $teamBResponse->assertStatus(201);
        $teamBId = $teamBResponse->json('data.id');

        // 3. Assign Teams to Match
        $this->putJson("/api/team/{$teamAId}", [
            'name' => 'Tigers Futsal Pro',
            'team_match_id' => $matchId,
        ])->assertStatus(200)
            ->assertJsonPath('data.name', 'Tigers Futsal Pro')
            ->assertJsonPath('data.team_match_id', $matchId);

        // 4. Register Players for Team A
        $playerResponse = $this->postJson('/api/player', [
            'name' => 'Falcao',
            'jersey_number' => 12,
            'team_id' => $teamAId,
        ]);
        $playerResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'Falcao')
            ->assertJsonPath('data.jersey_number', 12);
        $playerId = $playerResponse->json('data.id');

        // Update Player
        $this->putJson("/api/player/{$playerId}", [
            'name' => 'Falcao Legend',
            'jersey_number' => 10,
            'team_id' => $teamAId,
        ])->assertStatus(200)
            ->assertJsonPath('data.name', 'Falcao Legend')
            ->assertJsonPath('data.jersey_number', 10);

        // 5. Update Match Scoreboard
        $this->putJson("/api/teamMatch/{$matchId}", [
            'date_team_match' => '2026-11-10',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'scoreboard' => 5,
        ])->assertStatus(200)
            ->assertJsonPath('data.scoreboard', 5);

        // 6. Create Classifications and verify standings ordering by points DESC
        $classB = $this->postJson('/api/teamClassification', [
            'team_id' => $teamBId,
            'points' => 12,
            'number_of_goals' => 18,
        ])->assertStatus(201)->json('data.id');

        $classA = $this->postJson('/api/teamClassification', [
            'team_id' => $teamAId,
            'points' => 25,
            'number_of_goals' => 34,
        ])->assertStatus(201)->json('data.id');

        $standings = $this->getJson('/api/teamClassification?per_page=10')
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(2, $standings);
        $this->assertSame($classA, $standings[0]['id']);
        $this->assertSame($classB, $standings[1]['id']);

        // Update Classification
        $this->putJson("/api/teamClassification/{$classB}", [
            'team_id' => $teamBId,
            'points' => 30,
            'number_of_goals' => 40,
        ])->assertStatus(200)
            ->assertJsonPath('data.points', 30);

        // 7. Show individual resources (with eager-loaded relations) and list endpoints
        $this->getJson("/api/team/{$teamAId}")
            ->assertStatus(200)
            ->assertJsonPath('data.players.0.id', $playerId)
            ->assertJsonPath('data.team_match.id', $matchId);
        $this->getJson('/api/team?per_page=5')->assertStatus(200);
        $this->getJson("/api/player/{$playerId}")
            ->assertStatus(200)
            ->assertJsonPath('data.team.id', $teamAId);
        $this->getJson('/api/player?per_page=5')->assertStatus(200);
        $this->getJson("/api/teamMatch/{$matchId}")
            ->assertStatus(200)
            ->assertJsonPath('data.teams.0.id', $teamAId);
        $this->getJson('/api/teamMatch?per_page=5')->assertStatus(200);
        $this->getJson("/api/teamClassification/{$classA}")
            ->assertStatus(200)
            ->assertJsonPath('data.team.id', $teamAId);

        // 8. Delete resources and confirm 404 afterwards
        $this->deleteJson("/api/player/{$playerId}")->assertStatus(200);
        $this->getJson("/api/player/{$playerId}")->assertStatus(404);

        $this->deleteJson("/api/teamClassification/{$classA}")->assertStatus(200);
        $this->getJson("/api/teamClassification/{$classA}")->assertStatus(404);

        $this->deleteJson("/api/team/{$teamAId}")->assertStatus(200);
        $this->getJson("/api/team/{$teamAId}")->assertStatus(404);

        $this->deleteJson("/api/teamMatch/{$matchId}")->assertStatus(200);
        $this->getJson("/api/teamMatch/{$matchId}")->assertStatus(404);
    }

    public function test_validation_and_not_found_responses_across_endpoints(): void
    {
        $missingUuid = (string) Str::uuid();

        // 422 Validation errors
        $this->postJson('/api/team', [])->assertStatus(422);
        $this->postJson('/api/player', [])->assertStatus(422);
        $this->postJson('/api/teamMatch', [])->assertStatus(422);
        $this->postJson('/api/teamClassification', [])->assertStatus(422);

        // 404 Not Found on show and delete
        $this->getJson("/api/team/{$missingUuid}")->assertStatus(404);
        $this->deleteJson("/api/team/{$missingUuid}")->assertStatus(404);

        $this->getJson("/api/player/{$missingUuid}")->assertStatus(404);
        $this->deleteJson("/api/player/{$missingUuid}")->assertStatus(404);

        $this->getJson("/api/teamMatch/{$missingUuid}")->assertStatus(404);
        $this->putJson("/api/teamMatch/{$missingUuid}", [
            'date_team_match' => '2026-11-10',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'scoreboard' => 1,
        ])->assertStatus(404);
        $this->deleteJson("/api/teamMatch/{$missingUuid}")->assertStatus(404);

        $this->getJson("/api/teamClassification/{$missingUuid}")->assertStatus(404);
        $this->deleteJson("/api/teamClassification/{$missingUuid}")->assertStatus(404);

        // Invalid UUID route parameters return 404 (blocked at router level before hitting PostgreSQL)
        $this->getJson('/api/team/not-a-valid-uuid')->assertStatus(404);
        $this->getJson('/api/player/not-a-valid-uuid')->assertStatus(404);
        $this->getJson('/api/teamMatch/not-a-valid-uuid')->assertStatus(404);
        $this->getJson('/api/teamClassification/not-a-valid-uuid')->assertStatus(404);

        // Negative numeric bounds are rejected with 422
        $teamId = $this->postJson('/api/team', ['name' => 'Bounds Team'])->json('data.id');
        $this->postJson('/api/player', [
            'name' => 'Negative Jersey',
            'jersey_number' => -5,
            'team_id' => $teamId,
        ])->assertStatus(422)->assertJsonValidationErrors(['jersey_number']);

        $this->postJson('/api/teamClassification', [
            'team_id' => $teamId,
            'points' => -1,
            'number_of_goals' => -3,
        ])->assertStatus(422)->assertJsonValidationErrors(['points', 'number_of_goals']);
    }
}
