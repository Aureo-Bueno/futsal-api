<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Match;

use App\Domain\Match\Exceptions\MatchDomainException;
use App\Domain\Match\MatchAggregate;
use Futsal\Contracts\EventType;
use Futsal\EventSourcing\DomainEvent;
use PHPUnit\Framework\TestCase;

final class MatchAggregateTest extends TestCase
{
    private function scheduled(): MatchAggregate
    {
        $match = MatchAggregate::create('m1', 'home', 'away', '2026-11-12T20:00:00+00:00');
        $match->clearPendingEvents();

        return $match;
    }

    public function test_creates_match(): void
    {
        $match = MatchAggregate::create('m1', 'home', 'away', '2026-11-12T20:00:00+00:00');

        $this->assertSame('m1', $match->id());
        $this->assertSame('scheduled', $match->status());
        $this->assertSame(EventType::MATCH_SCHEDULED, $match->pendingEvents()[0]->eventType);
    }

    public function test_rejects_same_team(): void
    {
        $this->expectException(MatchDomainException::class);

        MatchAggregate::create('m1', 'same', 'same', '2026-11-12T20:00:00+00:00');
    }

    public function test_starts_match(): void
    {
        $match = $this->scheduled();
        $match->start();

        $this->assertSame('in_progress', $match->status());
        $this->assertSame(EventType::MATCH_STARTED, $match->pendingEvents()[0]->eventType);
    }

    public function test_scores_goal_for_home_team(): void
    {
        $match = $this->scheduled();
        $match->start();
        $match->clearPendingEvents();
        $match->scoreGoal('home', 'p1', 7);

        $this->assertSame(1, $match->homeScore());
        $this->assertSame(0, $match->awayScore());
        $this->assertSame(EventType::MATCH_GOAL_SCORED, $match->pendingEvents()[0]->eventType);
    }

    public function test_own_goal_counts_for_opponent(): void
    {
        $match = $this->scheduled();
        $match->start();
        $match->scoreGoal('away', 'p1', 9, true);

        $this->assertSame(1, $match->homeScore());
        $this->assertSame(0, $match->awayScore());
    }

    public function test_rejects_goal_when_not_live(): void
    {
        $match = $this->scheduled();

        $this->expectException(MatchDomainException::class);
        $match->scoreGoal('home', 'p1', 7);
    }

    public function test_rejects_goal_from_unknown_team(): void
    {
        $match = $this->scheduled();
        $match->start();

        $this->expectException(MatchDomainException::class);
        $match->scoreGoal('other', 'p1', 7);
    }

    public function test_rejects_invalid_minute(): void
    {
        $match = $this->scheduled();
        $match->start();

        $this->expectException(MatchDomainException::class);
        $match->scoreGoal('home', 'p1', 41);
    }

    public function test_finishes_live_match(): void
    {
        $match = $this->scheduled();
        $match->start();
        $match->clearPendingEvents();
        $match->finish();

        $this->assertSame('finished', $match->status());
        $this->assertSame(EventType::MATCH_FINISHED, $match->pendingEvents()[0]->eventType);
    }

    public function test_cannot_cancel_finished_match(): void
    {
        $match = $this->scheduled();
        $match->start();
        $match->finish();

        $this->expectException(MatchDomainException::class);
        $match->cancel('weather');
    }

    public function test_reconstitutes_from_events(): void
    {
        $match = MatchAggregate::reconstitute([
            new DomainEvent(EventType::MATCH_SCHEDULED, [
                'match_id' => 'm1', 'home_team_id' => 'home', 'away_team_id' => 'away',
                'scheduled_at' => '2026-11-12T20:00:00+00:00',
            ]),
            new DomainEvent(EventType::MATCH_STARTED, ['match_id' => 'm1']),
            new DomainEvent(EventType::MATCH_GOAL_SCORED, [
                'match_id' => 'm1', 'scoring_team_id' => 'home', 'player_id' => 'p1',
                'minute' => 7, 'own_goal' => false,
            ]),
        ]);

        $this->assertSame('in_progress', $match->status());
        $this->assertSame(1, $match->homeScore());
    }
}
