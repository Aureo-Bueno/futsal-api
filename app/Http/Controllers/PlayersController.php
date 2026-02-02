<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayersStoreRequest;
use App\Http\Requests\PlayersUpdateRequest;
use App\Http\Resources\PlayerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Services\Contracts\PlayersServiceInterface;

/**
 * Handle player endpoints.
 */
class PlayersController extends Controller
{
  public function __construct(
    private readonly PlayersServiceInterface $playersService
  ) {
  }

  /**
   * List players.
   *
   * @OA\Get(
   *   path="/api/player",
   *   tags={"Players"},
   *   summary="List players",
   *   security={{"bearerAuth":{}}},
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function index(Request $request): JsonResponse
  {
    $perPage = (int) $request->query('per_page', 15);
    $perPage = max(1, min(100, $perPage));

    $players = $this->playersService->paginate($perPage);
    return PlayerResource::collection($players)->additional(['status' => 200])->response()->setStatusCode(200);
  }

  /**
   * Get a player by id.
   *
   * @OA\Get(
   *   path="/api/player/{id}",
   *   tags={"Players"},
   *   summary="Get player",
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
    $player = $this->playersService->get($id);
    if (!$player) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new PlayerResource($player))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * Create a player.
   *
   * @OA\Post(
   *   path="/api/player",
   *   tags={"Players"},
   *   summary="Create player",
   *   security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\JsonContent(
   *       required={"name","jersey_number","team_id"},
   *       @OA\Property(property="name", type="string"),
   *       @OA\Property(property="jersey_number", type="integer"),
   *       @OA\Property(property="team_id", type="string", format="uuid")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=422, description="Validation error")
   * )
   */
  public function store(PlayersStoreRequest $request): JsonResponse
  {
    $validated = $request->validated();

    $player = $this->playersService->create($validated);

    return (new PlayerResource($player))
      ->additional(['status' => 201])
      ->response()
      ->setStatusCode(201);
  }

  /**
   * Update a player.
   *
   * @OA\Put(
   *   path="/api/player/{id}",
   *   tags={"Players"},
   *   summary="Update player",
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
   *       required={"name","jersey_number","team_id"},
   *       @OA\Property(property="name", type="string"),
   *       @OA\Property(property="jersey_number", type="integer"),
   *       @OA\Property(property="team_id", type="string", format="uuid")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
  public function update(PlayersUpdateRequest $request, $id): JsonResponse
  {
    $validated = $request->validated();

    $player = $this->playersService->update($id, $validated);
    if (!$player) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return (new PlayerResource($player))
      ->additional(['status' => 200])
      ->response()
      ->setStatusCode(200);

  }

  /**
   * Delete a player.
   *
   * @OA\Delete(
   *   path="/api/player/{id}",
   *   tags={"Players"},
   *   summary="Delete player",
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
    $deleted = $this->playersService->delete($id);
    if (!$deleted) {
      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Deleted'], 200);
  }
}
