<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\PlayerResource;
use App\Http\Resources\TeamClassificationResource;
use App\Http\Resources\TeamMatchResource;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Models\Players;
use App\Models\Team;
use App\Models\TeamClassification;
use App\Models\TeamMatch;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ResourcesTest extends TestCase
{
    public function test_team_resource_transforms_expected_fields(): void
    {
        $team = new Team([
            'id' => 'team-uuid',
            'name' => 'Lions Futsal',
            'team_match_id' => 'match-uuid',
        ]);

        $array = (new TeamResource($team))->toArray(Request::create('/api/team'));

        $this->assertSame('team-uuid', $array['id']);
        $this->assertSame('Lions Futsal', $array['name']);
        $this->assertSame('match-uuid', $array['team_match_id']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function test_player_resource_transforms_expected_fields(): void
    {
        $player = new Players([
            'id' => 'player-uuid',
            'name' => 'Falcao',
            'jersey_number' => 12,
            'team_id' => 'team-uuid',
        ]);

        $array = (new PlayerResource($player))->toArray(Request::create('/api/player'));

        $this->assertSame('player-uuid', $array['id']);
        $this->assertSame('Falcao', $array['name']);
        $this->assertSame(12, $array['jersey_number']);
        $this->assertSame('team-uuid', $array['team_id']);
    }

    public function test_team_match_resource_transforms_expected_fields(): void
    {
        $match = new TeamMatch([
            'id' => 'match-uuid',
            'date_team_match' => '2026-06-15',
            'start_time' => '18:00',
            'end_time' => '19:30',
            'scoreboard' => '4',
        ]);

        $array = (new TeamMatchResource($match))->toArray(Request::create('/api/teamMatch'));

        $this->assertSame('match-uuid', $array['id']);
        $this->assertSame('2026-06-15', $array['date_team_match']);
        $this->assertSame('18:00', $array['start_time']);
        $this->assertSame('19:30', $array['end_time']);
        $this->assertSame('4', $array['scoreboard']);
    }

    public function test_team_classification_resource_transforms_expected_fields(): void
    {
        $classification = new TeamClassification([
            'id' => 'class-uuid',
            'team_id' => 'team-uuid',
            'points' => 25,
            'number_of_goals' => 40,
        ]);

        $array = (new TeamClassificationResource($classification))->toArray(Request::create('/api/teamClassification'));

        $this->assertSame('class-uuid', $array['id']);
        $this->assertSame('team-uuid', $array['team_id']);
        $this->assertSame(25, $array['points']);
        $this->assertSame(40, $array['number_of_goals']);
    }

    public function test_user_resource_transforms_expected_fields(): void
    {
        $user = new User([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
        ]);
        $user->id = 1;

        $array = (new UserResource($user))->toArray(Request::create('/api/user'));

        $this->assertSame(1, $array['id']);
        $this->assertSame('Admin User', $array['name']);
        $this->assertSame('admin@admin.com', $array['email']);
        $this->assertArrayHasKey('lastLogin', $array);
    }
}
