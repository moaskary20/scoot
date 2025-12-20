# 🚀 إعداد Reverb للإنتاج

## الخطوات المطلوبة

### 1. تحديث `.env`

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=318253
REVERB_APP_KEY=m1k6cr5egrbe0p2eycaw
REVERB_APP_SECRET=meazymdqwetpjhangtyp
REVERB_HOST=linerscoot.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

---

### 2. تشغيل Reverb كخدمة (Supervisor)

**إنشاء ملف:** `/etc/supervisor/conf.d/reverb.conf`

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/scoot/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/scoot
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/scoot/storage/logs/reverb.log
stopwaitsecs=3600
```

**تشغيل Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
```

---

### 3. فتح المنفذ 8080

```bash
sudo ufw allow 8080/tcp
sudo ufw reload
```

---

### 4. إعداد Nginx (اختياري - للـ SSL)

```nginx
# في ملف site config
stream {
    upstream reverb_backend {
        server 127.0.0.1:8080;
    }

    server {
        listen 8080 ssl;
        proxy_pass reverb_backend;
        ssl_certificate /etc/ssl/certs/linerscoot.com.crt;
        ssl_certificate_key /etc/ssl/private/linerscoot.com.key;
    }
}
```

---

### 5. التحقق

```bash
# التحقق من Reverb
sudo supervisorctl status reverb:*

# التحقق من المنفذ
sudo netstat -tulpn | grep 8080

# اختبار الاتصال
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  http://localhost:8080/app/m1k6cr5egrbe0p2eycaw
```

---

## ✅ بعد الإعداد

**WebSocket URL:**
```
wss://linerscoot.com:8080/app/m1k6cr5egrbe0p2eycaw
```

**HTTP URL:**
```
POST https://linerscoot.com/api/v1/scooter/message
```

