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
        $teamId = $this->postJson('/teams', ['name' => 'Tigers'])->json('id');

        $this->postJson("/teams/{$teamId}/players", [
            'name' => 'John',
            'jersey_number' => 10,
        ])->assertCreated();

        $this->postJson("/teams/{$teamId}/players", [
            'name' => 'Doe',
            'jersey_number' => 10,
        ])->assertStatus(422);

        $this->assertDatabaseHas('players', ['team_id' => $teamId, 'jersey_number' => 10]);
    }
}
