# 🔍 التحقق من إعداد Apache WebSocket

## ✅ Apache يعمل بدون أخطاء

الآن يجب التحقق من إعداد WebSocket Proxy.

---

## 🔧 الخطوات

### 1. التحقق من Apache Virtual Host

```bash
sudo cat /etc/apache2/sites-available/linerscoot.com.conf | grep -A 10 "VirtualHost"
```

أو:

```bash
sudo nano /etc/apache2/sites-available/linerscoot.com.conf
```

**يجب أن يحتوي على:**

```apache
<VirtualHost *:443>
    # ... SSL config ...
    
    # WebSocket Proxy
    RewriteEngine on
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]
    
    ProxyPass /app/ http://127.0.0.1:8080/app/
    ProxyPassReverse /app/ http://127.0.0.1:8080/app/
    
    # ... باقي الإعدادات ...
</VirtualHost>
```

### 2. التحقق من Modules

```bash
apache2ctl -M | grep proxy
apache2ctl -M | grep rewrite
```

يجب أن ترى:
- `proxy_module`
- `proxy_http_module`
- `proxy_wstunnel_module`
- `rewrite_module`

### 3. التحقق من Reverb

```bash
cd /var/www/scoot
ps aux | grep reverb
netstat -tulpn | grep 8080
```

يجب أن ترى Reverb يعمل على `127.0.0.1:8080`

### 4. اختبار الاتصال

```bash
# اختبار من السيرفر
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

---

## 🔧 إذا لم يكن WebSocket Proxy موجود

### أضف هذا في VirtualHost:

```bash
sudo nano /etc/apache2/sites-available/linerscoot.com.conf
```

أضف داخل `<VirtualHost *:443>`:

```apache
# WebSocket Proxy for Reverb
RewriteEngine on
RewriteCond %{HTTP:Upgrade} websocket [NC]
RewriteCond %{HTTP:Connection} upgrade [NC]
RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]

ProxyPass /app/ http://127.0.0.1:8080/app/
ProxyPassReverse /app/ http://127.0.0.1:8080/app/
```

### ثم:

```bash
# التحقق من Config
sudo apache2ctl configtest

# إعادة تشغيل Apache
sudo systemctl restart apache2
```

---

## 📝 الأوامر الكاملة للتحقق

```bash
# 1. التحقق من Virtual Host
sudo cat /etc/apache2/sites-available/linerscoot.com.conf | grep -A 20 "VirtualHost.*443"

# 2. التحقق من Modules
apache2ctl -M | grep -E "proxy|rewrite"

# 3. التحقق من Reverb
ps aux | grep reverb
netstat -tulpn | grep 8080

# 4. التحقق من .env
cd /var/www/scoot
cat .env | grep REVERB

# 5. اختبار الاتصال
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==" \
  https://linerscoot.com/app/m1k6cr5egrbe0p2eycaw

# 6. التحقق من Access Logs
sudo tail -20 /var/log/apache2/access.log
```

---

## ✅ النتيجة المتوقعة

### إذا كان كل شيء صحيح:
- **curl:** `HTTP/1.1 101 Switching Protocols`
- **WebSocket Client:** Connection Established

### إذا كان هناك مشكلة:
- **curl:** `400`, `404`, `502`, أو `503`
- **WebSocket Client:** Connection Failed

---

## 🆘 إذا لم يعمل

أرسل:
1. مخرجات `sudo cat /etc/apache2/sites-available/linerscoot.com.conf | grep -A 20 "VirtualHost.*443"`
2. مخرجات `apache2ctl -M | grep proxy`
3. مخرجات `curl` من الاختبار

