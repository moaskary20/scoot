# 🚀 تعليمات إعداد السيرفر

## 📋 الخطوات السريعة

### 1. رفع الملفات للسيرفر

```bash
# من جهازك المحلي
scp setup_reverb.sh root@38.242.251.149:/var/www/scoot/
scp APACHE_VHOST_EXAMPLE.conf root@38.242.251.149:/tmp/
```

### 2. تشغيل Script على السيرفر

```bash
# SSH للسيرفر
ssh root@38.242.251.149

# تشغيل Script
cd /var/www/scoot
chmod +x setup_reverb.sh
bash setup_reverb.sh
```

### 3. إعداد Apache Virtual Host (إذا لم يكن موجود)

```bash
# نسخ المثال
sudo cp /tmp/APACHE_VHOST_EXAMPLE.conf /etc/apache2/sites-available/linerscoot.com.conf

# تعديل المسارات إذا لزم الأمر
sudo nano /etc/apache2/sites-available/linerscoot.com.conf

# تفعيل Site
sudo a2ensite linerscoot.com.conf

# التحقق
sudo apache2ctl configtest

# إعادة تشغيل
sudo systemctl restart apache2
```

---

## 🔧 الأوامر اليدوية (بدون Script)

### 1. تفعيل Modules

```bash
sudo a2enmod proxy proxy_http proxy_wstunnel rewrite ssl
sudo systemctl restart apache2
```

### 2. تحديث .env

```bash
cd /var/www/scoot
sed -i 's/REVERB_PORT=.*/REVERB_PORT=443/' .env
sed -i 's/REVERB_SCHEME=.*/REVERB_SCHEME=https/' .env
sed -i 's/REVERB_HOST=.*/REVERB_HOST=linerscoot.com/' .env
```

### 3. مسح Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 4. إعادة تشغيل Reverb

```bash
pkill -f "reverb:start"
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
```

### 5. إعداد Apache

```bash
sudo nano /etc/apache2/sites-available/linerscoot.com.conf
```

أضف في `<VirtualHost *:443>`:
```apache
RewriteEngine on
RewriteCond %{HTTP:Upgrade} websocket [NC]
RewriteCond %{HTTP:Connection} upgrade [NC]
RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]

ProxyPass /app/ http://127.0.0.1:8080/app/
ProxyPassReverse /app/ http://127.0.0.1:8080/app/
```

### 6. إعادة تشغيل Apache

```bash
sudo apache2ctl configtest
sudo systemctl restart apache2
```

---

## ✅ التحقق

```bash
# Reverb
ps aux | grep reverb
tail -50 reverb.log

# Apache
sudo systemctl status apache2
sudo tail -50 /var/log/apache2/error.log

# اختبار
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

---

## 🌐 Cloudflare

1. Dashboard → Network → WebSockets → **ON**
2. تأكد من أن الدومين في وضع **Proxy (Orange Cloud)**

---

## 📝 ملاحظات

- Reverb يعمل على `127.0.0.1:8080` (محلي فقط)
- Apache يتولى SSL والاتصال الخارجي
- WebSocket URL: `wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw`

