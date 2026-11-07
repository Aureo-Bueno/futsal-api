<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\PlayersStoreRequest;
use App\Http\Requests\PlayersUpdateRequest;
use App\Http\Requests\TeamClassificationStoreRequest;
use App\Http\Requests\TeamClassificationUpdateRequest;
use App\Http\Requests\TeamMatchStoreRequest;
use App\Http\Requests\TeamMatchUpdateRequest;
use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use PHPUnit\Framework\TestCase;

class FormRequestsTest extends TestCase
{
    public function test_auth_login_request_rules(): void
    {
        $request = new AuthLoginRequest;
        $this->assertTrue($request->authorize());
        $this->assertSame([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ], $request->rules());
    }

    public function test_team_requests_rules(): void
    {
        $store = new TeamStoreRequest;
        $this->assertTrue($store->authorize());
        $this->assertSame(['name' => 'required|string|max:255'], $store->rules());

        $update = new TeamUpdateRequest;
        $this->assertTrue($update->authorize());
        $this->assertSame([
            'name' => 'required|string|max:255',
            'team_match_id' => 'required|uuid|exists:team_match,id',
        ], $update->rules());
    }

    public function test_players_requests_rules(): void
    {
        $expected = [
            'name' => 'required|string|max:255',
            'jersey_number' => 'required|integer|min:0|max:99',
            'team_id' => 'required|uuid|exists:teams,id',
        ];

        $store = new PlayersStoreRequest;
        $this->assertTrue($store->authorize());
        $this->assertSame($expected, $store->rules());

        $update = new PlayersUpdateRequest;
        $this->assertTrue($update->authorize());
        $this->assertSame($expected, $update->rules());
    }

    public function test_team_match_requests_rules(): void
    {
        $expected = [
            'date_team_match' => 'required|date',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'scoreboard' => 'required|integer|min:0',
        ];

        $store = new TeamMatchStoreRequest;
        $this->assertTrue($store->authorize());
        $this->assertSame($expected, $store->rules());

        $update = new TeamMatchUpdateRequest;
        $this->assertTrue($update->authorize());
        $this->assertSame($expected, $update->rules());
    }

    public function test_team_classification_requests_rules(): void
    {
        $expected = [
            'team_id' => 'required|uuid|exists:teams,id',
            'points' => 'required|integer|min:0',
            'number_of_goals' => 'required|integer|min:0',
        ];

        $store = new TeamClassificationStoreRequest;
        $this->assertTrue($store->authorize());
        $this->assertSame($expected, $store->rules());

        $update = new TeamClassificationUpdateRequest;
        $this->assertTrue($update->authorize());
        $this->assertSame($expected, $update->rules());
    }
}
