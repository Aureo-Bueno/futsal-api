<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Tests;

use Aws\Result;
use Aws\Sns\SnsClient;
use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Publisher\SnsPublisher;
use PHPUnit\Framework\TestCase;

final class SnsPublisherTest extends TestCase
{
    public function test_resolves_logical_topic_to_configured_arn(): void
    {
        $sns = new class extends SnsClient
        {
            public array $arguments = [];

            public function __construct() {}

            public function publish(array $args): Result
            {
                $this->arguments = $args;

                return new Result([]);
            }
        };

        $publisher = new SnsPublisher($sns, [
            Topics::TEAM_EVENTS => 'arn:aws:sns:us-east-1:000000000000:futsal-team-events',
        ]);

        $publisher->publish(Topics::TEAM_EVENTS, $this->envelope());

        $this->assertSame(
            'arn:aws:sns:us-east-1:000000000000:futsal-team-events',
            $sns->arguments['TopicArn'],
        );
    }

    private function envelope(): EventEnvelope
    {
        return EventEnvelope::fromArray([
            'event_id' => '11111111-1111-4111-8111-111111111111',
            'event_type' => 'team.created',
            'event_version' => 1,
            'aggregate_type' => 'team',
            'aggregate_id' => '22222222-2222-4222-8222-222222222222',
            'aggregate_version' => 1,
            'payload' => ['team_id' => '22222222-2222-4222-8222-222222222222'],
            'metadata' => [],
            'occurred_at' => '2026-11-11T10:00:00+00:00',
            'producer' => 'team-service',
        ]);
    }
}
