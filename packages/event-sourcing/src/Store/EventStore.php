<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Store;

use Futsal\Contracts\EventEnvelope;
use Futsal\EventSourcing\DomainEvent;

interface EventStore
{
    /** @return EventEnvelope[] */
    public function load(string $aggregateType, string $aggregateId): array;

    /** @param DomainEvent[] $events @return EventEnvelope[] */
    public function append(
        string $aggregateType,
        string $aggregateId,
        int $expectedVersion,
        array $events,
        string $producer,
        array $metadata = [],
    ): array;
}
