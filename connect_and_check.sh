#!/usr/bin/expect -f

# Script to connect to server and check WebSocket issues
# Usage: ./connect_and_check.sh

set timeout 30
set host "38.242.251.149"
set user "root"
set password "askaryP@ssw0rd2040"

spawn ssh $user@$host

expect {
    "password:" {
        send "$password\r"
        exp_continue
    }
    "yes/no" {
        send "yes\r"
        exp_continue
    }
    "$ " {
        send "cd /var/www/scoot\r"
        expect "$ "
        
        send "echo '=========================================='\r"
        expect "$ "
        send "echo '🔍 Checking WebSocket Configuration'\r"
        expect "$ "
        send "echo '=========================================='\r"
        expect "$ "
        
        send "echo '1️⃣ Checking Reverb Server Status:'\r"
        expect "$ "
        send "ps aux | grep reverb | grep -v grep || echo '❌ Reverb Server is NOT running'\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '2️⃣ Checking Broadcast Configuration:'\r"
        expect "$ "
        send "php artisan tinker --execute=\"echo 'Broadcast default: ' . config('broadcasting.default') . PHP_EOL;\"\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '3️⃣ Checking .env Settings:'\r"
        expect "$ "
        send "grep -E 'BROADCAST_CONNECTION|REVERB_HOST|REVERB_PORT' .env | head -5\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '4️⃣ Checking Scooter 1 device_imei:'\r"
        expect "$ "
        send "php artisan tinker --execute=\"echo 'device_imei: ' . (\\\\App\\\\Models\\\\Scooter::find(1)->device_imei ?? 'NULL') . PHP_EOL;\"\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '5️⃣ Testing Broadcast:'\r"
        expect "$ "
        send "php artisan tinker --execute=\"try { \\\$scooter = \\\\App\\\\Models\\\\Scooter::find(1); if (!\\\$scooter->device_imei) { echo '❌ device_imei is NULL' . PHP_EOL; } else { broadcast(new \\\\App\\\\Events\\\\ScooterCommand(\\\$scooter->device_imei, ['lock' => true, 'unlock' => false])); echo '✅ Broadcast test: SUCCESS' . PHP_EOL; } } catch (\\\\Exception \\\$e) { echo '❌ Broadcast test: ERROR - ' . \\\$e->getMessage() . PHP_EOL; }\"\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '6️⃣ Checking Port 8080:'\r"
        expect "$ "
        send "netstat -tuln | grep 8080 || echo '❌ Port 8080 is not listening'\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '7️⃣ Recent Logs:'\r"
        expect "$ "
        send "tail -50 storage/logs/laravel.log | grep -i 'command\\|broadcast\\|sending' | tail -5 || echo 'No recent logs'\r"
        expect "$ "
        
        send "echo ''\r"
        expect "$ "
        send "echo '=========================================='\r"
        expect "$ "
        send "echo '✅ Check Complete'\r"
        expect "$ "
        send "echo '=========================================='\r"
        expect "$ "
        
        send "exit\r"
        expect eof
    }
}

