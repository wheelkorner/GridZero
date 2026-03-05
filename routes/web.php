<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use Illuminate\Support\Facades\Auth;

// SPA entry point
Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::get('/login', function () {
    return redirect('/');
});

// ── Auth (session-based) ─────────────────────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

    Route::get('/user', [UserController::class, 'show'])->middleware('auth');
    Route::post('/user/update-stats', [UserController::class, 'updateStats'])->middleware('auth');

    // ── Admin API Routes ─────────────────────────────────────────────────────
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{username}', [AdminController::class, 'userInfo']);
        Route::post('/impersonate/{username}', [AdminController::class, 'impersonate']);
    });

    // ── Game routes ─────────────────────────────────────────────────────────
    Route::middleware(['auth', 'throttle:10,1'])->group(function () {
        Route::get('/nodes', [NodeController::class, 'index']);
        Route::get('/scan', [ActionController::class, 'scan']);
        Route::post('/connect', [ActionController::class, 'connectByIp']);
        Route::post('/actions', [ActionController::class, 'store']);
        // Shop
        Route::get('/shop', [\App\Http\Controllers\ShopController::class, 'index']);
        Route::post('/shop/buy', [\App\Http\Controllers\ShopController::class, 'buy']);
        Route::post('/shop/use', [\App\Http\Controllers\ShopController::class, 'use']);
    });
});

// ── Admin Dashboard (Blade-based) ───────────────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'postLogin']);

    Route::middleware(['web', 'auth', 'admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'viewUsers'])->name('admin.users');
        Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
        Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::get('/nodes', [AdminController::class, 'viewNodes'])->name('admin.nodes');
        Route::get('/actions', [AdminController::class, 'viewActions'])->name('admin.actions');
        Route::match(['get', 'post'], '/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/admin/login');
        })->name('admin.logout');
    });
});

// ── Logs (Admin only) ──────────────────────────────────────────────────────
Route::get('logs', [LogViewerController::class, 'index'])
    ->middleware(['web', 'auth', 'admin']);
