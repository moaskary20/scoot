# 🔗 روابط API لـ ESP32 - دليل سريع

## 📍 Base URL (الرابط الأساسي)

### للتطوير (Development):
```
http://localhost:8000/api/v1/scooter
```

### للإنتاج (Production):
```
https://your-domain.com/api/v1/scooter
```

---

## 📋 قائمة الروابط الكاملة

### 1. 🔐 المصادقة (Authentication)
**الرابط:**
```
POST /api/v1/scooter/authenticate
```

**Request Body:**
```json
{
  "imei": "ESP32_IMEI_001"
}
```

**Response (نجاح):**
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

**Response (فشل):**
```json
{
  "success": false,
  "message": "Scooter not found or inactive"
}
```

---

### 2. 📍 تحديث الموقع والبطارية (Update Location)
**الرابط:**
```
POST /api/v1/scooter/update-location
```

**Request Body:**
```json
{
  "imei": "ESP32_IMEI_001",
  "latitude": 30.0444,
  "longitude": 31.2357,
  "battery_percentage": 85,
  "lock_status": true
}
```

**Response:**
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

### 3. 🔓 الحصول على الأوامر (Get Commands)
**الرابط:**
```
POST /api/v1/scooter/get-commands
```

**Request Body:**
```json
{
  "imei": "ESP32_IMEI_001"
}
```

**Response:**
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

---

### 4. 🔒 تحديث حالة القفل (Update Lock Status)
**الرابط:**
```
POST /api/v1/scooter/update-lock-status
```

**Request Body:**
```json
{
  "imei": "ESP32_IMEI_001",
  "lock_status": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Lock status updated",
  "is_locked": true
}
```

---

### 5. 🔋 تحديث البطارية فقط (Update Battery)
**الرابط:**
```
POST /api/v1/scooter/update-battery
```

**Request Body:**
```json
{
  "imei": "ESP32_IMEI_001",
  "battery_percentage": 85
}
```

**Response:**
```json
{
  "success": true,
  "message": "Battery updated",
  "battery_percentage": 85
}
```

---

## ⚡ خطة الاستخدام الموصى بها

### عند بدء تشغيل ESP32:
1. استدعي `authenticate` للتحقق من IMEI والحصول على معلومات السكوتر

### أثناء التشغيل (كل 5-10 ثواني):
2. استدعي `update-location` لإرسال الموقع والبطارية وحالة القفل
   - أو استدعي `get-commands` للحصول على الأوامر فقط

### بعد تنفيذ أمر القفل/الفتح:
3. استدعي `update-lock-status` لتأكيد تنفيذ الأمر

### عند تغيير البطارية فقط:
4. استدعي `update-battery` لتحديث نسبة البطارية

---

## 📝 ملاحظات مهمة

1. **IMEI**: يجب أن يكون `device_imei` في قاعدة البيانات مطابقاً لـ IMEI المرسل من ESP32
2. **Content-Type**: جميع الطلبات يجب أن تكون `application/json`
3. **الأوامر**: 
   - `lock: true` يعني أن السكوتر يجب أن يُقفل
   - `unlock: true` يعني أن السكوتر يجب أن يُفتح
   - إذا كان كلاهما `false`، لا يوجد أوامر جديدة
4. **حالة السكوتر**:
   - `available`: متاح للإيجار
   - `rented`: مستأجر حالياً
   - `charging`: قيد الشحن
   - `maintenance`: قيد الصيانة

---

## 🔧 أمثلة على الكود (ESP32 - Arduino)

### مثال 1: المصادقة
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverUrl = "http://localhost:8000/api/v1/scooter";
String imei = "ESP32_IMEI_001";

void authenticate() {
  HTTPClient http;
  http.begin(serverUrl + "/authenticate");
  http.addHeader("Content-Type", "application/json");
  
  String json = "{\"imei\":\"" + imei + "\"}";
  int httpResponseCode = http.POST(json);
  
  if (httpResponseCode == 200) {
    String response = http.getString();
    // Parse response
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, response);
    
    if (doc["success"] == true) {
      Serial.println("Authenticated! Scooter ID: " + String(doc["scooter_id"].as<int>()));
    }
  }
  
  http.end();
}
```

### مثال 2: تحديث الموقع
```cpp
void updateLocation(float lat, float lon, int battery, bool locked) {
  HTTPClient http;
  http.begin(serverUrl + "/update-location");
  http.addHeader("Content-Type", "application/json");
  
  String json = "{";
  json += "\"imei\":\"" + imei + "\",";
  json += "\"latitude\":" + String(lat, 7) + ",";
  json += "\"longitude\":" + String(lon, 7) + ",";
  json += "\"battery_percentage\":" + String(battery) + ",";
  json += "\"lock_status\":" + String(locked ? "true" : "false");
  json += "}";
  
  int httpResponseCode = http.POST(json);
  
  if (httpResponseCode == 200) {
    String response = http.getString();
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, response);
    
    if (doc["success"] == true) {
      bool lockCommand = doc["commands"]["lock"];
      bool unlockCommand = doc["commands"]["unlock"];
      
      if (lockCommand) {
        // تنفيذ أمر القفل
        lockScooter();
      } else if (unlockCommand) {
        // تنفيذ أمر الفتح
        unlockScooter();
      }
    }
  }
  
  http.end();
}
```

---

## 📞 للدعم
إذا واجهت أي مشاكل، تأكد من:
- أن IMEI مسجل في قاعدة البيانات
- أن السكوتر `is_active = true`
- أن الاتصال بالإنترنت يعمل
- أن الـ Base URL صحيح

