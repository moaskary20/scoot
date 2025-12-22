<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebSocket\ScooterWebSocketController;
use App\Http\Controllers\Api\MobileAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Mobile App Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::post('/register', [MobileAuthController::class, 'register']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [MobileAuthController::class, 'user']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});

// WebSocket Routes for ESP32 Scooter Control
Route::prefix('v1/scooter')->group(function () {
    // WebSocket message handler (HTTP fallback)
    Route::post('message', [ScooterWebSocketController::class, 'handleMessage']);
});

