<?php

namespace App\Services\Contracts;

use App\Models\TeamClassification;
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
     * @return Collection<int, TeamClassification>
     */
    public function list(): Collection;

    /**
     * Paginate team classifications ordered by points.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a team classification by id.
     */
    public function get(string $id): ?TeamClassification;

    /**
     * Create a team classification.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TeamClassification;

    /**
     * Update a team classification.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): ?TeamClassification;

    /**
     * Delete a team classification.
     */
    public function delete(string $id): bool;
}
