#!/bin/bash

# Script to start Reverb and test
cd /var/www/scoot

echo "=========================================="
echo "🚀 Starting Reverb Server"
echo "=========================================="

# أوقف Reverb Server القديم
pkill -f "reverb:start"
sleep 2

# مسح cache
php artisan config:clear
php artisan cache:clear

# شغّل Reverb Server في الخلفية
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &

# انتظر 3 ثواني
sleep 3

# تحقق أنه يعمل
if ps aux | grep -v grep | grep "reverb:start" > /dev/null; then
    echo "✅ Reverb Server is running"
else
    echo "❌ Reverb Server failed to start"
    exit 1
fi

echo ""
echo "=========================================="
echo "🧪 Testing HTTP API"
echo "=========================================="

# اختبار HTTP API
curl -X POST http://localhost:8080/apps/672193/events \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer xdpdwxtm0rcnowrrxafq" \
  -d '{
    "channels": ["scooter.ESP32_IMEI_001"],
    "name": "command",
    "data": {
      "commands": {"lock": true, "unlock": false}
    }
  }'

echo ""
echo ""
echo "=========================================="
echo "📋 Check Reverb logs:"
echo "tail -f storage/logs/reverb.log"
echo "=========================================="








