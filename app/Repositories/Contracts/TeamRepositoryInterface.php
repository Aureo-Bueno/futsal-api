<?php

namespace App\Repositories\Contracts;

use App\Models\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository contract for teams.
 */
interface TeamRepositoryInterface
{
  /**
   * Get all teams.
   *
   * @return Collection<int, Team>
   */
  public function all(): Collection;

  /**
   * Get paginated teams.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Find a team by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?Team;

  /**
   * Create a new team.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Team;

  /**
   * Update an existing team.
   *
   * @param Team $team
   * @param array<string, mixed> $data
   */
  public function update(Team $team, array $data): Team;

  /**
   * Delete an existing team.
   *
   * @param Team $team
   */
  public function delete(Team $team): bool;
}
