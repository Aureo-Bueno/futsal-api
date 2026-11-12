<?php

declare(strict_types=1);

namespace App\Application\Player\Handlers;

use App\Application\Team\Projectors\TeamProjector;
use App\Domain\Player\Exceptions\PlayerDomainException;
use App\Domain\Player\Player;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Futsal\EventSourcing\Store\EventStore;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TransferPlayerHandler
{
    public function __construct(
        private readonly AggregateRepository $repository,
        private readonly EventStore $store,
        private readonly TeamProjector $projector,
    ) {}

    public function handle(string $id, string $toTeamId): void
    {
        $events = $this->store->load('player', $id);

        if ($events === []) {
            throw new NotFoundHttpException('Player not found');
        }

        $team = DB::table('teams')->where('id', $toTeamId)->where('active', true)->first();

        if ($team === null) {
            throw new PlayerDomainException('Active destination team not found');
        }

        $player = Player::reconstitute($events);
        $player->transferTo($toTeamId);

        $this->repository->save(
            $player,
            Topics::TEAM_EVENTS,
            fn (array $pending) => $this->projector->project($pending),
        );
    }
}
