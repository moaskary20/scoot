# 🔧 إصلاح مشكلة MQTT على السيرفر الخارجي

## ❌ الخطأ

```
Class "Bluerhinos\phpMQTT" not found
```

## ✅ الحل

المشكلة: الحزمة `bluerhinos/phpmqtt` غير مثبتة أو autoload غير محدث.

### الخطوات على السيرفر:

```bash
# 1. الانتقال إلى مجلد المشروع
cd /var/www/scoot  # أو المسار الصحيح

# 2. تثبيت الحزم المفقودة
composer install --no-dev --optimize-autoloader

# 3. تحديث autoload
composer dump-autoload --optimize

# 4. مسح cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. التحقق من تثبيت الحزمة
composer show bluerhinos/phpmqtt
```

### التحقق من التثبيت:

```bash
# يجب أن ترى:
composer show bluerhinos/phpmqtt
# name     : bluerhinos/phpmqtt
# versions : * 1.0.1
```

### التحقق من الملف:

```bash
ls -la vendor/bluerhinos/phpmqtt/phpMQTT.php
# يجب أن يكون الملف موجود
```

---

## 🔍 إذا استمرت المشكلة

### 1. تحقق من composer.json

```bash
cat composer.json | grep bluerhinos
# يجب أن ترى: "bluerhinos/phpmqtt": "^1.0"
```

### 2. تحقق من composer.lock

```bash
grep -A 5 "bluerhinos/phpmqtt" composer.lock
```

### 3. إعادة تثبيت الحزمة

```bash
composer remove bluerhinos/phpmqtt
composer require bluerhinos/phpmqtt --no-interaction
composer dump-autoload --optimize
```

### 4. التحقق من Permissions

```bash
# تأكد من أن vendor قابل للقراءة
chmod -R 755 vendor/
chown -R www-data:www-data vendor/  # أو المستخدم الصحيح
```

---

## ✅ بعد الإصلاح

```bash
# مسح cache
php artisan config:clear
php artisan cache:clear

# اختبار MQTT
php artisan tinker
```

```php
$mqtt = app(\App\Services\MqttService::class);
$mqtt->publishCommand('ESP32_IMEI_001', ['lock' => true, 'unlock' => false]);
```

---

## 📝 ملاحظات

1. تأكد من أن `composer.json` يحتوي على `bluerhinos/phpmqtt`
2. تأكد من تشغيل `composer install` على السيرفر
3. تأكد من تحديث autoload
4. تأكد من مسح cache Laravel

