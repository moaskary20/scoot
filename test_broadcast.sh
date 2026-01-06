#!/bin/bash

# Test broadcast on server
# Run: ssh root@38.242.251.149 "bash -s" < test_broadcast.sh

cd /var/www/scoot

echo "Testing Broadcast..."
php artisan tinker --execute="
try {
    \$scooter = \App\Models\Scooter::find(1);
    if (!\$scooter->device_imei) {
        echo '❌ device_imei is NULL' . PHP_EOL;
    } else {
        echo '✅ device_imei: ' . \$scooter->device_imei . PHP_EOL;
        echo '✅ Channel: scooter.' . \$scooter->device_imei . PHP_EOL;
        broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false]));
        echo '✅ Broadcast sent successfully!' . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
}
"







