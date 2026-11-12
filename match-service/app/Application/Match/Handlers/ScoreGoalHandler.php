<?php

declare(strict_types=1);

namespace App\Application\Match\Handlers;

use App\Application\Match\Projectors\MatchProjector;
use App\Domain\Match\Exceptions\MatchDomainException;
use App\Domain\Match\MatchAggregate;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Futsal\EventSourcing\Store\EventStore;
use Illuminate\Support\Facades\DB;

final class ScoreGoalHandler
{
    public function __construct(
        private readonly EventStore $store,
        private readonly AggregateRepository $repository,
        private readonly MatchProjector $projector,
    ) {}

    public function handle(string $matchId, string $teamId, string $playerId, int $minute, bool $ownGoal = false): void
    {
        $player = DB::table('known_players')
            ->where('id', $playerId)
            ->where('team_id', $teamId)
            ->where('active', true)
            ->first();

        if ($player === null) {
            throw new MatchDomainException('Player must belong to the scoring team and be active');
        }

        $events = $this->store->load('match', $matchId);

        if ($events === []) {
            throw new MatchDomainException('Match not found');
        }

        $match = MatchAggregate::reconstitute($events);
        $match->scoreGoal($teamId, $playerId, $minute, $ownGoal);

        $this->repository->save(
            $match,
            Topics::MATCH_EVENTS,
            fn (array $pending) => $this->projector->project($pending),
        );
    }
}
