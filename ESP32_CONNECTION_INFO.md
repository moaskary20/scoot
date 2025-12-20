# 🔗 معلومات الاتصال لـ ESP32

## 📡 بيانات WebSocket

### WebSocket URL للاتصال (للإنتاج):
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

### WebSocket URL للاتصال (للتطوير):
```
ws://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```
أو
```
ws://YOUR_LOCAL_IP:8080/app/m1k6cr5egrbe0p2eycaw
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

## 🌐 بيانات HTTP

### HTTP Endpoint لإرسال البيانات (للإنتاج):
```
POST https://linerscoot.com/api/v1/scooter/message
```

### HTTP Endpoint لإرسال البيانات (للتطوير):
```
POST http://localhost:8000/api/v1/scooter/message
```
أو
```
POST http://YOUR_LOCAL_IP:8000/api/v1/scooter/message
```

---

## 🔑 المفاتيح المطلوبة

### App Key (للـ WebSocket):
```
m1k6cr5egrbe0p2eycaw
```

### App ID:
```
318253
```

---

## 💻 مثال الكود

### 1. إعداد المتغيرات (للإنتاج):

```cpp
// WebSocket
const char* wsServer = "linerscoot.com";
const int wsPort = 8080;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";
const bool useSSL = true;  // استخدام WSS

// HTTP
const char* httpServer = "https://linerscoot.com";

String imei = "YOUR_IMEI";  // يجب أن يكون مطابقاً لقاعدة البيانات
```

### 1. إعداد المتغيرات (للتطوير):

```cpp
// WebSocket
const char* wsServer = "localhost";  // أو IP المحلي
const int wsPort = 8080;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";
const bool useSSL = false;  // بدون SSL

// HTTP
const char* httpServer = "http://localhost:8000";  // أو IP المحلي

String imei = "YOUR_IMEI";  // يجب أن يكون مطابقاً لقاعدة البيانات
```

### 2. الاتصال بـ WebSocket:

```cpp
void setup() {
    // ... إعداد WiFi ...
    
    webSocket.begin(wsServer, wsPort, wsPath);
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
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
```

### 3. إرسال البيانات عبر HTTP:

```cpp
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
    
    http.POST(json);
    http.end();
}
```

---

## ⚠️ ملاحظات مهمة

1. **IP السيرفر:** يجب استبدال `YOUR_SERVER_IP` بـ IP السيرفر الخارجي الفعلي
2. **IMEI:** يجب أن يكون `device_imei` في قاعدة البيانات مطابقاً لـ IMEI في الكود
3. **Ports:** تأكد من أن المنافذ 8080 و 8000 مفتوحة في Firewall
4. **HTTPS/WSS:** في الإنتاج، استخدم `wss://` و `https://` بدلاً من `ws://` و `http://`

---

## 🔍 التحقق من الاتصال

### اختبار WebSocket:
استخدم أداة مثل [WebSocket King](https://websocketking.com/) أو [WebSocket Test Client](https://www.websocket.org/echo.html)

**URL:**
```
ws://YOUR_SERVER_IP:8080/app/m1k6cr5egrbe0p2eycaw
```

### اختبار HTTP:
استخدم Postman أو curl:

```bash
curl -X POST http://YOUR_SERVER_IP:8000/api/v1/scooter/message \
  -H "Content-Type: application/json" \
  -d '{
    "event": "authenticate",
    "imei": "YOUR_IMEI",
    "data": {}
  }'
```

---

## 📞 للدعم

إذا واجهت مشاكل في الاتصال:
1. تحقق من أن Reverb Server يعمل: `php artisan reverb:start`
2. تحقق من أن Laravel يعمل: `php artisan serve`
3. تحقق من Firewall والمنافذ
4. تأكد من أن IP السيرفر صحيح

