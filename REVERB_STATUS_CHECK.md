# ✅ التحقق من حالة Reverb Server

## ✅ Reverb Server تم تشغيله

**Process ID:** 214665

---

## 🔍 التحقق من الحالة

### 1. التحقق من أن Process يعمل:

```bash
ps aux | grep 214665
# أو
ps aux | grep reverb
```

### 2. التحقق من المنفذ 8080:

```bash
netstat -tulpn | grep 8080
# أو
ss -tulpn | grep 8080
```

يجب أن ترى:
```
tcp  0  0  0.0.0.0:8080  0.0.0.0:*  LISTEN  214665/php
```

### 3. التحقق من Logs:

```bash
tail -f reverb.log
```

### 4. اختبار الاتصال من السيرفر نفسه:

```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: test" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

---

## 🔧 إدارة Reverb Server

### إيقاف Reverb:

```bash
kill 214665
# أو
pkill -f "reverb:start"
```

### إعادة التشغيل:

```bash
# إيقاف
pkill -f "reverb:start"

# تشغيل مرة أخرى
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &
```

### عرض Logs:

```bash
tail -f reverb.log
# أو
cat reverb.log
```

---

## 🌐 اختبار من خارج السيرفر

### WebSocket URL:
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

### HTTP URL:
```
POST https://linerscoot.com/api/v1/scooter/message
```

---

## ⚠️ ملاحظات مهمة

1. **Reverb يجب أن يعمل دائماً** - استخدم Supervisor للإنتاج
2. **المنفذ 8080 يجب أن يكون مفتوحاً** في Firewall
3. **إذا توقف Reverb** - سيحتاج ESP32 إلى إعادة الاتصال

---

## 🚀 الخطوات التالية

1. ✅ Reverb Server يعمل
2. ⏳ تأكد من فتح المنفذ 8080 في Firewall
3. ⏳ اختبر الاتصال من WebSocket Client
4. ⏳ (اختياري) إعداد Supervisor للتشغيل التلقائي

---

## 📝 إعداد Supervisor (مستحسن للإنتاج)

راجع: `REVERB_PRODUCTION_SETUP.md`

