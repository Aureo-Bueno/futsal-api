<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Players;
use App\Repositories\Contracts\PlayersRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * Eloquent implementation of PlayersRepositoryInterface.
 */
class EloquentPlayersRepository implements PlayersRepositoryInterface
{
    /**
     * Get all players.
     *
     * @return Collection<int, Players>
     */
    #[Override]
    public function all(): Collection
    {
        return Players::with('team')->get();
    }

    /**
     * Get paginated players.
     */
    #[Override]
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Players::with('team')->paginate($perPage);
    }

    /**
     * Find a player by id.
     */
    #[Override]
    public function findById(string $id): ?Players
    {
        return Players::with('team')->find($id);
    }

    /**
     * Create a new player.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function create(array $data): Players
    {
        return DB::transaction(fn (): Players => Players::create($data));
    }

    /**
     * Update an existing player.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function update(Players $player, array $data): Players
    {
        return DB::transaction(function () use ($player, $data): Players {
            $player->update($data);

            return $player;
        });
    }

    /**
     * Delete an existing player.
     */
    #[Override]
    public function delete(Players $player): bool
    {
        return DB::transaction(fn (): bool => (bool) $player->delete());
    }
}
