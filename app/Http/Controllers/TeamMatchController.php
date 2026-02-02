<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamMatchStoreRequest;
use App\Http\Requests\TeamMatchUpdateRequest;
use App\Http\Resources\TeamMatchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Services\Contracts\TeamMatchServiceInterface;


/**
 * Handle team match endpoints.
 */
class TeamMatchController extends Controller
{
  public function __construct(
    private readonly TeamMatchServiceInterface $teamMatchService
  ) {
  }

  /**
   * List team matches.
   *
   * @OA\Get(
   *   path="/api/teamMatch",
   *   tags={"TeamMatch"},
   *   summary="List team matches",
   *   security={{"bearerAuth":{}}},
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function index(Request $request): JsonResponse
  {
    $perPage = (int) $request->query('per_page', 15);
    $perPage = max(1, min(100, $perPage));

    $teamMatches = $this->teamMatchService->paginate($perPage);
    return TeamMatchResource::collection($teamMatches)->additional(['status' => 200])->response()->setStatusCode(200);
  }

  /**
   * Get a team match by id.
   *
   * @OA\Get(
   *   path="/api/teamMatch/{id}",
   *   tags={"TeamMatch"},
   *   summary="Get team match",
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
    $teamMatch = $this->teamMatchService->get($id);
    if (!$teamMatch) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new TeamMatchResource($teamMatch))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * Create a team match.
   *
   * @OA\Post(
   *   path="/api/teamMatch",
   *   tags={"TeamMatch"},
   *   summary="Create team match",
   *   security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\JsonContent(
   *       required={"date_team_match","start_time","end_time","scoreboard"},
   *       @OA\Property(property="date_team_match", type="string", format="date"),
   *       @OA\Property(property="start_time", type="string"),
   *       @OA\Property(property="end_time", type="string"),
   *       @OA\Property(property="scoreboard", type="integer")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=422, description="Validation error")
   * )
   */
  public function store(TeamMatchStoreRequest $request): JsonResponse
  {
    $validated = $request->validated();

    $team_match = $this->teamMatchService->create($validated);

    return (new TeamMatchResource($team_match))
      ->additional(['status' => 201])
      ->response()
      ->setStatusCode(201);
  }

  /**
   * Update a team match.
   *
   * @OA\Put(
   *   path="/api/teamMatch/{id}",
   *   tags={"TeamMatch"},
   *   summary="Update team match",
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
   *       required={"date_team_match","start_time","end_time","scoreboard"},
   *       @OA\Property(property="date_team_match", type="string", format="date"),
   *       @OA\Property(property="start_time", type="string"),
   *       @OA\Property(property="end_time", type="string"),
   *       @OA\Property(property="scoreboard", type="integer")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=404, description="Not Found")
   * )
   */
  public function update(TeamMatchUpdateRequest $request, string $id): JsonResponse
  {
    $validated = $request->validated();

    $team_match = $this->teamMatchService->update($id, $validated);
    if (!$team_match) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new TeamMatchResource($team_match))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * Delete a team match.
   *
   * @OA\Delete(
   *   path="/api/teamMatch/{id}",
   *   tags={"TeamMatch"},
   *   summary="Delete team match",
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
    $deleted = $this->teamMatchService->delete($id);
    if (!$deleted) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
  }
}
