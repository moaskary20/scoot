# 🔧 حل مشكلة Port 8080

## المشكلة:
```
Failed to listen on "tcp://0.0.0.0:8080": Address already in use (EADDRINUSE)
```

## الحل:

### على السيرفر، شغّل:

```bash
cd /var/www/scoot

# 1. تحقق من ما يستخدم Port 8080
lsof -i :8080
# أو
netstat -tuln | grep 8080

# 2. أوقف جميع عمليات Reverb القديمة
pkill -f "reverb:start"

# 3. انتظر ثانيتين
sleep 2

# 4. شغّل Reverb Server مرة أخرى
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &

# 5. تحقق أنه يعمل
ps aux | grep reverb | grep -v grep
```

### أو استخدم السكريبت:

```bash
chmod +x fix_port_8080.sh
./fix_port_8080.sh
```

## ملاحظة:

إذا استمرت المشكلة، قد يكون هناك تطبيق آخر يستخدم Port 8080. في هذه الحالة:

1. **استخدم Port آخر:**
   ```bash
   php artisan reverb:start --host=0.0.0.0 --port=8081
   ```
   ثم غيّر `REVERB_PORT=8081` في `.env`

2. **أو أوقف التطبيق الآخر:**
   ```bash
   # ابحث عن العملية
   lsof -i :8080
   
   # أوقفها
   kill -9 <PID>
   ```





