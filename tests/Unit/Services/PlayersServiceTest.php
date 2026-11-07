<?php

namespace Tests\Unit\Services;

use App\Models\Players;
use App\Repositories\Contracts\PlayersRepositoryInterface;
use App\Services\Implementations\PlayersService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlayersServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private PlayersRepositoryInterface $repository;

    private LoggerInterface $logger;

    private PlayersService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(PlayersRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new PlayersService($this->repository, $this->logger);
    }

    public function test_list_returns_all_players(): void
    {
        $players = new Collection([new Players(['id' => 'p-1', 'name' => 'Falcao', 'jersey_number' => 12])]);
        $this->repository->shouldReceive('all')->once()->andReturn($players);

        $this->assertSame($players, $this->service->list());
    }

    public function test_paginate_returns_paginator(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $this->repository->shouldReceive('paginate')->once()->with(15)->andReturn($paginator);

        $this->assertSame($paginator, $this->service->paginate(15));
    }

    public function test_get_returns_player_or_null(): void
    {
        $player = new Players(['id' => 'p-1', 'name' => 'Falcao']);
        $this->repository->shouldReceive('findById')->once()->with('p-1')->andReturn($player);
        $this->repository->shouldReceive('findById')->once()->with('p-404')->andReturnNull();

        $this->assertSame($player, $this->service->get('p-1'));
        $this->assertNull($this->service->get('p-404'));
    }

    public function test_create_stores_and_logs_player(): void
    {
        $data = ['name' => 'Falcao', 'jersey_number' => 12, 'team_id' => 't-1'];
        $player = new Players(array_merge(['id' => 'p-1'], $data));

        $this->repository->shouldReceive('create')->once()->with($data)->andReturn($player);
        $this->logger->shouldReceive('info')->once()->with('Player created', ['player_id' => 'p-1']);

        $this->assertSame($player, $this->service->create($data));
    }

    public function test_update_handles_found_and_not_found(): void
    {
        $player = new Players(['id' => 'p-1', 'name' => 'Falcao']);
        $updated = new Players(['id' => 'p-1', 'name' => 'Ricardinho']);

        $this->repository->shouldReceive('findById')->once()->with('p-1')->andReturn($player);
        $this->repository->shouldReceive('update')->once()->with($player, ['name' => 'Ricardinho'])->andReturn($updated);
        $this->logger->shouldReceive('info')->once()->with('Player updated', ['player_id' => 'p-1']);

        $this->assertSame($updated, $this->service->update('p-1', ['name' => 'Ricardinho']));

        $this->repository->shouldReceive('findById')->once()->with('p-404')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Player not found for update', ['player_id' => 'p-404']);

        $this->assertNull($this->service->update('p-404', ['name' => 'Ricardinho']));
    }

    public function test_delete_handles_found_and_not_found(): void
    {
        $player = new Players(['id' => 'p-1', 'name' => 'Falcao']);

        $this->repository->shouldReceive('findById')->once()->with('p-1')->andReturn($player);
        $this->repository->shouldReceive('delete')->once()->with($player)->andReturnTrue();
        $this->logger->shouldReceive('info')->once()->with('Player deleted', ['player_id' => 'p-1']);

        $this->assertTrue($this->service->delete('p-1'));

        $this->repository->shouldReceive('findById')->once()->with('p-404')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Player not found for delete', ['player_id' => 'p-404']);

        $this->assertFalse($this->service->delete('p-404'));
    }
}
