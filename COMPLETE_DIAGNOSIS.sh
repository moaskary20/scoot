#!/bin/bash

# Complete diagnosis script
cd /var/www/scoot

echo "=========================================="
echo "🔍 Complete WebSocket Diagnosis"
echo "=========================================="
echo ""

echo "1️⃣ Checking Reverb Server:"
echo "-----------------------------------"
ps aux | grep reverb | grep -v grep || echo "❌ Reverb Server is NOT running"
echo ""

echo "2️⃣ Checking Broadcast Config:"
echo "-----------------------------------"
php artisan tinker --execute="
echo 'BROADCAST_CONNECTION: ' . config('broadcasting.default') . PHP_EOL;
echo 'REVERB_HOST: ' . config('broadcasting.connections.reverb.options.host') . PHP_EOL;
echo 'REVERB_PORT: ' . config('broadcasting.connections.reverb.options.port') . PHP_EOL;
echo 'REVERB_SCHEME: ' . config('broadcasting.connections.reverb.options.scheme') . PHP_EOL;
echo 'REVERB_APP_ID: ' . config('broadcasting.connections.reverb.app_id') . PHP_EOL;
"
echo ""

echo "3️⃣ Checking Reverb Apps Config:"
echo "-----------------------------------"
php artisan tinker --execute="
\$apps = config('reverb.apps.apps');
echo 'Apps count: ' . count(\$apps) . PHP_EOL;
foreach (\$apps as \$app) {
    echo 'App ID: ' . \$app['app_id'] . ' (type: ' . gettype(\$app['app_id']) . ')' . PHP_EOL;
    echo 'App Key: ' . \$app['key'] . PHP_EOL;
}
"
echo ""

echo "4️⃣ Testing Broadcast:"
echo "-----------------------------------"
php artisan tinker --execute="
try {
    echo 'Testing broadcast...' . PHP_EOL;
    \$scooter = \App\Models\Scooter::find(1);
    echo 'Scooter IMEI: ' . \$scooter->device_imei . PHP_EOL;
    echo 'Channel: scooter.' . \$scooter->device_imei . PHP_EOL;
    broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false]));
    echo '✅ Broadcast sent successfully!' . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
    echo 'File: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
}
"
echo ""

echo "5️⃣ Checking Port 8080:"
echo "-----------------------------------"
netstat -tuln | grep 8080 || echo "❌ Port 8080 is not listening"
echo ""

echo "6️⃣ Recent Laravel Logs (last 10 lines with 'command' or 'broadcast'):"
echo "-----------------------------------"
tail -50 storage/logs/laravel.log | grep -i "command\|broadcast\|sending" | tail -10 || echo "No recent broadcast logs"
echo ""

echo "7️⃣ Reverb Logs (last 20 lines):"
echo "-----------------------------------"
tail -20 storage/logs/reverb.log 2>/dev/null || echo "No reverb.log file found"
echo ""

echo "=========================================="
echo "✅ Diagnosis Complete"
echo "=========================================="
echo ""
echo "📋 Next Steps:"
echo "1. Make sure Postman is connected and subscribed to: scooter.ESP32_IMEI_001"
echo "2. Press Lock/Unlock from Admin Panel"
echo "3. Check if 'command' message arrives in Postman"
echo "4. Check Reverb logs for any errors"





