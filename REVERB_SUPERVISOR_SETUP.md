# 🔧 إعداد Reverb مع Supervisor

## ❌ المشكلة: Supervisor غير مُعدّ

Reverb لا يعمل عبر Supervisor. يمكن تشغيله يدوياً أو إعداده مع Supervisor.

---

## ✅ الحل السريع: تشغيل يدوي

### 1. التحقق من Reverb الحالي

```bash
cd /var/www/scoot
ps aux | grep reverb
```

### 2. إيقاف Reverb القديم (إذا كان يعمل)

```bash
pkill -f "reverb:start"
```

### 3. تشغيل Reverb

```bash
cd /var/www/scoot
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
```

### 4. التحقق من التشغيل

```bash
ps aux | grep reverb
tail -50 reverb.log
```

---

## 🔧 إعداد Supervisor (مستحسن للإنتاج)

### 1. إنشاء ملف Supervisor Config

```bash
sudo nano /etc/supervisor/conf.d/reverb.conf
```

أضف:

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/scoot/artisan reverb:start --host=127.0.0.1 --port=8080
directory=/var/www/scoot
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/scoot/storage/logs/reverb.log
stopwaitsecs=3600
```

### 2. تحديث Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

### 3. تشغيل Reverb

```bash
sudo supervisorctl start reverb:*
```

### 4. التحقق من الحالة

```bash
sudo supervisorctl status reverb:*
```

---

## 📝 الأوامر الكاملة

### للتشغيل اليدوي (سريع):

```bash
cd /var/www/scoot
pkill -f "reverb:start" || true
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
sleep 2
ps aux | grep reverb
tail -50 reverb.log
```

### لإعداد Supervisor:

```bash
# 1. إنشاء ملف Config
sudo nano /etc/supervisor/conf.d/reverb.conf
# (انسخ المحتوى أعلاه)

# 2. تحديث Supervisor
sudo supervisorctl reread
sudo supervisorctl update

# 3. تشغيل
sudo supervisorctl start reverb:*

# 4. التحقق
sudo supervisorctl status reverb:*
```

---

## ✅ بعد الإعداد

### التحقق من Reverb:

```bash
# إذا كان يدوياً
ps aux | grep reverb

# إذا كان عبر Supervisor
sudo supervisorctl status reverb:*
```

### اختبار الاتصال:

```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

---

## 🆘 استكشاف الأخطاء

### إذا Supervisor لا يعمل:

```bash
# التحقق من Supervisor
sudo systemctl status supervisor

# تشغيل Supervisor
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

### إذا Reverb لا يبدأ:

```bash
# التحقق من Logs
tail -100 reverb.log
tail -100 storage/logs/laravel.log

# التحقق من .env
cat .env | grep REVERB

# مسح Cache
php artisan config:clear
php artisan cache:clear
```

---

## 💡 ملاحظات

1. **للإنتاج:** استخدم Supervisor للتشغيل التلقائي
2. **للتطوير:** التشغيل اليدوي كافٍ
3. **Reverb** يجب أن يعمل على `127.0.0.1:8080` (محلي فقط)
4. **Apache** يتولى الاتصال الخارجي

