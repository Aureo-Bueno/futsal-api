<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TeamClassificationStoreRequest;
use App\Http\Requests\TeamClassificationUpdateRequest;
use App\Http\Resources\TeamClassificationResource;
use App\Services\Contracts\TeamClassificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TeamClassificationController extends Controller
{
    public function __construct(
        private readonly TeamClassificationServiceInterface $teamClassificationService
    ) {}

    #[OA\Get(
        path: '/api/teamClassification',
        summary: 'List team classification',
        security: [['bearerAuth' => []]],
        tags: ['TeamClassification'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $teamClassifications = $this->teamClassificationService->paginate($perPage);

        return TeamClassificationResource::collection($teamClassifications)
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Get(
        path: '/api/teamClassification/{id}',
        summary: 'Get team classification',
        security: [['bearerAuth' => []]],
        tags: ['TeamClassification'],
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
        $classification = $this->teamClassificationService->get($id);
        if (! $classification) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new TeamClassificationResource($classification))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Post(
        path: '/api/teamClassification',
        summary: 'Create team classification',
        security: [['bearerAuth' => []]],
        tags: ['TeamClassification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['team_id', 'points', 'number_of_goals'],
                properties: [
                    new OA\Property(property: 'team_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'points', type: 'integer'),
                    new OA\Property(property: 'number_of_goals', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(TeamClassificationStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $classification = $this->teamClassificationService->create($validated);

        return (new TeamClassificationResource($classification))
            ->additional(['status' => 201])
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/teamClassification/{id}',
        summary: 'Update team classification',
        security: [['bearerAuth' => []]],
        tags: ['TeamClassification'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['team_id', 'points', 'number_of_goals'],
                properties: [
                    new OA\Property(property: 'team_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'points', type: 'integer'),
                    new OA\Property(property: 'number_of_goals', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not Found'),
        ]
    )]
    public function update(TeamClassificationUpdateRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $classification = $this->teamClassificationService->update($id, $validated);
        if (! $classification) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return (new TeamClassificationResource($classification))
            ->additional(['status' => 200])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Delete(
        path: '/api/teamClassification/{id}',
        summary: 'Delete team classification',
        security: [['bearerAuth' => []]],
        tags: ['TeamClassification'],
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
        $deleted = $this->teamClassificationService->delete($id);
        if (! $deleted) {
            return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
        }

        return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
    }
}
