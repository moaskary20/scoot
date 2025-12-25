# 📡 ESP32 Commands Endpoint - JSON Object Format

## 🎯 الهدف

هذا الـ endpoint يرسل الأوامر كـ **JSON object مباشر** (بدون escape) في نفس التنسيق الذي يتوقعه ESP32.

---

## 🔗 Endpoint

**URL:** `POST /api/v1/scooter/commands`

**Base URL:**
- للتطوير: `http://localhost:8000/api/v1/scooter/commands`
- للإنتاج: `https://your-domain.com/api/v1/scooter/commands`

---

## 📤 Request

**Method:** `POST`

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "imei": "ESP32_IMEI_001"
}
```

---

## 📥 Response

**Success (200):**
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

**Error (404):**
```json
{
  "success": false,
  "message": "Scooter not found"
}
```

**Error (400):**
```json
{
  "success": false,
  "message": "Invalid request",
  "errors": {
    "imei": ["The imei field is required."]
  }
}
```

---

## 💻 مثال في ESP32

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverUrl = "https://your-domain.com/api/v1/scooter/commands";
const char* imei = "ESP32_IMEI_001";

void setup() {
    Serial.begin(115200);
    WiFi.begin(ssid, password);
    
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    
    Serial.println("WiFi connected");
}

void loop() {
    getCommands();
    delay(5000); // كل 5 ثواني
}

void getCommands() {
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");
    
    // إعداد Request Body
    DynamicJsonDocument requestDoc(256);
    requestDoc["imei"] = imei;
    
    String requestBody;
    serializeJson(requestDoc, requestBody);
    
    // إرسال Request
    int httpResponseCode = http.POST(requestBody);
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        
        // فك تشفير Response
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        
        if (!error) {
            String event = doc["event"] | "";
            
            if (event == "command") {
                // البيانات مباشرة كـ JSON object (بدون escape)
                JsonObject data = doc["data"];
                
                bool lock = data["commands"]["lock"] | false;
                bool unlock = data["commands"]["unlock"] | false;
                int timeout = data["timeout"] | 120;
                int pingInterval = data["ping_interval"] | 60;
                String timestamp = data["timestamp"] | "";
                
                Serial.print("Lock: ");
                Serial.println(lock);
                Serial.print("Unlock: ");
                Serial.println(unlock);
                Serial.print("Timeout: ");
                Serial.println(timeout);
                Serial.print("Ping Interval: ");
                Serial.println(pingInterval);
                
                // تنفيذ الأوامر
                if (lock) {
                    lockScooter();
                }
                if (unlock) {
                    unlockScooter();
                }
            }
        } else {
            Serial.print("JSON parse error: ");
            Serial.println(error.c_str());
        }
    } else {
        Serial.print("HTTP Error: ");
        Serial.println(httpResponseCode);
    }
    
    http.end();
}

void lockScooter() {
    // كود قفل السكوتر
    Serial.println("Locking scooter...");
}

void unlockScooter() {
    // كود فتح السكوتر
    Serial.println("Unlocking scooter...");
}
```

---

## 🔄 الفرق بين Endpoints

### 1. `/api/v1/scooter/get-commands` (القديم)
```json
{
  "success": true,
  "commands": {
    "lock": true,
    "unlock": false
  },
  "scooter_status": "available",
  "current_lock_status": false
}
```

### 2. `/api/v1/scooter/commands` (الجديد - WebSocket Format)
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

**الفرق:**
- الـ endpoint الجديد يرسل البيانات بنفس تنسيق WebSocket (مع `event`, `data`, `channel`)
- `data` هو JSON object مباشر (بدون escape)
- يتضمن `timeout` و `ping_interval` في البيانات

---

## ✅ متى تستخدمه؟

استخدم `/api/v1/scooter/commands` إذا كنت تريد:
- نفس تنسيق WebSocket (للتطوير والاختبار)
- JSON object مباشر في `data` (بدون escape)
- معلومات `timeout` و `ping_interval`

استخدم `/api/v1/scooter/get-commands` إذا كنت تريد:
- تنسيق أبسط
- معلومات إضافية مثل `scooter_status` و `current_lock_status`

---

## 📝 ملاحظات

1. **Polling:** هذا endpoint يعمل بـ polling - ESP32 يستدعيه كل 5-10 ثواني
2. **JSON Object:** `data` هو JSON object مباشر (ليس string) - لا حاجة لفك التشفير
3. **Timeout & Ping Interval:** متوفران في البيانات للمرجع
4. **WebSocket Alternative:** هذا endpoint بديل لـ WebSocket إذا كنت تفضل HTTP polling

---

## 🔧 اختبار في Postman

1. **Method:** `POST`
2. **URL:** `http://localhost:8000/api/v1/scooter/commands`
3. **Headers:**
   ```
   Content-Type: application/json
   ```
4. **Body:**
   ```json
   {
     "imei": "ESP32_IMEI_001"
   }
   ```
5. **Response:** يجب أن ترى نفس التنسيق المطلوب:
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

---

## ✅ الخلاصة

- ✅ `data` هو JSON object مباشر (بدون escape)
- ✅ نفس تنسيق WebSocket
- ✅ يتضمن `timeout` و `ping_interval`
- ✅ سهل الاستخدام في ESP32

