<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Publisher;

use Futsal\Contracts\EventEnvelope;

interface EventPublisher
{
    public function publish(string $topicArn, EventEnvelope $envelope): void;
}
