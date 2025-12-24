# 🔧 حل مشكلة 404 Not Found في Reverb Server

## المشكلة:
```
❌ Connection failed: Client error: `GET http://localhost:8080` resulted in a `404 Not Found` response
```

## السبب:
Reverb Server يعمل لكن لا يستقبل HTTP requests على الـ endpoint المتوقع.

## الحل:

### 1️⃣ Laravel Reverb Broadcaster يستخدم HTTP API داخلياً

Laravel Reverb Broadcaster يحاول إرسال HTTP POST requests إلى:
```
http://REVERB_HOST:REVERB_PORT/apps/{app_id}/events
```

لكن Reverb Server قد لا يستقبل HTTP requests على هذا الـ endpoint.

### 2️⃣ الحل: استخدام Queue للـ Broadcast

إذا كان Reverb Server لا يستقبل HTTP requests، استخدم Queue:

#### أ. في `app/Events/ScooterCommand.php`:

```php
public $broadcastQueue = 'default';
```

#### ب. شغّل Queue Worker:

```bash
php artisan queue:work
```

### 3️⃣ أو استخدام Redis للـ Broadcast

```env
BROADCAST_CONNECTION=redis
```

ثم شغّل Redis:
```bash
redis-server
```

### 4️⃣ أو فحص Reverb Server Logs

```bash
tail -f storage/logs/reverb.log
```

ابحث عن رسائل عن استقبال HTTP requests.

---

## 🎯 الحل الموصى به:

### استخدام Queue للـ Broadcast:

1. **عدّل `app/Events/ScooterCommand.php`:**
   ```php
   public $broadcastQueue = 'default';
   ```

2. **شغّل Queue Worker:**
   ```bash
   php artisan queue:work
   ```

3. **اختبر Broadcast:**
   ```bash
   php artisan tinker --execute="
   broadcast(new \App\Events\ScooterCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]));
   echo 'Sent';
   "
   ```

4. **في Postman:**
   - يجب أن ترى رسالة `command` تصل

---

## 📝 ملاحظة:

Laravel Reverb Broadcaster قد لا يعمل بشكل صحيح مع Reverb Server في بعض الحالات. استخدام Queue أو Redis هو حل أكثر موثوقية.

