<?php

declare(strict_types=1);

namespace App\Application\Team\Handlers;

use App\Application\Team\Projectors\TeamProjector;
use App\Domain\Team\Team;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Illuminate\Support\Str;

final class CreateTeamHandler
{
    public function __construct(
        private readonly AggregateRepository $repository,
        private readonly TeamProjector $projector,
    ) {}

    public function handle(string $name): string
    {
        $id = (string) Str::uuid();
        $team = Team::create($id, $name);

        $this->repository->save(
            $team,
            Topics::TEAM_EVENTS,
            fn (array $events) => $this->projector->project($events),
        );

        return $id;
    }
}
