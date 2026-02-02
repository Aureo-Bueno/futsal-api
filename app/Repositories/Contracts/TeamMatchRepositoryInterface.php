<?php

namespace App\Repositories\Contracts;

use App\Models\TeamMatch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository contract for team matches.
 */
interface TeamMatchRepositoryInterface
{
  /**
   * Get all team matches.
   *
   * @return Collection<int, TeamMatch>
   */
  public function all(): Collection;

  /**
   * Get paginated team matches.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Find a team match by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?TeamMatch;

  /**
   * Create a new team match.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): TeamMatch;

  /**
   * Update an existing team match.
   *
   * @param TeamMatch $teamMatch
   * @param array<string, mixed> $data
   */
  public function update(TeamMatch $teamMatch, array $data): TeamMatch;

  /**
   * Delete an existing team match.
   *
   * @param TeamMatch $teamMatch
   */
  public function delete(TeamMatch $teamMatch): bool;
}
