<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Player;

use App\Domain\Player\Exceptions\PlayerDomainException;
use App\Domain\Player\Player;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\DomainEvent;
use PHPUnit\Framework\TestCase;

final class PlayerTest extends TestCase
{
    public function test_registers_player(): void
    {
        $player = Player::register('p1', 't1', 'John', 10);

        $this->assertSame('p1', $player->id());
        $this->assertSame('t1', $player->teamId());
        $this->assertTrue($player->isActive());
        $this->assertSame(EventType::PLAYER_REGISTERED, $player->pendingEvents()[0]->eventType);
    }

    public function test_transfers_player(): void
    {
        $player = Player::reconstitute([
            new DomainEvent(EventType::PLAYER_REGISTERED, [
                'player_id' => 'p1', 'team_id' => 't1', 'name' => 'John', 'jersey_number' => 10,
            ]),
        ]);

        $player->transferTo('t2');

        $this->assertSame('t2', $player->teamId());
        $this->assertSame(EventType::PLAYER_TRANSFERRED, $player->pendingEvents()[0]->eventType);
    }

    public function test_removes_player(): void
    {
        $player = Player::reconstitute([
            new DomainEvent(EventType::PLAYER_REGISTERED, [
                'player_id' => 'p1', 'team_id' => 't1', 'name' => 'John', 'jersey_number' => 10,
            ]),
        ]);

        $player->remove();

        $this->assertFalse($player->isActive());
        $this->assertSame(EventType::PLAYER_REMOVED, $player->pendingEvents()[0]->eventType);
    }

    public function test_inactive_player_cannot_transfer(): void
    {
        $player = Player::reconstitute([
            new DomainEvent(EventType::PLAYER_REGISTERED, [
                'player_id' => 'p1', 'team_id' => 't1', 'name' => 'John', 'jersey_number' => 10,
            ]),
            new DomainEvent(EventType::PLAYER_REMOVED, ['player_id' => 'p1', 'team_id' => 't1']),
        ]);

        $this->expectException(PlayerDomainException::class);
        $player->transferTo('t2');
    }
}
