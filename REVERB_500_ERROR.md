# 🔧 إصلاح خطأ 500 في Reverb

## ❌ المشكلة: HTTP/1.1 500 Internal Server Error

هذا يعني أن Reverb يعمل لكن هناك مشكلة في الإعدادات أو الكود.

---

## 🔍 الخطوات للتشخيص

### 1. التحقق من Logs

```bash
cd /var/www/scoot
tail -100 reverb.log
tail -100 storage/logs/laravel.log
```

### 2. التحقق من إعدادات .env

```bash
cat .env | grep REVERB
cat .env | grep BROADCAST
```

يجب أن يكون:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=318253
REVERB_APP_KEY=m1k6cr5egrbe0p2eycaw
REVERB_APP_SECRET=meazymdqwetpjhangtyp
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

### 3. مسح Cache

```bash
cd /var/www/scoot
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 4. التحقق من الصلاحيات

```bash
ls -la storage/logs/
chmod -R 775 storage
chown -R www-data:www-data storage
```

### 5. إعادة تشغيل Reverb

```bash
# إيقاف Reverb
pkill -f "reverb:start"

# التحقق من الإيقاف
ps aux | grep reverb

# مسح Cache
php artisan config:clear

# تشغيل Reverb مرة أخرى
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &

# التحقق
ps aux | grep reverb
tail -50 reverb.log
```

---

## 🔧 الحلول المحتملة

### الحل 1: مشكلة في .env

**تأكد من:**
- `REVERB_APP_KEY` موجود وصحيح
- `REVERB_APP_SECRET` موجود وصحيح
- `REVERB_APP_ID` موجود وصحيح
- `REVERB_HOST` موجود (linerscoot.com)

### الحل 2: مشكلة في Config Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### الحل 3: مشكلة في Broadcasting Config

```bash
# التحقق من ملف config/broadcasting.php
cat config/broadcasting.php | grep reverb

# التحقق من ملف config/reverb.php
cat config/reverb.php | grep apps
```

### الحل 4: إعادة تثبيت Reverb

```bash
composer require laravel/reverb
php artisan vendor:publish --tag=reverb.config
php artisan config:clear
```

---

## 📝 الأوامر الكاملة

```bash
cd /var/www/scoot

# 1. التحقق من Logs
tail -100 reverb.log
tail -100 storage/logs/laravel.log

# 2. التحقق من .env
cat .env | grep REVERB

# 3. مسح Cache
php artisan config:clear
php artisan cache:clear

# 4. إيقاف Reverb
pkill -f "reverb:start"

# 5. التحقق من الإيقاف
ps aux | grep reverb

# 6. تشغيل Reverb مرة أخرى
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &

# 7. التحقق
ps aux | grep reverb
tail -50 reverb.log

# 8. اختبار
curl -I http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

---

## 🆘 إذا لم يعمل بعد

أرسل:
1. مخرجات `tail -100 reverb.log`
2. مخرجات `tail -100 storage/logs/laravel.log`
3. مخرجات `cat .env | grep REVERB`

---

## 💡 ملاحظة

خطأ 500 عادة يكون بسبب:
- إعدادات .env غير صحيحة
- Config cache قديم
- مشكلة في الصلاحيات
- مشكلة في Broadcasting config

