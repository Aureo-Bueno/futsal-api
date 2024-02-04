<?php

namespace App\Http\Controllers;

use App\Models\TeamClassification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TeamClassificationController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(): JsonResponse
  {
    if (Auth::guard('api')->check()) {
      $team_classification = TeamClassification::orderBy('points', 'desc')->get();
      return response()->json(['status' => 200, 'team_classifications' => $team_classification], 200);
    }

    return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
  }
}
