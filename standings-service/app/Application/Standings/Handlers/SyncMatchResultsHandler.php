<?php

declare(strict_types=1);

namespace App\Application\Standings\Handlers;

use App\Application\Standings\StandingsProjector;
use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\Consumer\DomainEventHandler;
use Illuminate\Support\Facades\DB;

final class SyncMatchResultsHandler implements DomainEventHandler
{
    private const CONSUMER = 'standings-service:match-results';

    public function __construct(private readonly StandingsProjector $projector) {}

    public function handle(EventEnvelope $event): void
    {
        DB::transaction(function () use ($event): void {
            $alreadyProcessed = DB::table('processed_events')
                ->where('consumer', self::CONSUMER)
                ->where('event_id', $event->eventId)
                ->exists();

            if ($alreadyProcessed) {
                return;
            }

            if ($event->eventType === EventType::MATCH_FINISHED) {
                $this->projector->applyMatchResult(
                    (string) $event->payload['match_id'],
                    (string) $event->payload['home_team_id'],
                    (string) $event->payload['away_team_id'],
                    (int) $event->payload['home_score'],
                    (int) $event->payload['away_score'],
                );
            }

            DB::table('processed_events')->insert([
                'consumer' => self::CONSUMER,
                'event_id' => $event->eventId,
                'processed_at' => now(),
            ]);
        });
    }
}
