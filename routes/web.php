<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

// SPA entry point
Route::get('/', function () {
    return view('welcome');
});

// ── Auth (session-based) ─────────────────────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

    Route::get('/user', [UserController::class, 'show'])->middleware('auth');
    Route::post('/user/update-stats', [UserController::class, 'updateStats'])->middleware('auth');

    // ── Admin Routes ─────────────────────────────────────────────────────────
    Route::middleware(['auth'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{username}', [AdminController::class, 'userInfo']);
        Route::post('/impersonate/{username}', [AdminController::class, 'impersonate']);
    });

    // ── Game routes ─────────────────────────────────────────────────────────
    Route::middleware(['auth', 'throttle:10,1'])->group(function () {
        Route::get('/nodes', [NodeController::class, 'index']);
        Route::post('/actions', [ActionController::class, 'store']);
    });
});
