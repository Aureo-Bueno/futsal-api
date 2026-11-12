<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TeamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_team_persists_event_outbox_and_read_model(): void
    {
        $body = json_encode(['name' => 'Tigers']);

        $response = $this->postJson('/teams', ['name' => 'Tigers'], $this->internalHeaders('POST', '/teams', $body));

        $response->assertCreated()->assertJsonPath('name', 'Tigers');

        $teamId = $response->json('id');

        $this->assertDatabaseHas('teams', ['id' => $teamId, 'name' => 'Tigers']);
        $this->assertDatabaseHas('domain_events', [
            'aggregate_type' => 'team',
            'aggregate_id' => $teamId,
            'event_type' => 'team.created',
        ]);
        $this->assertDatabaseHas('outbox_events', ['status' => 'pending']);
    }

    public function test_renames_team(): void
    {
        $createBody = json_encode(['name' => 'Tigers']);
        $id = $this->postJson('/teams', ['name' => 'Tigers'], $this->internalHeaders('POST', '/teams', $createBody))->json('id');

        $body = json_encode(['name' => 'Lions']);
        $this->putJson("/teams/{$id}", ['name' => 'Lions'], $this->internalHeaders('PUT', "/teams/{$id}", $body))
            ->assertOk()
            ->assertJsonPath('name', 'Lions');

        $this->assertDatabaseHas('teams', ['id' => $id, 'name' => 'Lions']);
    }

    public function test_deactivates_team(): void
    {
        $createBody = json_encode(['name' => 'Tigers']);
        $id = $this->postJson('/teams', ['name' => 'Tigers'], $this->internalHeaders('POST', '/teams', $createBody))->json('id');

        $body = json_encode([]);
        $this->deleteJson("/teams/{$id}", [], $this->internalHeaders('DELETE', "/teams/{$id}", $body))
            ->assertNoContent();

        $this->assertDatabaseHas('teams', ['id' => $id, 'active' => false]);
    }

    public function test_rejects_request_without_internal_signature(): void
    {
        $this->postJson('/teams', ['name' => 'Tigers'])->assertUnauthorized();
    }
}
