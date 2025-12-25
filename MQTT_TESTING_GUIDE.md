# 🧪 دليل اختبار MQTT

## ⚠️ ملاحظة مهمة

**Postman لا يدعم MQTT مباشرة!** لكن يمكنك استخدام:

1. **HTTP Endpoint** (أسهل طريقة - يعطيك نفس التنسيق)
2. **WebSocket في Postman** (إذا كان Reverb لا يزال يعمل - لكن سيصل JSON string)
3. **mosquitto_sub/mosquitto_pub** (من terminal)
4. **MQTT Client Tools** (MQTTX, MQTT.fx)

---

## 🔌 استخدام WebSocket في Postman (Legacy)

**ملاحظة:** إذا كنت تستخدم MQTT الآن، WebSocket لن يعمل للأوامر (لأننا استبدلناه). لكن يمكنك استخدامه للاختبار فقط.

### في Postman:

1. **New Request** → اختر **WebSocket**
2. **URL:** `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
3. اضغط **Connect**
4. أرسل للاشتراك:
   ```json
   {
     "event": "pusher:subscribe",
     "data": {
       "channel": "scooter.ESP32_IMEI_001"
     }
   }
   ```
5. أرسل ping response:
   ```json
   {"event":"pusher:pong","data":{}}
   ```

**⚠️ تحذير:** إذا كنت تستخدم MQTT، الأوامر لن تصل عبر WebSocket لأننا استبدلناه!

---

## 🚀 الطريقة 1: HTTP Endpoint (الأسهل)

استخدم الـ endpoint الذي أنشأناه للحصول على نفس تنسيق MQTT:

### في Postman:

**Method:** `POST`  
**URL:** `https://linerscoot.com/api/v1/scooter/commands`

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

**Response (نفس تنسيق MQTT):**
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

✅ **هذا يعطيك نفس التنسيق الذي ستحصل عليه من MQTT!**

---

## 🔧 الطريقة 2: mosquitto_sub/mosquitto_pub (Terminal)

### الخطوة 1: الاشتراك في Topic (Terminal 1)

```bash
mosquitto_sub -h localhost -t "scooter/ESP32_IMEI_001/commands" -v
```

**الخيارات:**
- `-h localhost` - MQTT broker host
- `-t "scooter/ESP32_IMEI_001/commands"` - Topic name
- `-v` - verbose (يعرض topic name مع الرسالة)

### الخطوة 2: إرسال أمر من Admin Panel

1. افتح Admin Panel: `https://linerscoot.com/admin/scooters/1`
2. اضغط **Lock** أو **Unlock**

### الخطوة 3: مشاهدة الرسالة في Terminal

سترى في Terminal 1:
```
scooter/ESP32_IMEI_001/commands {"event":"command","data":{"commands":{"lock":true,"unlock":false},"timestamp":"2025-12-25T13:30:06+00:00","timeout":120,"ping_interval":60}}
```

---

## 🧪 الطريقة 3: اختبار يدوي مع mosquitto_pub

### إرسال رسالة اختبار:

```bash
mosquitto_pub -h localhost -t "scooter/ESP32_IMEI_001/commands" \
  -m '{"event":"command","data":{"commands":{"lock":true,"unlock":false},"timestamp":"2025-12-25T13:30:06+00:00"}}'
```

### الاشتراك لاستقبال الرسالة:

```bash
mosquitto_sub -h localhost -t "scooter/ESP32_IMEI_001/commands" -v
```

---

## 🛠️ الطريقة 4: MQTT Client Tools

### 1. MQTTX (موصى به)

**تحميل:** https://mqttx.app/

**الاستخدام:**
1. افتح MQTTX
2. اضغط **New Connection**
3. إعدادات الاتصال:
   - **Name:** Linerscoot Test
   - **Host:** `linerscoot.com` (أو IP السيرفر)
   - **Port:** `1883`
   - **Client ID:** `test-client-001`
