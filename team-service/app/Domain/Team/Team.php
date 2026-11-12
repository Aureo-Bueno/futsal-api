<?php

declare(strict_types=1);

namespace App\Domain\Team;

use App\Domain\Team\Exceptions\TeamDomainException;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\AggregateRoot;
use Futsal\EventSourcing\DomainEvent;

final class Team extends AggregateRoot
{
    private string $id = '';

    private string $name = '';

    private bool $active = true;

    public static function create(string $id, string $name): self
    {
        $team = new self;
        $team->record(new DomainEvent(EventType::TEAM_CREATED, ['team_id' => $id, 'name' => $name]));

        return $team;
    }

    /** @param DomainEvent[] $events */
    public static function reconstitute(array $events): self
    {
        $team = new self;
        $team->replay($events);

        return $team;
    }

    public function aggregateId(): string
    {
        return $this->id;
    }

    public function aggregateType(): string
    {
        return 'team';
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function rename(string $name): void
    {
        if (! $this->active) {
            throw new TeamDomainException('Inactive team cannot be renamed');
        }

        if (trim($name) === '') {
            throw new TeamDomainException('Team name is required');
        }

        $this->record(new DomainEvent(EventType::TEAM_UPDATED, [
            'team_id' => $this->id,
            'name' => $name,
        ]));
    }

    public function deactivate(): void
    {
        if (! $this->active) {
            return;
        }

        $this->record(new DomainEvent(EventType::TEAM_DEACTIVATED, ['team_id' => $this->id]));
    }

    protected function apply(DomainEvent $event): void
    {
        switch ($event->eventType) {
            case EventType::TEAM_CREATED:
                $this->id = (string) $event->payload['team_id'];
                $this->name = (string) $event->payload['name'];
                $this->active = true;
                break;
            case EventType::TEAM_UPDATED:
                $this->name = (string) $event->payload['name'];
                break;
            case EventType::TEAM_DEACTIVATED:
                $this->active = false;
                break;
            default:
                throw new \LogicException('Unknown team event: '.$event->eventType);
        }
    }
}
