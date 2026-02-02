<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamClassification;
use App\Repositories\Contracts\TeamClassificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of TeamClassificationRepositoryInterface.
 */
class EloquentTeamClassificationRepository implements TeamClassificationRepositoryInterface
{
  /**
   * Get team classifications ordered by points.
   *
   * @return Collection<int, TeamClassification>
   */
  public function orderedByPoints(): Collection
  {
    return TeamClassification::orderBy('points', 'desc')->get();
  }

  /**
   * Get paginated team classifications ordered by points.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return TeamClassification::orderBy('points', 'desc')->paginate($perPage);
  }

  /**
   * Find a team classification by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?TeamClassification
  {
    return TeamClassification::find($id);
  }

  /**
   * Create a team classification.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): TeamClassification
  {
    return TeamClassification::create($data);
  }

  /**
   * Update an existing team classification.
   *
   * @param TeamClassification $teamClassification
   * @param array<string, mixed> $data
   */
  public function update(TeamClassification $teamClassification, array $data): TeamClassification
  {
    $teamClassification->update($data);
    return $teamClassification;
  }

  /**
   * Delete an existing team classification.
   *
   * @param TeamClassification $teamClassification
   */
  public function delete(TeamClassification $teamClassification): bool
  {
    return (bool) $teamClassification->delete();
  }
}
