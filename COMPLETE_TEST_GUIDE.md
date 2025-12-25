# ✅ دليل الاختبار الكامل

## 📋 الخطوات بالتفصيل:

### 1️⃣ في Postman:

#### الخطوة 1: الاتصال
- URL: `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
- اضغط **Connect**
- يجب أن ترى: `{"event":"pusher:connection_established",...}`

#### الخطوة 2: الاشتراك
أرسل:
```json
{
  "event": "pusher:subscribe",
  "data": {
    "channel": "scooter.ESP32_IMEI_001"
  }
}
```

يجب أن ترى: `{"event":"pusher_internal:subscription_succeeded","channel":"scooter.ESP32_IMEI_001"}`

#### الخطوة 3: معالجة Ping
عند استقبال:
```json
{"event":"pusher:ping"}
```

أرسل فوراً:
```json
{"event":"pusher:pong","data":{}}
```

**⚠️ مهم جداً:** إذا لم ترد على ping، الاتصال سينقطع بعد 30-120 ثانية!

### 2️⃣ على السيرفر:

#### راقب الـ logs:
```bash
cd /var/www/scoot
tail -f storage/logs/laravel.log
```

### 3️⃣ في Admin Panel:

1. افتح: `admin/scooters/1`
2. اضغط **Lock** أو **Unlock**

### 4️⃣ النتائج المتوقعة:

#### في Laravel Logs (على السيرفر):
```
[INFO] 🔒 Lock action triggered
[INFO] 📡 Calling sendCommandToScooter
[INFO] 🔍 sendCommandToScooter called
[INFO] 📡 Sending command to scooter via WebSocket
[INFO] Command broadcasted successfully
[INFO] ✅ sendCommandToScooter completed
```

#### في Postman:
يجب أن ترى رسالة:
```json
{
  "event": "command",
  "channel": "scooter.ESP32_IMEI_001",
  "data": {
    "commands": {
      "lock": true,
      "unlock": false
    },
    "timestamp": "2025-12-25T12:37:33+00:00",
    "timeout": 120,
    "ping_interval": 60
  }
}
```

**ملاحظة:** البيانات الآن تُرسل كـ JSON object مباشر (بدون escape)، مما يسهل التعامل معها في ESP32:

```cpp
// في ESP32، استقبل الرسالة مباشرة:
DynamicJsonDocument doc(1024);
deserializeJson(doc, message); // message هو الرسالة الكاملة

String event = doc["event"] | "";
JsonObject data = doc["data"];

if (event == "command") {
    bool lock = data["commands"]["lock"] | false;
    bool unlock = data["commands"]["unlock"] | false;
    int timeout = data["timeout"] | 120; // ثواني
    int pingInterval = data["ping_interval"] | 60; // ثواني
    String timestamp = data["timestamp"] | "";
    
    // تنفيذ الأوامر
    if (lock) {
        lockScooter();
    }
    if (unlock) {
        unlockScooter();
    }
}
```

**معلومات Timeout و Ping Interval:**
- `timeout` (activity_timeout): الوقت بالثواني قبل قطع الاتصال إذا لم يكن هناك نشاط (افتراضي: 120 ثانية)
- `ping_interval`: الفترة بين رسائل ping بالثواني (افتراضي: 60 ثانية)
- يمكن تعديلها في `.env`:
  ```env
  REVERB_APP_ACTIVITY_TIMEOUT=120
  REVERB_APP_PING_INTERVAL=60
  ```

---

## 🐛 إذا لم تصل الرسالة:

### التحقق 1: Postman متصل؟
- تأكد أن حالة الاتصال: **Connected** (أخضر)
- إذا كان **Disconnected**، أعد الاتصال

### التحقق 2: Channel صحيح؟
- تأكد أن Channel هو بالضبط: `scooter.ESP32_IMEI_001`
- **ليس:** `scooter.ESP32_IMEI_002` أو أي شيء آخر

### التحقق 3: الاشتراك نجح؟
- يجب أن ترى: `pusher_internal:subscription_succeeded`
- إذا لم تراها، أعد إرسال `pusher:subscribe`

### التحقق 4: Postman يرد على Ping؟
- راقب رسائل `pusher:ping`
- أرسل `pusher:pong` فوراً عند استقبالها
- إذا لم ترد، الاتصال سينقطع

### التحقق 5: Reverb Server يعمل؟
```bash
ps aux | grep reverb | grep -v grep
```

### التحقق 6: Broadcast يعمل؟
- تحقق من Laravel logs - يجب أن ترى `Command broadcasted successfully`
- إذا لم تراها، المشكلة في Broadcast
- إذا رأيتها، المشكلة في Reverb Server أو الاتصال

---

## 🔧 حلول سريعة:

### إذا Postman ينقطع:
- أعد الاتصال
- اشترك مرة أخرى
- راقب ping/pong

### إذا الرسائل لا تصل:
1. تحقق من Channel name
2. تحقق من Reverb Server
3. أعد تشغيل Reverb Server:
   ```bash
   pkill -f "reverb:start"
   sleep 2
   nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
   ```

### إذا Broadcast لا يعمل:
- تحقق من `.env`: `BROADCAST_CONNECTION=reverb`
- مسح cache: `php artisan config:clear`

---

## 📝 Checklist:

- [ ] Postman متصل (Connected)
- [ ] استقبلت `pusher:connection_established`
- [ ] أرسلت `pusher:subscribe`
- [ ] استقبلت `pusher_internal:subscription_succeeded`
- [ ] Channel name صحيح: `scooter.ESP32_IMEI_001`
- [ ] أردت على `pusher:ping` بـ `pusher:pong`
- [ ] Reverb Server يعمل
- [ ] Laravel logs تظهر `Command broadcasted successfully`
- [ ] رسالة `command` تصل في Postman

---

## 🎯 النتيجة المتوقعة:

عند الضغط على Lock/Unlock من Admin Panel:
- ✅ Laravel logs تظهر `Command broadcasted successfully`
- ✅ Postman يستقبل رسالة `command` فوراً

إذا حدث هذا، كل شيء يعمل بشكل صحيح! ✅



