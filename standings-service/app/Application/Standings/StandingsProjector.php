<?php

declare(strict_types=1);

namespace App\Application\Standings;

use App\Application\Standings\Exceptions\StandingsException;
use Illuminate\Support\Facades\DB;

final class StandingsProjector
{
    public function applyMatchResult(
        string $matchId,
        string $homeTeamId,
        string $awayTeamId,
        int $homeScore,
        int $awayScore,
    ): void {
        if ($homeScore < 0 || $awayScore < 0) {
            throw new StandingsException('Score cannot be negative');
        }

        if ($homeTeamId === $awayTeamId) {
            throw new StandingsException('Teams must be different');
        }

        DB::transaction(function () use ($matchId, $homeTeamId, $awayTeamId, $homeScore, $awayScore): void {
            $exists = DB::table('match_results')->where('match_id', $matchId)->exists();

            if ($exists) {
                return;
            }

            DB::table('match_results')->insert([
                'match_id' => $matchId,
                'home_team_id' => $homeTeamId,
                'away_team_id' => $awayTeamId,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'processed_at' => now(),
            ]);

            $this->ensureTeam($homeTeamId);
            $this->ensureTeam($awayTeamId);

            $homeResult = $homeScore <=> $awayScore;
            $awayResult = $awayScore <=> $homeScore;

            $this->applyTeamResult($homeTeamId, $homeScore, $awayScore, $homeResult);
            $this->applyTeamResult($awayTeamId, $awayScore, $homeScore, $awayResult);
        });
    }

    private function ensureTeam(string $teamId): void
    {
        $exists = DB::table('team_standings')->where('team_id', $teamId)->exists();

        if (! $exists) {
            DB::table('team_standings')->insert([
                'team_id' => $teamId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function applyTeamResult(string $teamId, int $goalsFor, int $goalsAgainst, int $result): void
    {
        $row = DB::table('team_standings')->where('team_id', $teamId)->first();

        $won = $result === 1 ? 1 : 0;
        $drawn = $result === 0 ? 1 : 0;
        $lost = $result === -1 ? 1 : 0;

        DB::table('team_standings')->where('team_id', $teamId)->update([
            'played' => (int) $row->played + 1,
            'won' => (int) $row->won + $won,
            'drawn' => (int) $row->drawn + $drawn,
            'lost' => (int) $row->lost + $lost,
            'goals_for' => (int) $row->goals_for + $goalsFor,
            'goals_against' => (int) $row->goals_against + $goalsAgainst,
            'goal_difference' => (int) $row->goals_for + $goalsFor - ((int) $row->goals_against + $goalsAgainst),
            'points' => (int) $row->points + ($won * 3) + $drawn,
            'updated_at' => now(),
        ]);
    }
}
