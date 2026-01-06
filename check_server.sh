#!/bin/bash

# Script to check WebSocket issues on server
# Run this on the server: ssh root@38.242.251.149

echo "=========================================="
echo "🔍 Checking WebSocket Configuration"
echo "=========================================="
echo ""

cd /var/www/scoot || exit 1

echo "1️⃣ Checking Reverb Server Status:"
echo "-----------------------------------"
ps aux | grep reverb | grep -v grep || echo "❌ Reverb Server is NOT running"
echo ""

echo "2️⃣ Checking Broadcast Configuration:"
echo "-----------------------------------"
php artisan tinker --execute="echo 'Broadcast default: ' . config('broadcasting.default') . PHP_EOL;"
echo ""

echo "3️⃣ Checking .env Settings:"
echo "-----------------------------------"
grep -E "BROADCAST_CONNECTION|REVERB_HOST|REVERB_PORT" .env | head -5
echo ""

echo "4️⃣ Checking Scooter 1 device_imei:"
echo "-----------------------------------"
php artisan tinker --execute="echo 'device_imei: ' . (\App\Models\Scooter::find(1)->device_imei ?? 'NULL') . PHP_EOL;"
echo ""

echo "5️⃣ Testing Broadcast:"
echo "-----------------------------------"
php artisan tinker --execute="try { \$scooter = \App\Models\Scooter::find(1); if (!\$scooter->device_imei) { echo '❌ device_imei is NULL' . PHP_EOL; } else { broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false])); echo '✅ Broadcast test: SUCCESS' . PHP_EOL; } } catch (\Exception \$e) { echo '❌ Broadcast test: ERROR - ' . \$e->getMessage() . PHP_EOL; }"
echo ""

echo "6️⃣ Recent Logs (last 5 lines with 'command' or 'broadcast'):"
echo "-----------------------------------"
tail -100 storage/logs/laravel.log | grep -i "command\|broadcast\|sending" | tail -5 || echo "No recent command/broadcast logs"
echo ""

echo "7️⃣ Checking Port 8080:"
echo "-----------------------------------"
netstat -tuln | grep 8080 || echo "❌ Port 8080 is not listening"
echo ""

echo "=========================================="
echo "✅ Check Complete"
echo "=========================================="





