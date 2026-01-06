# 🔍 فحص Reverb Server Logs

## على السيرفر، شغّل:

```bash
cd /var/www/scoot

# فحص Reverb logs
tail -f reverb.log

# أو إذا كان في storage/logs
tail -f storage/logs/reverb.log

# أو فحص جميع الـ logs
tail -f storage/logs/*.log
```

## ثم:

1. اضغط Lock/Unlock من Admin Panel
2. راقب Reverb logs
3. يجب أن ترى رسائل عن إرسال البيانات للعملاء المتصلين

## إذا لم تظهر Reverb logs:

قد يكون Reverb Server لا يكتب logs. في هذه الحالة:

1. **أعد تشغيل Reverb Server مع logs:**
   ```bash
   pkill -f "reverb:start"
   sleep 2
   nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
   ```

2. **تحقق من الاتصالات النشطة:**
   ```bash
   # Reverb لا يوفر طريقة مباشرة لرؤية الاتصالات
   # لكن يمكنك فحص Port 8080
   netstat -an | grep 8080
   ```

## اختبار في Postman:

1. ✅ تأكد أنك متصل بـ: `ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw`
2. ✅ تأكد أنك مشترك في: `scooter.ESP32_IMEI_001` (بالضبط)
3. ✅ عند استقبال `pusher:ping`، أرسل فوراً:
   ```json
   {"event":"pusher:pong","data":{}}
   ```
4. ✅ اضغط Lock/Unlock من Admin Panel
5. ✅ يجب أن ترى رسالة `command` في Postman

## إذا لم تصل الرسائل:

المشكلة قد تكون:
1. **Postman غير متصل** - تحقق من حالة الاتصال
2. **Channel name غير صحيح** - يجب أن يكون بالضبط `scooter.ESP32_IMEI_001`
3. **Postman لا يرد على ping** - الاتصال ينقطع قبل وصول الرسالة
4. **Reverb Server لا يرسل للعملاء** - مشكلة في Reverb نفسه





