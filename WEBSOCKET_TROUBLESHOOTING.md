# 🔧 استكشاف أخطاء WebSocket

## ❌ المشكلة: "Could not connect to wss://linerscoot.com:8080"

### الأسباب المحتملة:

1. **Reverb Server غير مشغل**
2. **المنفذ 8080 غير مفتوح في Firewall**
3. **مشكلة في SSL/HTTPS**
4. **إعدادات `.env` غير صحيحة**

---

## ✅ الحلول

### 1. تشغيل Reverb Server

**على السيرفر، قم بتشغيل:**

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

**أو للتشغيل في الخلفية (Production):**

```bash
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > reverb.log 2>&1 &
```

**أو استخدام Supervisor:**

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/reverb.log
stopwaitsecs=3600
```

---

### 2. فتح المنفذ 8080 في Firewall

**Ubuntu/Debian:**
```bash
sudo ufw allow 8080/tcp
sudo ufw reload
```

**CentOS/RHEL:**
```bash
sudo firewall-cmd --permanent --add-port=8080/tcp
sudo firewall-cmd --reload
```

**أو في Cloud Provider (AWS, DigitalOcean, etc.):**
- افتح Security Group / Firewall Rules
- أضف Inbound Rule للمنفذ 8080

---

### 3. إعداد `.env` للإنتاج

تأكد من أن ملف `.env` يحتوي على:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=318253
REVERB_APP_KEY=m1k6cr5egrbe0p2eycaw
REVERB_APP_SECRET=meazymdqwetpjhangtyp
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

**ملاحظة:** 
- `REVERB_HOST` يجب أن يكون الدومين (linerscoot.com) وليس localhost
- `REVERB_SCHEME` يجب أن يكون `https` للإنتاج

---

### 4. إعداد SSL/HTTPS

إذا كنت تستخدم SSL، تأكد من:

1. **شهادة SSL صحيحة** على السيرفر
2. **Nginx/Apache** موجه بشكل صحيح للمنفذ 8080

**مثال إعداد Nginx (Reverse Proxy):**

```nginx
server {
    listen 443 ssl;
    server_name linerscoot.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

**أو استخدام المنفذ مباشرة:**

```nginx
# في ملف nginx.conf أو site config
stream {
    upstream reverb_backend {
        server 127.0.0.1:8080;
    }

    server {
        listen 8080 ssl;
        proxy_pass reverb_backend;
        ssl_certificate /path/to/cert.pem;
        ssl_certificate_key /path/to/key.pem;
    }
}
```

---

### 5. اختبار الاتصال

**من السيرفر نفسه:**
```bash
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" \
  -H "Sec-WebSocket-Key: test" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

**من خارج السيرفر:**
```bash
# اختبار HTTP أولاً
curl https://linerscoot.com/api/v1/scooter/message

# اختبار WebSocket
wscat -c wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

---

### 6. استخدام HTTP فقط (Fallback)

إذا كان WebSocket لا يعمل، يمكن استخدام HTTP فقط:

**URL:**
```
POST https://linerscoot.com/api/v1/scooter/message
```

**الكود:**
```cpp
// استخدم HTTP فقط بدون WebSocket
const char* httpServer = "https://linerscoot.com";

void sendHttpMessage(String event, JsonObject data) {
    HTTPClient http;
    http.begin(httpServer + "/api/v1/scooter/message");
    http.addHeader("Content-Type", "application/json");
    
    // ... باقي الكود
}
```

**ملاحظة:** الأوامر ستكون في الرد من HTTP بدلاً من WebSocket.

---

## 🔍 التحقق من الحالة

### 1. التحقق من Reverb Server:

```bash
# على السيرفر
ps aux | grep reverb
netstat -tulpn | grep 8080
```

### 2. التحقق من Logs:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Reverb logs (إذا كنت تستخدم Supervisor)
tail -f reverb.log
```

### 3. اختبار الاتصال:

```bash
# من السيرفر
telnet localhost 8080

# من خارج السيرفر
telnet linerscoot.com 8080
```

---

## 📝 ملاحظات مهمة

1. **Reverb Server يجب أن يعمل دائماً** - استخدم Supervisor أو systemd
2. **المنفذ 8080 يجب أن يكون مفتوحاً** في Firewall
3. **SSL مهم للإنتاج** - استخدم `wss://` و `https://`
4. **REVERB_HOST يجب أن يكون الدومين** وليس localhost

---

## 🆘 إذا لم يعمل بعد

1. تحقق من أن Laravel يعمل: `php artisan serve`
2. تحقق من أن Reverb يعمل: `php artisan reverb:start`
3. تحقق من Firewall والمنافذ
4. راجع logs للأخطاء
5. جرب HTTP Fallback كحل مؤقت

