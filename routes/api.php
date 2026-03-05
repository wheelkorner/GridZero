<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\NodeController;

// Game routes — protected by Sanctum token/session + rate limiting
Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
    Route::post('/actions', [ActionController::class, 'store']);
    Route::get('/nodes', [NodeController::class, 'index']);
});
