<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// WebSocket channel for ESP32 scooters
Broadcast::channel('scooter.{imei}', function ($user, $imei) {
    // Allow connection for authenticated scooters
    // In production, you might want to add authentication here
    return true;
});
