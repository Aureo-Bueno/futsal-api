<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Outbox;

use Futsal\Contracts\EventEnvelope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OutboxRecorder
{
    public function record(string $topic, EventEnvelope $event): void
    {
        DB::table('outbox_events')->insert([
            'id' => (string) Str::uuid(),
            'event_id' => $event->eventId,
            'topic' => $topic,
            'payload' => json_encode($event->toArray(), JSON_THROW_ON_ERROR),
            'headers' => json_encode([
                'eventType' => $event->eventType,
                'aggregateType' => $event->aggregateType,
                'aggregateId' => $event->aggregateId,
                'eventVersion' => $event->eventVersion,
            ], JSON_THROW_ON_ERROR),
            'status' => 'pending',
            'attempts' => 0,
            'available_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