4. اضغط **Connect**
5. اضغط **New Subscription**
   - **Topic:** `scooter/ESP32_IMEI_001/commands`
   - **QoS:** `1`
6. اضغط **Subscribe**

**الآن:**
- عندما تضغط Lock/Unlock من Admin Panel، ستستقبل الرسالة في MQTTX
- الرسالة ستكون JSON object مباشر (بدون escape)

### 2. MQTT.fx

**تحميل:** http://www.mqttfx.org/

نفس الخطوات مثل MQTTX.

---

## 📋 خطوات الاختبار الكاملة

### 1. إعداد MQTT Subscription

**في Terminal:**
```bash
mosquitto_sub -h localhost -t "scooter/ESP32_IMEI_001/commands" -v
```

**أو في MQTTX:**
- Subscribe to: `scooter/ESP32_IMEI_001/commands`

### 2. إرسال أمر من Admin Panel

1. افتح: `https://linerscoot.com/admin/scooters/1`
2. تأكد أن `device_imei` = `ESP32_IMEI_001`
3. اضغط **Lock** أو **Unlock**

### 3. مشاهدة الرسالة

**في Terminal أو MQTTX:**
```
scooter/ESP32_IMEI_001/commands {"event":"command","data":{"commands":{"lock":true,"unlock":false},"timestamp":"2025-12-25T13:30:06+00:00","timeout":120,"ping_interval":60}}
```

### 4. التحقق من JSON Format

**الرسالة المستلمة:**
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
  }
}
```

✅ **`data` هو JSON object مباشر (ليس string)!**

---

## 🔍 اختبار من Laravel Tinker

```bash
php artisan tinker
```

```php
$scooter = \App\Models\Scooter::where('device_imei', 'ESP32_IMEI_001')->first();
$mqtt = app(\App\Services\MqttService::class);
$mqtt->publishCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]);
```

---

## ✅ Checklist للاختبار

- [ ] Mosquitto يعمل: `sudo systemctl status mosquitto`
- [ ] MQTT subscription جاهز (mosquitto_sub أو MQTTX)
- [ ] Topic صحيح: `scooter/ESP32_IMEI_001/commands`
- [ ] Admin Panel مفتوح: `https://linerscoot.com/admin/scooters/1`
- [ ] `device_imei` في قاعدة البيانات = `ESP32_IMEI_001`
- [ ] اضغط Lock/Unlock
- [ ] تحقق من استقبال الرسالة
- [ ] تحقق من أن `data` هو JSON object مباشر

---

## 🐛 استكشاف الأخطاء

### لا تصل الرسائل

1. **تحقق من Mosquitto:**
   ```bash
   sudo systemctl status mosquitto
   ```

2. **تحقق من Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep MQTT
   ```

3. **تحقق من Topic name:**
   - يجب أن يكون مطابق تماماً: `scooter/ESP32_IMEI_001/commands`
   - تأكد من `device_imei` في قاعدة البيانات

4. **اختبر الاتصال:**
   ```bash
   mosquitto_pub -h localhost -t test/topic -m "test"
   mosquitto_sub -h localhost -t test/topic
   ```

### خطأ في الاتصال

1. **تحقق من Firewall:**
   ```bash
   sudo ufw allow 1883/tcp
   ```

2. **تحقق من MQTT_HOST في .env:**
   ```env
   MQTT_HOST=localhost  # للتطوير
   # أو
   MQTT_HOST=linerscoot.com  # للإنتاج
   ```

---

## 📝 ملاحظات

1. **Postman:** استخدم HTTP endpoint `/api/v1/scooter/commands` للحصول على نفس التنسيق
2. **MQTT Testing:** استخدم mosquitto_sub/mosquitto_pub أو MQTTX
3. **JSON Format:** البيانات هي JSON object مباشر (ليس string)
4. **Topic:** يجب أن يكون مطابق تماماً: `scooter/{IMEI}/commands`

