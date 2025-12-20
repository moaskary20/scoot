# ✅ إصلاح Reverb - خطوات محددة

## ✅ الإعدادات في .env صحيحة

الآن يجب مسح Cache وإعادة تشغيل Reverb.

---

## 🔧 الخطوات المطلوبة

### 1. مسح Config Cache

```bash
cd /var/www/scoot
php artisan config:clear
php artisan cache:clear
```

### 2. إعادة بناء Config Cache

```bash
php artisan config:cache
```

### 3. إيقاف Reverb الحالي

```bash
pkill -f "reverb:start"
ps aux | grep reverb  # للتأكد من الإيقاف
```

### 4. تشغيل Reverb مرة أخرى

```bash
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &
```

### 5. التحقق من التشغيل

```bash
ps aux | grep reverb
tail -50 reverb.log
```

### 6. اختبار الاتصال

```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

يجب أن ترى:
```
HTTP/1.1 101 Switching Protocols
```

---

## 📝 الأوامر الكاملة (انسخ والصق)

```bash
cd /var/www/scoot

# مسح Cache
php artisan config:clear
php artisan cache:clear

# إعادة بناء Cache
php artisan config:cache

# إيقاف Reverb
pkill -f "reverb:start"

# انتظر ثانيتين
sleep 2

# التحقق من الإيقاف
ps aux | grep reverb

# تشغيل Reverb
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &

# انتظر ثانيتين
sleep 2

# التحقق من التشغيل
ps aux | grep reverb
tail -50 reverb.log

# اختبار
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

---

## ✅ بعد الإصلاح

إذا عمل كل شيء، يجب أن:
1. Reverb يعمل بدون أخطاء
2. الاتصال يعمل محلياً
3. يمكن فتح Firewall للاتصال من الخارج

---

## 🔍 إذا لم يعمل

أرسل:
1. مخرجات `tail -100 reverb.log`
2. مخرجات `tail -100 storage/logs/laravel.log`
3. مخرجات `php artisan config:show reverb`

