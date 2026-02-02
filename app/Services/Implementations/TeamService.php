<?php

namespace App\Services\Implementations;

use App\Models\Team;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Services\Contracts\TeamServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

/**
 * Application service for teams.
 */
class TeamService implements TeamServiceInterface
{
  public function __construct(
    private readonly TeamRepositoryInterface $teamRepository,
    private readonly LoggerInterface $logger
  ) {
  }

  /**
   * List all teams.
   *
   * @return Collection<int, Team>
   */
  public function list(): Collection
  {
    return $this->teamRepository->all();
  }

  /**
   * Paginate teams.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return $this->teamRepository->paginate($perPage);
  }

  /**
   * Get a team by id.
   *
   * @param string $id
   */
  public function get(string $id): ?Team
  {
    return $this->teamRepository->findById($id);
  }

  /**
   * Create a team.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Team
  {
    $team = $this->teamRepository->create($data);
    $this->logger->info('Team created', ['team_id' => $team->id]);
    return $team;
  }

  /**
   * Update a team.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?Team
  {
    $team = $this->teamRepository->findById($id);
    if (!$team) {
      $this->logger->warning('Team not found for update', ['team_id' => $id]);
      return null;
    }

    $team = $this->teamRepository->update($team, $data);
    $this->logger->info('Team updated', ['team_id' => $team->id]);
    return $team;
  }

  /**
   * Delete a team.
   *
   * @param string $id
   */
  public function delete(string $id): bool
  {
    $team = $this->teamRepository->findById($id);
    if (!$team) {
      $this->logger->warning('Team not found for delete', ['team_id' => $id]);
      return false;
    }

    $deleted = $this->teamRepository->delete($team);
    if ($deleted) {
      $this->logger->info('Team deleted', ['team_id' => $id]);
    }
    return $deleted;
  }
}
