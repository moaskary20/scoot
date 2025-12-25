# 🔌 دليل WebSocket لـ ESP32 (Legacy)

## ⚠️ ملاحظة مهمة

**تم استبدال WebSocket بـ MQTT!**

للاستخدام الحالي، راجع: [ESP32_MQTT_GUIDE.md](./ESP32_MQTT_GUIDE.md)

MQTT يوفر:
- ✅ JSON object مباشر (بدون escape)
- ✅ Retain messages
- ✅ QoS levels
- ✅ خفيف الوزن - مناسب لـ ESP32

---

## نظرة عامة (Legacy)
تم استبدال API بـ WebSocket للاتصال مع ESP32. يوفر WebSocket اتصالاً ثنائي الاتجاه في الوقت الفعلي، مما يسمح بإرسال الأوامر فوراً دون الحاجة إلى polling.

**⚠️ تم استبداله بـ MQTT للحصول على JSON object مباشر.**

---

## 📡 إعداد WebSocket Server

### 1. تشغيل Laravel Reverb Server

```bash
php artisan reverb:start
```

أو للتطوير:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 2. إعداد متغيرات البيئة (.env)

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

لإنشاء المفاتيح:
```bash
php artisan reverb:install
```

---

## 🔗 الاتصال من ESP32

### الطريقة 1: WebSocket للاستماع للأوامر (مستحسن)

**للتطوير:**
```
ws://localhost:8080/app/your-app-key
```

**للإنتاج:**
```
wss://your-domain.com:8080/app/your-app-key
```

**الاستخدام:** للاستماع للأوامر من Server فقط. يجب الاشتراك في channel: `scooter.{IMEI}`

### الطريقة 2: HTTP Endpoint لإرسال البيانات

**للتطوير:**
```
POST http://localhost:8000/api/v1/scooter/message
```

**للإنتاج:**
```
POST https://your-domain.com/api/v1/scooter/message
```

**الاستخدام:** لإرسال البيانات (location, battery, etc.) إلى Server

### مثال على الاتصال (Arduino/ESP32)

#### الطريقة المختلطة (WebSocket + HTTP) - مستحسنة

```cpp
#include <WiFi.h>
#include <WebSocketsClient.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// WebSocket for receiving commands
const char* wsServer = "localhost";
const int wsPort = 8080;
const char* wsPath = "/app/your-app-key";

// HTTP for sending data
const char* httpServer = "http://localhost:8000";

String imei = "ESP32_IMEI_001";
WebSocketsClient webSocket;
HTTPClient http;

void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_DISCONNECTED:
            Serial.println("WebSocket Disconnected");
            break;
            
        case WStype_CONNECTED:
            Serial.println("WebSocket Connected");
            // Subscribe to scooter channel
            subscribeToChannel();
            break;
            
        case WStype_TEXT:
            handleWebSocketMessage((char*)payload);
            break;
            
        default:
            break;
    }
}

void subscribeToChannel() {
    // Subscribe to scooter channel using Reverb protocol
    String channel = "scooter." + imei;
    // Reverb uses Pusher protocol, so we need to subscribe properly
    DynamicJsonDocument doc(256);
    doc["event"] = "pusher:subscribe";
    JsonObject data = doc.createNestedObject("data");
    data["channel"] = channel;
    
    String json;
    serializeJson(doc, json);
    webSocket.sendTXT(json);
}

void handleWebSocketMessage(String message) {
    Serial.println("Received: " + message); // للـ debugging
    
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, message);
    
    if (error) {
        Serial.print("JSON parse error: ");
        Serial.println(error.c_str());
        return;
    }
    
    String event = doc["event"].as<String>();
    
    // معالجة رسالة الاشتراك الناجح
    if (event == "pusher_internal:subscription_succeeded") {
        Serial.println("Successfully subscribed to channel!");
        return;
    }
    
    // معالجة ping من السيرفر (يجب الرد بـ pong)
    if (event == "pusher:ping") {
        Serial.println("Received ping, sending pong");
        webSocket.sendTXT("{\"event\":\"pusher:pong\",\"data\":{}}");
        return;
    }
    
    // معالجة الأوامر من السيرفر
    if (event == "command") {
        JsonObject data = doc["data"].as<JsonObject>();
        JsonObject commands = data["commands"].as<JsonObject>();
        
        bool lock = commands["lock"] | false;
        bool unlock = commands["unlock"] | false;
        
        if (lock) {
            Serial.println("Executing LOCK command");
            lockScooter();
        } else if (unlock) {
            Serial.println("Executing UNLOCK command");
            unlockScooter();
        }
    }
}

void sendHttpMessage(String event, JsonObject data) {
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
        // Parse response if needed
    }
    
    http.end();
}

void authenticate() {
    JsonObject data;
    sendHttpMessage("authenticate", data);
}

void updateLocation(float lat, float lon, int battery, bool locked) {
    DynamicJsonDocument doc(512);
    JsonObject data = doc.to<JsonObject>();
    data["latitude"] = lat;
    data["longitude"] = lon;
    data["battery_percentage"] = battery;
    data["lock_status"] = locked;
    
    sendHttpMessage("update-location", data);
}

void setup() {
    Serial.begin(115200);
    
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    
    Serial.println("WiFi Connected");
    
    // Connect to WebSocket for receiving commands
    webSocket.begin(wsServer, wsPort, wsPath);
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
    
    // Authenticate via HTTP
    authenticate();
}

void loop() {
    webSocket.loop();
    
    // Send location update every 5 seconds via HTTP
    static unsigned long lastUpdate = 0;
    if (millis() - lastUpdate > 5000) {
        updateLocation(30.0444, 31.2357, 85, true);
        lastUpdate = millis();
    }
    
    // Note: ping/pong يتم التعامل معه تلقائياً في handleWebSocketMessage
    // عند استقبال pusher:ping، يتم إرسال pusher:pong تلقائياً
}
```

