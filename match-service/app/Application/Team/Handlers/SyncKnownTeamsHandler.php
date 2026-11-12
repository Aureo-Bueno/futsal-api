<?php

declare(strict_types=1);

namespace App\Application\Team\Handlers;

use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\Consumer\DomainEventHandler;
use Illuminate\Support\Facades\DB;

final class SyncKnownTeamsHandler implements DomainEventHandler
{
    private const CONSUMER = 'match-service:known-teams';

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

            $teamId = $event->payload['team_id'] ?? null;

            switch ($event->eventType) {
                case EventType::TEAM_CREATED:
                    DB::table('known_teams')->insert([
                        'id' => $teamId,
                        'name' => $event->payload['name'],
                        'active' => true,
                        'players_count' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
                case EventType::TEAM_UPDATED:
                    DB::table('known_teams')->where('id', $teamId)->update([
                        'name' => $event->payload['name'],
                        'updated_at' => now(),
                    ]);
                    break;
                case EventType::TEAM_DEACTIVATED:
                    DB::table('known_teams')->where('id', $teamId)->update([
                        'active' => false,
                        'updated_at' => now(),
                    ]);
                    break;
                case EventType::PLAYER_REGISTERED:
                    DB::table('known_teams')->where('id', $teamId)->increment('players_count');

                    DB::table('known_players')->insert([
                        'id' => $event->payload['player_id'],
                        'team_id' => $teamId,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
                case EventType::PLAYER_REMOVED:
                    DB::table('known_teams')
                        ->where('id', $teamId)
                        ->where('players_count', '>', 0)
                        ->decrement('players_count');

                    DB::table('known_players')->where('id', $event->payload['player_id'])->update([
                        'active' => false,
                        'updated_at' => now(),
                    ]);
                    break;
                case EventType::PLAYER_TRANSFERRED:
                    DB::table('known_teams')
                        ->where('id', $event->payload['from_team_id'])
                        ->where('players_count', '>', 0)
                        ->decrement('players_count');

                    DB::table('known_teams')
                        ->where('id', $event->payload['to_team_id'])
                        ->increment('players_count');

                    DB::table('known_players')->where('id', $event->payload['player_id'])->update([
                        'team_id' => $event->payload['to_team_id'],
                        'updated_at' => now(),
                    ]);
                    break;
                default:
                    return;
            }

            DB::table('processed_events')->insert([
                'consumer' => self::CONSUMER,
                'event_id' => $event->eventId,
                'processed_at' => now(),
            ]);
        });
    }
}
