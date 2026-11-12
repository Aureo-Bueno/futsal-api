<?php

namespace App\Repositories\Contracts;

use App\Models\TeamClassification;
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
     * @return Collection<int, TeamClassification>
     */
    public function orderedByPoints(): Collection;

    /**
     * Get paginated team classifications ordered by points.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a team classification by id.
     */
    public function findById(string $id): ?TeamClassification;

    /**
     * Create a team classification.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TeamClassification;

    /**
     * Update an existing team classification.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(TeamClassification $teamClassification, array $data): TeamClassification;

    /**
     * Delete an existing team classification.
     */
    public function delete(TeamClassification $teamClassification): bool;
}
