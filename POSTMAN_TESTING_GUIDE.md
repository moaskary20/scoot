# 🧪 دليل التجربة على Postman - خطوة بخطوة

## 📋 المتطلبات
- Postman مثبت
- اتصال بالإنترنت
- Reverb Server يعمل على السيرفر

---

## 🔧 الخطوة 1: تجربة HTTP Endpoint

### 1.1. إعداد Request جديد

1. افتح Postman
2. اضغط على **New** → **HTTP Request**
3. احفظ الـ Request باسم: `ESP32 - authenticate`

### 1.2. تجربة authenticate

#### الإعدادات:
- **Method:** `POST`
- **URL:** `https://linerscoot.com/api/v1/scooter/message`

#### Headers:
```
Content-Type: application/json
```

#### Body (raw JSON):
```json
{
  "event": "authenticate",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

#### النتيجة المتوقعة:
```json
{
  "success": true,
  "scooter_id": 1,
  "code": "SCO-001",
  "commands": {
    "lock": false,
    "unlock": true
  },
  "status": "available"
}
```

---

### 1.3. تجربة update-location

#### الإعدادات:
- **Method:** `POST`
- **URL:** `https://linerscoot.com/api/v1/scooter/message`

#### Headers:
```
Content-Type: application/json
```

#### Body (raw JSON):
```json
{
  "event": "update-location",
  "imei": "ESP32_IMEI_001",
  "data": {
    "latitude": 30.0444,
    "longitude": 31.2357,
    "battery_percentage": 85,
    "lock_status": true
  }
}
```

#### النتيجة المتوقعة:
```json
{
  "success": true,
  "message": "Location updated",
  "commands": {
    "lock": false,
    "unlock": false
  },
  "scooter_status": "available"
}
```

---

### 1.4. تجربة update-lock-status

#### الإعدادات:
- **Method:** `POST`
- **URL:** `https://linerscoot.com/api/v1/scooter/message`

#### Headers:
```
Content-Type: application/json
```

#### Body (raw JSON):
```json
{
  "event": "update-lock-status",
  "imei": "ESP32_IMEI_001",
  "data": {
    "lock_status": true
  }
}
```

#### النتيجة المتوقعة:
```json
{
  "success": true,
  "message": "Lock status updated",
  "is_locked": true
}
```

---

### 1.5. تجربة update-battery

#### الإعدادات:
- **Method:** `POST`
- **URL:** `https://linerscoot.com/api/v1/scooter/message`

#### Headers:
```
Content-Type: application/json
```

#### Body (raw JSON):
```json
{
  "event": "update-battery",
  "imei": "ESP32_IMEI_001",
  "data": {
    "battery_percentage": 85
  }
}
```

#### النتيجة المتوقعة:
```json
{
  "success": true,
  "message": "Battery updated",
  "battery_percentage": 85
}
```

---

### 1.6. تجربة get-commands

#### الإعدادات:
- **Method:** `POST`
- **URL:** `https://linerscoot.com/api/v1/scooter/message`

#### Headers:
```
Content-Type: application/json
```

#### Body (raw JSON):
```json
{
  "event": "get-commands",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

#### النتيجة المتوقعة:
```json
{
  "success": true,
  "commands": {
    "lock": false,
    "unlock": true
  },
  "scooter_status": "available",
  "current_lock_status": false
}
```

---

## 🔌 الخطوة 2: تجربة WebSocket (للاستماع)

### 2.1. إنشاء WebSocket Request

1. في Postman، اضغط على **New** → **WebSocket Request**
2. احفظ الـ Request باسم: `ESP32 - WebSocket Listener`

### 2.2. الاتصال

#### الإعدادات:
- **URL:** `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
- اضغط على **Connect**

#### النتيجة المتوقعة:
- يجب أن ترى "Connected" في حالة الاتصال
- يجب أن ترى رسالة `pusher:connection_established` في Messages

---

### 2.3. الاشتراك في Channel

بعد الاتصال الناجح، في حقل الرسالة أدخل:

```json
{
  "event": "pusher:subscribe",
  "data": {
    "channel": "scooter.ESP32_IMEI_001"
  }
}
```

