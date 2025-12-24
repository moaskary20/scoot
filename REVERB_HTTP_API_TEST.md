# 🔧 اختبار Reverb HTTP API

## المشكلة:
Laravel Broadcast يعمل لكن Reverb Server لا يستقبل الرسائل.

## السبب:
Laravel Reverb يستخدم HTTP API لإرسال الرسائل إلى Reverb Server على:
```
http://REVERB_HOST:REVERB_PORT/apps/{app_id}/events
```

## اختبار الاتصال:

### 1️⃣ اختبار HTTP API مباشرة:

```bash
cd /var/www/scoot

# اختبار الاتصال
curl -v -X POST http://localhost:8080/apps/672193/events \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer xdpdwxtm0rcnowrrxafq" \
  -d '{
    "channels": ["scooter.ESP32_IMEI_001"],
    "name": "command",
    "data": {
      "commands": {
        "lock": true,
        "unlock": false
      }
    }
  }'
```

### 2️⃣ فحص Reverb Logs:

```bash
tail -f storage/logs/reverb.log
```

يجب أن ترى رسائل عن استقبال HTTP requests.

### 3️⃣ إذا ظهر "Authentication signature invalid":

Laravel Reverb يستخدم signature معقد. لا يمكن استخدام curl مباشرة.

**الحل:** استخدم Laravel Broadcast فقط - يتعامل مع التوقيع تلقائياً.

### 4️⃣ المشكلة الحقيقية:

إذا كان Broadcast يعمل لكن Reverb Server لا يستقبل، المشكلة قد تكون:

#### أ. Reverb Server لا يستقبل HTTP requests:
- تحقق من أن Reverb Server يعمل على Port 8080
- تحقق من Firewall

#### ب. Laravel لا يتصل بـ Reverb Server:
- تحقق من `REVERB_HOST=localhost` في `.env`
- تحقق من `REVERB_PORT=8080`
- مسح cache: `php artisan config:clear`

#### ج. Reverb Server لا يرسل للعملاء:
- قد تكون مشكلة في Reverb Server نفسه
- جرب إعادة تشغيل Reverb Server

---

## 🎯 الحل النهائي:

### 1. أعد تشغيل Reverb Server:

```bash
pkill -f "reverb:start"
sleep 3
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
```

### 2. مسح Cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### 3. اختبر Broadcast:

```bash
php artisan tinker --execute="
broadcast(new \App\Events\ScooterCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]));
echo 'Sent';
"
```

### 4. راقب Reverb Logs:

```bash
tail -f storage/logs/reverb.log
```

يجب أن ترى رسائل عن استقبال HTTP requests.

---

## 📝 إذا لم يعمل:

المشكلة قد تكون في Reverb Server نفسه. جرب:
1. إعادة تثبيت Reverb: `composer require laravel/reverb`
2. إعادة إنشاء المفاتيح: `php artisan reverb:install`
3. استخدام Redis للـ Broadcast كحل بديل

