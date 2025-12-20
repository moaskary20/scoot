# 🔧 إصلاح مشكلة Reverb 404

## ✅ Reverb Server يعمل
**Process ID:** 214665 ✅

## ❌ المشكلة: 404 Not Found

المشكلة أن Reverb يحتاج المسار الكامل `/app/{app-key}` وليس فقط `/`

---

## 🔍 التحقق الصحيح

### 1. اختبار المسار الصحيح:

```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: test" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

يجب أن ترى:
```
HTTP/1.1 101 Switching Protocols
```

### 2. التحقق من إعدادات .env:

```bash
cd /var/www/scoot
cat .env | grep REVERB
```

يجب أن يكون:
```
REVERB_APP_KEY=m1k6cr5egrbe0p2eycaw
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

### 3. التحقق من المنفذ:

```bash
netstat -tulpn | grep 8080
```

يجب أن ترى:
```
tcp  0  0  0.0.0.0:8080  0.0.0.0:*  LISTEN  214665/php
```

---

## ✅ الحل

### الخطوة 1: التحقق من Firewall

```bash
sudo ufw allow 8080/tcp
sudo ufw reload
sudo ufw status | grep 8080
```

### الخطوة 2: إعادة تشغيل Reverb (إذا لزم الأمر)

```bash
cd /var/www/scoot

# إيقاف Reverb
pkill -f "reverb:start"

# التحقق من الإيقاف
ps aux | grep reverb

# تشغيل Reverb مرة أخرى
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &

# التحقق
ps aux | grep reverb
```

### الخطوة 3: اختبار من خارج السيرفر

**WebSocket URL:**
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

**HTTP URL:**
```
POST https://linerscoot.com/api/v1/scooter/message
```

---

## 📝 ملاحظات مهمة

1. **404 على `/` طبيعي** - Reverb يحتاج المسار `/app/{key}`
2. **المنفذ 8080 يجب أن يكون مفتوحاً** في Firewall
3. **REVERB_HOST يجب أن يكون الدومين** (linerscoot.com)

---

## 🔍 اختبار كامل

```bash
# 1. اختبار Reverb من السيرفر
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: test" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw

# 2. التحقق من Logs
tail -50 reverb.log

# 3. التحقق من Firewall
sudo ufw status verbose
```

---

## ✅ بعد الإصلاح

Reverb يجب أن يعمل على:
- **WebSocket:** `wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
- **HTTP:** `https://linerscoot.com/api/v1/scooter/message`

