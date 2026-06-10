<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Helpdesk
|--------------------------------------------------------------------------
*/

// ── PHASE 0 – Auth ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {

    // Public
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    // Protégé
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// ── PHASE 1 – Tickets (toutes protégées) ────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('tickets')->group(function () {

    Route::get('/',                     [TicketController::class, 'index']);   // lister
    Route::post('/',                    [TicketController::class, 'store']);   // créer
    Route::get('/{ticket}',             [TicketController::class, 'show']);    // détail
    Route::patch('/{ticket}',           [TicketController::class, 'update']); // modifier
    Route::patch('/{ticket}/close',     [TicketController::class, 'close']);  // fermer

});

/*
|--------------------------------------------------------------------------
| Future phases
|--------------------------------------------------------------------------
| Phase 2 – Comments
| Phase 3 – Admin / Role management
*/
