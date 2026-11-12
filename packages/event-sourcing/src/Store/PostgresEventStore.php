<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Store;

use DateTimeImmutable;
use Futsal\Contracts\EventEnvelope;
use Futsal\EventSourcing\Exceptions\ConcurrencyException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PostgresEventStore implements EventStore
{
    public function load(string $aggregateType, string $aggregateId): array
    {
        return DB::table('domain_events')
            ->where('aggregate_type', $aggregateType)
            ->where('aggregate_id', $aggregateId)
            ->orderBy('aggregate_version')
            ->get()
            ->map(fn ($row): EventEnvelope => new EventEnvelope(
                eventId: (string) $row->id,
                eventType: (string) $row->event_type,
                eventVersion: (int) $row->event_version,
                aggregateType: (string) $row->aggregate_type,
                aggregateId: (string) $row->aggregate_id,
                aggregateVersion: (int) $row->aggregate_version,
                payload: json_decode((string) $row->payload, true, 512, JSON_THROW_ON_ERROR),
                metadata: json_decode((string) $row->metadata, true, 512, JSON_THROW_ON_ERROR),
                occurredAt: new DateTimeImmutable((string) $row->occurred_at),
                producer: (string) $row->producer,
            ))
            ->all();
    }

    public function append(
        string $aggregateType,
        string $aggregateId,
        int $expectedVersion,
        array $events,
        string $producer,
        array $metadata = [],
    ): array {
        $envelopes = [];
        $occurredAt = new DateTimeImmutable;

        foreach ($events as $index => $event) {
            $version = $expectedVersion + $index + 1;
            $eventId = (string) Str::uuid();
            $envelope = new EventEnvelope(
                eventId: $eventId,
                eventType: $event->eventType,
                eventVersion: 1,
                aggregateType: $aggregateType,
                aggregateId: $aggregateId,
                aggregateVersion: $version,
                payload: $event->payload,
                metadata: array_merge($metadata, $event->metadata),
                occurredAt: $occurredAt,
                producer: $producer,
            );

            try {
                DB::table('domain_events')->insert([
                    'id' => $eventId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_version' => $version,
                    'event_type' => $event->eventType,
                    'event_version' => 1,
                    'payload' => json_encode($event->payload, JSON_THROW_ON_ERROR),
                    'metadata' => json_encode($envelope->metadata, JSON_THROW_ON_ERROR),
                    'occurred_at' => $occurredAt->format('Y-m-d H:i:sP'),
                    'producer' => $producer,
                ]);
            } catch (QueryException $exception) {
                if (($exception->errorInfo[0] ?? null) === '23505') {
                    throw new ConcurrencyException('Aggregate version conflict', 0, $exception);
                }

                throw $exception;
            }

            $envelopes[] = $envelope;
        }

        return $envelopes;
    }
}
