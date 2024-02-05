<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Players;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlayersController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $players = Players::all();
      return response()->json(['status' => 200, 'players' => $players], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $validated = $request->validate([
        'name' => 'required|string',
        'jersey_number' => 'required|numeric',
        'team_id' => 'required|uuid',
      ]);

      if ($validated) {
        $player = Players::create($validated);

        return response()->json(['status' => 200, 'player' => $player], 200);
      }

      return response()->json(['status' => 422, 'message' => 'Bad Entity'], 422);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Update a player.
   *
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $player = Players::find($id);
      $player->name = $request->input('name');
      $player->jersey_number = $request->input('jersey_number');
      $player->team_id = $request->input('team_id');
      $player->save();

      return response()->json(['status' => 200, 'player' => $player], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

  }
}
