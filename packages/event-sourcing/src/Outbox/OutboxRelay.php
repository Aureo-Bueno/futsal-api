<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Outbox;

use Futsal\Contracts\EventEnvelope;
use Futsal\EventSourcing\Publisher\EventPublisher;
use Illuminate\Support\Facades\DB;
use Throwable;

final class OutboxRelay
{
    public function __construct(private readonly EventPublisher $publisher) {}

    public function relay(int $limit = 100): int
    {
        $published = 0;

        $rows = DB::table('outbox_events')
            ->where('status', 'pending')
            ->where('available_at', '<=', now())
            ->orderBy('available_at')
            ->limit($limit)
            ->lockForUpdate()
            ->get();

        foreach ($rows as $row) {
            try {
                $envelope = EventEnvelope::fromArray(
                    json_decode((string) $row->payload, true, 512, JSON_THROW_ON_ERROR)
                );

                $this->publisher->publish((string) $row->topic, $envelope);

                DB::table('outbox_events')->where('id', $row->id)->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'updated_at' => now(),
                ]);

                $published++;
            } catch (Throwable $exception) {
                $attempts = (int) $row->attempts + 1;

                DB::table('outbox_events')->where('id', $row->id)->update([
                    'attempts' => $attempts,
                    'status' => $attempts >= 10 ? 'failed' : 'pending',
                    'available_at' => now()->addSeconds(min(60, 2 ** $attempts)),
                    'updated_at' => now(),
                ]);

                report($exception);
            }
        }

        return $published;
    }
}
