<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Team;

use App\Domain\Team\Exceptions\TeamDomainException;
use App\Domain\Team\Team;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\DomainEvent;
use PHPUnit\Framework\TestCase;

final class TeamTest extends TestCase
{
    public function test_creates_team_and_records_event(): void
    {
        $team = Team::create('t1', 'Tigers');

        $this->assertSame('t1', $team->id());
        $this->assertSame('Tigers', $team->name());
        $this->assertTrue($team->isActive());
        $this->assertSame(EventType::TEAM_CREATED, $team->pendingEvents()[0]->eventType);
    }

    public function test_renames_team(): void
    {
        $team = Team::reconstitute([
            new DomainEvent(EventType::TEAM_CREATED, ['team_id' => 't1', 'name' => 'Tigers']),
        ]);

        $team->rename('Lions');

        $this->assertSame('Lions', $team->name());
        $this->assertSame(EventType::TEAM_UPDATED, $team->pendingEvents()[0]->eventType);
    }

    public function test_rejects_empty_name(): void
    {
        $team = Team::create('t1', 'Tigers');

        $this->expectException(TeamDomainException::class);
        $team->rename('   ');
    }

    public function test_deactivates_team(): void
    {
        $team = Team::reconstitute([
            new DomainEvent(EventType::TEAM_CREATED, ['team_id' => 't1', 'name' => 'Tigers']),
        ]);

        $team->deactivate();

        $this->assertFalse($team->isActive());
        $this->assertSame(EventType::TEAM_DEACTIVATED, $team->pendingEvents()[0]->eventType);
    }
}
