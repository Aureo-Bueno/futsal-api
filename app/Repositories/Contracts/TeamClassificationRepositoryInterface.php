<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository contract for team classifications.
 */
interface TeamClassificationRepositoryInterface
{
  /**
   * Get team classifications ordered by points.
   *
   * @return Collection<int, \App\Models\TeamClassification>
   */
  public function orderedByPoints(): Collection;

  /**
   * Get paginated team classifications ordered by points.
   *
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Find a team classification by id.
   *
   * @param string $id
   */
  public function findById(string $id): ?\App\Models\TeamClassification;

  /**
   * Create a team classification.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): \App\Models\TeamClassification;

  /**
   * Update an existing team classification.
   *
   * @param \App\Models\TeamClassification $teamClassification
   * @param array<string, mixed> $data
   */
  public function update(\App\Models\TeamClassification $teamClassification, array $data): \App\Models\TeamClassification;

  /**
   * Delete an existing team classification.
   *
   * @param \App\Models\TeamClassification $teamClassification
   */
  public function delete(\App\Models\TeamClassification $teamClassification): bool;
}
