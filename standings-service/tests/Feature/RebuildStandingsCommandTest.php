<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RebuildStandingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_rebuilds_from_match_service_event_export(): void
    {
        $stale = (string) Str::uuid();
        $home = (string) Str::uuid();
        $away = (string) Str::uuid();
        $matchId = (string) Str::uuid();

        DB::table('team_standings')->insert([
            'team_id' => $stale,
            'points' => 99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            '*match-service:8002/internal/events*' => Http::response([
                'events' => [
                    [
                        'global_position' => 1,
                        'event_id' => 'e1',
                        'event_type' => 'match.finished',
                        'event_version' => 1,
                        'aggregate_type' => 'match',
                        'aggregate_id' => $matchId,
                        'aggregate_version' => 4,
                        'payload' => [
                            'match_id' => $matchId,
                            'home_team_id' => $home,
                            'away_team_id' => $away,
                            'home_score' => 2,
                            'away_score' => 0,
                        ],
                        'metadata' => [],
                        'occurred_at' => '2026-11-11T10:00:00+00:00',
                        'producer' => 'match-service',
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('standings:rebuild')->assertExitCode(0);

        $this->assertDatabaseMissing('team_standings', ['team_id' => $stale]);
        $this->assertDatabaseHas('team_standings', ['team_id' => $home, 'points' => 3]);
        $this->assertDatabaseHas('team_standings', ['team_id' => $away, 'points' => 0]);
    }
}
