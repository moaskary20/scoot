<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebSocket\ScooterWebSocketController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileWalletController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileReferralController;
use App\Http\Controllers\Api\MobileScooterController;
use App\Http\Controllers\Api\MobileLoyaltyController;
use App\Http\Controllers\Api\MobileGeoZoneController;

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
        Route::post('/update-password', [MobileAuthController::class, 'updatePassword']);
        Route::post('/update-avatar', [MobileAuthController::class, 'updateAvatar']);
    });
});

// Mobile App Scooters Routes
Route::prefix('scooters')->group(function () {
    Route::get('/nearby', [MobileScooterController::class, 'getNearbyScooters']);
    Route::get('/', [MobileScooterController::class, 'getAllScooters']);
    Route::get('/{id}', [MobileScooterController::class, 'getScooterDetails']);
});

// Mobile App Trips Routes
Route::prefix('trips')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MobileTripController::class, 'index']);
    Route::post('/start', [MobileTripController::class, 'start']);
    Route::get('/active', [MobileTripController::class, 'getActiveTrip']);
    Route::post('/{id}/complete', [MobileTripController::class, 'complete']);
});

// Mobile App Wallet Routes
Route::prefix('wallet')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MobileWalletController::class, 'getBalance']);
    Route::get('/transactions', [MobileWalletController::class, 'getTransactions']);
    Route::post('/top-up', [MobileWalletController::class, 'topUp']);
    Route::post('/validate-promo', [MobileWalletController::class, 'validatePromoCode']);
    
    // Cards
    Route::get('/cards', [MobileWalletController::class, 'getCards']);
    Route::post('/cards', [MobileWalletController::class, 'saveCard']);
    Route::delete('/cards/{id}', [MobileWalletController::class, 'deleteCard']);
});

// Paymob Callback (no auth required - Paymob calls this)
Route::post('/wallet/paymob/callback', [\App\Http\Controllers\Api\MobileWalletController::class, 'paymobCallback']);

// Mobile App Referral Routes
Route::prefix('referral')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MobileReferralController::class, 'getReferralData']);
});

// Mobile App Loyalty Routes
Route::prefix('loyalty')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MobileLoyaltyController::class, 'index']);
});

// Mobile App Geo Zones Routes (no auth required - public map data)
Route::get('/geo-zones', [MobileGeoZoneController::class, 'index']);

// WebSocket Routes for ESP32 Scooter Control
Route::prefix('v1/scooter')->group(function () {
    // WebSocket message handler (HTTP fallback)
    Route::post('message', [ScooterWebSocketController::class, 'handleMessage']);
    
    // Get commands in WebSocket format (JSON object, not string)
    Route::post('commands', [ScooterWebSocketController::class, 'getCommandsWebSocketFormat']);
});

