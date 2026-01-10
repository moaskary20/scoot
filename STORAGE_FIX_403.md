# إصلاح خطأ 403 عند تحميل الصور

## المشكلة
عند محاولة تحميل الصور (avatar, national_id, etc.) من `https://linerscoot.com/storage/...`، يظهر خطأ 403 Forbidden.

## الحل

### 1. إنشاء Symbolic Link
قم بتنفيذ الأمر التالي على السيرفر:
```bash
cd /path/to/your/laravel/project
php artisan storage:link
```

هذا الأمر ينشئ symbolic link من `public/storage` إلى `storage/app/public`.

### 2. التحقق من الصلاحيات
تأكد من الصلاحيات الصحيحة:

```bash
# صلاحيات المجلدات
chmod -R 775 storage/app/public
chmod -R 775 public/storage

# صلاحيات الملفات
find storage/app/public -type f -exec chmod 664 {} \;
find public/storage -type f -exec chmod 664 {} \;

# ملكية الملفات (إذا كان السيرفر يستخدم www-data)
chown -R www-data:www-data storage/app/public
chown -R www-data:www-data public/storage
```

### 3. التحقق من وجود الملف
تأكد من أن الملف موجود في المسار الصحيح:
```bash
ls -la storage/app/public/avatars/
ls -la public/storage/avatars/
```

### 4. التحقق من .htaccess
تأكد من أن ملف `public/.htaccess` يسمح بالوصول إلى ملفات storage:
```
# Allow access to storage files
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^storage/ - [L]
```

### 5. إذا كان Symbolic Link غير موجود
إذا كان symbolic link غير موجود، قم بإنشائه يدوياً:
```bash
ln -s /path/to/your/laravel/project/storage/app/public /path/to/your/laravel/project/public/storage
```

## ملاحظات
- بعد إصلاح الصلاحيات، قد تحتاج إلى إعادة تحميل الصفحة أو مسح cache المتصفح
- تأكد من أن المجلد `storage/app/public/avatars` موجود وله صلاحيات 775
- تأكد من أن الملفات المحفوظة في `storage/app/public/avatars` لها صلاحيات 664
