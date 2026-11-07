<?php

namespace Tests\Unit\Services;

use App\Models\TeamMatch;
use App\Repositories\Contracts\TeamMatchRepositoryInterface;
use App\Services\Implementations\TeamMatchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TeamMatchServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TeamMatchRepositoryInterface $repository;

    private LoggerInterface $logger;

    private TeamMatchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TeamMatchRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new TeamMatchService($this->repository, $this->logger);
    }

    public function test_list_and_paginate(): void
    {
        $collection = new Collection([new TeamMatch(['id' => 'm-1'])]);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->repository->shouldReceive('all')->once()->andReturn($collection);
        $this->repository->shouldReceive('paginate')->once()->with(15)->andReturn($paginator);

        $this->assertSame($collection, $this->service->list());
        $this->assertSame($paginator, $this->service->paginate(15));
    }

    public function test_get_create_update_and_delete(): void
    {
        $data = [
            'date_team_match' => '2026-05-10',
            'start_time' => '19:00',
            'end_time' => '20:00',
            'scoreboard' => 3,
        ];
        $match = new TeamMatch(array_merge(['id' => 'm-1'], $data));

        $this->repository->shouldReceive('create')->once()->with($data)->andReturn($match);
        $this->logger->shouldReceive('info')->once()->with('Team match created', ['team_match_id' => 'm-1']);
        $this->assertSame($match, $this->service->create($data));

        $this->repository->shouldReceive('findById')->once()->with('m-1')->andReturn($match);
        $this->assertSame($match, $this->service->get('m-1'));

        $this->repository->shouldReceive('findById')->once()->with('m-1')->andReturn($match);
        $this->repository->shouldReceive('update')->once()->with($match, ['scoreboard' => 5])->andReturn($match);
        $this->logger->shouldReceive('info')->once()->with('Team match updated', ['team_match_id' => 'm-1']);
        $this->assertSame($match, $this->service->update('m-1', ['scoreboard' => 5]));

        $this->repository->shouldReceive('findById')->once()->with('m-404')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Team match not found for update', ['team_match_id' => 'm-404']);
        $this->assertNull($this->service->update('m-404', ['scoreboard' => 5]));

        $this->repository->shouldReceive('findById')->once()->with('m-1')->andReturn($match);
        $this->repository->shouldReceive('delete')->once()->with($match)->andReturnTrue();
        $this->logger->shouldReceive('info')->once()->with('Team match deleted', ['team_match_id' => 'm-1']);
        $this->assertTrue($this->service->delete('m-1'));

        $this->repository->shouldReceive('findById')->once()->with('m-404')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Team match not found for delete', ['team_match_id' => 'm-404']);
        $this->assertFalse($this->service->delete('m-404'));
    }
}
