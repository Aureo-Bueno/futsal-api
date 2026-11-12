<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Team;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;

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
    #[Override]
    public function all(): Collection
    {
        return Team::with(['players', 'teamMatch'])->get();
    }

    /**
     * Get paginated teams.
     */
    #[Override]
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Team::with(['players', 'teamMatch'])->paginate($perPage);
    }

    /**
     * Find a team by id.
     */
    #[Override]
    public function findById(string $id): ?Team
    {
        return Team::with(['players', 'teamMatch'])->find($id);
    }

    /**
     * Create a new team.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function create(array $data): Team
    {
        return DB::transaction(fn (): Team => Team::create($data));
    }

    /**
     * Update an existing team.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function update(Team $team, array $data): Team
    {
        return DB::transaction(function () use ($team, $data): Team {
            $team->update($data);

            return $team;
        });
    }

    /**
     * Delete an existing team.
     */
    #[Override]
    public function delete(Team $team): bool
    {
        return DB::transaction(fn (): bool => (bool) $team->delete());
    }
}
