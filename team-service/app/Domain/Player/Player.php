<?php

declare(strict_types=1);

namespace App\Domain\Player;

use App\Domain\Player\Exceptions\PlayerDomainException;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\AggregateRoot;
use Futsal\EventSourcing\DomainEvent;

final class Player extends AggregateRoot
{
    private string $id = '';

    private string $teamId = '';

    private string $name = '';

    private int $jerseyNumber = 0;

    private bool $active = true;

    public static function register(string $id, string $teamId, string $name, int $jerseyNumber): self
    {
        if (trim($name) === '') {
            throw new PlayerDomainException('Player name is required');
        }

        if ($jerseyNumber < 1 || $jerseyNumber > 99) {
            throw new PlayerDomainException('Jersey number must be between 1 and 99');
        }

        $player = new self;
        $player->record(new DomainEvent(EventType::PLAYER_REGISTERED, [
            'player_id' => $id,
            'team_id' => $teamId,
            'name' => $name,
            'jersey_number' => $jerseyNumber,
        ]));

        return $player;
    }

    /** @param DomainEvent[] $events */
    public static function reconstitute(array $events): self
    {
        $player = new self;
        $player->replay($events);

        return $player;
    }

    public function aggregateId(): string
    {
        return $this->id;
    }

    public function aggregateType(): string
    {
        return 'player';
    }

    public function id(): string
    {
        return $this->id;
    }

    public function teamId(): string
    {
        return $this->teamId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function transferTo(string $toTeamId): void
    {
        if (! $this->active) {
            throw new PlayerDomainException('Inactive player cannot be transferred');
        }

        if ($toTeamId === $this->teamId) {
            throw new PlayerDomainException('Player is already in this team');
        }

        $this->record(new DomainEvent(EventType::PLAYER_TRANSFERRED, [
            'player_id' => $this->id,
            'from_team_id' => $this->teamId,
            'to_team_id' => $toTeamId,
        ]));
    }

    public function remove(): void
    {
        if (! $this->active) {
            return;
        }

        $this->record(new DomainEvent(EventType::PLAYER_REMOVED, [
            'player_id' => $this->id,
            'team_id' => $this->teamId,
        ]));
    }

    protected function apply(DomainEvent $event): void
    {
        switch ($event->eventType) {
            case EventType::PLAYER_REGISTERED:
                $this->id = (string) $event->payload['player_id'];
                $this->teamId = (string) $event->payload['team_id'];
                $this->name = (string) $event->payload['name'];
                $this->jerseyNumber = (int) $event->payload['jersey_number'];
                $this->active = true;
                break;
            case EventType::PLAYER_TRANSFERRED:
                $this->teamId = (string) $event->payload['to_team_id'];
                break;
            case EventType::PLAYER_REMOVED:
                $this->active = false;
                break;
            default:
                throw new \LogicException('Unknown player event: '.$event->eventType);
        }
    }
}
