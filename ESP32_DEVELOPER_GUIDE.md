# 📱 دليل مبرمج ESP32 - دليل سريع

## 🎯 نظرة عامة

هذا الدليل يحتوي على كل ما تحتاجه لربط ESP32 مع النظام.

---

## 🔗 معلومات الاتصال

### WebSocket URL (للاستماع للأوامر)
```
ws://localhost:8080/app/your-app-key
```

### HTTP Endpoint (لإرسال البيانات)
```
POST http://localhost:8000/api/v1/scooter/message
```

**ملاحظة:** استبدل `localhost` بـ IP أو domain الخادم في الإنتاج.

---

## 📦 المكتبات المطلوبة

```cpp
#include <WiFi.h>              // للاتصال بالإنترنت
#include <WebSocketsClient.h>  // للاتصال بـ WebSocket
#include <HTTPClient.h>        // لإرسال HTTP requests
#include <ArduinoJson.h>       // لمعالجة JSON
```

**تثبيت المكتبات في Arduino IDE:**
- WebSocketsClient: من Library Manager
- ArduinoJson: من Library Manager

---

## 🔧 الكود الأساسي

### 1. المتغيرات الأساسية

```cpp
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// WebSocket للاستماع للأوامر
const char* wsServer = "localhost";  // أو IP الخادم
const int wsPort = 8080;
const char* wsPath = "/app/your-app-key";

// HTTP لإرسال البيانات
const char* httpServer = "http://localhost:8000";  // أو IP الخادم

String imei = "ESP32_IMEI_001";  // يجب أن يكون مطابقاً لقاعدة البيانات

WebSocketsClient webSocket;
HTTPClient http;
```

### 2. إعداد WebSocket

```cpp
void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_DISCONNECTED:
            Serial.println("WebSocket Disconnected");
            break;
            
        case WStype_CONNECTED:
            Serial.println("WebSocket Connected");
            subscribeToChannel();
            break;
            
        case WStype_TEXT:
            handleWebSocketMessage((char*)payload);
            break;
    }
}

void subscribeToChannel() {
    String channel = "scooter." + imei;
    DynamicJsonDocument doc(256);
    doc["event"] = "pusher:subscribe";
    JsonObject data = doc.createNestedObject("data");
    data["channel"] = channel;
    
    String json;
    serializeJson(doc, json);
    webSocket.sendTXT(json);
}

void handleWebSocketMessage(String message) {
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, message);
    
    String event = doc["event"];
    
    if (event == "command" || event.indexOf("ScooterCommand") >= 0) {
        JsonObject data = doc["data"];
        JsonObject commands = data["commands"];
        
        bool lock = commands["lock"];
        bool unlock = commands["unlock"];
        
        if (lock) {
            // تنفيذ أمر القفل
            lockScooter();
            // تأكيد تنفيذ الأمر
            updateLockStatus(true);
        } else if (unlock) {
            // تنفيذ أمر الفتح
            unlockScooter();
            // تأكيد تنفيذ الأمر
            updateLockStatus(false);
        }
    }
}
```

### 3. إرسال البيانات عبر HTTP

```cpp
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
        Serial.println("Response: " + response);
    } else {
        Serial.println("Error: " + String(httpResponseCode));
    }
    
    http.end();
}
```

### 4. الأحداث المتاحة

#### أ) المصادقة (عند بدء التشغيل)

```cpp
void authenticate() {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    sendHttpMessage("authenticate", data);
}
```

#### ب) تحديث الموقع والبطارية

```cpp
void updateLocation(float lat, float lon, int battery, bool locked) {
    DynamicJsonDocument doc(512);
    JsonObject data = doc.to<JsonObject>();
    data["latitude"] = lat;
    data["longitude"] = lon;
    data["battery_percentage"] = battery;
    data["lock_status"] = locked;
    
    sendHttpMessage("update-location", data);
}
```

#### ج) تحديث حالة القفل فقط

```cpp
void updateLockStatus(bool locked) {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    data["lock_status"] = locked;
    
    sendHttpMessage("update-lock-status", data);
}
```

#### د) تحديث البطارية فقط

```cpp
void updateBattery(int battery) {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    data["battery_percentage"] = battery;
    
    sendHttpMessage("update-battery", data);
}
```

#### هـ) الحصول على الأوامر

```cpp
void getCommands() {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    sendHttpMessage("get-commands", data);
}
```

### 5. Setup و Loop

```cpp
void setup() {
    Serial.begin(115200);
    
    // الاتصال بالإنترنت
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi Connected");
    Serial.println("IP: " + WiFi.localIP().toString());
    
    // الاتصال بـ WebSocket
    webSocket.begin(wsServer, wsPort, wsPath);
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
    
    // المصادقة
    delay(1000);
    authenticate();
}

void loop() {
    webSocket.loop();
    
    // تحديث الموقع كل 5 ثواني
    static unsigned long lastUpdate = 0;
    if (millis() - lastUpdate > 5000) {
        // احصل على GPS coordinates
        float lat = getGPSLatitude();  // استبدل بقراءة GPS الفعلية
        float lon = getGPSLongitude(); // استبدل بقراءة GPS الفعلية
        int battery = getBatteryPercentage(); // استبدل بقراءة البطارية الفعلية
        bool locked = getLockStatus(); // استبدل بقراءة حالة القفل الفعلية
        
        updateLocation(lat, lon, battery, locked);
        lastUpdate = millis();
    }
}
```

---

## 📋 تنسيق الرسائل

### الرسائل المرسلة من ESP32:

```json
{
  "event": "authenticate",
  "imei": "ESP32_IMEI_001",
  "data": {}
}
```

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

### الرسائل المستلمة من Server:

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

## ⚡ خطة العمل الموصى بها

### عند بدء التشغيل:
1. الاتصال بالإنترنت
2. الاتصال بـ WebSocket
3. الاشتراك في channel: `scooter.{IMEI}`
4. إرسال `authenticate`

### أثناء التشغيل:
1. إرسال `update-location` كل 5-10 ثواني
2. الاستماع للأحداث `command` من WebSocket
3. تنفيذ الأوامر فوراً
4. إرسال `update-lock-status` بعد تنفيذ الأمر

---

## 🔍 استكشاف الأخطاء

### WebSocket لا يتصل:
- تحقق من URL والمنفذ
- تأكد من أن Reverb Server يعمل
- تحقق من إعدادات Firewall

### HTTP requests تفشل:
- تحقق من URL الخادم
- تأكد من أن Laravel يعمل
- تحقق من IMEI في قاعدة البيانات

### الأوامر لا تصل:
- تأكد من الاشتراك في channel الصحيح
- تحقق من Serial Monitor للأخطاء
- تأكد من أن IMEI صحيح

---

## 📞 للدعم

إذا واجهت مشاكل:
1. تحقق من Serial Monitor
2. راجع `ESP32_WEBSOCKET_GUIDE.md` للتفاصيل الكاملة
3. تأكد من أن IMEI مسجل في قاعدة البيانات

---

## 💡 نصائح

1. استخدم `webSocket.setReconnectInterval()` لإعادة الاتصال التلقائي
2. أضف error handling لجميع HTTP requests
3. استخدم `millis()` بدلاً من `delay()` لتجنب blocking
4. احفظ IMEI في EEPROM لتجنب إعادة إدخاله

---

## 📝 مثال كامل

راجع `ESP32_WEBSOCKET_GUIDE.md` للحصول على مثال كامل للكود.

