# 🚀 دليل تشغيل Reverb Server واختباره

## المشكلة:
Reverb Server لا يعرف التطبيق أو لا يستقبل broadcasts.

## الحل:

### 1️⃣ شغّل Reverb Server:

```bash
cd /var/www/scoot

# أوقف Reverb Server القديم
pkill -f "reverb:start"
sleep 2

# مسح cache
php artisan config:clear
php artisan cache:clear

# شغّل Reverb Server
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &

# انتظر 3 ثواني
sleep 3

# تحقق أنه يعمل
ps aux | grep reverb | grep -v grep
```

### 2️⃣ اختبر HTTP API:

```bash
curl -X POST http://localhost:8080/apps/672193/events \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer xdpdwxtm0rcnowrrxafq" \
  -d '{
    "channels": ["scooter.ESP32_IMEI_001"],
    "name": "command",
    "data": {
      "commands": {"lock": true, "unlock": false}
    }
  }'
```

### 3️⃣ إذا ظهر "No matching application":

#### أ. تحقق من الإعدادات:

```bash
php artisan tinker --execute="
\$apps = config('reverb.apps.apps');
echo 'Apps: ' . count(\$apps) . PHP_EOL;
foreach (\$apps as \$app) {
    echo 'App ID: ' . \$app['app_id'] . ' (type: ' . gettype(\$app['app_id']) . ')' . PHP_EOL;
}
"
```

#### ب. جرب app_id كـ string:

في `.env`:
```env
REVERB_APP_ID="672193"
```

ثم:
```bash
php artisan config:clear
php artisan cache:clear
pkill -f "reverb:start"
sleep 2
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
```

#### ج. شغّل Reverb Server في foreground لرؤية الأخطاء:

```bash
# في terminal واحد
php artisan reverb:start --host=0.0.0.0 --port=8080

# في terminal آخر
curl -X POST http://localhost:8080/apps/672193/events ...
```

### 4️⃣ اختبر Broadcast من Laravel:

```bash
php artisan tinker --execute="
broadcast(new \App\Events\ScooterCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]));
echo 'Broadcast sent';
"
```

### 5️⃣ في Postman:

يجب أن ترى رسالة `command` تصل.

---

## 🔍 Debugging:

### فحص Reverb logs:

```bash
tail -f storage/logs/reverb.log
```

### فحص الإعدادات:

```bash
php artisan tinker --execute="
print_r(config('reverb.apps'));
"
```

### فحص الاتصال:

```bash
netstat -tuln | grep 8080
```

---

## 📝 ملاحظات:

1. **REVERB_HOST:** يجب أن يكون `localhost` في `.env` (للاتصال من Laravel)
2. **--host=0.0.0.0:** يسمح بالاتصالات من الخارج (للعملاء)
3. **app_id:** قد يحتاج أن يكون string وليس number






