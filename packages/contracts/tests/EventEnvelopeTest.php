<?php

declare(strict_types=1);

namespace Futsal\Contracts\Tests;

use DateTimeImmutable;
use Futsal\Contracts\EventEnvelope;
use PHPUnit\Framework\TestCase;

final class EventEnvelopeTest extends TestCase
{
    public function test_round_trip(): void
    {
        $envelope = new EventEnvelope(
            eventId: '11111111-1111-4111-8111-111111111111',
            eventType: 'team.created',
            eventVersion: 1,
            aggregateType: 'team',
            aggregateId: '22222222-2222-4222-8222-222222222222',
            aggregateVersion: 1,
            payload: ['team_id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Tigers'],
            metadata: ['correlation_id' => '33333333-3333-4333-8333-333333333333'],
            occurredAt: new DateTimeImmutable('2026-11-11T10:00:00+00:00'),
            producer: 'team-service',
        );

        $decoded = EventEnvelope::fromArray($envelope->toArray());

        $this->assertSame($envelope->toArray(), $decoded->toArray());
    }

    public function test_requires_all_envelope_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        EventEnvelope::fromArray(['event_id' => 'only-id']);
    }
}
