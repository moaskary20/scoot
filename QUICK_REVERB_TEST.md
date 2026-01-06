# ⚡ اختبار سريع لـ Reverb Server

## على السيرفر:

### 1️⃣ تحديث الكود:

```bash
cd /var/www/scoot
git pull origin main
```

### 2️⃣ اختبار الاتصال:

```bash
php test_reverb_connection.php
```

### 3️⃣ أو اختبار يدوي:

```bash
# فحص إعدادات Broadcast
php artisan tinker --execute="
echo 'REVERB_HOST: ' . config('broadcasting.connections.reverb.options.host') . PHP_EOL;
echo 'REVERB_PORT: ' . config('broadcasting.connections.reverb.options.port') . PHP_EOL;
echo 'REVERB_SCHEME: ' . config('broadcasting.connections.reverb.options.scheme') . PHP_EOL;
echo 'REVERB_APP_ID: ' . config('broadcasting.connections.reverb.app_id') . PHP_EOL;
"

# اختبار Broadcast
php artisan tinker --execute="
broadcast(new \App\Events\ScooterCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]));
echo 'Broadcast sent';
"
```

### 4️⃣ راقب Reverb Logs:

```bash
tail -f storage/logs/reverb.log
```

---

## 🔍 المشكلة المحتملة:

إذا كان Broadcast يعمل لكن Reverb Server لا يستقبل، المشكلة قد تكون:

1. **Laravel لا يتصل بـ Reverb Server** - HTTP connection fails silently
2. **Reverb Server لا يستقبل HTTP requests** - مشكلة في Reverb Server نفسه
3. **مشكلة في التوقيع** - لكن Laravel يتعامل مع هذا تلقائياً

---

## 🎯 الحل البديل: استخدام Queue

إذا استمرت المشكلة، استخدم Queue:

```php
// في app/Events/ScooterCommand.php
public $broadcastQueue = 'default';
```

ثم شغّل Queue worker:
```bash
php artisan queue:work
```

---

## 📝 أرسل:

بعد الاختبار، أرسل:
1. نتيجة `php test_reverb_connection.php` (بعد git pull)
2. آخر 30 سطر من `reverb.log` بعد Broadcast
3. هل تصل رسالة `command` في Postman؟







