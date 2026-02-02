<?php

namespace App\Services\Contracts;

use App\Models\TeamMatch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service contract for team matches.
 */
interface TeamMatchServiceInterface
{
  /**
   * List all team matches.
   *
   * @return Collection<int, TeamMatch>
   */
  public function list(): Collection;

  /**
   * Paginate team matches.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator;

  /**
   * Get a team match by id.
   *
   * @param string $id
   */
  public function get(string $id): ?TeamMatch;

  /**
   * Create a team match.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): TeamMatch;

  /**
   * Update a team match.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?TeamMatch;

  /**
   * Delete a team match.
   *
   * @param string $id
   */
  public function delete(string $id): bool;
}
