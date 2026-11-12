<?php

declare(strict_types=1);

namespace App\Application\Team\Projectors;

use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\EventType;
use Illuminate\Support\Facades\DB;

final class TeamProjector
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
        switch ($event->eventType) {
            case EventType::TEAM_CREATED:
                DB::table('teams')->insert([
                    'id' => $event->payload['team_id'],
                    'name' => $event->payload['name'],
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case EventType::TEAM_UPDATED:
                DB::table('teams')->where('id', $event->payload['team_id'])->update([
                    'name' => $event->payload['name'],
                    'updated_at' => now(),
                ]);
                break;
            case EventType::TEAM_DEACTIVATED:
                DB::table('teams')->where('id', $event->payload['team_id'])->update([
                    'active' => false,
                    'updated_at' => now(),
                ]);
                break;
            case EventType::PLAYER_REGISTERED:
                DB::table('players')->insert([
                    'id' => $event->payload['player_id'],
                    'team_id' => $event->payload['team_id'],
                    'name' => $event->payload['name'],
                    'jersey_number' => (int) $event->payload['jersey_number'],
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case EventType::PLAYER_TRANSFERRED:
                DB::table('players')->where('id', $event->payload['player_id'])->update([
                    'team_id' => $event->payload['to_team_id'],
                    'updated_at' => now(),
                ]);
                break;
            case EventType::PLAYER_REMOVED:
                DB::table('players')->where('id', $event->payload['player_id'])->update([
                    'active' => false,
                    'updated_at' => now(),
                ]);
                break;
        }
    }
}
