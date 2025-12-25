# 🔌 دليل التكامل مع ESP32 - البيانات الصحيحة

## 📡 معلومات الاتصال

### MQTT Broker (للاستماع للأوامر) - **مستحسن**
```
Host: linerscoot.com (أو IP السيرفر)
Port: 1883
Topic: scooter/{IMEI}/commands
```

**راجع:** [ESP32_MQTT_GUIDE.md](./ESP32_MQTT_GUIDE.md) للتفاصيل الكاملة

### HTTP Endpoint (لإرسال البيانات)
```
POST https://linerscoot.com/api/v1/scooter/message
```

### WebSocket (Legacy - تم استبداله بـ MQTT)
```
ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```
**ملاحظة:** تم استبدال WebSocket بـ MQTT للحصول على JSON object مباشر. استخدم MQTT للحصول على أفضل تجربة.

---

## 📤 البيانات الصحيحة لإرسالها من ESP32

### 1. authenticate
**الغرض:** التحقق من IMEI والحصول على معلومات السكوتر

**HTTP POST Request:**
```
URL: https://linerscoot.com/api/v1/scooter/message
Method: POST
Headers:
  Content-Type: application/json

Body:
{
  "event": "authenticate",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

**الرد المتوقع:**
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

### 2. update-location
**الغرض:** إرسال الموقع والبطارية وحالة القفل

**HTTP POST Request:**
```
URL: https://linerscoot.com/api/v1/scooter/message
Method: POST
Headers:
  Content-Type: application/json

Body:
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

**الرد المتوقع:**
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

### 3. update-lock-status
**الغرض:** تحديث حالة القفل بعد تنفيذ الأمر

**HTTP POST Request:**
```
URL: https://linerscoot.com/api/v1/scooter/message
Method: POST
Headers:
  Content-Type: application/json

Body:
{
  "event": "update-lock-status",
  "imei": "ESP32_IMEI_001",
  "data": {
    "lock_status": true
  }
}
```

**الرد المتوقع:**
```json
{
  "success": true,
  "message": "Lock status updated",
  "is_locked": true
}
```

---

### 4. update-battery
**الغرض:** تحديث نسبة البطارية فقط

**HTTP POST Request:**
```
URL: https://linerscoot.com/api/v1/scooter/message
Method: POST
Headers:
  Content-Type: application/json

Body:
{
  "event": "update-battery",
  "imei": "ESP32_IMEI_001",
  "data": {
    "battery_percentage": 85
  }
}
```

**الرد المتوقع:**
```json
{
  "success": true,
  "message": "Battery updated",
  "battery_percentage": 85
}
```

---

### 5. get-commands
**الغرض:** الحصول على الأوامر الحالية

**HTTP POST Request:**
```
URL: https://linerscoot.com/api/v1/scooter/message
Method: POST
Headers:
  Content-Type: application/json

Body:
{
  "event": "get-commands",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

**الرد المتوقع:**
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

## 📥 استقبال الأوامر من السيرفر (WebSocket)

### الاتصال بـ WebSocket
```
URL: ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

### الاشتراك في Channel
بعد الاتصال، أرسل:
```json
{
  "event": "pusher:subscribe",
  "data": {
    "channel": "scooter.ESP32_IMEI_001"
  }
}
```

### استقبال الأوامر
ستستقبل من السيرفر:
```json
{
  "event": "command",
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

## 🧪 طريقة التجربة على Postman

### الخطوة 1: تجربة HTTP Endpoint

#### 1.1. تجربة authenticate

1. افتح Postman
2. اختر **New Request**
3. حدد **POST**
4. أدخل URL: `https://linerscoot.com/api/v1/scooter/message`
5. اذهب إلى **Headers** وأضف:
   ```
   Key: Content-Type
   Value: application/json
   ```
6. اذهب إلى **Body** → اختر **raw** → اختر **JSON**
7. أدخل:
   ```json
   {
     "event": "authenticate",
     "imei": "ESP32_IMEI_001",
     "data": {}
   }
   ```
8. اضغط **Send**
9. يجب أن تحصل على رد يحتوي على `success: true` ومعلومات السكوتر

#### 1.2. تجربة update-location

1. نفس الخطوات السابقة
2. في **Body**، أدخل:
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
3. اضغط **Send**

#### 1.3. تجربة update-lock-status

1. نفس الخطوات السابقة
2. في **Body**، أدخل:
   ```json
   {
     "event": "update-lock-status",
     "imei": "ESP32_IMEI_001",
     "data": {
       "lock_status": true
     }
   }
   ```
3. اضغط **Send**

#### 1.4. تجربة update-battery

1. نفس الخطوات السابقة
2. في **Body**، أدخل:
   ```json
   {
     "event": "update-battery",
     "imei": "ESP32_IMEI_001",
     "data": {
       "battery_percentage": 85
     }
   }
   ```
3. اضغط **Send**

#### 1.5. تجربة get-commands

1. نفس الخطوات السابقة
2. في **Body**، أدخل:
   ```json
   {
     "event": "get-commands",
     "imei": "ESP32_IMEI_001",
     "data": {}
   }
   ```
3. اضغط **Send**

---

### الخطوة 2: تجربة WebSocket (للاستماع)

#### 2.1. الاتصال بـ WebSocket

