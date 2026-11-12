<?php

declare(strict_types=1);

namespace App\Domain\Match;

use App\Domain\Match\Exceptions\MatchDomainException;
use DateTimeImmutable;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\AggregateRoot;
use Futsal\EventSourcing\DomainEvent;

final class MatchAggregate extends AggregateRoot
{
    private string $id = '';

    private string $homeTeamId = '';

    private string $awayTeamId = '';

    private string $status = 'scheduled';

    private int $homeScore = 0;

    private int $awayScore = 0;

    private string $scheduledAt = '';

    public static function create(string $id, string $homeTeamId, string $awayTeamId, string $scheduledAt): self
    {
        if ($homeTeamId === $awayTeamId) {
            throw new MatchDomainException('Home and away teams must be different');
        }

        if ($homeTeamId === '' || $awayTeamId === '') {
            throw new MatchDomainException('Both teams are required');
        }

        new DateTimeImmutable($scheduledAt);

        $match = new self;
        $match->record(new DomainEvent(EventType::MATCH_SCHEDULED, [
            'match_id' => $id,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'scheduled_at' => $scheduledAt,
        ]));

        return $match;
    }

    /** @param DomainEvent[] $events */
    public static function reconstitute(array $events): self
    {
        $match = new self;
        $match->replay($events);

        return $match;
    }

    public function aggregateId(): string
    {
        return $this->id;
    }

    public function aggregateType(): string
    {
        return 'match';
    }

    public function id(): string
    {
        return $this->id;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function homeScore(): int
    {
        return $this->homeScore;
    }

    public function awayScore(): int
    {
        return $this->awayScore;
    }

    public function start(): void
    {
        if ($this->status !== 'scheduled') {
            throw new MatchDomainException('Only scheduled matches can start');
        }

        $this->record(new DomainEvent(EventType::MATCH_STARTED, ['match_id' => $this->id]));
    }

    public function scoreGoal(string $teamId, string $playerId, int $minute, bool $ownGoal = false): void
    {
        if ($this->status !== 'in_progress') {
            throw new MatchDomainException('Goals can only be scored in a live match');
        }

        if ($teamId !== $this->homeTeamId && $teamId !== $this->awayTeamId) {
            throw new MatchDomainException('Team does not play this match');
        }

        if ($minute < 1 || $minute > 40) {
            throw new MatchDomainException('Goal minute must be between 1 and 40');
        }

        $scoringTeamId = $teamId;

        if ($ownGoal) {
            $scoringTeamId = $teamId === $this->homeTeamId ? $this->awayTeamId : $this->homeTeamId;
        }

        $this->record(new DomainEvent(EventType::MATCH_GOAL_SCORED, [
            'match_id' => $this->id,
            'scoring_team_id' => $scoringTeamId,
            'player_id' => $playerId,
            'team_id' => $teamId,
            'minute' => $minute,
            'own_goal' => $ownGoal,
        ]));
    }

    public function finish(): void
    {
        if ($this->status !== 'in_progress') {
            throw new MatchDomainException('Only live matches can finish');
        }

        $this->record(new DomainEvent(EventType::MATCH_FINISHED, [
            'match_id' => $this->id,
            'home_team_id' => $this->homeTeamId,
            'away_team_id' => $this->awayTeamId,
            'home_score' => $this->homeScore,
            'away_score' => $this->awayScore,
        ]));
    }

    public function cancel(string $reason): void
    {
        if ($this->status === 'finished') {
            throw new MatchDomainException('Finished match cannot be cancelled');
        }

        if ($this->status === 'cancelled') {
            return;
        }

        if (trim($reason) === '') {
            throw new MatchDomainException('Cancellation reason is required');
        }

        $this->record(new DomainEvent(EventType::MATCH_CANCELLED, [
            'match_id' => $this->id,
            'reason' => $reason,
        ]));
    }

    protected function apply(DomainEvent $event): void
    {
        switch ($event->eventType) {
            case EventType::MATCH_SCHEDULED:
                $this->id = (string) $event->payload['match_id'];
                $this->homeTeamId = (string) $event->payload['home_team_id'];
                $this->awayTeamId = (string) $event->payload['away_team_id'];
                $this->scheduledAt = (string) $event->payload['scheduled_at'];
                $this->status = 'scheduled';
                break;
            case EventType::MATCH_STARTED:
                $this->status = 'in_progress';
                break;
            case EventType::MATCH_GOAL_SCORED:
                if ($event->payload['scoring_team_id'] === $this->homeTeamId) {
                    $this->homeScore++;
                } else {
                    $this->awayScore++;
                }
                break;
            case EventType::MATCH_FINISHED:
                $this->status = 'finished';
                break;
            case EventType::MATCH_CANCELLED:
                $this->status = 'cancelled';
                break;
            default:
                throw new \LogicException('Unknown match event: '.$event->eventType);
        }
    }
}
