<?php

use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TeamClassificationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMatchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('health', function () {
  $database = 'ok';
  try {
    DB::connection()->getPdo();
  } catch (\Throwable $e) {
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

  Route::controller(PlayersController::class)->group(function () {
    Route::get('player', 'index');
    Route::get('player/{id}', 'show');
    Route::post('player', 'store');
    Route::put('player/{id}', 'update');
    Route::delete('player/{id}', 'destroy');
  });

  Route::controller(TeamController::class)->group(function () {
    Route::get('team', 'index');
    Route::get('team/{id}', 'show');
    Route::post('team', 'store');
    Route::put('team/{id}', 'update');
    Route::delete('team/{id}', 'destroy');
  });

  Route::controller(TeamClassificationController::class)->group(function () {
    Route::get('teamClassification', 'index');
    Route::get('teamClassification/{id}', 'show');
    Route::post('teamClassification', 'store');
    Route::put('teamClassification/{id}', 'update');
    Route::delete('teamClassification/{id}', 'destroy');
  });

  Route::controller(TeamMatchController::class)->group(function () {
    Route::get('teamMatch', 'index');
    Route::get('teamMatch/{id}', 'show');
    Route::post('teamMatch', 'store');
    Route::put('teamMatch/{id}', 'update');
    Route::delete('teamMatch/{id}', 'destroy');
  });
});
