<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Publisher;

use Aws\Sns\SnsClient;
use Futsal\Contracts\EventEnvelope;

final class SnsPublisher implements EventPublisher
{
    public function __construct(private readonly SnsClient $sns) {}

    public function publish(string $topicArn, EventEnvelope $envelope): void
    {
        $arguments = [
            'TopicArn' => $topicArn,
            'Message' => json_encode($envelope->toArray(), JSON_THROW_ON_ERROR),
            'MessageAttributes' => [
                'eventType' => ['DataType' => 'String', 'StringValue' => $envelope->eventType],
                'aggregateType' => ['DataType' => 'String', 'StringValue' => $envelope->aggregateType],
                'aggregateId' => ['DataType' => 'String', 'StringValue' => $envelope->aggregateId],
            ],
        ];

        if (str_ends_with($topicArn, '.fifo')) {
            $arguments['MessageGroupId'] = $envelope->aggregateId;
            $arguments['MessageDeduplicationId'] = $envelope->eventId;
        }

        $this->sns->publish($arguments);
    }
}
