<?php

declare(strict_types=1);

namespace App\Application\Player\Handlers;

use App\Application\Team\Projectors\TeamProjector;
use App\Domain\Player\Player;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Futsal\EventSourcing\Store\EventStore;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RemovePlayerHandler
{
    public function __construct(
        private readonly AggregateRepository $repository,
        private readonly EventStore $store,
        private readonly TeamProjector $projector,
    ) {}

    public function handle(string $id): void
    {
        $events = $this->store->load('player', $id);

        if ($events === []) {
            throw new NotFoundHttpException('Player not found');
        }

        $player = Player::reconstitute($events);
        $player->remove();

        $this->repository->save(
            $player,
            Topics::TEAM_EVENTS,
            fn (array $pending) => $this->projector->project($pending),
        );
    }
}
