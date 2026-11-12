<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Team\Handlers\SyncKnownTeamsHandler;
use DateTimeImmutable;
use Futsal\Contracts\EventEnvelope;
use Futsal\Contracts\EventType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SyncKnownTeamsHandlerTest extends TestCase
{
    use RefreshDatabase;

    private function envelope(string $type, array $payload, string $eventId): EventEnvelope
    {
        return new EventEnvelope(
            eventId: $eventId,
            eventType: $type,
            eventVersion: 1,
            aggregateType: 'team',
            aggregateId: $payload['team_id'],
            aggregateVersion: 1,
            payload: $payload,
            metadata: [],
            occurredAt: new DateTimeImmutable,
            producer: 'team-service',
        );
    }

    public function test_projects_team_lifecycle_and_is_idempotent(): void
    {
        $teamId = (string) Str::uuid();
        $eventId = (string) Str::uuid();
        $handler = $this->app->make(SyncKnownTeamsHandler::class);

        $handler->handle($this->envelope(EventType::TEAM_CREATED, ['team_id' => $teamId, 'name' => 'Tigers'], $eventId));

        $this->assertDatabaseHas('known_teams', ['id' => $teamId, 'name' => 'Tigers', 'active' => true]);

        $handler->handle($this->envelope(EventType::TEAM_CREATED, ['team_id' => $teamId, 'name' => 'Tigers'], $eventId));

        $this->assertSame(1, \DB::table('known_teams')->where('id', $teamId)->count());
        $this->assertSame(1, \DB::table('processed_events')->where('event_id', $eventId)->count());
    }

    public function test_updates_and_deactivates_team(): void
    {
        $teamId = (string) Str::uuid();
        $handler = $this->app->make(SyncKnownTeamsHandler::class);

        $handler->handle($this->envelope(EventType::TEAM_CREATED, ['team_id' => $teamId, 'name' => 'Tigers'], (string) Str::uuid()));
        $handler->handle($this->envelope(EventType::TEAM_UPDATED, ['team_id' => $teamId, 'name' => 'Lions'], (string) Str::uuid()));
        $handler->handle($this->envelope(EventType::TEAM_DEACTIVATED, ['team_id' => $teamId], (string) Str::uuid()));

        $this->assertDatabaseHas('known_teams', ['id' => $teamId, 'name' => 'Lions', 'active' => false]);
    }
}
