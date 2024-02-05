<?php

namespace App\Http\Controllers;

use App\Models\TeamMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TeamMatchController extends Controller
{
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
        $team_match = TeamMatch::create($validated);

        return response()->json(['status' => 200, 'team' => $team_match], 200);
      }

      return response()->json(['status' => 422, 'message' => 'Bad Entity'], 422);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param string $id
   * @return JsonResponse
   */
  public function update(Request $request, string $id): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $team_match = TeamMatch::find($id);

      if ($team_match) {
        $validated = $request->validate([
          'date_team_match' => 'required|date',
          'start_time' => 'required|string',
          'end_time' => 'required|string',
          'scoreboard' => 'required|numeric',
        ]);

        if ($validated) {
          $team_match->update($validated);

          return response()->json(['status' => 200, 'team' => $team_match], 200);
        }

        return response()->json(['status' => 422, 'message' => 'Bad Entity'], 422);
      }

      return response()->json(['status' => 404, 'message' => 'Not Found'], 404);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }
}
