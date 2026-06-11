<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ── PHASE 0 – Auth ───────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// ── PHASES 1-4 — Routes protégées ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── Tickets — accessibles à tous les rôles (scope géré dans le service) ──
    Route::prefix('tickets')->group(function () {
        Route::get('/',                        [TicketController::class, 'index']);
        Route::post('/',                       [TicketController::class, 'store']);
        Route::get('/{ticket}',                [TicketController::class, 'show']);
        Route::patch('/{ticket}',              [TicketController::class, 'update']);
        Route::patch('/{ticket}/close',        [TicketController::class, 'close']);

        // Admin uniquement
        Route::middleware('role:admin')->group(function () {
            Route::patch('/{ticket}/assign',   [TicketController::class, 'assign']);
            Route::patch('/{ticket}/priority', [TicketController::class, 'setPriority']);
        });

        // Comments
        Route::get('/{ticket}/comments',       [CommentController::class, 'index']);
        Route::post('/{ticket}/comments',      [CommentController::class, 'store']);
    });

    // ── Users — admin uniquement ──────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('users')->group(function () {
        Route::get('/',                        [UserController::class, 'index']);
        Route::post('/{user}/roles',           [UserController::class, 'assignRole']);
        Route::delete('/{user}/roles/{role}',  [UserController::class, 'removeRole']);
    });
});
