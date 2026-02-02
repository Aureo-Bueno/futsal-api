<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamClassificationStoreRequest;
use App\Http\Requests\TeamClassificationUpdateRequest;
use App\Http\Resources\TeamClassificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Services\Contracts\TeamClassificationServiceInterface;

/**
 * Handle team classification endpoints.
 */
class TeamClassificationController extends Controller
{
  public function __construct(
    private readonly TeamClassificationServiceInterface $teamClassificationService
  ) {
  }

  /**
   * List team classifications ordered by points.
   *
   * @OA\Get(
   *   path="/api/teamClassification",
   *   tags={"TeamClassification"},
   *   summary="List team classification",
   *   security={{"bearerAuth":{}}},
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
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

  /**
   * Get a team classification by id.
   *
   * @OA\Get(
   *   path="/api/teamClassification/{id}",
   *   tags={"TeamClassification"},
   *   summary="Get team classification",
   *   security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="string")
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=404, description="Not Found"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function show(string $id): JsonResponse
  {
    $classification = $this->teamClassificationService->get($id);
    if (!$classification) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new TeamClassificationResource($classification))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * Create a team classification.
   *
   * @OA\Post(
   *   path="/api/teamClassification",
   *   tags={"TeamClassification"},
   *   summary="Create team classification",
   *   security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\JsonContent(
   *       required={"team_id","points","number_of_goals"},
   *       @OA\Property(property="team_id", type="string", format="uuid"),
   *       @OA\Property(property="points", type="integer"),
   *       @OA\Property(property="number_of_goals", type="integer")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=422, description="Validation error")
   * )
   */
  public function store(TeamClassificationStoreRequest $request): JsonResponse
  {
    $validated = $request->validated();

    $classification = $this->teamClassificationService->create($validated);

    return (new TeamClassificationResource($classification))
      ->additional(['status' => 201])
      ->response()
      ->setStatusCode(201);
  }

  /**
   * Update a team classification.
   *
   * @OA\Put(
   *   path="/api/teamClassification/{id}",
   *   tags={"TeamClassification"},
   *   summary="Update team classification",
   *   security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="string")
   *   ),
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\JsonContent(
   *       required={"team_id","points","number_of_goals"},
   *       @OA\Property(property="team_id", type="string", format="uuid"),
   *       @OA\Property(property="points", type="integer"),
   *       @OA\Property(property="number_of_goals", type="integer")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=404, description="Not Found")
   * )
   */
  public function update(TeamClassificationUpdateRequest $request, string $id): JsonResponse
  {
    $validated = $request->validated();

    $classification = $this->teamClassificationService->update($id, $validated);
    if (!$classification) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new TeamClassificationResource($classification))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * Delete a team classification.
   *
   * @OA\Delete(
   *   path="/api/teamClassification/{id}",
   *   tags={"TeamClassification"},
   *   summary="Delete team classification",
   *   security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="string")
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=404, description="Not Found"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function destroy(string $id): JsonResponse
  {
    $deleted = $this->teamClassificationService->delete($id);
    if (!$deleted) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
  }
}
