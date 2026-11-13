<?php

use App\Http\Controllers\StandingsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::middleware('internal')->group(function (): void {
    Route::get('/standings', [StandingsController::class, 'index']);
});
