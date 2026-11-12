<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlayerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_player_and_rejects_duplicate_jersey(): void
    {
        $teamBody = json_encode(['name' => 'Tigers']);
        $teamId = $this->postJson('/teams', ['name' => 'Tigers'], $this->internalHeaders('POST', '/teams', $teamBody))->json('id');

        $body = json_encode(['name' => 'John', 'jersey_number' => 10]);
        $this->postJson("/teams/{$teamId}/players", [
            'name' => 'John',
            'jersey_number' => 10,
        ], $this->internalHeaders('POST', "/teams/{$teamId}/players", $body))->assertCreated();

        $duplicateBody = json_encode(['name' => 'Doe', 'jersey_number' => 10]);
        $this->postJson("/teams/{$teamId}/players", [
            'name' => 'Doe',
            'jersey_number' => 10,
        ], $this->internalHeaders('POST', "/teams/{$teamId}/players", $duplicateBody))->assertStatus(422);

        $this->assertDatabaseHas('players', ['team_id' => $teamId, 'jersey_number' => 10]);
    }
}
