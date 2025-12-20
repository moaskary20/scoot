# 🚀 دليل سريع لمبرمج ESP32

## 📋 المعلومات الأساسية

### 1. روابط الاتصال

**🌐 الدومين:**
```
https://linerscoot.com
```

**WebSocket (للاستماع للأوامر) - للإنتاج:**
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

**WebSocket (للاستماع للأوامر) - للتطوير:**
```
ws://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

**HTTP (لإرسال البيانات) - للإنتاج:**
```
POST https://linerscoot.com/api/v1/scooter/message
```

**HTTP (لإرسال البيانات) - للتطوير:**
```
POST http://localhost:8000/api/v1/scooter/message
```

### 2. تنسيق الرسائل

#### إرسال بيانات:
```json
POST /api/v1/scooter/message
Content-Type: application/json

{
  "event": "update-location",
  "imei": "YOUR_IMEI",
  "data": {
    "latitude": 30.0444,
    "longitude": 31.2357,
    "battery_percentage": 85,
    "lock_status": true
  }
}
```

#### استقبال أوامر:
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

### 3. الأحداث المتاحة

| الحدث | الوصف | البيانات المطلوبة |
|------|-------|------------------|
| `authenticate` | التحقق من IMEI | `{}` |
| `update-location` | تحديث الموقع والبطارية | `latitude`, `longitude`, `battery_percentage`, `lock_status` |
| `update-lock-status` | تحديث حالة القفل | `lock_status` |
| `update-battery` | تحديث البطارية | `battery_percentage` |
| `get-commands` | الحصول على الأوامر | `{}` |

### 4. المكتبات المطلوبة

```cpp
#include <WiFi.h>
#include <WebSocketsClient.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
```

### 5. مثال بسيط

```cpp
// إعداد المتغيرات (للإنتاج)
const char* wsServer = "linerscoot.com";
const int wsPort = 8080;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";
const char* httpServer = "https://linerscoot.com";
String imei = "YOUR_IMEI";  // يجب أن يكون مطابقاً لقاعدة البيانات

// للتطوير استخدم:
// const char* wsServer = "localhost";
// const char* httpServer = "http://localhost:8000";

void sendLocation(float lat, float lon, int battery, bool locked) {
    HTTPClient http;
    http.begin(httpServer + "/api/v1/scooter/message");
    http.addHeader("Content-Type", "application/json");
    
    String json = "{"
        "\"event\":\"update-location\","
        "\"imei\":\"" + imei + "\","
        "\"data\":{"
            "\"latitude\":" + String(lat, 7) + ","
            "\"longitude\":" + String(lon, 7) + ","
            "\"battery_percentage\":" + String(battery) + ","
            "\"lock_status\":" + String(locked ? "true" : "false") +
        "}"
    "}";
    
    http.POST(json);
    http.end();
}
```

---

## 📚 للتفاصيل الكاملة

- **دليل المطور:** `ESP32_DEVELOPER_GUIDE.md` - دليل شامل مع أمثلة كاملة
- **دليل WebSocket:** `ESP32_WEBSOCKET_GUIDE.md` - تفاصيل WebSocket

---

## ⚠️ ملاحظات مهمة

1. **IMEI:** يجب أن يكون `device_imei` في قاعدة البيانات مطابقاً لـ IMEI في الكود
2. **الاتصال:** أرسل `update-location` كل 5-10 ثواني
3. **الأوامر:** استمع للأحداث `command` من WebSocket
4. **التأكيد:** أرسل `update-lock-status` بعد تنفيذ أي أمر

