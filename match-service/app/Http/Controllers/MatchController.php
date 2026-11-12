<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Match\Handlers\CancelMatchHandler;
use App\Application\Match\Handlers\CreateMatchHandler;
use App\Application\Match\Handlers\FinishMatchHandler;
use App\Application\Match\Handlers\ScoreGoalHandler;
use App\Application\Match\Handlers\StartMatchHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MatchController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('matches')->orderBy('scheduled_at')->get(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $match = DB::table('matches')->where('id', $id)->first();

        if ($match === null) {
            return response()->json(['message' => 'Match not found'], 404);
        }

        return response()->json($match);
    }

    public function store(Request $request, CreateMatchHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'home_team_id' => ['required', 'uuid'],
            'away_team_id' => ['required', 'uuid'],
            'scheduled_at' => ['required', 'date'],
        ]);

        $id = $handler->handle(
            (string) $validated['home_team_id'],
            (string) $validated['away_team_id'],
            (string) $validated['scheduled_at'],
        );

        return response()->json(DB::table('matches')->where('id', $id)->first(), 201);
    }

    public function start(string $id, StartMatchHandler $handler): JsonResponse
    {
        $handler->handle($id);

        return response()->json(DB::table('matches')->where('id', $id)->first());
    }

    public function goal(string $id, Request $request, ScoreGoalHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'uuid'],
            'player_id' => ['required', 'uuid'],
            'minute' => ['required', 'integer', 'between:1,40'],
            'own_goal' => ['sometimes', 'boolean'],
        ]);

        $handler->handle(
            $id,
            (string) $validated['team_id'],
            (string) $validated['player_id'],
            (int) $validated['minute'],
            (bool) ($validated['own_goal'] ?? false),
        );

        return response()->json(DB::table('matches')->where('id', $id)->first());
    }

    public function finish(string $id, FinishMatchHandler $handler): JsonResponse
    {
        $handler->handle($id);

        return response()->json(DB::table('matches')->where('id', $id)->first());
    }

    public function cancel(string $id, Request $request, CancelMatchHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $handler->handle($id, (string) $validated['reason']);

        return response()->json(DB::table('matches')->where('id', $id)->first());
    }
}
