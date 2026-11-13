<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class StandingsController extends Controller
{
    public function index(): JsonResponse
    {
        $standings = DB::table('team_standings')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        return response()->json($standings);
    }
}
