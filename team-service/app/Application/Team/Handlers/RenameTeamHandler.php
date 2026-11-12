<?php

declare(strict_types=1);

namespace App\Application\Team\Handlers;

use App\Application\Team\Projectors\TeamProjector;
use App\Domain\Team\Team;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Futsal\EventSourcing\Store\EventStore;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RenameTeamHandler
{
    public function __construct(
        private readonly AggregateRepository $repository,
        private readonly EventStore $store,
        private readonly TeamProjector $projector,
    ) {}

    public function handle(string $id, string $name): void
    {
        $events = $this->store->load('team', $id);

        if ($events === []) {
            throw new NotFoundHttpException('Team not found');
        }

        $team = Team::reconstitute($events);
        $team->rename($name);

        $this->repository->save(
            $team,
            Topics::TEAM_EVENTS,
            fn (array $pending) => $this->projector->project($pending),
        );
    }
}
