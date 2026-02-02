<?php

namespace App\Services\Contracts;

use App\Models\Players;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service contract for players.
 */
interface PlayersServiceInterface
{
  /**
   * List all players.
   *
   * @return Collection<int, Players>
   */
  public function list(): Collection;

  /**
   * Paginate players.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Get a player by id.
   *
   * @param string $id
   */
  public function get(string $id): ?Players;

  /**
   * Create a player.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Players;

  /**
   * Update a player.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?Players;

  /**
   * Delete a player.
   *
   * @param string $id
   */
  public function delete(string $id): bool;
}
