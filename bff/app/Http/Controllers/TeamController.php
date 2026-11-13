<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TeamServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(private readonly TeamServiceClient $client) {}

    public function index(Request $request): JsonResponse
    {
        $response = $this->client->index($request->user());

        return response()->json($response->json(), $response->status());
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $response = $this->client->show($id, $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function store(Request $request): JsonResponse
    {
        $response = $this->client->store($request->all(), $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $response = $this->client->update($id, $request->all(), $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $response = $this->client->delete($id, $request->user());

        if ($response->status() === 204) {
            return response()->json(null, 204);
        }

        return response()->json($response->json(), $response->status());
    }
}
