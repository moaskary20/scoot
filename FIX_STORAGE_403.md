# إصلاح مشكلة 403 Forbidden للصور المخزنة

## المشكلة:
عند محاولة الوصول لصور البطاقة الشخصية من الـ admin panel، يظهر خطأ:
```
403 Forbidden
storage/national_ids/1767714069_front_national_id_front.jpg
```

## الأسباب المحتملة:

### 1. الصلاحيات غير صحيحة على السيرفر
الملفات والمجلدات يجب أن تكون قابلة للقراءة من قبل الـ web server.

### 2. الـ web server يمنع الوصول للملفات
Apache أو Nginx قد يمنع الوصول للملفات في مجلد `storage`.

## الحل:

### على السيرفر الخارجي:

#### 1. التحقق من الصلاحيات:
```bash
# الانتقال لمجلد المشروع
cd /path/to/your/project

# إعطاء الصلاحيات الصحيحة للمجلدات والملفات
chmod -R 755 storage
chmod -R 755 storage/app
chmod -R 755 storage/app/public
chmod -R 755 storage/app/public/national_ids

# إعطاء الصلاحيات للملفات
find storage/app/public -type f -exec chmod 644 {} \;

# إعطاء الصلاحيات للمجلدات
find storage/app/public -type d -exec chmod 755 {} \;
```

#### 2. التأكد من symbolic link:
```bash
# التحقق من وجود الـ link
ls -la public/storage

# إذا لم يكن موجوداً، أنشئه
php artisan storage:link
```

#### 3. إذا كان Apache:
أنشئ ملف `.htaccess` في `storage/app/public/national_ids/`:
```bash
cd storage/app/public/national_ids
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Allow access to all files
    RewriteRule .* - [L]
</IfModule>
EOF
```

أو أضف هذا في `.htaccess` الرئيسي في `public/` (موجود بالفعل):
```apache
# Allow access to storage files
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^storage/ - [L]
```

#### 4. إذا كان Nginx:
تأكد من أن الـ configuration يسمح بالوصول للملفات:
```nginx
location /storage {
    alias /path/to/your/project/storage/app/public;
    try_files $uri =404;
}
```

#### 5. التحقق من مالك الملفات:
```bash
# التأكد من أن الـ web server يملك الملفات أو يمكنه قراءتها
# عادة Apache/Nginx يعمل كـ www-data أو nginx
sudo chown -R www-data:www-data storage/app/public
# أو
sudo chown -R nginx:nginx storage/app/public

# ثم إعطاء الصلاحيات
sudo chmod -R 755 storage/app/public
```

#### 6. التحقق من SELinux (إذا كان مفعلاً):
```bash
# إذا كان SELinux مفعلاً، قد تحتاج لتعديل السياق
sudo chcon -R -t httpd_sys_content_t storage/app/public
```

### 7. التحقق من وجود الملف:
```bash
# التحقق من وجود الملف فعلاً
ls -la storage/app/public/national_ids/1767714069_front_national_id_front.jpg

# إذا لم يكن موجوداً، قد يكون هناك مشكلة في حفظ الملف
# تحقق من الـ logs
tail -f storage/logs/laravel.log
```

## التحقق من الحل:

1. افتح المتصفح واذهب إلى:
   ```
   https://linerscoot.com/storage/national_ids/1767714069_front_national_id_front.jpg
   ```

2. يجب أن تظهر الصورة بدون خطأ 403.

3. إذا استمرت المشكلة، تحقق من:
   - الـ web server error logs
   - Laravel logs
   - الصلاحيات على الملفات

## ملاحظات:

- تأكد من أن `storage/app/public` موجود وله الصلاحيات الصحيحة
- تأكد من أن `public/storage` هو symbolic link يشير إلى `storage/app/public`
- تأكد من أن الـ web server يمكنه قراءة الملفات

