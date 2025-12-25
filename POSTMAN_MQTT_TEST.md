# 🧪 اختبار MQTT في Postman

## ⚠️ الوضع الحالي

**الأوامر الآن تُرسل عبر MQTT فقط!** WebSocket لم يعد يُستخدم لإرسال الأوامر.

---

## ✅ الطريقة 1: HTTP Endpoint في Postman (الأسهل والأفضل)

هذا يعطيك نفس التنسيق الذي ستحصل عليه من MQTT!

### الخطوات:

1. **New Request** → اختر **POST**
2. **URL:** `https://linerscoot.com/api/v1/scooter/commands`
3. **Headers:**
   ```
   Content-Type: application/json
   ```
4. **Body (raw JSON):**
   ```json
   {
     "imei": "ESP32_IMEI_001"
   }
   ```
5. اضغط **Send**

### Response (نفس تنسيق MQTT):
```json
{
  "event": "command",
  "data": {
    "commands": {
      "lock": true,
      "unlock": false
    },
    "timestamp": "2025-12-25T13:30:06+00:00",
    "timeout": 120,
    "ping_interval": 60
  },
  "channel": "scooter.ESP32_IMEI_001"
}
```

✅ **`data` هو JSON object مباشر (ليس string)!**

---

## 🔌 الطريقة 2: WebSocket في Postman (Legacy - للاختبار فقط)

**⚠️ تحذير:** الأوامر لن تصل عبر WebSocket لأننا استبدلناه بـ MQTT!

لكن يمكنك استخدامه للاختبار إذا كان Reverb لا يزال يعمل:

### الخطوات:

1. **New Request** → اختر **WebSocket**
2. **URL:** `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
3. اضغط **Connect**
4. يجب أن ترى: `{"event":"pusher:connection_established",...}`
5. أرسل للاشتراك:
   ```json
   {
     "event": "pusher:subscribe",
     "data": {
       "channel": "scooter.ESP32_IMEI_001"
     }
   }
   ```
6. يجب أن ترى: `{"event":"pusher_internal:subscription_succeeded","channel":"scooter.ESP32_IMEI_001"}`
7. عند استقبال ping:
   ```json
   {"event":"pusher:ping"}
   ```
   أرسل:
   ```json
   {"event":"pusher:pong","data":{}}
   ```

**⚠️ ملاحظة:** الأوامر لن تصل عبر WebSocket لأن `sendCommandToScooter()` يستخدم MQTT الآن!

---

## 🧪 الطريقة 3: اختبار MQTT مباشرة (Terminal)

### الخطوة 1: الاشتراك في Topic

```bash
mosquitto_sub -h localhost -t "scooter/ESP32_IMEI_001/commands" -v
```

### الخطوة 2: إرسال أمر من Admin Panel

1. افتح: `https://linerscoot.com/admin/scooters/1`
2. تأكد أن `device_imei` = `ESP32_IMEI_001`
3. اضغط **Lock** أو **Unlock**

### الخطوة 3: مشاهدة الرسالة

سترى في Terminal:
```
scooter/ESP32_IMEI_001/commands {"event":"command","data":{"commands":{"lock":true,"unlock":false},"timestamp":"2025-12-25T13:30:06+00:00","timeout":120,"ping_interval":60}}
```

---

## 📊 مقارنة الطرق

| الطريقة | Postman | JSON Format | يعمل الآن؟ |
|---------|---------|-------------|------------|
| HTTP Endpoint | ✅ | Object مباشر | ✅ نعم |
| WebSocket | ✅ | String (escape) | ⚠️ لا (استبدلناه) |
| MQTT (Terminal) | ❌ | Object مباشر | ✅ نعم |

---

## ✅ الخلاصة

**للاستخدام في Postman:**

1. **استخدم HTTP Endpoint:** `POST /api/v1/scooter/commands`
   - ✅ يعمل في Postman
   - ✅ يعطيك نفس تنسيق MQTT
   - ✅ JSON object مباشر

2. **WebSocket:** لا يعمل للأوامر (استبدلناه بـ MQTT)

3. **MQTT مباشرة:** استخدم Terminal أو MQTTX

---

## 🎯 الخطوات السريعة في Postman

1. **New Request** → **POST**
2. **URL:** `https://linerscoot.com/api/v1/scooter/commands`
3. **Body:**
   ```json
   {
     "imei": "ESP32_IMEI_001"
   }
   ```
4. **Send**
5. ✅ تحصل على نفس تنسيق MQTT!

