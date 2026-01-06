# 🔧 حل مشكلة "Pong reply not received in time"

## المشكلة
عند الاتصال بـ WebSocket، يظهر خطأ:
```
1006 Abnormal Closure: Pong reply not received in time
```

## السبب
Laravel Reverb (Pusher protocol) يرسل **ping messages** كل 60 ثانية للحفاظ على الاتصال، ويتوقع **pong response**. إذا لم يتم الرد، يقطع الاتصال بعد 30 ثانية (أو 120 ثانية بعد التحديث).

## الحل

### 1. إضافة معالجة Ping/Pong في ESP32

في دالة `handleWebSocketMessage`، أضف:

```cpp
void handleWebSocketMessage(String message) {
    Serial.println("Received: " + message);
    
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, message);
    
    if (error) {
        Serial.print("JSON parse error: ");
        Serial.println(error.c_str());
        return;
    }
    
    String event = doc["event"].as<String>();
    
    // معالجة ping من السيرفر (يجب الرد بـ pong)
    if (event == "pusher:ping") {
        Serial.println("Received ping, sending pong");
        webSocket.sendTXT("{\"event\":\"pusher:pong\",\"data\":{}}");
        return;
    }
    
    // معالجة رسالة الاشتراك الناجح
    if (event == "pusher_internal:subscription_succeeded") {
        Serial.println("Successfully subscribed to channel!");
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
```

### 2. الكود الكامل المحدث

```cpp
#include <WiFi.h>
#include <WebSocketsClient.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// WebSocket for receiving commands
const char* wsServer = "linerscoot.com";
const int wsPort = 8080;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";

// HTTP for sending data
const char* httpServer = "https://linerscoot.com";

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
    Serial.println("Received: " + message);
    
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, message);
    
    if (error) {
        Serial.print("JSON parse error: ");
        Serial.println(error.c_str());
        return;
    }
    
    String event = doc["event"].as<String>();
    
    // معالجة ping من السيرفر
    if (event == "pusher:ping") {
        Serial.println("Received ping, sending pong");
        webSocket.sendTXT("{\"event\":\"pusher:pong\",\"data\":{}}");
        return;
    }
    
    // معالجة رسالة الاشتراك الناجح
    if (event == "pusher_internal:subscription_succeeded") {
        Serial.println("Successfully subscribed to channel!");
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

void setup() {
    Serial.begin(115200);
    
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    
    Serial.println("WiFi Connected");
    
    webSocket.begin(wsServer, wsPort, wsPath);
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
}

void loop() {
    webSocket.loop();
}
```

## ملاحظات مهمة

1. **Ping/Pong ضروري:** بدون معالجة `pusher:ping`، الاتصال سينقطع بعد 30-120 ثانية
2. **الرد السريع:** يجب الرد بـ `pusher:pong` فوراً عند استقبال `pusher:ping`
3. **التنسيق:** الرد يجب أن يكون JSON: `{"event":"pusher:pong","data":{}}`

## للتحقق

بعد إضافة الكود:
1. افتح Serial Monitor
2. راقب الرسائل الواردة
3. يجب أن ترى: `"Received ping, sending pong"` كل 60 ثانية تقريباً
4. الاتصال يجب أن يبقى مستمراً بدون انقطاع

## إذا استمرت المشكلة

1. تأكد من أن `webSocket.loop()` يتم استدعاؤه باستمرار في `loop()`
2. تأكد من أن الرد على ping يتم فوراً
3. تحقق من استقرار الاتصال بالإنترنت







