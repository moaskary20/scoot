# 🚀 إعداد WebSocket لـ ESP32 - دليل سريع

## الخطوات السريعة

### 1. تثبيت Reverb (تم بالفعل ✅)
```bash
composer require laravel/reverb
```

### 2. إعداد متغيرات البيئة

أضف إلى ملف `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=linerscoot-app
REVERB_APP_KEY=linerscoot-key
REVERB_APP_SECRET=linerscoot-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http
```

لإنشاء مفاتيح آمنة:
```bash
php artisan reverb:install
```

### 3. تشغيل Reverb Server

```bash
php artisan reverb:start
```

أو للتطوير:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 4. تشغيل Laravel Application

في terminal آخر:
```bash
php artisan serve
```

---

## 🔗 معلومات الاتصال

### WebSocket URL للتطوير:
```
ws://localhost:8080/app/linerscoot-key
```

### WebSocket URL للإنتاج:
```
wss://your-domain.com:8080/app/your-app-key
```

---

## 📋 الأحداث المتاحة

### من ESP32 إلى Server:
- `authenticate` - التحقق من IMEI
- `update-location` - تحديث الموقع والبطارية
- `update-lock-status` - تحديث حالة القفل
- `update-battery` - تحديث البطارية فقط
- `get-commands` - الحصول على الأوامر

### من Server إلى ESP32:
- `command` - أمر قفل/فتح

---

## 🔧 HTTP Fallback

إذا كان ESP32 لا يدعم WebSocket بعد، يمكن استخدام:

```
POST /api/v1/scooter/message
```

مع نفس format الأحداث.

---

## 📖 للتفاصيل الكاملة
راجع: [ESP32_WEBSOCKET_GUIDE.md](./ESP32_WEBSOCKET_GUIDE.md)

