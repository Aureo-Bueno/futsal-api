<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StandingsServiceClient;
use App\Services\TeamServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamClassificationController extends Controller
{
    public function __construct(private readonly StandingsServiceClient $client) {}

    public function index(Request $request): JsonResponse
    {
        $response = $this->client->index($request->user());
        $data = $response->json();

        if (! is_array($data)) {
            return response()->json($data, $response->status());
        }

        $teamsResponse = app(TeamServiceClient::class)->index($request->user());
        $teams = collect($teamsResponse->json('data') ?? $teamsResponse->json() ?? [])
            ->keyBy('id');

        $enriched = collect($data)->map(function ($row) use ($teams) {
            $row['team_name'] = $teams[$row['team_id']]['name'] ?? null;

            return $row;
        })->all();

        return response()->json($enriched, $response->status());
    }
}
