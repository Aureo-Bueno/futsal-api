<?php

use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::middleware('internal')->group(function (): void {
    Route::get('/matches', [MatchController::class, 'index']);
    Route::get('/matches/{id}', [MatchController::class, 'show']);
    Route::post('/matches', [MatchController::class, 'store']);
    Route::post('/matches/{id}/start', [MatchController::class, 'start']);
    Route::post('/matches/{id}/goals', [MatchController::class, 'goal']);
    Route::post('/matches/{id}/finish', [MatchController::class, 'finish']);
    Route::post('/matches/{id}/cancel', [MatchController::class, 'cancel']);
});
