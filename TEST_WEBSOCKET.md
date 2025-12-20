# 🧪 اختبار WebSocket

## ✅ التحقق من الإعداد

### 1. التحقق من Reverb

```bash
# على السيرفر
ssh root@38.242.251.149

# التحقق من Reverb
ps aux | grep reverb

# يجب أن ترى process يعمل على 127.0.0.1:8080
```

### 2. التحقق من Apache

```bash
# التحقق من Apache
sudo systemctl status apache2

# التحقق من Config
sudo apache2ctl configtest

# التحقق من Logs
sudo tail -50 /var/log/apache2/error.log
```

### 3. التحقق من .env

```bash
cd /var/www/scoot
cat .env | grep REVERB
```

يجب أن يكون:
```
REVERB_HOST=linerscoot.com
REVERB_PORT=443
REVERB_SCHEME=https
```

---

## 🧪 الاختبارات

### الاختبار 1: من السيرفر نفسه

```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

**النتيجة المتوقعة:**
```
HTTP/1.1 101 Switching Protocols
```

### الاختبار 2: من WebSocket Client (مستحسن)

استخدم أحد هذه الأدوات:

#### أ) WebSocket King (Chrome Extension)
1. افتح Chrome
2. ابحث عن "WebSocket King" في Chrome Web Store
3. أضف Extension
4. افتح WebSocket King
5. أدخل URL:
   ```
   wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
   ```
6. اضغط Connect

#### ب) WebSocket Test Client (Online)
1. اذهب إلى: https://www.websocket.org/echo.html
2. أدخل:
   - **Location:** `wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw`
3. اضغط Connect

#### ج) wscat (Command Line)

```bash
# تثبيت wscat
npm install -g wscat

# اختبار الاتصال
wscat -c wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

### الاختبار 3: من ESP32 Code

```cpp
// في ESP32
const char* wsServer = "linerscoot.com";
const int wsPort = 443;
const char* wsPath = "/app/m1k6cr5egrbe0p2eycaw";

// استخدام SSL
webSocket.beginSSL(wsServer, wsPort, wsPath);
```

### الاختبار 4: HTTP Endpoint (Fallback)

```bash
curl -X POST https://linerscoot.com/api/v1/scooter/message \
  -H "Content-Type: application/json" \
  -d '{
    "event": "authenticate",
    "imei": "TEST_IMEI",
    "data": {}
  }'
```

---

## 🔍 استكشاف الأخطاء

### إذا فشل الاتصال:

#### 1. تحقق من Reverb Logs

```bash
cd /var/www/scoot
tail -100 reverb.log
```

#### 2. تحقق من Apache Logs

```bash
sudo tail -100 /var/log/apache2/error.log
sudo tail -100 /var/log/apache2/access.log
```

#### 3. تحقق من Cloudflare

- تأكد من أن WebSockets مفعل
- تأكد من أن الدومين في وضع Proxy (Orange Cloud)
- جرب تعطيل Cloudflare مؤقتاً للاختبار

#### 4. اختبار بدون SSL (للتشخيص)

```bash
# على السيرفر
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  http://127.0.0.1:8080/app/m1k6cr5egrbe0p2eycaw
```

---

## ✅ قائمة التحقق

- [ ] Reverb يعمل على 127.0.0.1:8080
- [ ] Apache يعمل
- [ ] Apache Config صحيح
- [ ] .env محدث (PORT=443, SCHEME=https)
- [ ] Cloudflare WebSockets مفعل
- [ ] SSL Certificate صحيح
- [ ] Apache Modules مفعلة (proxy_wstunnel)

---

## 📝 اختبار كامل

```bash
# 1. على السيرفر
ssh root@38.242.251.149
cd /var/www/scoot

# 2. التحقق من Reverb
ps aux | grep reverb
tail -50 reverb.log

# 3. اختبار محلي
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw

# 4. من جهاز آخر (WebSocket Client)
# استخدم: wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

---

## 🎯 النتيجة المتوقعة

### نجاح الاتصال:
- **HTTP Response:** `101 Switching Protocols`
- **WebSocket Client:** Connection Established
- **ESP32:** يمكن الاتصال وإرسال/استقبال البيانات

### فشل الاتصال:
- **HTTP Response:** `400`, `404`, `500`, أو `502`
- **WebSocket Client:** Connection Failed
- **ESP32:** لا يمكن الاتصال

---

## 💡 نصائح

1. **ابدأ بالاختبار من السيرفر** - إذا عمل محلياً، المشكلة في Cloudflare أو Firewall
2. **استخدم WebSocket Client** - أسهل طريقة للاختبار
3. **راجع Logs** - دائماً تحقق من Logs عند الفشل
4. **اختبر HTTP Fallback** - إذا WebSocket لا يعمل، HTTP يجب أن يعمل

