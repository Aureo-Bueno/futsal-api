<?php

declare(strict_types=1);

namespace Futsal\EventSourcing;

use Futsal\Contracts\EventEnvelope;

final readonly class DomainEvent
{
    public function __construct(
        public string $eventType,
        public array $payload,
        public array $metadata = [],
    ) {}

    public static function fromEnvelope(EventEnvelope $envelope): self
    {
        return new self($envelope->eventType, $envelope->payload, $envelope->metadata);
    }
}