1. في Postman، اختر **New** → **WebSocket Request**
2. أدخل URL: `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
3. اضغط **Connect**
4. يجب أن ترى "Connected" في حالة الاتصال

#### 2.2. الاشتراك في Channel

1. بعد الاتصال، في حقل الرسالة، أدخل:
   ```json
   {
     "event": "pusher:subscribe",
     "data": {
       "channel": "scooter.ESP32_IMEI_001"
     }
   }
   ```
2. اضغط **Send**
3. يجب أن تحصل على رد `pusher_internal:subscription_succeeded`

#### 2.3. استقبال الأوامر

- بعد الاشتراك، ستستقبل الأوامر من السيرفر في قسم "Messages"
- الأوامر ستكون على شكل:
  ```json
  {
    "event": "command",
    "data": {
      "commands": {
        "lock": true,
        "unlock": false
      }
    }
  }
  ```

---

## 📋 ملخص للبرمجة في ESP32

### الكود المطلوب في ESP32

```cpp
// HTTP Server URL
const char* httpServer = "https://linerscoot.com";

// WebSocket Server (للاستماع فقط)
const char* wsServer = "linerscoot.com";
const int wsPort = 8080;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";

// IMEI الخاص بالسكوتر
String imei = "ESP32_IMEI_001";

// دالة لإرسال HTTP Request
void sendHttpMessage(String event, JsonObject data) {
    HTTPClient http;
    http.begin(httpServer + "/api/v1/scooter/message");
    http.addHeader("Content-Type", "application/json");
    
    DynamicJsonDocument doc(512);
    doc["event"] = event;
    doc["imei"] = imei;
    doc["data"] = data;
    
    String json;
    serializeJson(doc, json);
    
    int httpResponseCode = http.POST(json);
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        // معالجة الرد
    }
    
    http.end();
}

// مثال: authenticate
void authenticate() {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    sendHttpMessage("authenticate", data);
}

// مثال: update-location
void updateLocation(float lat, float lon, int battery, bool locked) {
    DynamicJsonDocument doc(512);
    JsonObject data = doc.to<JsonObject>();
    data["latitude"] = lat;
    data["longitude"] = lon;
    data["battery_percentage"] = battery;
    data["lock_status"] = locked;
    
    sendHttpMessage("update-location", data);
}

// مثال: update-lock-status
void updateLockStatus(bool locked) {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    data["lock_status"] = locked;
    
    sendHttpMessage("update-lock-status", data);
}

// مثال: update-battery
void updateBattery(int battery) {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    data["battery_percentage"] = battery;
    
    sendHttpMessage("update-battery", data);
}

// مثال: get-commands
void getCommands() {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    sendHttpMessage("get-commands", data);
}
```

---

## 🔄 خطة العمل الموصى بها

### عند بدء تشغيل ESP32:
1. ✅ الاتصال بـ WebSocket: `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
2. ✅ الاشتراك في channel: `scooter.ESP32_IMEI_001`
3. ✅ إرسال `authenticate` عبر HTTP POST
4. ✅ معالجة الأوامر الواردة في الرد

### أثناء التشغيل:
1. ✅ إرسال `update-location` كل 5-10 ثواني عبر HTTP
2. ✅ الاستماع لأحداث `command` من Server عبر WebSocket
3. ✅ تنفيذ الأوامر فوراً عند استلامها (lock/unlock)
4. ✅ إرسال `update-lock-status` بعد تنفيذ الأمر عبر HTTP

### عند تغيير البطارية فقط:
- ✅ إرسال `update-battery` عبر HTTP

---

## ⚠️ ملاحظات مهمة

1. **WebSocket = للاستماع فقط**
   - لا ترسل البيانات عبر WebSocket مباشرة
   - استخدم WebSocket فقط للاستماع للأوامر من السيرفر

2. **HTTP = لإرسال البيانات**
   - جميع الرسائل (authenticate, update-location, etc.) ترسل عبر HTTP POST
   - Endpoint: `https://linerscoot.com/api/v1/scooter/message`

3. **IMEI مهم**
   - تأكد من أن `device_imei` في قاعدة البيانات مطابق لـ IMEI المرسل
   - Channel name: `scooter.{IMEI}`

4. **Content-Type مهم**
   - يجب إرسال Header: `Content-Type: application/json`

---

## 🐛 استكشاف الأخطاء

### خطأ "Invalid message format"
- **السبب:** محاولة إرسال بيانات مباشرة عبر WebSocket
- **الحل:** استخدم HTTP POST endpoint لإرسال البيانات

### خطأ "Scooter not found"
- **السبب:** IMEI غير موجود في قاعدة البيانات
- **الحل:** تأكد من إضافة السكوتر في قاعدة البيانات مع `device_imei` الصحيح

### لا تصل الأوامر من السيرفر
- ✅ تأكد من الاشتراك في channel الصحيح
- ✅ تحقق من أن IMEI صحيح في قاعدة البيانات
- ✅ تأكد من أن Reverb Server يعمل

---

## 📞 للدعم

إذا واجهت أي مشاكل:
1. تحقق من logs: `storage/logs/laravel.log`
2. تحقق من Reverb logs
3. تأكد من أن جميع المتغيرات في `.env` صحيحة

