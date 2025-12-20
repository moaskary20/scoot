# 🔧 إعداد Reverb مع Apache و Cloudflare

## ⚠️ مع Apache و Cloudflare

Apache يحتاج إعداد خاص لـ WebSocket Proxy.

---

## ✅ الحل الموصى به

### 1. تفعيل Modules المطلوبة

```bash
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### 2. إعداد Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/linerscoot.com.conf
```

أضف هذا داخل `<VirtualHost *:443>`:

```apache
<VirtualHost *:443>
    ServerName linerscoot.com
    ServerAlias www.linerscoot.com

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/linerscoot.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/linerscoot.com/privkey.pem

    # WebSocket Proxy for Reverb
    RewriteEngine on
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]

    # Fallback for non-WebSocket requests
    ProxyPass /app/ http://127.0.0.1:8080/app/
    ProxyPassReverse /app/ http://127.0.0.1:8080/app/

    # Laravel Application
    DocumentRoot /var/www/scoot/public
    <Directory /var/www/scoot/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/linerscoot_error.log
    CustomLog ${APACHE_LOG_DIR}/linerscoot_access.log combined
</VirtualHost>
```

**أو استخدام ProxyPass مباشرة:**

```apache
<VirtualHost *:443>
    ServerName linerscoot.com
    ServerAlias www.linerscoot.com

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/linerscoot.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/linerscoot.com/privkey.pem

    # WebSocket Proxy
    ProxyPreserveHost On
    ProxyRequests Off
    
    # WebSocket Upgrade
    RewriteEngine on
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]
    
    # HTTP Proxy
    ProxyPass /app/ http://127.0.0.1:8080/app/
    ProxyPassReverse /app/ http://127.0.0.1:8080/app/

    # Laravel Application
    DocumentRoot /var/www/scoot/public
    <Directory /var/www/scoot/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. تحديث .env

```bash
cd /var/www/scoot
nano .env
```

غيّر:
```env
REVERB_HOST=linerscoot.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### 4. إعادة تشغيل Reverb على localhost

```bash
cd /var/www/scoot
pkill -f "reverb:start"
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
```

### 5. التحقق من Apache Config

```bash
sudo apache2ctl configtest
```

يجب أن ترى: `Syntax OK`

### 6. إعادة تشغيل Apache

```bash
sudo systemctl restart apache2
```

### 7. تفعيل WebSocket في Cloudflare

1. اذهب إلى Cloudflare Dashboard
2. اختر الدومين `linerscoot.com`
3. **Network** → **WebSockets** → **ON**

---

## 🔧 الطريقة البديلة (أبسط)

إذا لم تعمل الطريقة الأولى، استخدم هذا:

```apache
<VirtualHost *:443>
    ServerName linerscoot.com

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/linerscoot.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/linerscoot.com/privkey.pem

    # WebSocket Proxy
    ProxyPass /app/ ws://127.0.0.1:8080/app/
    ProxyPassReverse /app/ ws://127.0.0.1:8080/app/

    # Laravel
    DocumentRoot /var/www/scoot/public
    <Directory /var/www/scoot/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## ✅ بعد الإعداد

**WebSocket URL:**
```
wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

**ملاحظة:** بدون منفذ `:8080` لأن Apache يتولى ذلك عبر المنفذ 443.

---

## 🔍 التحقق

### 1. التحقق من Apache

```bash
sudo apache2ctl configtest
sudo systemctl status apache2
```

### 2. التحقق من Reverb

```bash
ps aux | grep reverb
tail -50 reverb.log
```

### 3. اختبار الاتصال

```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

---

## 📝 الأوامر الكاملة

```bash
# 1. تفعيل Modules
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo a2enmod ssl

# 2. إعداد Virtual Host
sudo nano /etc/apache2/sites-available/linerscoot.com.conf
# (أضف الإعدادات أعلاه)

# 3. تفعيل Site
sudo a2ensite linerscoot.com.conf

# 4. التحقق من Config
sudo apache2ctl configtest

# 5. إعادة تشغيل Apache
sudo systemctl restart apache2

# 6. تحديث .env
cd /var/www/scoot
nano .env
# (غيّر REVERB_PORT=443)

# 7. إعادة تشغيل Reverb
pkill -f "reverb:start"
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &

# 8. التحقق
ps aux | grep reverb
tail -50 reverb.log
```

---

## 🆘 استكشاف الأخطاء

### إذا لم يعمل:

1. **تحقق من Modules:**
```bash
apache2ctl -M | grep proxy
apache2ctl -M | grep rewrite
```

2. **تحقق من Logs:**
```bash
sudo tail -50 /var/log/apache2/error.log
tail -50 reverb.log
```

3. **تحقق من Cloudflare:**
- Dashboard → Network → WebSockets → ON

4. **تحقق من SSL:**
```bash
sudo certbot certificates
```

---

## 💡 ملاحظات مهمة

1. **Apache Modules** يجب أن تكون مفعّلة
2. **Reverb** يعمل على `127.0.0.1:8080` (محلي فقط)
3. **Apache** يتولى SSL والاتصال الخارجي
4. **Cloudflare** يجب أن يكون WebSocket مفعّل
5. **URL** بدون منفذ: `wss://linerscoot.com/app/...`

