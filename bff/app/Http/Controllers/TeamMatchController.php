<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MatchServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMatchController extends Controller
{
    public function __construct(private readonly MatchServiceClient $client) {}

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

    public function start(Request $request, string $id): JsonResponse
    {
        $response = $this->client->start($id, $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function goal(Request $request, string $id): JsonResponse
    {
        $response = $this->client->goal($id, $request->all(), $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function finish(Request $request, string $id): JsonResponse
    {
        $response = $this->client->finish($id, $request->user());

        return response()->json($response->json(), $response->status());
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $response = $this->client->cancel($id, $request->all(), $request->user());

        return response()->json($response->json(), $response->status());
    }
}
