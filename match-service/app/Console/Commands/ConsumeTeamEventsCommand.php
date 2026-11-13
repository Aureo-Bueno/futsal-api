<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Team\Handlers\SyncKnownTeamsHandler;
use Futsal\EventSourcing\Consumer\SqsEventConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class ConsumeTeamEventsCommand extends Command
{
    protected $signature = 'events:consume-team {--once}';

    protected $description = 'Consome eventos de time para manter a projeção known_teams';

    public function handle(SqsEventConsumer $consumer, SyncKnownTeamsHandler $handler): int
    {
        $queueUrl = (string) env('TEAM_EVENTS_QUEUE_URL');

        do {
            $count = $consumer->consume($queueUrl, $handler);
            $this->info("Processed {$count} events");

            if ($count > 0) {
                Log::info('Team events consumed', ['count' => $count]);
            }
        } while (! $this->option('once'));

        return self::SUCCESS;
    }
}
