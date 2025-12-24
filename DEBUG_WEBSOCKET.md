# 🔍 دليل Debugging لمشكلة WebSocket

## المشكلة: لا تصل الأوامر من Admin Panel إلى ESP32

### الخطوات للتحقق:

#### 1. التحقق من إعدادات .env

تأكد من وجود هذه المتغيرات في `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=http
```

**للتحقق:**
```bash
php artisan tinker
>>> config('broadcasting.default')
# يجب أن يرجع: "reverb"
```

#### 2. التحقق من device_imei في قاعدة البيانات

```bash
php artisan tinker
>>> $scooter = \App\Models\Scooter::find(1);
>>> $scooter->device_imei;
# يجب أن يرجع IMEI (مثل: "ESP32_IMEI_001")
# إذا كان null، يجب إضافته:
>>> $scooter->update(['device_imei' => 'ESP32_IMEI_001']);
```

#### 3. التحقق من Reverb Server

تأكد أن Reverb Server يعمل:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

#### 4. التحقق من Logs

بعد الضغط على Lock/Unlock في Admin Panel، تحقق من logs:

```bash
tail -f storage/logs/laravel.log
```

يجب أن ترى:
```
[INFO] Sending command to scooter via WebSocket
[INFO] Command broadcasted successfully
```

إذا رأيت:
```
[WARNING] Cannot send command: Scooter has no device_imei
```
يعني أن `device_imei` غير موجود في قاعدة البيانات.

#### 5. اختبار Broadcast مباشرة

```bash
php artisan tinker
>>> $scooter = \App\Models\Scooter::find(1);
>>> broadcast(new \App\Events\ScooterCommand($scooter->device_imei, ['lock' => true, 'unlock' => false]));
```

إذا لم يظهر خطأ، الـ broadcast يعمل.

#### 6. التحقق من Channel في Postman

في Postman، تأكد أنك مشترك في Channel الصحيح:
- Channel يجب أن يكون: `scooter.{device_imei}`
- مثال: إذا `device_imei = "ESP32_IMEI_001"`، Channel يجب أن يكون: `scooter.ESP32_IMEI_001`

### الحلول الشائعة:

#### المشكلة 1: device_imei غير موجود
**الحل:** أضف `device_imei` في قاعدة البيانات:
```sql
UPDATE scooters SET device_imei = 'ESP32_IMEI_001' WHERE id = 1;
```

#### المشكلة 2: BROADCAST_CONNECTION غير مضبوط
**الحل:** في `.env`:
```env
BROADCAST_CONNECTION=reverb
```
ثم:
```bash
php artisan config:clear
php artisan cache:clear
```

#### المشكلة 3: Reverb Server غير مشغل
**الحل:** شغل Reverb Server:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

#### المشكلة 4: Channel name غير صحيح
**الحل:** تأكد أن Channel في Postman يطابق `scooter.{device_imei}` بالضبط

### اختبار سريع:

1. افتح Postman واتصل بـ WebSocket
2. اشترك في channel: `scooter.ESP32_IMEI_001` (استبدل IMEI بالصحيح)
3. افتح Admin Panel: `admin/scooters/1`
4. اضغط Lock أو Unlock
5. في Postman، يجب أن ترى رسالة `command`

### إذا لم تعمل:

1. تحقق من logs: `storage/logs/laravel.log`
2. تحقق من Reverb logs
3. تأكد من أن `device_imei` موجود ومطابق في Postman
4. تأكد من أن Reverb Server يعمل

