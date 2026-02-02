<?php

namespace App\Repositories\Eloquent;

use App\Models\Team;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of TeamRepositoryInterface.
 */
class EloquentTeamRepository implements TeamRepositoryInterface
{
  /**
   * Get all teams.
   *
   * @return Collection<int, Team>
   */
  public function all(): Collection
  {
    return Team::all();
  }

  /**
   * Get paginated teams.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return Team::paginate($perPage);
  }

  /**
   * Find a team by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?Team
  {
    return Team::find($id);
  }

  /**
   * Create a new team.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Team
  {
    return Team::create($data);
  }

  /**
   * Update an existing team.
   *
   * @param Team $team
   * @param array<string, mixed> $data
   */
  public function update(Team $team, array $data): Team
  {
    $team->update($data);
    return $team;
  }

  /**
   * Delete an existing team.
   *
   * @param Team $team
   */
  public function delete(Team $team): bool
  {
    return (bool) $team->delete();
  }
}
