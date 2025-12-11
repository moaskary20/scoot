# ESP32 Integration Guide
## دليل ربط ESP32 مع Laravel Backend

## ⚠️ ملاحظة مهمة
**تم استبدال API بـ WebSocket!** 

يرجى الرجوع إلى [ESP32_WEBSOCKET_GUIDE.md](./ESP32_WEBSOCKET_GUIDE.md) للحصول على دليل WebSocket الكامل.

## نظرة عامة
تم إنشاء نظام متكامل للتحكم في السكوترات من خلال ESP32 يتصل مع Laravel Backend عبر WebSocket لإرسال واستقبال البيانات والأوامر في الوقت الفعلي.

## WebSocket Integration

النظام يستخدم الآن WebSocket بدلاً من REST API لتحسين الأداء والاستجابة الفورية.

---

## ~~API Endpoints في Laravel~~ (مهمل - تم استبداله بـ WebSocket)

### 1. Authentication
```
POST /api/v1/scooter/authenticate
Body: {
  "imei": "YOUR_DEVICE_IMEI"
}
Response: {
  "success": true,
  "scooter_id": 1,
  "code": "SCOOTER001",
  "commands": {
    "lock": false,
    "unlock": true
  },
  "status": "available"
}
```

### 2. Update Location
```
POST /api/v1/scooter/update-location
Body: {
  "imei": "YOUR_DEVICE_IMEI",
  "latitude": 30.0444,
  "longitude": 31.2357,
  "battery_percentage": 85,
  "lock_status": true
}
Response: {
  "success": true,
  "message": "Location updated",
  "commands": {
    "lock": false,
    "unlock": false
  },
  "scooter_status": "available"
}
```

### 3. Get Commands
```
POST /api/v1/scooter/get-commands
Body: {
  "imei": "YOUR_DEVICE_IMEI"
}
Response: {
  "success": true,
  "commands": {
    "lock": true,
    "unlock": false
  },
  "scooter_status": "available",
  "current_lock_status": false
}
```

### 4. Update Lock Status
```
POST /api/v1/scooter/update-lock-status
Body: {
  "imei": "YOUR_DEVICE_IMEI",
  "lock_status": true
}
Response: {
  "success": true,
  "message": "Lock status updated",
  "is_locked": true
}
```

### 5. Update Battery
```
POST /api/v1/scooter/update-battery
Body: {
  "imei": "YOUR_DEVICE_IMEI",
  "battery_percentage": 85
}
Response: {
  "success": true,
  "message": "Battery updated",
  "battery_percentage": 85
}
```

## إعداد قاعدة البيانات

### 1. إضافة IMEI للسكوتر
عند إنشاء سكوتر جديد، تأكد من إضافة `device_imei`:
```php
Scooter::create([
    'code' => 'SCOOTER001',
    'device_imei' => 'ESP32_IMEI_123456',
    // ... other fields
]);
```

### 2. التحقق من IMEI
الـ IMEI في ESP32 يجب أن يتطابق مع `device_imei` في جدول `scooters`.

## الميزات المدمجة

### 1. كشف الحركة غير المصرح بها
- عند إرسال GPS update، يتم التحقق تلقائياً من وجود رحلة نشطة
- إذا تحرك السكوتر بدون رحلة، يتم قفله تلقائياً وتسجيل الحدث

### 2. كشف خروج المنطقة
- يتم التحقق من خروج السكوتر من المنطقة المسموحة
- يتم تسجيل الحدث في `scooter_logs`

### 3. تسجيل انخفاض البطارية
- يتم تسجيل انخفاضات البطارية تلقائياً
- يتم تحديد مستوى الخطورة حسب مقدار الانخفاض

### 4. الأوامر التلقائية
- إذا كان السكوتر في حالة `rented` ومقفول، يتم إرسال أمر unlock
- إذا كان السكوتر في حالة `available` وغير مقفول، يتم إرسال أمر lock

## الأمان

### ملاحظات مهمة:
1. **في الإنتاج:** أضف API Authentication (API Keys)
2. **HTTPS:** استخدم HTTPS فقط
3. **Rate Limiting:** أضف rate limiting للـ API endpoints
4. **Validation:** جميع البيانات يتم التحقق منها قبل المعالجة

## اختبار API

يمكنك اختبار API باستخدام Postman أو curl:

```bash
# Authentication
curl -X POST https://your-domain.com/api/v1/scooter/authenticate \
  -H "Content-Type: application/json" \
  -d '{"imei": "YOUR_IMEI"}'

# Update Location
curl -X POST https://your-domain.com/api/v1/scooter/update-location \
  -H "Content-Type: application/json" \
  -d '{
    "imei": "YOUR_IMEI",
    "latitude": 30.0444,
    "longitude": 31.2357,
    "battery_percentage": 85,
    "lock_status": true
  }'
```

## استكشاف الأخطاء

### ESP32 لا يستطيع الاتصال بالـ API
1. تحقق من API URL
2. تأكد من أن IMEI موجود في قاعدة البيانات
3. تحقق من Serial Monitor للأخطاء
4. تأكد من اتصال WiFi

### الأوامر لا تصل للـ ESP32
1. تحقق من أن ESP32 يطلب الأوامر كل 5 ثواني
2. تحقق من Serial Monitor
3. تأكد من أن السكوتر في حالة صحيحة

### GPS لا يعمل
1. تحقق من التوصيلات
2. تأكد من أن GPS في مكان مكشوف
3. انتظر دقائق قليلة للحصول على إشارة

