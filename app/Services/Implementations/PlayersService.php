<?php

namespace App\Services\Implementations;

use App\Models\Players;
use App\Repositories\Contracts\PlayersRepositoryInterface;
use App\Services\Contracts\PlayersServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

/**
 * Application service for players.
 */
class PlayersService implements PlayersServiceInterface
{
  public function __construct(
    private readonly PlayersRepositoryInterface $playersRepository,
    private readonly LoggerInterface $logger
  ) {
  }

  /**
   * List all players.
   *
   * @return Collection<int, Players>
   */
  public function list(): Collection
  {
    return $this->playersRepository->all();
  }

  /**
   * Paginate players.
   */
  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
    return $this->playersRepository->paginate($perPage);
  }

  /**
   * Get a player by id.
   *
   * @param string $id
   */
  public function get(string $id): ?Players
  {
    return $this->playersRepository->findById($id);
  }

  /**
   * Create a player.
   *
   * @param array<string, mixed> $data
   */
  public function create(array $data): Players
  {
    $player = $this->playersRepository->create($data);
    $this->logger->info('Player created', ['player_id' => $player->id]);
    return $player;
  }

  /**
   * Update a player.
   *
   * @param string $id
   * @param array<string, mixed> $data
   */
  public function update(string $id, array $data): ?Players
  {
    $player = $this->playersRepository->findById($id);
    if (!$player) {
      $this->logger->warning('Player not found for update', ['player_id' => $id]);
      return null;
    }

    $player = $this->playersRepository->update($player, $data);
    $this->logger->info('Player updated', ['player_id' => $player->id]);
    return $player;
  }

  /**
   * Delete a player.
   *
   * @param string $id
   */
  public function delete(string $id): bool
  {
    $player = $this->playersRepository->findById($id);
    if (!$player) {
      $this->logger->warning('Player not found for delete', ['player_id' => $id]);
      return false;
    }

    $deleted = $this->playersRepository->delete($player);
    if ($deleted) {
      $this->logger->info('Player deleted', ['player_id' => $id]);
    }
    return $deleted;
  }
}
