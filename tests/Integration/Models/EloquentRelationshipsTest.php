<?php

namespace Tests\Integration\Models;

use App\Models\Players;
use App\Models\Team;
use App\Models\TeamClassification;
use App\Models\TeamMatch;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_match_and_team_relationships(): void
    {
        $match = TeamMatch::factory()->create();
        $team1 = Team::factory()->create(['team_match_id' => $match->id]);
        $team2 = Team::factory()->create(['team_match_id' => $match->id]);

        $this->assertCount(2, $match->teams);
        $this->assertTrue($match->teams->contains($team1));
        $this->assertTrue($match->teams->contains($team2));
        $this->assertSame($match->id, $team1->team_match->id);
    }

    public function test_team_and_players_relationships(): void
    {
        $team = Team::factory()->create();
        $player1 = Players::factory()->create(['team_id' => $team->id]);
        $player2 = Players::factory()->create(['team_id' => $team->id]);

        $this->assertCount(2, $team->players);
        $this->assertSame($team->id, $player1->team->id);
        $this->assertSame($team->id, $player2->team->id);
    }

    public function test_team_classification_belongs_to_team(): void
    {
        $team = Team::factory()->create(['name' => 'Joinville Futsal']);
        $classification = TeamClassification::factory()->create([
            'team_id' => $team->id,
            'points' => 28,
            'number_of_goals' => 35,
        ]);

        $this->assertNotNull($classification->team);
        $this->assertSame($team->id, $classification->team->id);
        $this->assertSame('Joinville Futsal', $classification->team->name);
    }

    public function test_user_last_login_accessor_and_mutator(): void
    {
        $user = User::factory()->create();
        $this->assertNull($user->lastLogin);

        $now = now()->startOfSecond();
        $user->lastLogin = $now;
        $user->save();

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->lastLogin);
        $this->assertSame($now->timestamp, $fresh->lastLogin->timestamp);
    }

    public function test_database_seeder_populates_all_tables(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@admin.com']);
        $this->assertGreaterThan(0, Team::count());
        $this->assertGreaterThan(0, Players::count());
        $this->assertGreaterThan(0, TeamMatch::count());
        $this->assertGreaterThan(0, TeamClassification::count());
    }
}
