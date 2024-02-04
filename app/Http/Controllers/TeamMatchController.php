<?php

namespace App\Http\Controllers;
use App\Models\TeamMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TeamMatchController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $team_match = TeamMatch::all();
      return response()->json(['status' => 200, 'team_matchs' => $team_match], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $validated = $request->validate([
        'date_team_match' => 'required|date',
        'start_time' => 'required|string',
        'end_time' => 'required|string',
        'scoreboard' => 'required|numeric',
      ]);

      if ($validated) {
        $team = TeamMatch::create($validated);

        return response()->json(['status' => 200, 'team' => $team], 200);
      }

      return response()->json(['status' => 422, 'message' => 'Bad Entity'], 422);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }
}
