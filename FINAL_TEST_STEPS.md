# 🧪 خطوات الاختبار النهائية

## على السيرفر:

### 1️⃣ شغّل سكريبت التشخيص:

```bash
cd /var/www/scoot
chmod +x COMPLETE_DIAGNOSIS.sh
./COMPLETE_DIAGNOSIS.sh
```

### 2️⃣ راقب Laravel Logs:

```bash
tail -f storage/logs/laravel.log
```

### 3️⃣ راقب Reverb Logs:

```bash
tail -f storage/logs/reverb.log
```

---

## في Postman:

### 1️⃣ تأكد من الاتصال:
- ✅ متصل: `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
- ✅ Connected (أخضر)

### 2️⃣ تأكد من الاشتراك:
- ✅ أرسلت: `{"event":"pusher:subscribe","data":{"channel":"scooter.ESP32_IMEI_001"}}`
- ✅ استقبلت: `{"event":"pusher_internal:subscription_succeeded",...}`

### 3️⃣ معالجة Ping:
- ✅ عند استقبال `pusher:ping`، أرسل فوراً `pusher:pong`

---

## في Admin Panel:

### 1️⃣ افتح:
`admin/scooters/1`

### 2️⃣ اضغط:
- **Lock** أو **Unlock**

---

## النتيجة المتوقعة:

### في Laravel Logs:
```
[INFO] 🔒 Lock action triggered
[INFO] 📡 Calling sendCommandToScooter
[INFO] 🔍 sendCommandToScooter called
[INFO] 📡 Sending command to scooter via WebSocket
[INFO] Command broadcasted successfully
[INFO] ✅ sendCommandToScooter completed
```

### في Reverb Logs:
يجب أن ترى رسائل عن استقبال HTTP requests وإرسال الرسائل للعملاء.

### في Postman:
يجب أن ترى:
```json
{
  "event": "command",
  "channel": "scooter.ESP32_IMEI_001",
  "data": {
    "commands": {
      "lock": true,
      "unlock": false
    },
    "timestamp": "..."
  }
}
```

---

## 🐛 إذا لم تصل الرسالة:

### 1. تحقق من Reverb Logs:
```bash
tail -50 storage/logs/reverb.log
```

ابحث عن:
- رسائل عن استقبال HTTP requests
- رسائل عن إرسال الرسائل للعملاء
- أي أخطاء

### 2. تحقق من Laravel Logs:
```bash
tail -50 storage/logs/laravel.log | grep -i "command\|broadcast"
```

### 3. تحقق من Postman:
- هل متصل؟
- هل مشترك في Channel الصحيح؟
- هل أردت على ping؟

### 4. أعد تشغيل Reverb Server:
```bash
pkill -f "reverb:start"
sleep 3
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
```

---

## 📝 أرسل النتائج:

بعد تشغيل سكريبت التشخيص والاختبار، أرسل:
1. نتيجة `./COMPLETE_DIAGNOSIS.sh`
2. آخر 30 سطر من `reverb.log` بعد الضغط على Lock/Unlock
3. آخر 20 سطر من `laravel.log` بعد الضغط على Lock/Unlock
4. هل تصل رسالة `command` في Postman؟







