<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Resources\TeamResource;
use App\Services\Contracts\TeamServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamServiceInterface $teamService
    ) {}

    #[OA\Get(
        path: '/api/team',
        summary: 'List teams',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $teams = $this->teamService->paginate($perPage);

        return TeamResource::collection($teams)->additional(['status' => 200])->response()->setStatusCode(200);
    }

    #[OA\Get(
        path: '/api/team/{id}',
        summary: 'Get team',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
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
        $team = $this->teamService->get($id);
        if (! $team) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new TeamResource($team))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Post(
        path: '/api/team',
        summary: 'Create team',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(TeamStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $team = $this->teamService->create($validated);

        return (new TeamResource($team))
            ->additional(['status' => 201])
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/team/{id}',
        summary: 'Update team',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'team_match_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'team_match_id', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not Found'),
        ]
    )]
    public function update(TeamUpdateRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $team = $this->teamService->update($id, $validated);
        if (! $team) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new TeamResource($team))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Delete(
        path: '/api/team/{id}',
        summary: 'Delete team',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
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
        $deleted = $this->teamService->delete($id);
        if (! $deleted) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
    }
}
