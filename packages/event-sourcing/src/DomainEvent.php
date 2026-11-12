<?php

declare(strict_types=1);

namespace Futsal\EventSourcing;

final readonly class DomainEvent
{
    public function __construct(
        public string $eventType,
        public array $payload,
        public array $metadata = [],
    ) {}
}
