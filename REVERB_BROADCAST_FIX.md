# 🔧 حل مشكلة Reverb Server لا يستقبل Broadcasts

## المشكلة:
- ✅ Reverb Server يعمل
- ✅ Postman متصل ومشترك
- ✅ Laravel Broadcast يعمل (`Command broadcasted successfully`)
- ❌ Reverb Server لا يستقبل أو يرسل الرسائل

## السبب:
Laravel Broadcast لا يتصل بـ Reverb Server بشكل صحيح.

## الحل:

### 1️⃣ التحقق من إعدادات Broadcast:

```bash
cd /var/www/scoot

php artisan tinker --execute="
echo 'BROADCAST_CONNECTION: ' . config('broadcasting.default') . PHP_EOL;
echo 'REVERB_APP_ID: ' . config('broadcasting.connections.reverb.app_id') . PHP_EOL;
echo 'REVERB_APP_KEY: ' . config('broadcasting.connections.reverb.key') . PHP_EOL;
echo 'REVERB_HOST: ' . config('broadcasting.connections.reverb.options.host') . PHP_EOL;
echo 'REVERB_PORT: ' . config('broadcasting.connections.reverb.options.port') . PHP_EOL;
echo 'REVERB_SCHEME: ' . config('broadcasting.connections.reverb.options.scheme') . PHP_EOL;
"
```

### 2️⃣ التحقق من .env:

```bash
grep -E "BROADCAST_CONNECTION|REVERB_" .env
```

يجب أن يكون:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=672193
REVERB_APP_KEY=xhuexrhwppynlmrgmxff
REVERB_APP_SECRET=xdpdwxtm0rcnowrrxafq
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 3️⃣ مسح Cache وإعادة التحميل:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 4️⃣ مشكلة محتملة: REVERB_HOST

إذا كان `REVERB_HOST=linerscoot.com`، Laravel قد يحاول الاتصال بـ `http://linerscoot.com:8080` من الخارج، لكن Reverb Server يعمل محلياً.

**الحل:** غيّر `REVERB_HOST` إلى `localhost` أو `127.0.0.1`:

```bash
# في .env
REVERB_HOST=localhost
# أو
REVERB_HOST=127.0.0.1
```

ثم:
```bash
php artisan config:clear
php artisan cache:clear
```

### 5️⃣ اختبار الاتصال:

```bash
php artisan tinker --execute="
try {
    echo 'Testing broadcast connection...' . PHP_EOL;
    \$scooter = \App\Models\Scooter::find(1);
    broadcast(new \App\Events\ScooterCommand(\$scooter->device_imei, ['lock' => true, 'unlock' => false]));
    echo 'Broadcast sent - check Reverb logs!' . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
    echo 'File: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
}
"
```

### 6️⃣ فحص Reverb Logs بعد Broadcast:

```bash
tail -20 storage/logs/reverb.log
```

يجب أن ترى رسائل عن استقبال/إرسال البيانات.

### 7️⃣ إذا لم يعمل: استخدام HTTP للـ Broadcast

Reverb يستخدم HTTP للاتصال مع Laravel. تحقق من:

```bash
# تحقق من أن Laravel يمكنه الوصول إلى Reverb
curl http://localhost:8080/app/xhuexrhwppynlmrgmxff

# يجب أن يرجع WebSocket upgrade response
```

---

## 🎯 الخطوات النهائية:

1. **غيّر REVERB_HOST في .env:**
   ```bash
   # في .env
   REVERB_HOST=localhost
   ```

2. **مسح Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **أعد تشغيل Reverb Server:**
   ```bash
   pkill -f "reverb:start"
   sleep 2
   nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
   ```

4. **اختبر Broadcast:**
   ```bash
   php artisan tinker --execute="
   broadcast(new \App\Events\ScooterCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]));
   echo 'Sent';
   "
   ```

5. **راقب Reverb logs:**
   ```bash
   tail -f storage/logs/reverb.log
   ```

---

## 📝 إذا لم يعمل:

أرسل:
1. نتيجة `php artisan tinker --execute="echo config('broadcasting.connections.reverb.options.host');"`
2. محتوى `.env` (REVERB_* فقط)
3. ماذا يظهر في `reverb.log` بعد Broadcast؟





