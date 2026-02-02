<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service contract for team classifications.
 */
interface TeamClassificationServiceInterface
{
  /**
   * List team classifications ordered by points.
   *
   * @return Collection<int, \App\Models\TeamClassification>
   */
  public function list(): Collection;

  /**
   * Paginate team classifications ordered by points.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Get a team classification by id.
   *
   * @param string $id
   */
  public function get(string $id): ?\App\Models\TeamClassification;

  /**
   * Create a team classification.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): \App\Models\TeamClassification;

  /**
   * Update a team classification.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?\App\Models\TeamClassification;

  /**
   * Delete a team classification.
   *
   * @param string $id
   */
  public function delete(string $id): bool;
}
