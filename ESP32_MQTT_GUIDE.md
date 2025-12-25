# 🔌 دليل MQTT لـ ESP32

## نظرة عامة

تم استبدال WebSocket بـ MQTT (Mosquitto) لإرسال الأوامر إلى ESP32. MQTT يوفر:
- ✅ JSON object مباشر في payload (بدون escape)
- ✅ خفيف الوزن - مناسب لـ ESP32
- ✅ Publish/Subscribe pattern مثالي
- ✅ QoS levels للرسائل
- ✅ Retain messages (ESP32 يستقبل آخر أمر حتى لو كان offline)

---

## 📡 إعداد MQTT Broker (Mosquitto)

### 1. تثبيت Mosquitto

**على Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install mosquitto mosquitto-clients -y
```

**على CentOS/RHEL:**
```bash
sudo yum install epel-release
sudo yum install mosquitto mosquitto-clients -y
```

### 2. تشغيل Mosquitto

```bash
sudo systemctl start mosquitto
sudo systemctl enable mosquitto
sudo systemctl status mosquitto
```

### 3. إعداد Configuration (اختياري)

```bash
sudo nano /etc/mosquitto/mosquitto.conf
```

إعدادات أساسية:
```
listener 1883
allow_anonymous true  # للتطوير فقط - في الإنتاج استخدم authentication
```

### 4. إعادة تشغيل Mosquitto

```bash
sudo systemctl restart mosquitto
```

### 5. اختبار الاتصال

```bash
# Terminal 1 - Subscribe
mosquitto_sub -h localhost -t test/topic

# Terminal 2 - Publish
mosquitto_pub -h localhost -t test/topic -m "Hello MQTT"
```

---

## 🔗 الاتصال من ESP32

### MQTT Broker Information

**للتطوير:**
```
Host: localhost (أو IP السيرفر)
Port: 1883
```

**للإنتاج:**
```
Host: your-domain.com (أو IP السيرفر)
Port: 1883 (أو 8883 لـ TLS)
```

### Topic Structure

**Topic للأوامر:**
```
scooter/{IMEI}/commands
```

مثال: `scooter/ESP32_IMEI_001/commands`

---

## 📤 Message Format

**Topic:** `scooter/{imei}/commands`

**Payload (JSON object مباشر):**
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

**ملاحظة:** البيانات هي JSON object مباشر (ليس string) - لا حاجة لفك التشفير!

---

## 💻 مثال كود ESP32 (Arduino)

### 1. تثبيت المكتبات

في Arduino IDE:
- Tools → Manage Libraries
- ابحث عن `PubSubClient` وثبتها
- ابحث عن `ArduinoJson` وثبتها

### 2. الكود الكامل

```cpp
#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// WiFi credentials
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Broker
const char* mqtt_server = "your-domain.com";  // أو IP السيرفر
const int mqtt_port = 1883;

// IMEI الخاص بالسكوتر
const char* imei = "ESP32_IMEI_001";

// MQTT Client
WiFiClient espClient;
PubSubClient client(espClient);

// Topic للأوامر
String commandTopic;

void setup() {
    Serial.begin(115200);
    
    // الاتصال بـ WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
    
    // إعداد MQTT
    client.setServer(mqtt_server, mqtt_port);
    client.setCallback(mqttCallback);
    
    // Topic للأوامر
    commandTopic = "scooter/" + String(imei) + "/commands";
}

void loop() {
    if (!client.connected()) {
        reconnect();
    }
    client.loop();
}

void reconnect() {
    while (!client.connected()) {
        Serial.print("Attempting MQTT connection...");
        
        // Client ID فريد
        String clientId = "ESP32-" + String(imei) + "-" + String(random(0xffff), HEX);
        
        if (client.connect(clientId.c_str())) {
            Serial.println("connected");
            
            // الاشتراك في topic الأوامر
            client.subscribe(commandTopic.c_str());
            Serial.print("Subscribed to: ");
            Serial.println(commandTopic);
        } else {
            Serial.print("failed, rc=");
            Serial.print(client.state());
            Serial.println(" try again in 5 seconds");
            delay(5000);
        }
    }
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
    Serial.print("Message arrived [");
    Serial.print(topic);
    Serial.print("] ");
    
    // تحويل payload إلى string
    String message = "";
    for (int i = 0; i < length; i++) {
        message += (char)payload[i];
    }
    Serial.println(message);
    
    // فك تشفير JSON (JSON object مباشر - ليس string)
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, message);
    
    if (error) {
        Serial.print("JSON parse error: ");
        Serial.println(error.c_str());
        return;
    }
    
    String event = doc["event"] | "";
    
    if (event == "command") {
        // البيانات مباشرة كـ JSON object
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

void lockScooter() {
    // كود قفل السكوتر
    Serial.println("Locking scooter...");
    // TODO: تنفيذ قفل السكوتر
}

void unlockScooter() {
    // كود فتح السكوتر
    Serial.println("Unlocking scooter...");
    // TODO: تنفيذ فتح السكوتر
}
```

---

## 🔧 إعدادات Laravel

### 1. إضافة متغيرات `.env`

```env
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=laravel-scooter-app
MQTT_QOS=1
MQTT_RETAIN=true
```

### 2. اختبار MQTT من Laravel

```bash
php artisan tinker
```

```php
$mqtt = app(\App\Services\MqttService::class);
$mqtt->publishCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]);
```

---

## 📊 مقارنة WebSocket vs MQTT

| الميزة | WebSocket (Reverb) | MQTT |
|--------|-------------------|------|
| JSON Format | String (escape) | Object مباشر |
| Protocol | Pusher | MQTT |
| Weight | متوسط | خفيف جداً |
| Retain Messages | ❌ | ✅ |
| QoS Levels | ❌ | ✅ (0, 1, 2) |
| Last Will | ❌ | ✅ |
| مناسب لـ IoT | متوسط | ممتاز |

---

## ✅ المزايا الرئيسية لـ MQTT

1. **JSON Object مباشر:** لا حاجة لفك تشفير JSON string
2. **Retain Messages:** ESP32 يستقبل آخر أمر حتى لو كان offline
3. **QoS Levels:** ضمان وصول الرسائل (QoS 1)
4. **خفيف الوزن:** مناسب جداً لـ ESP32
5. **Last Will and Testament:** إشعار عند انقطاع الاتصال

---

## 🐛 استكشاف الأخطاء

### ESP32 لا يتصل بـ MQTT Broker

1. تحقق من أن Mosquitto يعمل:
   ```bash
   sudo systemctl status mosquitto
   ```

2. تحقق من Firewall:
   ```bash
   sudo ufw allow 1883/tcp
   ```

3. اختبر الاتصال:
   ```bash
   mosquitto_pub -h localhost -t test/topic -m "test"
   ```

### الرسائل لا تصل

1. تحقق من Topic name (يجب أن يكون مطابق تماماً)
2. تحقق من QoS level
3. تحقق من Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## 📝 ملاحظات مهمة

1. **Topic Name:** يجب أن يكون مطابق تماماً: `scooter/{IMEI}/commands`
2. **JSON Format:** البيانات هي JSON object مباشر - لا حاجة لفك تشفير
3. **QoS:** استخدم QoS 1 لضمان وصول الرسالة مرة واحدة على الأقل
4. **Retain:** تفعيل Retain يضمن استقبال آخر أمر حتى لو كان ESP32 offline

---

## 🔗 روابط مفيدة

- [Mosquitto Documentation](https://mosquitto.org/documentation/)
- [PubSubClient Library](https://github.com/knolleary/pubsubclient)
- [ArduinoJson Library](https://arduinojson.org/)

