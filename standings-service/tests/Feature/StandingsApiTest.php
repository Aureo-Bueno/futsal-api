<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class StandingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_standings_ordered_by_points_and_goal_difference(): void
    {
        $a = (string) Str::uuid();
        $b = (string) Str::uuid();
        $c = (string) Str::uuid();

        DB::table('team_standings')->insert([
            ['team_id' => $a, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 1, 'goal_difference' => 2, 'points' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['team_id' => $b, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['team_id' => $c, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 4, 'goals_against' => 2, 'goal_difference' => 2, 'points' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->getJson('/standings', $this->internalHeaders('GET', '/standings'))
            ->assertOk()
            ->assertJsonPath('0.team_id', $c)
            ->assertJsonPath('1.team_id', $a)
            ->assertJsonPath('2.team_id', $b);
    }
}
