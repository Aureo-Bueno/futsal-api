<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Standings\Exceptions\StandingsException;
use App\Application\Standings\StandingsProjector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class StandingsProjectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_win_draw_and_loss_points(): void
    {
        $projector = $this->app->make(StandingsProjector::class);
        $homeId = (string) Str::uuid();
        $awayId = (string) Str::uuid();
        $otherId = (string) Str::uuid();

        $projector->applyMatchResult((string) Str::uuid(), $homeId, $awayId, 3, 1);
        $projector->applyMatchResult((string) Str::uuid(), $homeId, $otherId, 2, 2);

        $home = DB::table('team_standings')->where('team_id', $homeId)->first();
        $away = DB::table('team_standings')->where('team_id', $awayId)->first();
        $other = DB::table('team_standings')->where('team_id', $otherId)->first();

        $this->assertSame(4, (int) $home->points);
        $this->assertSame(2, (int) $home->played);
        $this->assertSame(1, (int) $home->won);
        $this->assertSame(1, (int) $home->drawn);
        $this->assertSame(5, (int) $home->goals_for);
        $this->assertSame(3, (int) $home->goals_against);
        $this->assertSame(2, (int) $home->goal_difference);

        $this->assertSame(0, (int) $away->points);
        $this->assertSame(1, (int) $away->lost);
        $this->assertSame(-2, (int) $away->goal_difference);

        $this->assertSame(1, (int) $other->points);
        $this->assertSame(1, (int) $other->drawn);
    }

    public function test_is_idempotent_by_match_id(): void
    {
        $projector = $this->app->make(StandingsProjector::class);
        $matchId = (string) Str::uuid();
        $homeId = (string) Str::uuid();
        $awayId = (string) Str::uuid();

        $projector->applyMatchResult($matchId, $homeId, $awayId, 1, 0);
        $projector->applyMatchResult($matchId, $homeId, $awayId, 1, 0);

        $home = DB::table('team_standings')->where('team_id', $homeId)->first();

        $this->assertSame(1, (int) $home->played);
        $this->assertSame(3, (int) $home->points);
        $this->assertSame(1, DB::table('match_results')->count());
    }

    public function test_rejects_negative_score(): void
    {
        $this->expectException(StandingsException::class);

        $this->app->make(StandingsProjector::class)
            ->applyMatchResult((string) Str::uuid(), (string) Str::uuid(), (string) Str::uuid(), -1, 0);
    }
}
