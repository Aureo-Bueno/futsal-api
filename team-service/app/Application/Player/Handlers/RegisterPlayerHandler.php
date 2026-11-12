<?php

declare(strict_types=1);

namespace App\Application\Player\Handlers;

use App\Application\Team\Projectors\TeamProjector;
use App\Domain\Player\Exceptions\PlayerDomainException;
use App\Domain\Player\Player;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegisterPlayerHandler
{
    public function __construct(
        private readonly AggregateRepository $repository,
        private readonly TeamProjector $projector,
    ) {}

    public function handle(string $teamId, string $name, int $jerseyNumber): string
    {
        $team = DB::table('teams')->where('id', $teamId)->where('active', true)->first();

        if ($team === null) {
            throw new PlayerDomainException('Active team not found');
        }

        $exists = DB::table('players')
            ->where('team_id', $teamId)
            ->where('jersey_number', $jerseyNumber)
            ->where('active', true)
            ->exists();

        if ($exists) {
            throw new PlayerDomainException('Jersey number already in use');
        }

        $id = (string) Str::uuid();
        $player = Player::register($id, $teamId, $name, $jerseyNumber);

        $this->repository->save(
            $player,
            Topics::TEAM_EVENTS,
            fn (array $events) => $this->projector->project($events),
        );

        return $id;
    }
}
