<?php

use App\Http\Controllers\Api\AuthController;
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

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Future phases
|--------------------------------------------------------------------------
| Phase 1 – Tickets
| Phase 2 – Comments
| Phase 3 – Admin / Role management
*/
