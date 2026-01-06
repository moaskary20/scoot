# ⏱️ شرح Timeout و Ping Interval

## 📋 نظرة عامة

عند إرسال الأوامر إلى ESP32 (عبر WebSocket أو MQTT)، يتم إرسال قيمتين مهمتين:

1. **`timeout`** (activity_timeout)
2. **`ping_interval`** (ping_interval)

---

## 🔍 القيم الحالية

### القيم الافتراضية:
- **`timeout`**: `120` ثانية (دقيقتان)
- **`ping_interval`**: `60` ثانية (دقيقة واحدة)

### من أين تأتي هذه القيم؟

يتم قراءتها من:
1. `config/reverb.php` → `apps[0]['activity_timeout']` و `apps[0]['ping_interval']`
2. أو من متغيرات البيئة `.env`:
   - `REVERB_APP_ACTIVITY_TIMEOUT` (افتراضي: 120)
   - `REVERB_APP_PING_INTERVAL` (افتراضي: 60)

---

## 📝 معنى كل قيمة

### 1. `timeout` (Activity Timeout)

**المعنى:**
- الوقت بالثواني قبل قطع الاتصال إذا لم يكن هناك نشاط
- إذا لم يرسل ESP32 أي رسالة خلال هذا الوقت، سيتم اعتبار الاتصال منقطعاً

**الاستخدام:**
- يستخدم في WebSocket (Laravel Reverb)
- يساعد في اكتشاف الاتصالات الميتة (dead connections)
- إذا انقطع الاتصال، يمكن إعادة الاتصال تلقائياً

**مثال:**
```json
{
  "event": "command",
  "data": {
    "commands": {"lock": true, "unlock": false},
    "timestamp": "2025-12-25T13:30:06+00:00",
    "timeout": 120,  // ← إذا لم يكن هناك نشاط لمدة 120 ثانية، الاتصال سينقطع
    "ping_interval": 60
  }
}
```

### 2. `ping_interval` (Ping Interval)

**المعنى:**
- الفترة بالثواني بين رسائل ping
- يجب على ESP32 إرسال رسالة ping كل `ping_interval` ثانية لإبقاء الاتصال نشطاً

**الاستخدام:**
- يستخدم في WebSocket (Laravel Reverb)
- يمنع انقطاع الاتصال بسبب `timeout`
- إذا أرسل ESP32 ping كل `ping_interval` ثانية، لن يتم قطع الاتصال

**مثال:**
```json
{
  "event": "command",
  "data": {
    "commands": {"lock": true, "unlock": false},
    "timestamp": "2025-12-25T13:30:06+00:00",
    "timeout": 120,
    "ping_interval": 60  // ← أرسل ping كل 60 ثانية
  }
}
```

---

## 🔧 كيفية تعديل القيم

### 1. تعديل في `.env`

أضف أو عدّل هذه الأسطر في `.env`:

```env
# Timeout: الوقت بالثواني قبل قطع الاتصال إذا لم يكن هناك نشاط
REVERB_APP_ACTIVITY_TIMEOUT=120

# Ping Interval: الفترة بالثواني بين رسائل ping
REVERB_APP_PING_INTERVAL=60
```

### 2. إعادة تشغيل Reverb Server

بعد تعديل `.env`، يجب إعادة تشغيل Reverb Server:

```bash
# إيقاف Reverb Server (Ctrl+C)
# ثم إعادة التشغيل:
php artisan reverb:start
```

### 3. مسح Cache (اختياري)

```bash
php artisan config:clear
php artisan config:cache
```

---

## 📡 كيف يتم إرسالها في الرسائل؟

### في WebSocket (Postman):

```json
{
  "event": "command",
  "channel": "scooter.ESP32_IMEI_001",
  "data": "{\"commands\":{\"lock\":true,\"unlock\":false},\"timestamp\":\"2025-12-25T13:30:06+00:00\",\"timeout\":120,\"ping_interval\":60}"
}
```

**ملاحظة:** في WebSocket، `data` يكون JSON string (بسبب Pusher protocol).

### في MQTT:

```json
{
  "event": "command",
  "data": {
    "commands": {"lock": true, "unlock": false},
    "timestamp": "2025-12-25T13:30:06+00:00",
    "timeout": 120,
    "ping_interval": 60
  }
}
```

**ملاحظة:** في MQTT، `data` يكون JSON object مباشر.

---

## 💻 استخدامها في ESP32

### مثال كود ESP32 (Arduino):

```cpp
#include <ArduinoJson.h>

void handleCommand(String message) {
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, message);

    // استخراج البيانات
    String event = doc["event"];
    if (event == "command") {
        // في WebSocket: data هو JSON string
        String dataString = doc["data"];
        DynamicJsonDocument dataDoc(1024);
        deserializeJson(dataDoc, dataString);
        
        // في MQTT: data هو JSON object مباشر
        // DynamicJsonDocument dataDoc = doc["data"];
        
        // استخراج الأوامر
        bool lock = dataDoc["commands"]["lock"] | false;
        bool unlock = dataDoc["commands"]["unlock"] | false;
        
        // استخراج timeout و ping_interval
        int timeout = dataDoc["timeout"] | 120;  // افتراضي: 120 ثانية
        int pingInterval = dataDoc["ping_interval"] | 60;  // افتراضي: 60 ثانية
        
        Serial.print("Timeout: ");
        Serial.println(timeout);
        Serial.print("Ping Interval: ");
        Serial.println(pingInterval);
        
        // استخدام القيم
        // ...
        
        // إرسال ping كل pingInterval ثانية
        // يمكن استخدام millis() لتتبع الوقت
    }
}
```

---

## ⚙️ توصيات الإعداد

### للتطوير (Development):
```env
REVERB_APP_ACTIVITY_TIMEOUT=120
REVERB_APP_PING_INTERVAL=60
```

### للإنتاج (Production):
```env
REVERB_APP_ACTIVITY_TIMEOUT=180  # 3 دقائق
REVERB_APP_PING_INTERVAL=30      # 30 ثانية (أكثر تكراراً)
```

**السبب:**
- في الإنتاج، قد تحتاج timeout أطول للتعامل مع انقطاع الشبكة المؤقت
- ping_interval أقصر يضمن اكتشاف المشاكل بسرعة أكبر

---

## 🔍 التحقق من القيم الحالية

### 1. من الكود:

القيم موجودة في:
- `app/Events/ScooterCommand.php` (سطر 57-58)
- `app/Services/MqttService.php` (سطر 69-70)

### 2. من `.env`:

```bash
grep REVERB_APP_ACTIVITY_TIMEOUT .env
grep REVERB_APP_PING_INTERVAL .env
```

### 3. من Config:

```bash
php artisan tinker
```

```php
config('reverb.apps.apps.0.activity_timeout');
config('reverb.apps.apps.0.ping_interval');
```

---

## 📊 ملخص

| القيمة | الافتراضي | الوحدة | الاستخدام |
|--------|-----------|--------|-----------|
| `timeout` | 120 | ثانية | الوقت قبل قطع الاتصال إذا لم يكن هناك نشاط |
| `ping_interval` | 60 | ثانية | الفترة بين رسائل ping |

---

## ✅ الخلاصة

- **`timeout`**: الوقت بالثواني قبل قطع الاتصال (افتراضي: 120)
- **`ping_interval`**: الفترة بالثواني بين رسائل ping (افتراضي: 60)
- يتم إرسالهما مع كل أمر في `data.timeout` و `data.ping_interval`
- يمكن تعديلهما من `.env` ثم إعادة تشغيل Reverb Server

