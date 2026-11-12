<?php

declare(strict_types=1);

namespace App\Application\Match\Projectors;

use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\EventType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class MatchProjector
{
    /** @param EventEnvelope[] $events */
    public function project(array $events): void
    {
        foreach ($events as $event) {
            $this->projectOne($event);
        }
    }

    private function projectOne(EventEnvelope $event): void
    {
        $matchId = $event->payload['match_id'] ?? null;

        switch ($event->eventType) {
            case EventType::MATCH_SCHEDULED:
                DB::table('matches')->insert([
                    'id' => $matchId,
                    'home_team_id' => $event->payload['home_team_id'],
                    'away_team_id' => $event->payload['away_team_id'],
                    'status' => 'scheduled',
                    'home_score' => 0,
                    'away_score' => 0,
                    'scheduled_at' => $event->payload['scheduled_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case EventType::MATCH_STARTED:
                DB::table('matches')->where('id', $matchId)->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case EventType::MATCH_GOAL_SCORED:
                $match = DB::table('matches')->where('id', $matchId)->first();

                $column = $event->payload['scoring_team_id'] === $match->home_team_id
                    ? 'home_score'
                    : 'away_score';

                DB::table('matches')->where('id', $matchId)->update([
                    $column => ((int) $match->{$column}) + 1,
                    'updated_at' => now(),
                ]);

                DB::table('match_goals')->insert([
                    'id' => (string) Str::uuid(),
                    'match_id' => $matchId,
                    'team_id' => $event->payload['scoring_team_id'],
                    'player_id' => $event->payload['player_id'],
                    'minute' => (int) $event->payload['minute'],
                    'own_goal' => (bool) $event->payload['own_goal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case EventType::MATCH_FINISHED:
                DB::table('matches')->where('id', $matchId)->update([
                    'status' => 'finished',
                    'finished_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case EventType::MATCH_CANCELLED:
                DB::table('matches')->where('id', $matchId)->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $event->payload['reason'],
                    'updated_at' => now(),
                ]);
                break;
        }
    }
}
