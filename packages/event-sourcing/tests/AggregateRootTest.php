<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Tests;

use Futsal\EventSourcing\AggregateRoot;
use Futsal\EventSourcing\DomainEvent;
use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    public function test_records_events_and_increments_version(): void
    {
        $aggregate = FakeAggregate::create('a1');

        $this->assertSame(1, $aggregate->version());
        $this->assertCount(1, $aggregate->pendingEvents());
        $this->assertSame('fake.created', $aggregate->pendingEvents()[0]->eventType);
    }

    public function test_replays_events_without_pending(): void
    {
        $aggregate = FakeAggregate::reconstitute([
            new DomainEvent('fake.created', ['id' => 'a1', 'name' => 'First']),
            new DomainEvent('fake.renamed', ['name' => 'Second']),
        ]);

        $this->assertSame('Second', $aggregate->name());
        $this->assertSame(2, $aggregate->version());
        $this->assertSame([], $aggregate->pendingEvents());
    }
}

final class FakeAggregate extends AggregateRoot
{
    private string $id = '';

    private string $name = '';

    public static function create(string $id): self
    {
        $aggregate = new self;
        $aggregate->record(new DomainEvent('fake.created', ['id' => $id, 'name' => 'First']));

        return $aggregate;
    }

    public static function reconstitute(array $events): self
    {
        $aggregate = new self;
        $aggregate->replay($events);

        return $aggregate;
    }

    public function aggregateId(): string
    {
        return $this->id;
    }

    public function aggregateType(): string
    {
        return 'fake';
    }

    public function name(): string
    {
        return $this->name;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event->eventType === 'fake.created') {
            $this->id = $event->payload['id'];
            $this->name = $event->payload['name'];
        }

        if ($event->eventType === 'fake.renamed') {
            $this->name = $event->payload['name'];
        }
    }
}
