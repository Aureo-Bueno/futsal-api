<?php

use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TeamClassificationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMatchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('health', function () {
    $database = 'ok';
    try {
        DB::connection()->getPdo();
    } catch (Throwable $e) {
        $database = 'down';
    }

    return response()->json([
        'status' => 'ok',
        'database' => $database,
        'timestamp' => now()->toISOString(),
    ], $database === 'ok' ? 200 : 503);
});

Route::controller(UserController::class)->group(function () {
    Route::post('login', 'loginUser')->middleware('throttle:login');
});

Route::middleware('auth:api')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('user', 'getUser');
        Route::post('logout', 'userLogout');
    });

    Route::get('teams', [TeamController::class, 'index']);
    Route::get('teams/{id}', [TeamController::class, 'show']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::put('teams/{id}', [TeamController::class, 'update']);
    Route::delete('teams/{id}', [TeamController::class, 'destroy']);
    Route::get('team', [TeamController::class, 'index']);

    Route::get('players', [PlayersController::class, 'index']);
    Route::get('players/{id}', [PlayersController::class, 'show']);
    Route::post('teams/{teamId}/players', [PlayersController::class, 'store']);
    Route::put('players/{id}/transfer', [PlayersController::class, 'transfer']);
    Route::delete('players/{id}', [PlayersController::class, 'destroy']);
    Route::get('player', [PlayersController::class, 'index']);

    Route::get('matches', [TeamMatchController::class, 'index']);
    Route::get('matches/{id}', [TeamMatchController::class, 'show']);
    Route::post('matches', [TeamMatchController::class, 'store']);
    Route::post('matches/{id}/start', [TeamMatchController::class, 'start']);
    Route::post('matches/{id}/goals', [TeamMatchController::class, 'goal']);
    Route::post('matches/{id}/finish', [TeamMatchController::class, 'finish']);
    Route::post('matches/{id}/cancel', [TeamMatchController::class, 'cancel']);
    Route::get('teamMatch', [TeamMatchController::class, 'index']);

    Route::get('standings', [TeamClassificationController::class, 'index']);
    Route::get('teamClassification', [TeamClassificationController::class, 'index']);
    Route::any('teamClassification/{any?}', fn () => response()->json(['message' => 'Method Not Allowed'], 405))->where('any', '.*');
});
