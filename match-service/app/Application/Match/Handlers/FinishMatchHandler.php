<?php

declare(strict_types=1);

namespace App\Application\Match\Handlers;

use App\Application\Match\Projectors\MatchProjector;
use App\Domain\Match\Exceptions\MatchDomainException;
use App\Domain\Match\MatchAggregate;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Futsal\EventSourcing\Store\EventStore;

final class FinishMatchHandler
{
    public function __construct(
        private readonly EventStore $store,
        private readonly AggregateRepository $repository,
        private readonly MatchProjector $projector,
    ) {}

    public function handle(string $matchId): void
    {
        $events = $this->store->load('match', $matchId);

        if ($events === []) {
            throw new MatchDomainException('Match not found');
        }

        $match = MatchAggregate::reconstitute($events);
        $match->finish();

        $this->repository->save(
            $match,
            Topics::MATCH_EVENTS,
            fn (array $pending) => $this->projector->project($pending),
        );
    }
}
