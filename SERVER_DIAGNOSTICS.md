# 🔍 تشخيص مشاكل السيرفر

## 📋 الأوامر للتحقق من المشكلة

### 1. التحقق من Reverb Server

```bash
# التحقق من Process
ps aux | grep reverb

# التحقق من المنفذ 8080
netstat -tulpn | grep 8080
# أو
ss -tulpn | grep 8080

# التحقق من Logs
tail -50 /var/www/scoot/reverb.log
# أو
tail -50 reverb.log
```

### 2. التحقق من Firewall

```bash
# عرض قواعد Firewall
sudo ufw status verbose

# التحقق من المنفذ 8080
sudo ufw status | grep 8080

# فتح المنفذ إذا لم يكن مفتوحاً
sudo ufw allow 8080/tcp
sudo ufw reload
```

### 3. التحقق من إعدادات .env

```bash
cd /var/www/scoot
cat .env | grep REVERB
```

يجب أن ترى:
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=318253
REVERB_APP_KEY=m1k6cr5egrbe0p2eycaw
REVERB_APP_SECRET=meazymdqwetpjhangtyp
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

### 4. اختبار الاتصال من السيرفر نفسه

```bash
# اختبار HTTP
curl -I http://localhost:8000/api/v1/scooter/message

# اختبار WebSocket (سيظهر خطأ لكن هذا طبيعي)
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: test" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

### 5. التحقق من Laravel

```bash
cd /var/www/scoot
php artisan config:cache
php artisan config:clear
php artisan cache:clear
```

### 6. إعادة تشغيل Reverb

```bash
# إيقاف Reverb الحالي
pkill -f "reverb:start"

# التحقق من الإيقاف
ps aux | grep reverb

# تشغيل Reverb مرة أخرى
cd /var/www/scoot
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &

# التحقق من التشغيل
ps aux | grep reverb
tail -f reverb.log
```

---

## 🔧 حلول المشاكل الشائعة

### المشكلة 1: Reverb لا يعمل

**الحل:**
```bash
cd /var/www/scoot
php artisan reverb:start --host=0.0.0.0 --port=8080
```

إذا ظهرت أخطاء، راجع:
- `.env` يحتوي على إعدادات Reverb الصحيحة
- PHP version >= 8.2
- Laravel Reverb مثبت

### المشكلة 2: المنفذ 8080 مغلق

**الحل:**
```bash
sudo ufw allow 8080/tcp
sudo ufw reload
sudo ufw status
```

### المشكلة 3: Reverb يتوقف بعد فترة

**الحل:** استخدم Supervisor (راجع `REVERB_PRODUCTION_SETUP.md`)

### المشكلة 4: SSL/HTTPS لا يعمل

**الحل:**
- تأكد من أن `REVERB_SCHEME=https` في `.env`
- تأكد من أن شهادة SSL صحيحة
- قد تحتاج إلى إعداد Nginx reverse proxy

---

## 📝 سجل الأوامر الكاملة

انسخ والصق هذه الأوامر بالترتيب:

```bash
# 1. الانتقال للمجلد
cd /var/www/scoot

# 2. التحقق من Reverb
ps aux | grep reverb

# 3. التحقق من المنفذ
netstat -tulpn | grep 8080

# 4. التحقق من Firewall
sudo ufw status | grep 8080

# 5. فتح المنفذ إذا لزم الأمر
sudo ufw allow 8080/tcp
sudo ufw reload

# 6. التحقق من .env
cat .env | grep REVERB

# 7. إيقاف Reverb القديم
pkill -f "reverb:start"

# 8. تشغيل Reverb
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &

# 9. التحقق من التشغيل
ps aux | grep reverb
tail -20 reverb.log

# 10. اختبار الاتصال
curl -I http://localhost:8080
```

---

## 🆘 إذا لم يعمل بعد

1. **تحقق من Logs:**
```bash
tail -100 /var/www/scoot/storage/logs/laravel.log
tail -100 /var/www/scoot/reverb.log
```

2. **تحقق من PHP:**
```bash
php -v
php artisan --version
```

3. **تحقق من الصلاحيات:**
```bash
ls -la /var/www/scoot/storage/logs/
```

4. **تحقق من Network:**
```bash
sudo netstat -tulpn | grep LISTEN
```

---

## 📞 معلومات إضافية

بعد تشغيل الأوامر، أرسل:
- مخرجات `ps aux | grep reverb`
- مخرجات `netstat -tulpn | grep 8080`
- مخرجات `tail -50 reverb.log`
- أي أخطاء تظهر

