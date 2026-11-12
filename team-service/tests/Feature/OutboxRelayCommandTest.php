<?php

declare(strict_types=1);

namespace Tests\Feature;

use Futsal\Contracts\EventEnvelope;
use Futsal\EventSourcing\Publisher\EventPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OutboxRelayCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_relays_pending_outbox_to_publisher(): void
    {
        $body = json_encode(['name' => 'Tigers']);
        $this->postJson('/teams', ['name' => 'Tigers'], $this->internalHeaders('POST', '/teams', $body))->assertCreated();

        $publisher = new class implements EventPublisher
        {
            public array $published = [];

            public function publish(string $topicArn, EventEnvelope $envelope): void
            {
                $this->published[] = [$topicArn, $envelope];
            }
        };

        $this->app->instance(EventPublisher::class, $publisher);

        $this->artisan('outbox:relay')->assertExitCode(0);

        $this->assertCount(1, $publisher->published);
        $this->assertDatabaseHas('outbox_events', ['status' => 'published']);
    }
}
