<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Standings\StandingsProjector;
use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class RebuildStandingsCommand extends Command
{
    protected $signature = 'standings:rebuild';

    protected $description = 'Reconstrói a classificação a partir do event store de partidas';

    public function handle(StandingsProjector $projector, RequestSigner $signer): int
    {
        DB::transaction(function (): void {
            DB::table('match_results')->delete();
            DB::table('team_standings')->delete();
        });

        $after = 0;

        do {
            $path = '/internal/events';
            $timestamp = (string) time();
            $signature = $signer->sign('GET', $path, '', $timestamp);

            $response = Http::withHeaders([
                'X-User-Id' => '00000000-0000-0000-0000-000000000000',
                'X-Request-Id' => (string) Str::uuid(),
                'X-Correlation-Id' => (string) Str::uuid(),
                'X-Internal-Timestamp' => $timestamp,
                'X-Signature' => $signature,
            ])->get(rtrim((string) config('services.match.url'), '/').$path, [
                'after' => $after,
                'limit' => 1000,
            ]);

            $response->throw();

            $events = $response->json('events', []);

            foreach ($events as $event) {
                $after = (int) $event['global_position'];

                if ($event['event_type'] !== 'match.finished') {
                    continue;
                }

                $projector->applyMatchResult(
                    (string) $event['payload']['match_id'],
                    (string) $event['payload']['home_team_id'],
                    (string) $event['payload']['away_team_id'],
                    (int) $event['payload']['home_score'],
                    (int) $event['payload']['away_score'],
                );
            }
        } while (count($events) === 1000);

        $this->info('Standings rebuilt');

        return self::SUCCESS;
    }
}