#### الطريقة البديلة: HTTP فقط

إذا كان ESP32 لا يدعم WebSocket، يمكن استخدام HTTP فقط:

```cpp
// Use sendHttpMessage() for all events
// Commands will be returned in HTTP responses
```

---

## 📨 الأحداث (Events)

### الأحداث المرسلة من ESP32 إلى Server

#### 1. authenticate
**الغرض:** التحقق من IMEI والحصول على معلومات السكوتر

```json
{
  "event": "authenticate",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

**الرد:**
```json
{
  "event": "authenticate",
  "data": {
    "success": true,
    "scooter_id": 1,
    "code": "SCO-001",
    "commands": {
      "lock": false,
      "unlock": true
    },
    "status": "available"
  },
  "timestamp": "2024-01-01T12:00:00Z"
}
```

#### 2. update-location
**الغرض:** إرسال الموقع والبطارية وحالة القفل

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

**الرد:**
```json
{
  "event": "update-location",
  "data": {
    "success": true,
    "message": "Location updated",
    "commands": {
      "lock": false,
      "unlock": false
    },
    "scooter_status": "available"
  },
  "timestamp": "2024-01-01T12:00:00Z"
}
```

#### 3. update-lock-status
**الغرض:** تحديث حالة القفل بعد تنفيذ الأمر

```json
{
  "event": "update-lock-status",
  "imei": "ESP32_IMEI_001",
  "data": {
    "lock_status": true
  }
}
```

#### 4. update-battery
**الغرض:** تحديث نسبة البطارية فقط

```json
{
  "event": "update-battery",
  "imei": "ESP32_IMEI_001",
  "data": {
    "battery_percentage": 85
  }
}
```

#### 5. get-commands
**الغرض:** الحصول على الأوامر الحالية

```json
{
  "event": "get-commands",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

---

### الأحداث المرسلة من Server إلى ESP32

#### command
**الغرض:** إرسال أمر قفل/فتح للسكوتر

```json
{
  "event": "command",
  "data": {
    "success": true,
    "commands": {
      "lock": true,
      "unlock": false
    }
  },
  "timestamp": "2024-01-01T12:00:00Z"
}
```

---

## ⚡ خطة الاستخدام الموصى بها

### عند بدء تشغيل ESP32:
1. الاتصال بـ WebSocket Server
2. إرسال حدث `authenticate` فور الاتصال
3. معالجة الأوامر الواردة في الرد

### أثناء التشغيل:
1. إرسال `update-location` كل 5-10 ثواني
2. الاستماع للأحداث `command` من Server
3. تنفيذ الأوامر فوراً عند استلامها
4. إرسال `update-lock-status` بعد تنفيذ الأمر

### عند تغيير البطارية فقط:
- إرسال `update-battery` عند تغيير نسبة البطارية

---

## 🔧 HTTP Fallback (للتوافق)

إذا كان ESP32 لا يدعم WebSocket بعد، يمكن استخدام HTTP endpoint:

```
POST /api/v1/scooter/message
Content-Type: application/json

{
  "event": "authenticate",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

---

## 📝 ملاحظات مهمة

1. **الاتصال المستمر:** WebSocket يوفر اتصالاً مستمراً، لا حاجة للاتصال في كل مرة
2. **إعادة الاتصال:** تأكد من إعداد إعادة الاتصال التلقائي في ESP32
3. **Heartbeat:** يمكن إرسال ping/pong للحفاظ على الاتصال
4. **الأمان:** في الإنتاج، استخدم WSS (WebSocket Secure) مع SSL
5. **IMEI:** يجب أن يكون `device_imei` في قاعدة البيانات مطابقاً لـ IMEI المرسل

---

## 🐛 استكشاف الأخطاء

### ESP32 لا يستطيع الاتصال
- تحقق من WebSocket URL والمنفذ
- تأكد من أن Reverb Server يعمل
- تحقق من إعدادات Firewall

### الأوامر لا تصل
- تأكد من أن ESP32 متصل ومستمع للأحداث
- تحقق من أن IMEI صحيح في قاعدة البيانات
- راجع logs في Laravel

### الاتصال ينقطع (Pong reply not received in time)
- **السبب:** السيرفر يرسل ping messages كل 60 ثانية ويتوقع pong response
- **الحل:** تأكد من معالجة `pusher:ping` وإرسال `pusher:pong` في `handleWebSocketMessage`
- **الكود المطلوب:**
  ```cpp
  if (event == "pusher:ping") {
      webSocket.sendTXT("{\"event\":\"pusher:pong\",\"data\":{}}");
      return;
  }
  ```
- إذا استمرت المشكلة، يمكن زيادة `REVERB_APP_ACTIVITY_TIMEOUT` في `.env` إلى 120 ثانية

---

## 📞 للدعم
إذا واجهت أي مشاكل:
1. تحقق من logs: `storage/logs/laravel.log`
2. تحقق من Reverb logs
3. تأكد من أن جميع المتغيرات في `.env` صحيحة

