<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $team = Team::all();
      return response()->json(['status' => 200, 'teams' => $team], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Create a new team.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $validated = $request->validate([
        'name' => 'required|string',
      ]);

      if ($validated) {
        $team = Team::create($validated);

        return response()->json(['status' => 200, 'team' => $team], 200);
      }

      return response()->json(['status' => 422, 'message' => 'Bad Entity'], 422);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Update a team.
   *
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $team = Team::find($id);
      $team->name = $request->input('name');
      $team->team_match_id = $request->input('team_match_id');
      $team->save();

      return response()->json(['status' => 200, 'team' => $team], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

  }
}
