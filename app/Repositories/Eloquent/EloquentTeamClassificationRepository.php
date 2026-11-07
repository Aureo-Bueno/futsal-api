<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\TeamClassification;
use App\Repositories\Contracts\TeamClassificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * Eloquent implementation of TeamClassificationRepositoryInterface.
 */
class EloquentTeamClassificationRepository implements TeamClassificationRepositoryInterface
{
    public const STANDINGS_CACHE_KEY = 'team_classification:ordered_by_points';

    private const STANDINGS_CACHE_TTL_SECONDS = 60;

    /**
     * Get team classifications ordered by points.
     *
     * @return Collection<int, TeamClassification>
     */
    #[Override]
    public function orderedByPoints(): Collection
    {
        return Cache::remember(
            self::STANDINGS_CACHE_KEY,
            self::STANDINGS_CACHE_TTL_SECONDS,
            fn (): Collection => TeamClassification::with('team')
                ->orderByDesc('points')
                ->orderByDesc('number_of_goals')
                ->get()
        );
    }

    /**
     * Get paginated team classifications ordered by points.
     */
    #[Override]
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return TeamClassification::with('team')
            ->orderByDesc('points')
            ->orderByDesc('number_of_goals')
            ->paginate($perPage);
    }

    /**
     * Find a team classification by id.
     */
    #[Override]
    public function findById(string $id): ?TeamClassification
    {
        return TeamClassification::with('team')->find($id);
    }

    /**
     * Create a team classification.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function create(array $data): TeamClassification
    {
        return DB::transaction(function () use ($data): TeamClassification {
            $classification = TeamClassification::create($data);
            Cache::forget(self::STANDINGS_CACHE_KEY);

            return $classification;
        });
    }

    /**
     * Update an existing team classification.
     *
     * @param  array<string, mixed>  $data
     */
    #[Override]
    public function update(TeamClassification $teamClassification, array $data): TeamClassification
    {
        return DB::transaction(function () use ($teamClassification, $data): TeamClassification {
            $teamClassification->update($data);
            Cache::forget(self::STANDINGS_CACHE_KEY);

            return $teamClassification;
        });
    }

    /**
     * Delete an existing team classification.
     */
    #[Override]
    public function delete(TeamClassification $teamClassification): bool
    {
        return DB::transaction(function () use ($teamClassification): bool {
            $deleted = (bool) $teamClassification->delete();
            Cache::forget(self::STANDINGS_CACHE_KEY);

            return $deleted;
        });
    }
}
