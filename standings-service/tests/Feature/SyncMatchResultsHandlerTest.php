<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Standings\Handlers\SyncMatchResultsHandler;
use DateTimeImmutable;
use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\EventType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SyncMatchResultsHandlerTest extends TestCase
{
    use RefreshDatabase;

    private function finishedEnvelope(string $matchId, string $eventId, string $homeId, string $awayId, int $homeScore = 3, int $awayScore = 1): EventEnvelope
    {
        return new EventEnvelope(
            eventId: $eventId,
            eventType: EventType::MATCH_FINISHED,
            eventVersion: 1,
            aggregateType: 'match',
            aggregateId: $matchId,
            aggregateVersion: 4,
            payload: [
                'match_id' => $matchId,
                'home_team_id' => $homeId,
                'away_team_id' => $awayId,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
            ],
            metadata: [],
            occurredAt: new DateTimeImmutable,
            producer: 'match-service',
        );
    }

    public function test_projects_finished_match(): void
    {
        $matchId = (string) Str::uuid();
        $homeId = (string) Str::uuid();
        $awayId = (string) Str::uuid();

        $this->app->make(SyncMatchResultsHandler::class)
            ->handle($this->finishedEnvelope($matchId, (string) Str::uuid(), $homeId, $awayId));

        $this->assertDatabaseHas('team_standings', ['team_id' => $homeId, 'points' => 3, 'played' => 1]);
        $this->assertDatabaseHas('team_standings', ['team_id' => $awayId, 'points' => 0, 'played' => 1]);
        $this->assertDatabaseHas('match_results', ['match_id' => $matchId]);
    }

    public function test_ignores_duplicate_event_id(): void
    {
        $matchId = (string) Str::uuid();
        $eventId = (string) Str::uuid();
        $homeId = (string) Str::uuid();
        $awayId = (string) Str::uuid();
        $handler = $this->app->make(SyncMatchResultsHandler::class);

        $handler->handle($this->finishedEnvelope($matchId, $eventId, $homeId, $awayId));
        $handler->handle($this->finishedEnvelope($matchId, $eventId, $homeId, $awayId));

        $this->assertSame(1, DB::table('match_results')->count());
        $this->assertSame(1, DB::table('processed_events')->where('event_id', $eventId)->count());

        $home = DB::table('team_standings')->where('team_id', $homeId)->first();
        $this->assertSame(1, (int) $home->played);
    }
}
