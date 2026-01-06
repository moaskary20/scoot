<?php

// Test script to check Reverb Server connection
// Run: php test_reverb_connection.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==========================================\n";
echo "🔍 Testing Reverb Server Connection\n";
echo "==========================================\n\n";

// Get config
$host = config('broadcasting.connections.reverb.options.host');
$port = config('broadcasting.connections.reverb.options.port');
$scheme = config('broadcasting.connections.reverb.options.scheme');
$appId = config('broadcasting.connections.reverb.app_id');

echo "1️⃣ Broadcast Config:\n";
echo "   Host: {$host}\n";
echo "   Port: {$port}\n";
echo "   Scheme: {$scheme}\n";
echo "   App ID: {$appId}\n\n";

// Test HTTP connection
$url = "{$scheme}://{$host}:{$port}/apps/{$appId}/events";
echo "2️⃣ Testing HTTP Connection:\n";
echo "   URL: {$url}\n\n";

try {
    $client = new \GuzzleHttp\Client([
        'timeout' => 5,
        'verify' => false,
    ]);
    
    $response = $client->get("{$scheme}://{$host}:{$port}");
    echo "   ✅ Connection successful!\n";
    echo "   Status: {$response->getStatusCode()}\n\n";
} catch (\Exception $e) {
    echo "   ❌ Connection failed: {$e->getMessage()}\n\n";
}

// Test broadcast
echo "3️⃣ Testing Broadcast:\n";
try {
    $scooter = \App\Models\Scooter::find(1);
    echo "   Scooter IMEI: {$scooter->device_imei}\n";
    echo "   Channel: scooter.{$scooter->device_imei}\n";
    
    broadcast(new \App\Events\ScooterCommand($scooter->device_imei, ['lock' => true, 'unlock' => false]));
    echo "   ✅ Broadcast sent!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Broadcast failed: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n\n";
}

echo "==========================================\n";
echo "✅ Test Complete\n";
echo "==========================================\n";