ثم اضغط **Send**

#### النتيجة المتوقعة:
```json
{
  "event": "pusher_internal:subscription_succeeded",
  "channel": "scooter.ESP32_IMEI_001"
}
```

---

### 2.4. استقبال الأوامر

بعد الاشتراك في channel، ستستقبل الأوامر من السيرفر في قسم "Messages" عند إرسال أمر من Admin Panel:

```json
{
  "event": "command",
  "channel": "scooter.ESP32_IMEI_001",
  "data": {
    "commands": {
      "lock": true,
      "unlock": false
    },
    "timestamp": "2024-01-01T12:00:00Z"
  }
}
```

---

## 📝 ملاحظات مهمة

### ⚠️ لا ترسل البيانات عبر WebSocket مباشرة

**خطأ:**
```json
{
  "event": "update-lock-status",
  "imei": "ESP32_IMEI_001",
  "data": {"lock_status": true}
}
```
هذا سيسبب خطأ: `Invalid message format`

**صحيح:**
استخدم HTTP POST:
```
POST https://linerscoot.com/api/v1/scooter/message
```

---

## 🔄 سيناريو تجربة كامل

### السيناريو 1: بدء التشغيل

1. **WebSocket:** الاتصال والاشتراك في channel
2. **HTTP:** إرسال `authenticate`
3. **النتيجة:** الحصول على معلومات السكوتر والأوامر الأولية

### السيناريو 2: تحديث الموقع

1. **HTTP:** إرسال `update-location` كل 5 ثواني
2. **النتيجة:** تحديث الموقع في قاعدة البيانات

### السيناريو 3: استقبال أمر من Admin

1. **Admin Panel:** إرسال أمر lock/unlock
2. **WebSocket:** استقبال الأمر في Postman
3. **HTTP:** إرسال `update-lock-status` بعد التنفيذ

---

## ✅ Checklist للتجربة

### HTTP Endpoints:
- [ ] authenticate - يعمل بنجاح
- [ ] update-location - يعمل بنجاح
- [ ] update-lock-status - يعمل بنجاح
- [ ] update-battery - يعمل بنجاح
- [ ] get-commands - يعمل بنجاح

### WebSocket:
- [ ] الاتصال بنجاح
- [ ] الاشتراك في channel بنجاح
- [ ] استقبال الأوامر من السيرفر

---

## 🐛 حل المشاكل الشائعة

### المشكلة 1: "Invalid message format"
**السبب:** محاولة إرسال بيانات عبر WebSocket مباشرة  
**الحل:** استخدم HTTP POST endpoint

### المشكلة 2: "Scooter not found"
**السبب:** IMEI غير موجود في قاعدة البيانات  
**الحل:** أضف السكوتر في قاعدة البيانات مع `device_imei` الصحيح

### المشكلة 3: لا تصل الأوامر عبر WebSocket
**السبب:** لم يتم الاشتراك في channel  
**الحل:** تأكد من إرسال `pusher:subscribe` بعد الاتصال

### المشكلة 4: "Connection refused"
**السبب:** Reverb Server غير مشغل  
**الحل:** شغل Reverb Server على السيرفر:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### المشكلة 5: "Pong reply not received in time" أو "1006 Abnormal Closure"
**السبب:** السيرفر يرسل ping messages ويتوقع pong response  
**الحل:** 
- في Postman: لا يمكن إرسال pong تلقائياً، لكن يمكن تجربة الاتصال مرة أخرى
- في ESP32: تأكد من معالجة `pusher:ping` وإرسال `pusher:pong`:
  ```cpp
  if (event == "pusher:ping") {
      webSocket.sendTXT("{\"event\":\"pusher:pong\",\"data\":{}}");
  }
  ```
- يمكن زيادة `REVERB_APP_ACTIVITY_TIMEOUT` في `.env` إلى 120 ثانية

---

## 📞 للدعم

إذا واجهت أي مشاكل:
1. تحقق من logs: `storage/logs/laravel.log`
2. تحقق من Reverb logs
3. تأكد من أن جميع المتغيرات في `.env` صحيحة

