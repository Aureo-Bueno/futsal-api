<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Futsal\EventSourcing\Outbox\OutboxRelay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class RelayOutboxCommand extends Command
{
    protected $signature = 'outbox:relay {--daemon} {--limit=100}';

    protected $description = 'Publica eventos pendentes da outbox no SNS';

    public function handle(OutboxRelay $relay): int
    {
        do {
            $count = $relay->relay((int) $this->option('limit'));
            $this->info("Published {$count} events");

            if ($count > 0) {
                Log::info('Outbox events relayed', ['count' => $count]);
            }

            if (! $this->option('daemon')) {
                break;
            }

            sleep(1);
        } while (true);

        return self::SUCCESS;
    }
}
