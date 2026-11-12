<?php

declare(strict_types=1);

namespace App\Application\Match\Handlers;

use App\Application\Match\Projectors\MatchProjector;
use App\Domain\Match\Exceptions\MatchDomainException;
use App\Domain\Match\MatchAggregate;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateMatchHandler
{
    public function __construct(
        private readonly AggregateRepository $repository,
        private readonly MatchProjector $projector,
    ) {}

    public function handle(string $homeTeamId, string $awayTeamId, string $scheduledAt): string
    {
        $activeTeams = DB::table('known_teams')
            ->whereIn('id', [$homeTeamId, $awayTeamId])
            ->where('active', true)
            ->count();

        if ($activeTeams !== 2) {
            throw new MatchDomainException('Both teams must exist and be active');
        }

        $id = (string) Str::uuid();
        $match = MatchAggregate::create($id, $homeTeamId, $awayTeamId, $scheduledAt);

        $this->repository->save(
            $match,
            Topics::MATCH_EVENTS,
            fn (array $events) => $this->projector->project($events),
        );

        return $id;
    }
}
