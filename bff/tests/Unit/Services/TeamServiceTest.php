<?php

namespace Tests\Unit\Services;

use App\Models\Team;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Services\Implementations\TeamService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TeamServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TeamRepositoryInterface $repository;

    private LoggerInterface $logger;

    private TeamService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TeamRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new TeamService($this->repository, $this->logger);
    }

    public function test_list_returns_all_teams(): void
    {
        $teams = new Collection([new Team(['id' => 'uuid-1', 'name' => 'Alpha FC'])]);
        $this->repository->shouldReceive('all')->once()->andReturn($teams);

        $result = $this->service->list();

        $this->assertSame($teams, $result);
    }

    public function test_paginate_returns_paginator(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $this->repository->shouldReceive('paginate')->once()->with(10)->andReturn($paginator);

        $result = $this->service->paginate(10);

        $this->assertSame($paginator, $result);
    }

    public function test_get_returns_team_when_found(): void
    {
        $team = new Team(['id' => 'uuid-1', 'name' => 'Alpha FC']);
        $this->repository->shouldReceive('findById')->once()->with('uuid-1')->andReturn($team);

        $result = $this->service->get('uuid-1');

        $this->assertSame($team, $result);
    }

    public function test_get_returns_null_when_not_found(): void
    {
        $this->repository->shouldReceive('findById')->once()->with('missing-id')->andReturnNull();

        $this->assertNull($this->service->get('missing-id'));
    }

    public function test_create_stores_and_logs_team(): void
    {
        $data = ['name' => 'Alpha FC'];
        $team = new Team(['id' => 'uuid-1', 'name' => 'Alpha FC']);

        $this->repository->shouldReceive('create')->once()->with($data)->andReturn($team);
        $this->logger->shouldReceive('info')->once()->with('Team created', ['team_id' => 'uuid-1']);

        $result = $this->service->create($data);

        $this->assertSame($team, $result);
    }

    public function test_update_returns_null_and_logs_warning_when_team_not_found(): void
    {
        $this->repository->shouldReceive('findById')->once()->with('missing-id')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Team not found for update', ['team_id' => 'missing-id']);

        $result = $this->service->update('missing-id', ['name' => 'Updated FC']);

        $this->assertNull($result);
    }

    public function test_update_updates_and_logs_when_team_found(): void
    {
        $team = new Team(['id' => 'uuid-1', 'name' => 'Old FC']);
        $updated = new Team(['id' => 'uuid-1', 'name' => 'New FC']);

        $this->repository->shouldReceive('findById')->once()->with('uuid-1')->andReturn($team);
        $this->repository->shouldReceive('update')->once()->with($team, ['name' => 'New FC'])->andReturn($updated);
        $this->logger->shouldReceive('info')->once()->with('Team updated', ['team_id' => 'uuid-1']);

        $result = $this->service->update('uuid-1', ['name' => 'New FC']);

        $this->assertSame($updated, $result);
    }

    public function test_delete_returns_false_and_logs_warning_when_team_not_found(): void
    {
        $this->repository->shouldReceive('findById')->once()->with('missing-id')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Team not found for delete', ['team_id' => 'missing-id']);

        $this->assertFalse($this->service->delete('missing-id'));
    }

    public function test_delete_deletes_and_logs_when_team_found(): void
    {
        $team = new Team(['id' => 'uuid-1', 'name' => 'Alpha FC']);

        $this->repository->shouldReceive('findById')->once()->with('uuid-1')->andReturn($team);
        $this->repository->shouldReceive('delete')->once()->with($team)->andReturnTrue();
        $this->logger->shouldReceive('info')->once()->with('Team deleted', ['team_id' => 'uuid-1']);

        $this->assertTrue($this->service->delete('uuid-1'));
    }
}
