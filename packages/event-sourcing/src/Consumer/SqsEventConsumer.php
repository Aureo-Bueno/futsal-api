<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Consumer;

use Aws\Sqs\SqsClient;
use Futsal\Contracts\EventEnvelope;
use Psr\Log\LoggerInterface;
use Throwable;

final class SqsEventConsumer
{
    public function __construct(
        private readonly SqsClient $sqs,
        private readonly LoggerInterface $logger,
    ) {}

    public function consume(string $queueUrl, DomainEventHandler $handler, int $maxMessages = 10): int
    {
        $result = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => $maxMessages,
            'WaitTimeSeconds' => 20,
            'VisibilityTimeout' => 60,
        ]);

        $processed = 0;

        foreach ($result->get('Messages') ?? [] as $message) {
            try {
                $payload = json_decode((string) $message['Body'], true, 512, JSON_THROW_ON_ERROR);
                $handler->handle(EventEnvelope::fromArray($payload));

                $this->sqs->deleteMessage([
                    'QueueUrl' => $queueUrl,
                    'ReceiptHandle' => $message['ReceiptHandle'],
                ]);

                $processed++;
            } catch (Throwable $exception) {
                $this->logger->error('Domain event processing failed', [
                    'queue' => $queueUrl,
                    'message_id' => $message['MessageId'] ?? null,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $processed;
    }
}
