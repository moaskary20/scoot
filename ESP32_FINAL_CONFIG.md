# 🔗 البيانات الكاملة لمبرمج ESP32

## 📋 معلومات الاتصال

### 🌐 الدومين:
```
https://linerscoot.com
```

---

## 📡 WebSocket (للاستماع للأوامر)

### URL للإنتاج:
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

### URL للتطوير:
```
ws://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

### Channel للاشتراك:
```
scooter.{YOUR_IMEI}
```

**مثال:** إذا كان IMEI = `ESP32_001`
```
scooter.ESP32_001
```

---

## 🌐 HTTP (لإرسال البيانات)

### Endpoint للإنتاج:
```
POST https://linerscoot.com/api/v1/scooter/message
```

### Endpoint للتطوير:
```
POST http://localhost:8000/api/v1/scooter/message
```

---

## 🔑 المفاتيح

### App Key:
```
m1k6cr5egrbe0p2eycaw
```

### App ID:
```
318253
```

### App Secret:
```
meazymdqwetpjhangtyp
```
*(يستخدم في السيرفر فقط، لا حاجة له في ESP32)*

---

## 💻 الكود الكامل

### للإنتاج (Production):

```cpp
#include <WiFi.h>
#include <WebSocketsClient.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>

// إعدادات WiFi
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// WebSocket للإنتاج
const char* wsServer = "linerscoot.com";
const int wsPort = 8080;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";

// HTTP للإنتاج
const char* httpServer = "https://linerscoot.com";

// IMEI - يجب أن يكون مطابقاً لقاعدة البيانات
String imei = "YOUR_IMEI";

WebSocketsClient webSocket;
HTTPClient http;
WiFiClientSecure client;

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
    
    // إعداد SSL للـ HTTPS
    client.setInsecure(); // للاختبار فقط، في الإنتاج استخدم شهادة SSL صحيحة
    
    // الاتصال بـ WebSocket
    webSocket.beginSSL(wsServer, wsPort, wsPath);
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
    
    // المصادقة
    delay(1000);
    authenticate();
}

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
            lockScooter();
            updateLockStatus(true);
        } else if (unlock) {
            unlockScooter();
            updateLockStatus(false);
        }
    }
}

void sendHttpMessage(String event, JsonObject data) {
    http.begin(client, httpServer + "/api/v1/scooter/message");
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

void authenticate() {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
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

void updateLockStatus(bool locked) {
    DynamicJsonDocument doc(256);
    JsonObject data = doc.to<JsonObject>();
    data["lock_status"] = locked;
    
    sendHttpMessage("update-lock-status", data);
}

void loop() {
    webSocket.loop();
    
    // تحديث الموقع كل 5 ثواني
    static unsigned long lastUpdate = 0;
    if (millis() - lastUpdate > 5000) {
        float lat = getGPSLatitude();  // استبدل بقراءة GPS
        float lon = getGPSLongitude(); // استبدل بقراءة GPS
        int battery = getBatteryPercentage(); // استبدل بقراءة البطارية
        bool locked = getLockStatus(); // استبدل بقراءة حالة القفل
        
        updateLocation(lat, lon, battery, locked);
        lastUpdate = millis();
    }
}
```

### للتطوير (Development):

```cpp
// استبدل فقط:
const char* wsServer = "localhost";  // أو IP المحلي
const char* httpServer = "http://localhost:8000";

// واستخدم:
webSocket.begin(wsServer, wsPort, wsPath);  // بدون SSL
http.begin(httpServer + "/api/v1/scooter/message");  // بدون SSL client
```

---

## 📨 الأحداث المتاحة

| الحدث | الوصف | البيانات المطلوبة |
|------|-------|------------------|
| `authenticate` | التحقق من IMEI | `{}` |
| `update-location` | تحديث الموقع والبطارية | `latitude`, `longitude`, `battery_percentage`, `lock_status` |
| `update-lock-status` | تحديث حالة القفل | `lock_status` |
| `update-battery` | تحديث البطارية | `battery_percentage` |
| `get-commands` | الحصول على الأوامر | `{}` |

---

## ⚠️ ملاحظات مهمة

1. **IMEI:** يجب أن يكون `device_imei` في قاعدة البيانات مطابقاً لـ IMEI في الكود
2. **SSL:** في الإنتاج استخدم `wss://` و `https://`
3. **المنافذ:** تأكد من فتح المنافذ 8080 و 443 في Firewall
4. **الاتصال:** أرسل `update-location` كل 5-10 ثواني
5. **الأوامر:** استمع للأحداث `command` من WebSocket

---

## 🔍 اختبار الاتصال

### اختبار WebSocket:
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

### اختبار HTTP:
```bash
curl -X POST https://linerscoot.com/api/v1/scooter/message \
  -H "Content-Type: application/json" \
  -d '{
    "event": "authenticate",
    "imei": "YOUR_IMEI",
    "data": {}
  }'
```

---

## 📞 للدعم

إذا واجهت مشاكل:
1. تحقق من Serial Monitor
2. تأكد من أن IMEI مسجل في قاعدة البيانات
3. تحقق من اتصال الإنترنت
4. راجع `ESP32_DEVELOPER_GUIDE.md` للتفاصيل

