<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Helpdesk
|--------------------------------------------------------------------------
*/

// ── PHASE 0 – Auth ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// ── PHASE 1 & 2 – Tickets + Comments (toutes protégées) ─────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/',                 [TicketController::class, 'index']);
        Route::post('/',                [TicketController::class, 'store']);
        Route::get('/{ticket}',         [TicketController::class, 'show']);
        Route::patch('/{ticket}',       [TicketController::class, 'update']);
        Route::patch('/{ticket}/close', [TicketController::class, 'close']);

        // Comments — routes imbriquées sous /tickets/{ticket}/comments
        Route::get('/{ticket}/comments',  [CommentController::class, 'index']);
        Route::post('/{ticket}/comments', [CommentController::class, 'store']);

        // assignement et priorité
        Route::patch('/{ticket}/assign', [TicketController::class, 'assign']);
        Route::patch('/{ticket}/priority', [TicketController::class, 'setPriority']);
    });

    // Utilisateurs — pour le dropdown d'assignation
    Route::get('/users', [UserController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Future phases
|--------------------------------------------------------------------------
| Phase 3 – Admin / Role management
*/
