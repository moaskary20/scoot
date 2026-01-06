# 🔍 تعليمات فحص السيرفر

## الاتصال بالسيرفر:
```bash
ssh root@38.242.251.149
# Password: askaryP@ssw0rd2040
```

## بعد الاتصال، شغّل هذه الأوامر:

### 1. التحقق من Reverb Server:
```bash
cd /var/www/scoot
ps aux | grep reverb | grep -v grep
```

### 2. التحقق من إعدادات Broadcast:
```bash
php artisan tinker --execute="echo config('broadcasting.default');"
```

### 3. التحقق من .env:
```bash
grep -E "BROADCAST_CONNECTION|REVERB_HOST|REVERB_PORT" .env
```

### 4. التحقق من device_imei:
```bash
php artisan tinker --execute="echo \App\Models\Scooter::find(1)->device_imei;"
```

### 5. اختبار Broadcast:
```bash
php artisan tinker --execute="\$scooter = \App\Models\Scooter::find(1); broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false])); echo 'Broadcast sent';"
```

### 6. التحقق من Port 8080:
```bash
netstat -tuln | grep 8080
```

### 7. فحص Logs:
```bash
tail -50 storage/logs/laravel.log | grep -i "command\|broadcast\|sending"
```

### 8. تشغيل Reverb Server (إذا لم يكن يعمل):
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

أو للتشغيل في الخلفية:
```bash
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
```

---

## أو استخدم السكريبت الجاهز:

بعد رفع `check_server.sh` على السيرفر:
```bash
chmod +x check_server.sh
./check_server.sh
```







