<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScooterApiController;

/*
|--------------------------------------------------------------------------
| API Routes for ESP32 Scooter Control
|--------------------------------------------------------------------------
*/

Route::prefix('v1/scooter')->group(function () {
    // Authentication by IMEI
    Route::post('authenticate', [ScooterApiController::class, 'authenticate']);
    
    // Update GPS location and battery
    Route::post('update-location', [ScooterApiController::class, 'updateLocation']);
    
    // Update lock status
    Route::post('update-lock-status', [ScooterApiController::class, 'updateLockStatus']);
    
    // Get commands (lock/unlock)
    Route::post('get-commands', [ScooterApiController::class, 'getCommands']);
    
    // Update battery only
    Route::post('update-battery', [ScooterApiController::class, 'updateBattery']);
});

