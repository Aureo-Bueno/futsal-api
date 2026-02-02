<?php

namespace App\Repositories\Eloquent;

use App\Models\Players;
use App\Repositories\Contracts\PlayersRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
  public function all(): Collection
  {
    return Players::all();
  }

  /**
   * Get paginated players.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return Players::paginate($perPage);
  }

  /**
   * Find a player by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?Players
  {
    return Players::find($id);
  }

  /**
   * Create a new player.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Players
  {
    return Players::create($data);
  }

  /**
   * Update an existing player.
   *
   * @param Players $player
   * @param array<string, mixed> $data
   */
  public function update(Players $player, array $data): Players
  {
    $player->update($data);
    return $player;
  }

  /**
   * Delete an existing player.
   *
   * @param Players $player
   */
  public function delete(Players $player): bool
  {
    return (bool) $player->delete();
  }
}
