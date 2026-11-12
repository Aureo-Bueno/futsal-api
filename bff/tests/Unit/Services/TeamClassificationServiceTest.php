<?php

namespace Tests\Unit\Services;

use App\Models\TeamClassification;
use App\Repositories\Contracts\TeamClassificationRepositoryInterface;
use App\Services\Implementations\TeamClassificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TeamClassificationServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TeamClassificationRepositoryInterface $repository;

    private LoggerInterface $logger;

    private TeamClassificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TeamClassificationRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new TeamClassificationService($this->repository, $this->logger);
    }

    public function test_list_returns_ordered_classifications_and_logs_count(): void
    {
        $items = new Collection([
            new TeamClassification(['id' => 'c-1', 'points' => 30]),
            new TeamClassification(['id' => 'c-2', 'points' => 20]),
        ]);

        $this->repository->shouldReceive('orderedByPoints')->once()->andReturn($items);
        $this->logger->shouldReceive('info')->once()->with('Team classification listed', ['count' => 2]);

        $this->assertSame($items, $this->service->list());
    }

    public function test_paginate_get_create_update_and_delete(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $this->repository->shouldReceive('paginate')->once()->with(15)->andReturn($paginator);
        $this->assertSame($paginator, $this->service->paginate(15));

        $data = ['team_id' => 't-1', 'points' => 15, 'number_of_goals' => 12];
        $classification = new TeamClassification(array_merge(['id' => 'c-1'], $data));

        $this->repository->shouldReceive('create')->once()->with($data)->andReturn($classification);
        $this->logger->shouldReceive('info')->once()->with('Team classification created', ['team_classification_id' => 'c-1']);
        $this->assertSame($classification, $this->service->create($data));

        $this->repository->shouldReceive('findById')->once()->with('c-1')->andReturn($classification);
        $this->assertSame($classification, $this->service->get('c-1'));

        $this->repository->shouldReceive('findById')->once()->with('c-1')->andReturn($classification);
        $this->repository->shouldReceive('update')->once()->with($classification, ['points' => 18])->andReturn($classification);
        $this->logger->shouldReceive('info')->once()->with('Team classification updated', ['team_classification_id' => 'c-1']);
        $this->assertSame($classification, $this->service->update('c-1', ['points' => 18]));

        $this->repository->shouldReceive('findById')->once()->with('c-404')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Team classification not found for update', ['team_classification_id' => 'c-404']);
        $this->assertNull($this->service->update('c-404', ['points' => 18]));

        $this->repository->shouldReceive('findById')->once()->with('c-1')->andReturn($classification);
        $this->repository->shouldReceive('delete')->once()->with($classification)->andReturnTrue();
        $this->logger->shouldReceive('info')->once()->with('Team classification deleted', ['team_classification_id' => 'c-1']);
        $this->assertTrue($this->service->delete('c-1'));

        $this->repository->shouldReceive('findById')->once()->with('c-404')->andReturnNull();
        $this->logger->shouldReceive('warning')->once()->with('Team classification not found for delete', ['team_classification_id' => 'c-404']);
        $this->assertFalse($this->service->delete('c-404'));
    }
}
