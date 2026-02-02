<?php

namespace App\Repositories\Contracts;

use App\Models\Players;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository contract for players.
 */
interface PlayersRepositoryInterface
{
  /**
   * Get all players.
   *
   * @return Collection<int, Players>
   */
  public function all(): Collection;

  /**
   * Get paginated players.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Find a player by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?Players;

  /**
   * Create a new player.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Players;

  /**
   * Update an existing player.
   *
   * @param Players $player
   * @param array<string, mixed> $data
   */
  public function update(Players $player, array $data): Players;

  /**
   * Delete an existing player.
   *
   * @param Players $player
   */
  public function delete(Players $player): bool;
}
