<?php

namespace App\Services\Contracts;

use App\Models\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service contract for teams.
 */
interface TeamServiceInterface
{
  /**
   * List all teams.
   *
   * @return Collection<int, Team>
   */
  public function list(): Collection;

  /**
   * Paginate teams.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Get a team by id.
   *
   * @param string $id
   */
  public function get(string $id): ?Team;

  /**
   * Create a team.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Team;

  /**
   * Update a team.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?Team;

  /**
   * Delete a team.
   *
   * @param string $id
   */
  public function delete(string $id): bool;
}
