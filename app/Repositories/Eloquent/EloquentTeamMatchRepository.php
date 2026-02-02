<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamMatch;
use App\Repositories\Contracts\TeamMatchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of TeamMatchRepositoryInterface.
 */
class EloquentTeamMatchRepository implements TeamMatchRepositoryInterface
{
  /**
   * Get all team matches.
   *
   * @return Collection<int, TeamMatch>
   */
  public function all(): Collection
  {
    return TeamMatch::all();
  }

  /**
   * Get paginated team matches.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return TeamMatch::paginate($perPage);
  }

  /**
   * Find a team match by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?TeamMatch
  {
    return TeamMatch::find($id);
  }

  /**
   * Create a new team match.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): TeamMatch
  {
    return TeamMatch::create($data);
  }

  /**
   * Update an existing team match.
   *
   * @param TeamMatch $teamMatch
   * @param array<string, mixed> $data
   */
  public function update(TeamMatch $teamMatch, array $data): TeamMatch
  {
    $teamMatch->update($data);
    return $teamMatch;
  }

  /**
   * Delete an existing team match.
   *
   * @param TeamMatch $teamMatch
   */
  public function delete(TeamMatch $teamMatch): bool
  {
    return (bool) $teamMatch->delete();
  }
}
