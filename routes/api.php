<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebSocket\ScooterWebSocketController;

/*
|--------------------------------------------------------------------------
| WebSocket Routes for ESP32 Scooter Control
|--------------------------------------------------------------------------
| 
| Note: For full WebSocket functionality, use Laravel Reverb WebSocket server.
| This endpoint provides HTTP fallback for compatibility.
*/

Route::prefix('v1/scooter')->group(function () {
    // WebSocket message handler (HTTP fallback)
    Route::post('message', [ScooterWebSocketController::class, 'handleMessage']);
});

