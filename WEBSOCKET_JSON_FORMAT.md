# 📋 تنسيق JSON في WebSocket Messages

## ⚠️ التنسيق الحالي (Pusher Protocol)

عند استقبال رسالة `command` من Laravel Reverb في Postman، البيانات تأتي بهذا الشكل:

```json
{
  "event": "command",
  "data": "{\"commands\":{\"lock\":true,\"unlock\":false},\"timestamp\":\"2025-12-25T12:37:33+00:00\",\"timeout\":120,\"ping_interval\":60}",
  "channel": "scooter.ESP32_IMEI_001"
}
```

**⚠️ ملاحظة مهمة:** Laravel Reverb يستخدم **بروتوكول Pusher** الذي يتطلب `data` كـ **JSON string** (مشفر). هذا جزء من مواصفات Pusher protocol ولا يمكن تغييره.

**لماذا؟** لأن Pusher protocol مصمم بهذه الطريقة لأسباب أمنية وأداء. جميع broadcasters التي تستخدم Pusher protocol (مثل Laravel Reverb, Pusher.com, Ably) تتبع نفس القاعدة.

---

## ✅ الاستخدام في ESP32

في ESP32، **يجب فك تشفير JSON string من `data`**:

```cpp
#include <ArduinoJson.h>

void handleWebSocketMessage(String message) {
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, message);
    
    if (error) {
        Serial.println("Failed to parse JSON");
        return;
    }
    
    String event = doc["event"] | "";
    
    if (event == "command") {
        // data هو JSON string، يجب فك تشفيره
        String dataString = doc["data"] | "";
        
        // فك تشفير data (JSON string)
        DynamicJsonDocument dataDoc(512);
        DeserializationError dataError = deserializeJson(dataDoc, dataString);
        
        if (dataError) {
            Serial.println("Failed to parse data JSON");
            return;
        }
        
        // استخراج البيانات
        bool lock = dataDoc["commands"]["lock"] | false;
        bool unlock = dataDoc["commands"]["unlock"] | false;
        int timeout = dataDoc["timeout"] | 120; // ثواني
        int pingInterval = dataDoc["ping_interval"] | 60; // ثواني
        String timestamp = dataDoc["timestamp"] | "";
        
        // تنفيذ الأوامر
        if (lock) {
            // قفل السكوتر
            lockScooter();
        }
        
        if (unlock) {
            // فتح السكوتر
            unlockScooter();
        }
        
        Serial.print("Timeout: ");
        Serial.println(timeout);
        Serial.print("Ping Interval: ");
        Serial.println(pingInterval);
    }
}
```

---

## 📊 هيكل البيانات الكامل

البيانات في `data` مباشرة:

```json
{
  "commands": {
    "lock": true,
    "unlock": false
  },
  "timestamp": "2025-12-25T12:37:33+00:00",
  "timeout": 120,
  "ping_interval": 60
}
```

### الحقول:

- **`commands.lock`** (boolean): `true` إذا كان يجب قفل السكوتر
- **`commands.unlock`** (boolean): `true` إذا كان يجب فتح السكوتر
- **`timestamp`** (string): وقت إرسال الأمر بصيغة ISO 8601
- **`timeout`** (integer): الوقت بالثواني قبل قطع الاتصال إذا لم يكن هناك نشاط (افتراضي: 120)
- **`ping_interval`** (integer): الفترة بين رسائل ping بالثواني (افتراضي: 60)

---

## ⚙️ إعدادات Timeout و Ping Interval

يمكن تعديل هذه القيم في ملف `.env`:

```env
REVERB_APP_ACTIVITY_TIMEOUT=120
REVERB_APP_PING_INTERVAL=60
```

### شرح الإعدادات:

1. **`REVERB_APP_ACTIVITY_TIMEOUT`** (activity_timeout):
   - الوقت بالثواني قبل قطع الاتصال إذا لم يكن هناك نشاط
   - القيمة الافتراضية: 120 ثانية (دقيقتان)
   - إذا لم يرد ESP32 على ping خلال هذا الوقت، الاتصال سينقطع

2. **`REVERB_APP_PING_INTERVAL`** (ping_interval):
   - الفترة بين رسائل ping بالثواني
   - القيمة الافتراضية: 60 ثانية (دقيقة واحدة)
   - السيرفر يرسل `pusher:ping` كل هذه الفترة
   - ESP32 يجب أن يرد بـ `pusher:pong` فوراً

---

## 🔄 مثال كامل في ESP32

```cpp
#include <WiFi.h>
#include <WebSocketsClient.h>
#include <ArduinoJson.h>

WebSocketsClient webSocket;

void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_DISCONNECTED:
            Serial.println("WebSocket Disconnected");
            break;
            
        case WStype_CONNECTED:
            Serial.println("WebSocket Connected");
            // الاشتراك في channel
            String subscribeMsg = "{\"event\":\"pusher:subscribe\",\"data\":{\"channel\":\"scooter.ESP32_IMEI_001\"}}";
            webSocket.sendTXT(subscribeMsg);
            break;
            
        case WStype_TEXT:
            // استقبال رسالة
            handleWebSocketMessage((char*)payload);
            break;
    }
}

void handleWebSocketMessage(String message) {
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, message);
    
    String event = doc["event"] | "";
    
    if (event == "pusher:ping") {
        // الرد على ping
        webSocket.sendTXT("{\"event\":\"pusher:pong\",\"data\":{}}");
    }
    else if (event == "command") {
        // استقبال أمر - البيانات مباشرة كـ object
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
}

void setup() {
    Serial.begin(115200);
    
    // الاتصال بـ WiFi
    WiFi.begin("SSID", "PASSWORD");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    
    // الاتصال بـ WebSocket
    webSocket.begin("linerscoot.com", 8080, "/app/m1k6cr5egrbe0p2eycaw");
    webSocket.onEvent(webSocketEvent);
}

void loop() {
    webSocket.loop();
}
```

---

## 📝 ملاحظات مهمة

1. **JSON Object مباشر**: البيانات الآن تُرسل كـ JSON object مباشر (بدون escape)، مما يسهل التعامل معها في ESP32.

2. **سهولة الاستخدام**: لا حاجة لفك تشفير JSON string - البيانات جاهزة للاستخدام مباشرة.

3. **Timeout**: إذا لم يرد ESP32 على ping خلال `timeout` ثانية، الاتصال سينقطع.

4. **Ping/Pong**: يجب الرد على `pusher:ping` بـ `pusher:pong` فوراً.

5. **تعديل الإعدادات**: يمكن تعديل `timeout` و `ping_interval` في `.env` ثم إعادة تشغيل Reverb Server.

---

## 🔧 اختبار في Postman

1. **الاتصال**: `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
2. **الاشتراك**: `{"event":"pusher:subscribe","data":{"channel":"scooter.ESP32_IMEI_001"}}`
3. **الرد على Ping**: `{"event":"pusher:pong","data":{}}`
4. **استقبال الأوامر**: ستستقبل رسالة `command` مع `data` كـ JSON string

---

## ✅ الخلاصة

- `data` في رسالة `command` هو JSON object مباشر (بدون escape)
- يمكن استخدامه مباشرة في ESP32 بدون فك تشفير
- `timeout` و `ping_interval` متوفران في البيانات
- يمكن تعديلهما في `.env`

