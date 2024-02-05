<?php

use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TeamClassificationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMatchController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::controller(UserController::class)->group(function() {
  Route::post('login', 'loginUser');
});

Route::controller(UserController::class)->group(function() {
  Route::post('user', 'getUser');
  Route::post('user', 'userLogout');
})->middleware('auth:api');

Route::controller(PlayersController::class)->group(function() {
  Route::get('player', 'index');
  Route::post('player', 'store');
  Route::post('player', 'update');
})->middleware('auth:api');

Route::controller(TeamController::class)->group(function() {
  Route::get('team', 'index');
  Route::post('team', 'store');
  Route::put('team/{id}', 'update');
})->middleware('auth:api');

Route::controller(TeamClassificationController::class)->group(function() {
  Route::get('teamClassification', 'index');
})->middleware('auth:api');

Route::controller(TeamMatchController::class)->group(function() {
  Route::get('teamMatch', 'index');
  Route::post('teamMatch', 'store');
  Route::put('teamMatch/{id}', 'update');
})->middleware('auth:api');

