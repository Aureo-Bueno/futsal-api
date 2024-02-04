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
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\Players  $player
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, Players $player)
  {
    if (Auth::guard('api')->check()) {
      $request->validate([
        'name' => 'required',
        'jersey_number' => 'required|numeric',
      ]);

      $player->update($request->all());

      return response()->json(['status' => 200, 'player' => $player], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }
}
