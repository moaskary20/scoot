#!/bin/bash

# Test broadcast directly on server
# Run: ssh root@38.242.251.149 "bash -s" < test_broadcast_direct.sh

cd /var/www/scoot

echo "=========================================="
echo "🧪 Testing Broadcast"
echo "=========================================="
echo ""

echo "1️⃣ Getting Scooter Info:"
echo "-----------------------------------"
php artisan tinker --execute="
\$scooter = \App\Models\Scooter::find(1);
echo 'Scooter ID: ' . \$scooter->id . PHP_EOL;
echo 'Scooter Code: ' . \$scooter->code . PHP_EOL;
echo 'device_imei: ' . \$scooter->device_imei . PHP_EOL;
echo 'Channel: scooter.' . \$scooter->device_imei . PHP_EOL;
"
echo ""

echo "2️⃣ Testing Broadcast:"
echo "-----------------------------------"
php artisan tinker --execute="
try {
    \$scooter = \App\Models\Scooter::find(1);
    if (!\$scooter->device_imei) {
        echo '❌ device_imei is NULL' . PHP_EOL;
    } else {
        echo '✅ Broadcasting to channel: scooter.' . \$scooter->device_imei . PHP_EOL;
        broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false]));
        echo '✅ Broadcast sent successfully!' . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
    echo 'File: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
}
"
echo ""

echo "3️⃣ Checking Recent Logs:"
echo "-----------------------------------"
tail -20 storage/logs/laravel.log | grep -i "command\|broadcast\|sending" || echo "No recent broadcast logs"
echo ""

echo "=========================================="
echo "✅ Test Complete"
echo "=========================================="





