<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class BffTeamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_teams_through_team_service(): void
    {
        Passport::actingAs(User::factory()->create());

        Http::fake([
            '*team-service:8001/teams*' => Http::response([
                'data' => [['id' => 't1', 'name' => 'Tigers', 'active' => true]],
            ], 200),
        ]);

        $this->getJson('/api/teams')
            ->assertOk()
            ->assertJsonPath('data.0.id', 't1');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/teams'));
    }

    public function test_creates_team_through_team_service(): void
    {
        Passport::actingAs(User::factory()->create());

        Http::fake([
            '*team-service:8001/teams' => Http::response(['id' => 't1', 'name' => 'Tigers', 'active' => true], 201),
        ]);

        $this->postJson('/api/teams', ['name' => 'Tigers'])
            ->assertCreated()
            ->assertJsonPath('name', 'Tigers');
    }

    public function test_legacy_team_alias_is_read_only(): void
    {
        Passport::actingAs(User::factory()->create());

        Http::fake([
            '*team-service:8001/teams*' => Http::response(['data' => []], 200),
        ]);

        $this->getJson('/api/team')->assertOk();
        $this->postJson('/api/team', ['name' => 'Tigers'])->assertStatus(405);
    }
}
