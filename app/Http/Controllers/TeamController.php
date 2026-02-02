<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Resources\TeamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Services\Contracts\TeamServiceInterface;

/**
 * Handle team endpoints.
 */
class TeamController extends Controller
{
  public function __construct(
    private readonly TeamServiceInterface $teamService
  ) {
  }

  /**
   * List teams.
   *
   * @OA\Get(
   *   path="/api/team",
   *   tags={"Teams"},
   *   summary="List teams",
   *   security={{"bearerAuth":{}}},
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function index(Request $request): JsonResponse
  {
    $perPage = (int) $request->query('per_page', 15);
    $perPage = max(1, min(100, $perPage));

    $teams = $this->teamService->paginate($perPage);
    return TeamResource::collection($teams)->additional(['status' => 200])->response()->setStatusCode(200);
  }

  /**
   * Get a team by id.
   *
   * @OA\Get(
   *   path="/api/team/{id}",
   *   tags={"Teams"},
   *   summary="Get team",
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
    $team = $this->teamService->get($id);
    if (!$team) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new TeamResource($team))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * Create a team.
   *
   * @OA\Post(
   *   path="/api/team",
   *   tags={"Teams"},
   *   summary="Create team",
   *   security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\JsonContent(
   *       required={"name"},
   *       @OA\Property(property="name", type="string")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=422, description="Validation error")
   * )
   */
  public function store(TeamStoreRequest $request): JsonResponse
  {
    $validated = $request->validated();

    $team = $this->teamService->create($validated);

    return (new TeamResource($team))
      ->additional(['status' => 201])
      ->response()
      ->setStatusCode(201);
  }

  /**
   * Update a team.
   *
   * @OA\Put(
   *   path="/api/team/{id}",
   *   tags={"Teams"},
   *   summary="Update team",
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
   *       required={"name","team_match_id"},
   *       @OA\Property(property="name", type="string"),
   *       @OA\Property(property="team_match_id", type="string", format="uuid")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function update(TeamUpdateRequest $request, $id): JsonResponse
  {
    $validated = $request->validated();

    $team = $this->teamService->update($id, $validated);
    if (!$team) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new TeamResource($team))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);

  }

  /**
   * Delete a team.
   *
   * @OA\Delete(
   *   path="/api/team/{id}",
   *   tags={"Teams"},
   *   summary="Delete team",
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
    $deleted = $this->teamService->delete($id);
    if (!$deleted) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
  }
}
