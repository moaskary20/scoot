# ⚡ فحص سريع للسيرفر

## 📋 الأوامر السريعة (انسخ والصق):

```bash
# 1. الاتصال
ssh root@38.242.251.149
# Password: askaryP@ssw0rd2040

# 2. بعد الاتصال، شغّل كل هذه الأوامر:
cd /var/www/scoot

# فحص Reverb Server
ps aux | grep reverb | grep -v grep

# فحص Broadcast Config
php artisan tinker --execute="echo config('broadcasting.default');"

# فحص .env
grep -E "BROADCAST_CONNECTION|REVERB_HOST" .env

# فحص device_imei
php artisan tinker --execute="echo \App\Models\Scooter::find(1)->device_imei;"

# فحص Port 8080
netstat -tuln | grep 8080

# فحص Logs
tail -20 storage/logs/laravel.log | grep -i "command\|broadcast"
```

## 🔧 إذا Reverb Server غير مشغل:

```bash
# شغّله في الخلفية
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &

# تحقق أنه يعمل
ps aux | grep reverb
```

## 📤 أرسل النتائج هنا

بعد تشغيل الأوامر، أرسل النتائج لأتمكن من تحديد المشكلة بدقة.





