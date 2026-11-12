<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Consumer;

use Futsal\Contracts\EventEnvelope;

interface DomainEventHandler
{
    public function handle(EventEnvelope $event): void;
}
