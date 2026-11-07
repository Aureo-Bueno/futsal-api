<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TeamMatchStoreRequest;
use App\Http\Requests\TeamMatchUpdateRequest;
use App\Http\Resources\TeamMatchResource;
use App\Services\Contracts\TeamMatchServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TeamMatchController extends Controller
{
    public function __construct(
        private readonly TeamMatchServiceInterface $teamMatchService
    ) {}

    #[OA\Get(
        path: '/api/teamMatch',
        summary: 'List team matches',
        security: [['bearerAuth' => []]],
        tags: ['TeamMatch'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $teamMatches = $this->teamMatchService->paginate($perPage);

        return TeamMatchResource::collection($teamMatches)->additional(['status' => 200])->response()->setStatusCode(200);
    }

    #[OA\Get(
        path: '/api/teamMatch/{id}',
        summary: 'Get team match',
        security: [['bearerAuth' => []]],
        tags: ['TeamMatch'],
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
        $teamMatch = $this->teamMatchService->get($id);
        if (! $teamMatch) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new TeamMatchResource($teamMatch))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Post(
        path: '/api/teamMatch',
        summary: 'Create team match',
        security: [['bearerAuth' => []]],
        tags: ['TeamMatch'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['date_team_match', 'start_time', 'end_time', 'scoreboard'],
                properties: [
                    new OA\Property(property: 'date_team_match', type: 'string', format: 'date'),
                    new OA\Property(property: 'start_time', type: 'string'),
                    new OA\Property(property: 'end_time', type: 'string'),
                    new OA\Property(property: 'scoreboard', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(TeamMatchStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $teamMatch = $this->teamMatchService->create($validated);

        return (new TeamMatchResource($teamMatch))
            ->additional(['status' => 201])
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/teamMatch/{id}',
        summary: 'Update team match',
        security: [['bearerAuth' => []]],
        tags: ['TeamMatch'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['date_team_match', 'start_time', 'end_time', 'scoreboard'],
                properties: [
                    new OA\Property(property: 'date_team_match', type: 'string', format: 'date'),
                    new OA\Property(property: 'start_time', type: 'string'),
                    new OA\Property(property: 'end_time', type: 'string'),
                    new OA\Property(property: 'scoreboard', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not Found'),
        ]
    )]
    public function update(TeamMatchUpdateRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $teamMatch = $this->teamMatchService->update($id, $validated);
        if (! $teamMatch) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new TeamMatchResource($teamMatch))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Delete(
        path: '/api/teamMatch/{id}',
        summary: 'Delete team match',
        security: [['bearerAuth' => []]],
        tags: ['TeamMatch'],
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
        $deleted = $this->teamMatchService->delete($id);
        if (! $deleted) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
    }
}
