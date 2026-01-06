# 📋 خطوات تجربة Postman - خطوة بخطوة

## ✅ الخطوات الكاملة:

### 1️⃣ افتح Postman

### 2️⃣ أنشئ WebSocket Request جديد:
- اضغط **New** → **WebSocket Request**
- أو اضغط `Ctrl+N` ثم اختر **WebSocket Request**

### 3️⃣ الاتصال:
- في حقل URL، أدخل:
  ```
  ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
  ```
- اضغط **Connect**

### 4️⃣ التحقق من الاتصال:
- يجب أن ترى **Connected** (أخضر) في أسفل الشاشة
- يجب أن ترى رسالة:
  ```json
  {
    "event": "pusher:connection_established",
    "data": {
      "socket_id": "...",
      "activity_timeout": 120
    }
  }
  ```

### 5️⃣ الاشتراك في Channel:
- في تبويب **Message**، أدخل:
  ```json
  {
    "event": "pusher:subscribe",
    "data": {
      "channel": "scooter.ESP32_IMEI_001"
    }
  }
  ```
- اضغط **Send**

### 6️⃣ التحقق من الاشتراك:
- يجب أن ترى رسالة:
  ```json
  {
    "event": "pusher_internal:subscription_succeeded",
    "channel": "scooter.ESP32_IMEI_001"
  }
  ```

### 7️⃣ معالجة Ping (مهم جداً!):
- عند استقبال:
  ```json
  {"event":"pusher:ping"}
  ```
- أرسل فوراً:
  ```json
  {"event":"pusher:pong","data":{}}
  ```
- **⚠️ مهم:** إذا لم ترد على ping، الاتصال سينقطع!

### 8️⃣ اختبار Broadcast:
- افتح Admin Panel: `admin/scooters/1`
- اضغط **Lock** أو **Unlock**
- في Postman، يجب أن ترى رسالة:
  ```json
  {
    "event": "command",
    "channel": "scooter.ESP32_IMEI_001",
    "data": {
      "commands": {
        "lock": true,
        "unlock": false
      },
      "timestamp": "2025-12-24T..."
    }
  }
  ```

---

## 🎯 Checklist:

- [ ] Postman متصل (Connected - أخضر)
- [ ] استقبلت `pusher:connection_established`
- [ ] أرسلت `pusher:subscribe`
- [ ] استقبلت `pusher_internal:subscription_succeeded`
- [ ] Channel name صحيح: `scooter.ESP32_IMEI_001`
- [ ] أردت على `pusher:ping` بـ `pusher:pong`
- [ ] اضغطت Lock/Unlock من Admin Panel
- [ ] استقبلت رسالة `command` في Postman

---

## 🐛 إذا لم تصل الرسالة:

### 1. تحقق من الاتصال:
- تأكد أن Postman متصل (Connected)
- إذا كان Disconnected، أعد الاتصال

### 2. تحقق من Channel:
- تأكد أن Channel هو بالضبط: `scooter.ESP32_IMEI_001`
- **ليس:** `scooter.ESP32_IMEI_002` أو أي شيء آخر

### 3. تحقق من Ping/Pong:
- راقب رسائل `pusher:ping`
- أرسل `pusher:pong` فوراً عند استقبالها
- إذا لم ترد، الاتصال سينقطع

### 4. تحقق من Reverb Server:
```bash
ps aux | grep reverb | grep -v grep
```
يجب أن ترى Reverb Server يعمل.

### 5. تحقق من Logs:
على السيرفر:
```bash
tail -f storage/logs/laravel.log
```
يجب أن ترى:
```
[INFO] Command broadcasted successfully
```

---

## ✅ النتيجة المتوقعة:

عند الضغط على Lock/Unlock من Admin Panel:
- ✅ Laravel logs تظهر `Command broadcasted successfully`
- ✅ Postman يستقبل رسالة `command` فوراً

إذا حدث هذا، كل شيء يعمل بشكل صحيح! 🎉







