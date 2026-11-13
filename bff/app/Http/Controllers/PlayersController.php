<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TeamServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayersController extends Controller
{
    public function __construct(private readonly TeamServiceClient $client) {}

    public function index(Request $request): JsonResponse
    {
        // Players listing not provided by team-service API; return empty for compatibility
        return response()->json(['data' => []], 200);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Not Found'], 404);
    }

    public function store(Request $request, string $teamId): JsonResponse
    {
        $response = $this->client->storePlayer($teamId, $request->all(), $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function transfer(Request $request, string $id): JsonResponse
    {
        $response = $this->client->transferPlayer($id, $request->all(), $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $response = $this->client->deletePlayer($id, $request->user());

        if ($response->status() === 204) {
            return response()->json(null, 204);
        }

        return response()->json($response->json(), $response->status());
    }
}
