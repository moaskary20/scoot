# 🔍 Debugging Reverb Server

## المشكلة:
- ✅ Laravel Broadcast يعمل
- ✅ `Command broadcasted successfully` يظهر في logs
- ❌ الرسائل لا تصل إلى Postman

## الحلول:

### 1️⃣ فحص Reverb Logs:

```bash
cd /var/www/scoot

# فحص Reverb logs
tail -f reverb.log

# أو
tail -f storage/logs/reverb.log

# أو فحص جميع الـ logs
tail -f storage/logs/*.log | grep -i "reverb\|websocket\|command"
```

### 2️⃣ إعادة تشغيل Reverb Server:

```bash
# أوقف Reverb Server
pkill -f "reverb:start"

# انتظر ثانيتين
sleep 2

# شغّله مرة أخرى مع logs مفصلة
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 --debug > storage/logs/reverb.log 2>&1 &

# تحقق أنه يعمل
ps aux | grep reverb | grep -v grep
```

### 3️⃣ التحقق من إعدادات Reverb:

```bash
php artisan tinker --execute="
echo 'REVERB_APP_ID: ' . env('REVERB_APP_ID') . PHP_EOL;
echo 'REVERB_APP_KEY: ' . env('REVERB_APP_KEY') . PHP_EOL;
echo 'REVERB_HOST: ' . env('REVERB_HOST') . PHP_EOL;
echo 'REVERB_PORT: ' . env('REVERB_PORT') . PHP_EOL;
echo 'BROADCAST_CONNECTION: ' . env('BROADCAST_CONNECTION') . PHP_EOL;
"
```

### 4️⃣ اختبار Broadcast مباشرة مع Reverb:

```bash
php artisan tinker --execute="
\$scooter = \App\Models\Scooter::find(1);
echo 'Testing broadcast to: scooter.' . \$scooter->device_imei . PHP_EOL;
broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false]));
echo 'Broadcast sent - check Postman now!' . PHP_EOL;
"
```

### 5️⃣ التحقق من Channel Authorization:

في `routes/channels.php`، تأكد من:
```php
Broadcast::channel('scooter.{imei}', function ($user, $imei) {
    return true; // يجب أن يرجع true
});
```

### 6️⃣ مشكلة محتملة: Reverb لا يرسل للعملاء

إذا كان Reverb Server يعمل لكن لا يرسل الرسائل، قد تكون المشكلة في:
- Reverb Server version
- إعدادات الشبكة
- Firewall يمنع الاتصال

### 7️⃣ حل بديل: استخدام Queue

إذا استمرت المشكلة، يمكن استخدام Queue للـ broadcast:

```php
// في ScooterCommand event
public $broadcastQueue = 'default';
```

ثم شغّل Queue worker:
```bash
php artisan queue:work
```

---

## 🎯 الخطوات النهائية:

1. **أعد تشغيل Reverb Server:**
   ```bash
   pkill -f "reverb:start"
   sleep 2
   nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
   ```

2. **في Postman:**
   - تأكد أنك متصل
   - تأكد أنك مشترك في `scooter.ESP32_IMEI_001`
   - أرد على `pusher:ping`

3. **اختبر Broadcast مباشرة:**
   ```bash
   php artisan tinker --execute="
   broadcast(new \App\Events\ScooterCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]));
   echo 'Sent';
   "
   ```

4. **راقب Reverb logs:**
   ```bash
   tail -f storage/logs/reverb.log
   ```

---

## 📝 إذا لم تعمل:

أرسل:
1. محتوى `reverb.log` أو `storage/logs/reverb.log`
2. نتيجة `ps aux | grep reverb`
3. هل Postman متصل ومشترك قبل الاختبار؟





