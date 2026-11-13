<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Standings\Handlers\SyncMatchResultsHandler;
use Futsal\EventSourcing\Consumer\SqsEventConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class ConsumeMatchEventsCommand extends Command
{
    protected $signature = 'events:consume-match {--once}';

    protected $description = 'Consome eventos de partida para manter a classificação';

    public function handle(SqsEventConsumer $consumer, SyncMatchResultsHandler $handler): int
    {
        $queueUrl = (string) env('STANDINGS_QUEUE_URL');

        do {
            $count = $consumer->consume($queueUrl, $handler);
            $this->info("Processed {$count} events");

            if ($count > 0) {
                Log::info('Match events consumed', ['count' => $count]);
            }
        } while (! $this->option('once'));

        return self::SUCCESS;
    }
}
