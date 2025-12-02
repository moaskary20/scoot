# دليل API لـ ESP32
## روابط API المطلوبة لمبرمج ESP32

## 🔗 الروابط الأساسية

**Base URL:** `http://localhost:8000/api/v1/scooter`  
**أو في الإنتاج:** `https://your-domain.com/api/v1/scooter`

---

## 📋 قائمة API Endpoints

### 1. 🔐 Authentication (المصادقة)
**الغرض:** التحقق من IMEI والحصول على معلومات السكوتر والأوامر الأولية

**الرابط:**
```
POST /api/v1/scooter/authenticate
```

**Request Body:**
```json
{
  "imei": "IMEI123456789"
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

**متى تستخدمه:**
- عند بدء تشغيل ESP32
- للتحقق من أن IMEI مسجل في النظام

---

### 2. 📍 Update Location (تحديث الموقع)
**الغرض:** إرسال موقع GPS الحالي، حالة البطارية، وحالة القفل

**الرابط:**
```
POST /api/v1/scooter/update-location
```

**Request Body:**
```json
{
  "imei": "IMEI123456789",
  "latitude": 30.0444,
  "longitude": 31.2357,
  "battery_percentage": 85,
  "lock_status": true
}
```

**ملاحظات:**
- `latitude`: خط العرض (بين -90 و 90)
- `longitude`: خط الطول (بين -180 و 180)
- `battery_percentage`: نسبة البطارية (0-100) - اختياري
- `lock_status`: حالة القفل (true/false) - اختياري

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

**متى تستخدمه:**
- كل 5-10 ثواني لإرسال الموقع الحالي
- عند تغيير حالة القفل
- عند تغيير نسبة البطارية

---

### 3. 🔓 Get Commands (الحصول على الأوامر)
**الغرض:** الحصول على أوامر القفل/الفتح من السيرفر

**الرابط:**
```
POST /api/v1/scooter/get-commands
```

**Request Body:**
```json
{
  "imei": "IMEI123456789"
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

**كيفية الاستخدام:**
- إذا كان `commands.lock = true` → قفل السكوتر
- إذا كان `commands.unlock = true` → افتح السكوتر
- بعد تنفيذ الأمر، استدعي `update-lock-status` لتأكيد التغيير

**متى تستخدمه:**
- كل 5-10 ثواني للتحقق من الأوامر الجديدة
- بعد تنفيذ أمر القفل/الفتح

---

### 4. 🔒 Update Lock Status (تحديث حالة القفل)
**الغرض:** إبلاغ السيرفر بحالة القفل الحالية بعد تنفيذ الأمر

**الرابط:**
```
POST /api/v1/scooter/update-lock-status
```

**Request Body:**
```json
{
  "imei": "IMEI123456789",
  "lock_status": true
}
```

**ملاحظات:**
- `lock_status`: true = مقفول، false = مفتوح

**Response:**
```json
{
  "success": true,
  "message": "Lock status updated",
  "is_locked": true
}
```

**متى تستخدمه:**
- بعد تنفيذ أمر القفل/الفتح من `get-commands`
- عند تغيير حالة القفل يدوياً

---

### 5. 🔋 Update Battery (تحديث البطارية فقط)
**الغرض:** إرسال نسبة البطارية فقط (بدون GPS)

**الرابط:**
```
POST /api/v1/scooter/update-battery
```

**Request Body:**
```json
{
  "imei": "IMEI123456789",
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

**متى تستخدمه:**
- عند تغيير نسبة البطارية فقط (بدون تغيير الموقع)
- كل دقيقة تقريباً

---

## 🔄 سيناريو الاستخدام الموصى به

### عند بدء التشغيل:
1. استدعي `authenticate` للتحقق من IMEI
2. احصل على الأوامر الأولية

### أثناء التشغيل (كل 5-10 ثواني):
1. استدعي `update-location` لإرسال:
   - الموقع GPS
   - نسبة البطارية
   - حالة القفل
2. احصل على الأوامر في الـ response

### عند الحاجة للأوامر فقط:
1. استدعي `get-commands` كل 5-10 ثواني
2. نفذ الأوامر (lock/unlock)
3. استدعي `update-lock-status` لتأكيد التنفيذ

---

## 📝 مثال كود ESP32 (Arduino)

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverUrl = "http://localhost:8000/api/v1/scooter";
const char* imei = "IMEI123456789";

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
  // 1. تحديث الموقع والحصول على الأوامر
  updateLocationAndGetCommands();
  
  delay(5000); // انتظر 5 ثواني
}

void updateLocationAndGetCommands() {
  HTTPClient http;
  http.begin(String(serverUrl) + "/update-location");
  http.addHeader("Content-Type", "application/json");
  
  // بيانات GPS (استبدلها ببيانات GPS الحقيقية)
  float latitude = 30.0444;
  float longitude = 31.2357;
  int battery = 85;
  bool isLocked = true;
  
  String json = "{\"imei\":\"" + String(imei) + 
                "\",\"latitude\":" + String(latitude) + 
                ",\"longitude\":" + String(longitude) + 
                ",\"battery_percentage\":" + String(battery) + 
                ",\"lock_status\":" + String(isLocked ? "true" : "false") + "}";
  
  int httpResponseCode = http.POST(json);
  
  if (httpResponseCode == 200) {
    String response = http.getString();
    Serial.println("Response: " + response);
    
    // Parse JSON response
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, response);
    
    if (doc["success"] == true) {
      bool lockCommand = doc["commands"]["lock"];
      bool unlockCommand = doc["commands"]["unlock"];
      
      if (lockCommand) {
        // قفل السكوتر
        lockScooter();
      }
      
      if (unlockCommand) {
        // افتح السكوتر
        unlockScooter();
      }
    }
  } else {
    Serial.println("Error: " + String(httpResponseCode));
  }
  
  http.end();
}

void lockScooter() {
  // كود قفل السكوتر
  Serial.println("Locking scooter...");
  // ... كود التحكم في القفل
}

void unlockScooter() {
  // كود فتح السكوتر
  Serial.println("Unlocking scooter...");
  // ... كود التحكم في القفل
}
```

---

## ⚠️ ملاحظات مهمة

1. **IMEI:** يجب أن يتطابق IMEI في ESP32 مع `device_imei` في قاعدة البيانات
2. **التكرار:** استدعي `get-commands` أو `update-location` كل 5-10 ثواني
3. **الأخطاء:** تحقق من `success: false` في الـ response
4. **HTTPS:** في الإنتاج، استخدم HTTPS بدلاً من HTTP
5. **Rate Limiting:** لا ترسل طلبات أكثر من مرة كل 5 ثواني

---

## 🔍 رموز الأخطاء

- **200:** نجاح
- **400:** بيانات غير صحيحة (تحقق من الـ JSON)
- **404:** السكوتر غير موجود أو غير نشط
- **500:** خطأ في السيرفر

---

## 📞 للدعم

إذا واجهت مشاكل:
1. تحقق من Serial Monitor في Arduino IDE
2. تأكد من اتصال WiFi
3. تأكد من أن IMEI مسجل في قاعدة البيانات
4. تحقق من أن السكوتر `is_active = true`

