<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\TeamMatch;
use App\Repositories\Contracts\TeamMatchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;

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
    #[Override]
    public function all(): Collection
    {
        return TeamMatch::with('teams')->get();
    }

    /**
     * Get paginated team matches.
     */
    #[Override]
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return TeamMatch::with('teams')->paginate($perPage);
    }

    /**
     * Find a team match by id.
     */
    #[Override]
    public function findById(string $id): ?TeamMatch
    {
        return TeamMatch::with('teams')->find($id);
    }

    /**
     * Create a new team match.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function create(array $data): TeamMatch
    {
        return DB::transaction(fn (): TeamMatch => TeamMatch::create($data));
    }

    /**
     * Update an existing team match.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function update(TeamMatch $teamMatch, array $data): TeamMatch
    {
        return DB::transaction(function () use ($teamMatch, $data): TeamMatch {
            $teamMatch->update($data);

            return $teamMatch;
        });
    }

    /**
     * Delete an existing team match.
     */
    #[Override]
    public function delete(TeamMatch $teamMatch): bool
    {
        return DB::transaction(fn (): bool => (bool) $teamMatch->delete());
    }
}
