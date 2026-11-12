<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Repository;

use Futsal\Contracts\EventEnvelope;
use Futsal\EventSourcing\AggregateRoot;
use Futsal\EventSourcing\Outbox\OutboxRecorder;
use Futsal\EventSourcing\Store\EventStore;
use Illuminate\Support\Facades\DB;

final class AggregateRepository
{
    public function __construct(
        private readonly EventStore $store,
        private readonly OutboxRecorder $outbox,
        private readonly string $producer,
    ) {}

    /** @param callable(EventEnvelope[]): void|null $withinTransaction */
    public function save(AggregateRoot $aggregate, string $topic, ?callable $withinTransaction = null): void
    {
        $events = $aggregate->pendingEvents();

        if ($events === []) {
            return;
        }

        $expectedVersion = $aggregate->version() - count($events);

        DB::transaction(function () use ($aggregate, $topic, $events, $expectedVersion, $withinTransaction): void {
            $envelopes = $this->store->append(
                $aggregate->aggregateType(),
                $aggregate->aggregateId(),
                $expectedVersion,
                $events,
                $this->producer,
            );

            foreach ($envelopes as $envelope) {
                $this->outbox->record($topic, $envelope);
            }

            if ($withinTransaction !== null) {
                $withinTransaction($envelopes);
            }
        });

        $aggregate->clearPendingEvents();
    }
}
