<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Team\Handlers\CreateTeamHandler;
use App\Application\Team\Handlers\DeactivateTeamHandler;
use App\Application\Team\Handlers\RenameTeamHandler;
use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('teams')->orderBy('name')->get(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $team = DB::table('teams')->where('id', $id)->first();

        if ($team === null) {
            return response()->json(['message' => 'Team not found'], 404);
        }

        return response()->json($team);
    }

    public function store(TeamStoreRequest $request, CreateTeamHandler $handler): JsonResponse
    {
        $id = $handler->handle((string) $request->validated('name'));

        return response()->json(DB::table('teams')->where('id', $id)->first(), 201);
    }

    public function update(string $id, TeamUpdateRequest $request, RenameTeamHandler $handler): JsonResponse
    {
        $handler->handle($id, (string) $request->validated('name'));

        return response()->json(DB::table('teams')->where('id', $id)->first());
    }

    public function destroy(string $id, DeactivateTeamHandler $handler): Response
    {
        $handler->handle($id);

        return response()->noContent();
    }
}
