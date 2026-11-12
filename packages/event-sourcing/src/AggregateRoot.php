<?php

declare(strict_types=1);

namespace Futsal\EventSourcing;

abstract class AggregateRoot
{
    protected int $version = 0;

    /** @var DomainEvent[] */
    private array $pendingEvents = [];

    abstract public function aggregateId(): string;

    abstract public function aggregateType(): string;

    abstract protected function apply(DomainEvent $event): void;

    public function version(): int
    {
        return $this->version;
    }

    /** @return DomainEvent[] */
    public function pendingEvents(): array
    {
        return $this->pendingEvents;
    }

    /** @param DomainEvent[] $events */
    public function replay(array $events): void
    {
        foreach ($events as $event) {
            $this->apply($event);
            $this->version++;
        }
    }

    protected function record(DomainEvent $event): void
    {
        $this->apply($event);
        $this->version++;
        $this->pendingEvents[] = $event;
    }

    public function clearPendingEvents(): void
    {
        $this->pendingEvents = [];
    }
}
