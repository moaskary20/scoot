# ☁️ إعداد Reverb مع Cloudflare و Let's Encrypt

## ⚠️ المشكلة

عند استخدام Cloudflare مع Let's Encrypt:
- Cloudflare قد يحجب المنفذ 8080
- WebSocket يحتاج إعداد خاص في Cloudflare
- SSL يجب أن يكون صحيح

---

## ✅ الحلول

### الحل 1: استخدام Cloudflare WebSocket Proxy (مستحسن)

Cloudflare يدعم WebSocket عبر نفس المنفذ 443.

#### الخطوة 1: إعداد Nginx Reverse Proxy

```nginx
# في ملف site config (مثل /etc/nginx/sites-available/linerscoot.com)
server {
    listen 443 ssl http2;
    server_name linerscoot.com;

    ssl_certificate /etc/letsencrypt/live/linerscoot.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/linerscoot.com/privkey.pem;

    # WebSocket Proxy
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }

    # Laravel Application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        # ... باقي إعدادات Laravel
    }
}
```

#### الخطوة 2: تحديث .env

```env
REVERB_HOST=linerscoot.com
REVERB_PORT=443
REVERB_SCHEME=https
```

#### الخطوة 3: تحديث Reverb للاستماع على localhost فقط

```bash
# إيقاف Reverb
pkill -f "reverb:start"

# تشغيل Reverb على localhost فقط (Nginx سيتولى SSL)
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
```

#### الخطوة 4: إعادة تحميل Nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

#### الخطوة 5: تفعيل WebSocket في Cloudflare

1. اذهب إلى Cloudflare Dashboard
2. اختر الدومين `linerscoot.com`
3. اذهب إلى **Network** → **WebSockets**
4. فعّل **WebSockets**

---

### الحل 2: استخدام منفذ مخصص عبر Cloudflare (بدون SSL على Reverb)

#### الخطوة 1: إعداد Reverb

```bash
# Reverb يعمل على HTTP محلياً
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
```

#### الخطوة 2: إعداد Nginx

```nginx
# WebSocket على منفذ 8080 عبر SSL
stream {
    upstream reverb_backend {
        server 127.0.0.1:8080;
    }

    server {
        listen 8080 ssl;
        proxy_pass reverb_backend;
        ssl_certificate /etc/letsencrypt/live/linerscoot.com/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/linerscoot.com/privkey.pem;
        
        proxy_ssl off;
        proxy_protocol off;
    }
}
```

#### الخطوة 3: تحديث .env

```env
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

---

### الحل 3: استخدام HTTP فقط (للتطوير)

إذا كان Cloudflare يحجب المنفذ 8080، يمكن استخدام HTTP فقط:

```env
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=http
```

**WebSocket URL:**
```
ws://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

**ملاحظة:** هذا غير آمن للإنتاج.

---

## 🔧 الخطوات الموصى بها (الحل 1)

### 1. إعداد Nginx

```bash
sudo nano /etc/nginx/sites-available/linerscoot.com
```

أضف:
```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

### 2. تحديث .env

```env
REVERB_HOST=linerscoot.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### 3. إعادة تشغيل Reverb

```bash
cd /var/www/scoot
pkill -f "reverb:start"
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
```

### 4. إعادة تحميل Nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 5. تفعيل WebSocket في Cloudflare

- Dashboard → Network → WebSockets → ON

---

## ✅ بعد الإعداد

**WebSocket URL:**
```
wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw
```

**ملاحظة:** بدون المنفذ 8080 لأن Nginx يتولى ذلك.

---

## 🔍 التحقق

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

## 📝 ملاحظات مهمة

1. **Cloudflare يجب أن يكون في وضع Proxy (Orange Cloud)**
2. **WebSocket يجب أن يكون مفعّل في Cloudflare**
3. **Nginx يتولى SSL** - Reverb يعمل على HTTP محلياً
4. **المنفذ 443** يستخدم عبر Nginx

---

## 🆘 استكشاف الأخطاء

### إذا لم يعمل:

1. **تحقق من Cloudflare WebSocket:**
   - Dashboard → Network → WebSockets → يجب أن يكون ON

2. **تحقق من Nginx:**
   ```bash
   sudo nginx -t
   sudo tail -50 /var/log/nginx/error.log
   ```

3. **تحقق من Reverb:**
   ```bash
   tail -50 reverb.log
   ps aux | grep reverb
   ```

4. **تحقق من SSL:**
   ```bash
   sudo certbot certificates
   ```

