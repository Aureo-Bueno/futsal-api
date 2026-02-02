<?php

namespace App\Services\Implementations;

use App\Models\TeamMatch;
use App\Repositories\Contracts\TeamMatchRepositoryInterface;
use App\Services\Contracts\TeamMatchServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

/**
 * Application service for team matches.
 */
class TeamMatchService implements TeamMatchServiceInterface
{
  public function __construct(
    private readonly TeamMatchRepositoryInterface $teamMatchRepository,
    private readonly LoggerInterface $logger
  ) {
  }

  /**
   * List all team matches.
   *
   * @return Collection<int, TeamMatch>
   */
  public function list(): Collection
  {
    return $this->teamMatchRepository->all();
  }

  /**
   * Paginate team matches.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return $this->teamMatchRepository->paginate($perPage);
  }

  /**
   * Get a team match by id.
   *
   * @param string $id
   */
  public function get(string $id): ?TeamMatch
  {
    return $this->teamMatchRepository->findById($id);
  }

  /**
   * Create a team match.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): TeamMatch
  {
    $teamMatch = $this->teamMatchRepository->create($data);
    $this->logger->info('Team match created', ['team_match_id' => $teamMatch->id]);
    return $teamMatch;
  }

  /**
   * Update a team match.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?TeamMatch
  {
    $teamMatch = $this->teamMatchRepository->findById($id);
    if (!$teamMatch) {
      $this->logger->warning('Team match not found for update', ['team_match_id' => $id]);
      return null;
    }

    $teamMatch = $this->teamMatchRepository->update($teamMatch, $data);
    $this->logger->info('Team match updated', ['team_match_id' => $teamMatch->id]);
    return $teamMatch;
  }

  /**
   * Delete a team match.
   *
   * @param string $id
   */
  public function delete(string $id): bool
  {
    $teamMatch = $this->teamMatchRepository->findById($id);
    if (!$teamMatch) {
      $this->logger->warning('Team match not found for delete', ['team_match_id' => $id]);
      return false;
    }

    $deleted = $this->teamMatchRepository->delete($teamMatch);
    if ($deleted) {
      $this->logger->info('Team match deleted', ['team_match_id' => $id]);
    }
    return $deleted;
  }
}
