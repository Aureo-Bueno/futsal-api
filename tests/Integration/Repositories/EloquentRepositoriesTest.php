<?php

namespace Tests\Integration\Repositories;

use App\Models\Team;
use App\Models\User;
use App\Repositories\Eloquent\EloquentPlayersRepository;
use App\Repositories\Eloquent\EloquentTeamClassificationRepository;
use App\Repositories\Eloquent\EloquentTeamMatchRepository;
use App\Repositories\Eloquent\EloquentTeamRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EloquentRepositoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_eloquent_team_repository_crud_and_soft_deletes(): void
    {
        $repo = new EloquentTeamRepository;

        $team = $repo->create(['name' => 'Corinthians Futsal']);
        $this->assertNotEmpty($team->id);
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Corinthians Futsal']);

        $found = $repo->findById($team->id);
        $this->assertNotNull($found);
        $this->assertSame('Corinthians Futsal', $found->name);
        $this->assertTrue($found->relationLoaded('players'));
        $this->assertTrue($found->relationLoaded('teamMatch'));

        $updated = $repo->update($team, ['name' => 'Magnus Futsal']);
        $this->assertSame('Magnus Futsal', $updated->name);
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Magnus Futsal']);

        $this->assertCount(1, $repo->all());
        $this->assertSame(1, $repo->paginate(10)->total());

        $this->assertTrue($repo->delete($team));
        $this->assertSoftDeleted('teams', ['id' => $team->id]);
        $this->assertNull($repo->findById($team->id));
    }

    public function test_eloquent_players_repository_crud_and_soft_deletes(): void
    {
        $team = Team::factory()->create();
        $repo = new EloquentPlayersRepository;

        $player = $repo->create([
            'name' => 'Falcao',
            'jersey_number' => 12,
            'team_id' => $team->id,
        ]);

        $foundPlayer = $repo->findById($player->id);
        $this->assertNotNull($foundPlayer);
        $this->assertTrue($foundPlayer->relationLoaded('team'));
        $this->assertCount(1, $repo->all());
        $this->assertSame(1, $repo->paginate(10)->total());

        $updated = $repo->update($player, ['jersey_number' => 10]);
        $this->assertSame(10, $updated->jersey_number);

        $this->assertTrue($repo->delete($player));
        $this->assertSoftDeleted('players', ['id' => $player->id]);
        $this->assertNull($repo->findById($player->id));
    }

    public function test_eloquent_team_match_repository_crud_and_soft_deletes(): void
    {
        $repo = new EloquentTeamMatchRepository;

        $match = $repo->create([
            'date_team_match' => '2026-08-20',
            'start_time' => '20:00',
            'end_time' => '21:30',
            'scoreboard' => '3',
        ]);

        $foundMatch = $repo->findById($match->id);
        $this->assertNotNull($foundMatch);
        $this->assertTrue($foundMatch->relationLoaded('teams'));
        $this->assertCount(1, $repo->all());
        $this->assertSame(1, $repo->paginate(10)->total());

        $updated = $repo->update($match, ['scoreboard' => '5']);
        $this->assertSame('5', $updated->scoreboard);

        $this->assertTrue($repo->delete($match));
        $this->assertSoftDeleted('team_match', ['id' => $match->id]);
        $this->assertNull($repo->findById($match->id));
    }

    public function test_eloquent_team_classification_repository_orders_by_points_descending(): void
    {
        $teamA = Team::factory()->create(['name' => 'Team A']);
        $teamB = Team::factory()->create(['name' => 'Team B']);
        $teamC = Team::factory()->create(['name' => 'Team C']);
        $teamD = Team::factory()->create(['name' => 'Team D']);

        $repo = new EloquentTeamClassificationRepository;

        $low = $repo->create(['team_id' => $teamA->id, 'points' => 10, 'number_of_goals' => 5]);
        $high = $repo->create(['team_id' => $teamB->id, 'points' => 30, 'number_of_goals' => 25]);
        $midLowerGoals = $repo->create(['team_id' => $teamC->id, 'points' => 20, 'number_of_goals' => 15]);
        $midHigherGoals = $repo->create(['team_id' => $teamD->id, 'points' => 20, 'number_of_goals' => 22]);

        $ordered = $repo->orderedByPoints();
        $this->assertTrue(Cache::has(EloquentTeamClassificationRepository::STANDINGS_CACHE_KEY));
        $this->assertSame(
            [$high->id, $midHigherGoals->id, $midLowerGoals->id, $low->id],
            $ordered->pluck('id')->all()
        );
        $this->assertTrue($ordered->first()->relationLoaded('team'));

        $paginated = $repo->paginate(2);
        $this->assertSame(4, $paginated->total());
        $this->assertSame([$high->id, $midHigherGoals->id], collect($paginated->items())->pluck('id')->all());

        $updated = $repo->update($low, ['points' => 40]);
        $this->assertFalse(Cache::has(EloquentTeamClassificationRepository::STANDINGS_CACHE_KEY));
        $this->assertSame(40, $updated->points);

        // Re-warm cache and verify delete invalidates it
        $repo->orderedByPoints();
        $this->assertTrue(Cache::has(EloquentTeamClassificationRepository::STANDINGS_CACHE_KEY));
        $this->assertTrue($repo->delete($low));
        $this->assertFalse(Cache::has(EloquentTeamClassificationRepository::STANDINGS_CACHE_KEY));
        $this->assertSoftDeleted('team_classification', ['id' => $low->id]);
    }

    public function test_eloquent_user_repository_finds_by_id_and_email(): void
    {
        $user = User::factory()->create(['email' => 'coach@futsal.com']);
        $repo = new EloquentUserRepository;

        $byId = $repo->findById($user->id);
        $byEmail = $repo->findByEmail('coach@futsal.com');

        $this->assertNotNull($byId);
        $this->assertNotNull($byEmail);
        $this->assertSame($user->id, $byId->id);
        $this->assertSame($user->id, $byEmail->id);
        $this->assertNull($repo->findByEmail('nonexistent@futsal.com'));
    }
}
