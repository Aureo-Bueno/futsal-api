<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'Futsal API',
        'status' => 'ok',
    ]);
});
