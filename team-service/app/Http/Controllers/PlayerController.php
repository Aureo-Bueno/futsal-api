<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Player\Handlers\RegisterPlayerHandler;
use App\Application\Player\Handlers\RemovePlayerHandler;
use App\Application\Player\Handlers\TransferPlayerHandler;
use App\Http\Requests\PlayerStoreRequest;
use App\Http\Requests\PlayerTransferRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final class PlayerController extends Controller
{
    public function store(string $teamId, PlayerStoreRequest $request, RegisterPlayerHandler $handler): JsonResponse
    {
        $id = $handler->handle(
            $teamId,
            (string) $request->validated('name'),
            (int) $request->validated('jersey_number'),
        );

        return response()->json(DB::table('players')->where('id', $id)->first(), 201);
    }

    public function transfer(string $id, PlayerTransferRequest $request, TransferPlayerHandler $handler): JsonResponse
    {
        $handler->handle($id, (string) $request->validated('team_id'));

        return response()->json(DB::table('players')->where('id', $id)->first());
    }

    public function destroy(string $id, RemovePlayerHandler $handler): Response
    {
        $handler->handle($id);

        return response()->noContent();
    }
}
