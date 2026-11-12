<?php

declare(strict_types=1);

namespace Futsal\Contracts;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class EventEnvelope
{
    public function __construct(
        public string $eventId,
        public string $eventType,
        public int $eventVersion,
        public string $aggregateType,
        public string $aggregateId,
        public int $aggregateVersion,
        public array $payload,
        public array $metadata,
        public DateTimeImmutable $occurredAt,
        public string $producer,
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->eventType,
            'event_version' => $this->eventVersion,
            'aggregate_type' => $this->aggregateType,
            'aggregate_id' => $this->aggregateId,
            'aggregate_version' => $this->aggregateVersion,
            'payload' => $this->payload,
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'producer' => $this->producer,
        ];
    }

    public static function fromArray(array $data): self
    {
        foreach (['event_id', 'event_type', 'event_version', 'aggregate_type', 'aggregate_id', 'aggregate_version', 'payload', 'metadata', 'occurred_at', 'producer'] as $field) {
            if (! array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing envelope field: {$field}");
            }
        }

        return new self(
            eventId: (string) $data['event_id'],
            eventType: (string) $data['event_type'],
            eventVersion: (int) $data['event_version'],
            aggregateType: (string) $data['aggregate_type'],
            aggregateId: (string) $data['aggregate_id'],
            aggregateVersion: (int) $data['aggregate_version'],
            payload: (array) $data['payload'],
            metadata: (array) $data['metadata'],
            occurredAt: new DateTimeImmutable((string) $data['occurred_at']),
            producer: (string) $data['producer'],
        );
    }
}
