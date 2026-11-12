<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PlayersStoreRequest;
use App\Http\Requests\PlayersUpdateRequest;
use App\Http\Resources\PlayerResource;
use App\Services\Contracts\PlayersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PlayersController extends Controller
{
    public function __construct(
        private readonly PlayersServiceInterface $playersService
    ) {}

    #[OA\Get(
        path: '/api/player',
        summary: 'List players',
        security: [['bearerAuth' => []]],
        tags: ['Players'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $players = $this->playersService->paginate($perPage);

        return PlayerResource::collection($players)->additional(['status' => 200])->response()->setStatusCode(200);
    }

    #[OA\Get(
        path: '/api/player/{id}',
        summary: 'Get player',
        security: [['bearerAuth' => []]],
        tags: ['Players'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Not Found'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $player = $this->playersService->get($id);
        if (! $player) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new PlayerResource($player))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Post(
        path: '/api/player',
        summary: 'Create player',
        security: [['bearerAuth' => []]],
        tags: ['Players'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'jersey_number', 'team_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'jersey_number', type: 'integer'),
                    new OA\Property(property: 'team_id', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(PlayersStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $player = $this->playersService->create($validated);

        return (new PlayerResource($player))
            ->additional(['status' => 201])
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/player/{id}',
        summary: 'Update player',
        security: [['bearerAuth' => []]],
        tags: ['Players'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'jersey_number', 'team_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'jersey_number', type: 'integer'),
                    new OA\Property(property: 'team_id', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not Found'),
        ]
    )]
    public function update(PlayersUpdateRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $player = $this->playersService->update($id, $validated);
        if (! $player) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new PlayerResource($player))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Delete(
        path: '/api/player/{id}',
        summary: 'Delete player',
        security: [['bearerAuth' => []]],
        tags: ['Players'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Not Found'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->playersService->delete($id);
        if (! $deleted) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
    }
}
