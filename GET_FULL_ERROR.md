# 🔍 الحصول على الخطأ الكامل

## على السيرفر، شغّل:

```bash
cd /var/www/scoot

# للحصول على آخر خطأ كامل
tail -200 storage/logs/laravel.log | grep -A 30 -B 5 "ERROR\|Exception" | tail -50

# أو للحصول على آخر 100 سطر
tail -100 storage/logs/laravel.log

# أو للبحث عن أخطاء Broadcast
tail -200 storage/logs/laravel.log | grep -i "broadcast\|command\|websocket" | tail -20
```

## أو استخدم السكريبت:

```bash
chmod +x check_error.sh
./check_error.sh
```

## أرسل النتائج

بعد تشغيل الأوامر، أرسل:
1. آخر خطأ كامل (مع stack trace)
2. أي رسائل متعلقة بـ broadcast أو command





