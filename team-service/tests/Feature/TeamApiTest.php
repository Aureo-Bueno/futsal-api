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
        $response = $this->postJson('/teams', ['name' => 'Tigers']);

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
        $id = $this->postJson('/teams', ['name' => 'Tigers'])->json('id');

        $this->putJson("/teams/{$id}", ['name' => 'Lions'])
            ->assertOk()
            ->assertJsonPath('name', 'Lions');

        $this->assertDatabaseHas('teams', ['id' => $id, 'name' => 'Lions']);
    }
}
