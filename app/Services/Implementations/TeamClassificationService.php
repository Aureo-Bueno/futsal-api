<?php

namespace App\Services\Implementations;

use App\Repositories\Contracts\TeamClassificationRepositoryInterface;
use App\Services\Contracts\TeamClassificationServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

/**
 * Application service for team classifications.
 */
class TeamClassificationService implements TeamClassificationServiceInterface
{
  public function __construct(
    private readonly TeamClassificationRepositoryInterface $teamClassificationRepository,
    private readonly LoggerInterface $logger
  ) {
  }

  /**
   * List team classifications ordered by points.
   *
   * @return Collection<int, \App\Models\TeamClassification>
   */
  public function list(): Collection
  {
    $result = $this->teamClassificationRepository->orderedByPoints();
    $this->logger->info('Team classification listed', ['count' => $result->count()]);
    return $result;
  }

  /**
   * Paginate team classifications ordered by points.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return $this->teamClassificationRepository->paginate($perPage);
  }

  /**
   * Get a team classification by id.
   *
   * @param string $id
   */
  public function get(string $id): ?\App\Models\TeamClassification
  {
    return $this->teamClassificationRepository->findById($id);
  }

  /**
   * Create a team classification.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): \App\Models\TeamClassification
  {
    $classification = $this->teamClassificationRepository->create($data);
    $this->logger->info('Team classification created', ['team_classification_id' => $classification->id]);
    return $classification;
  }

  /**
   * Update a team classification.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?\App\Models\TeamClassification
  {
    $classification = $this->teamClassificationRepository->findById($id);
    if (!$classification) {
      $this->logger->warning('Team classification not found for update', ['team_classification_id' => $id]);
      return null;
    }

    $classification = $this->teamClassificationRepository->update($classification, $data);
    $this->logger->info('Team classification updated', ['team_classification_id' => $classification->id]);
    return $classification;
  }

  /**
   * Delete a team classification.
   *
   * @param string $id
   */
  public function delete(string $id): bool
  {
    $classification = $this->teamClassificationRepository->findById($id);
    if (!$classification) {
      $this->logger->warning('Team classification not found for delete', ['team_classification_id' => $id]);
      return false;
    }

    $deleted = $this->teamClassificationRepository->delete($classification);
    if ($deleted) {
      $this->logger->info('Team classification deleted', ['team_classification_id' => $id]);
    }
    return $deleted;
  }
}
