#!/bin/bash

# Script لإعداد Reverb مع Apache و Cloudflare
# استخدم: bash setup_reverb.sh

set -e

echo "=========================================="
echo "  إعداد Reverb مع Apache و Cloudflare"
echo "=========================================="

# الانتقال لمجلد المشروع
cd /var/www/scoot

echo ""
echo "1. تفعيل Apache Modules..."
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo a2enmod ssl

echo ""
echo "2. التحقق من .env..."
if grep -q "REVERB_APP_KEY" .env; then
    echo "✅ .env يحتوي على إعدادات Reverb"
else
    echo "❌ .env لا يحتوي على إعدادات Reverb"
    exit 1
fi

echo ""
echo "3. تحديث .env..."
# تحديث REVERB_PORT و REVERB_SCHEME
sed -i 's/REVERB_PORT=.*/REVERB_PORT=443/' .env
sed -i 's/REVERB_SCHEME=.*/REVERB_SCHEME=https/' .env
sed -i 's/REVERB_HOST=.*/REVERB_HOST=linerscoot.com/' .env

echo "✅ تم تحديث .env"

echo ""
echo "4. مسح Cache..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache

echo ""
echo "5. إيقاف Reverb القديم..."
pkill -f "reverb:start" || true
sleep 2

echo ""
echo "6. تشغيل Reverb على localhost..."
nohup php artisan reverb:start --host=127.0.0.1 --port=8080 > reverb.log 2>&1 &
sleep 2

echo ""
echo "7. التحقق من Reverb..."
if ps aux | grep -q "[r]everb:start"; then
    echo "✅ Reverb يعمل"
    ps aux | grep "[r]everb:start"
else
    echo "❌ Reverb لم يبدأ"
    tail -50 reverb.log
    exit 1
fi

echo ""
echo "8. إعداد Apache Virtual Host..."
APACHE_CONF="/etc/apache2/sites-available/linerscoot.com.conf"

if [ -f "$APACHE_CONF" ]; then
    echo "✅ ملف Apache موجود"
    
    # التحقق من وجود WebSocket Proxy
    if grep -q "proxy_wstunnel" "$APACHE_CONF"; then
        echo "✅ WebSocket Proxy موجود في Apache"
    else
        echo "⚠️  يجب إضافة WebSocket Proxy يدوياً"
        echo ""
        echo "أضف هذا في <VirtualHost *:443>:"
        echo ""
        echo "RewriteEngine on"
        echo "RewriteCond %{HTTP:Upgrade} websocket [NC]"
        echo "RewriteCond %{HTTP:Connection} upgrade [NC]"
        echo "RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/\$1 [P,L]"
        echo ""
        echo "ProxyPass /app/ http://127.0.0.1:8080/app/"
        echo "ProxyPassReverse /app/ http://127.0.0.1:8080/app/"
    fi
else
    echo "❌ ملف Apache غير موجود: $APACHE_CONF"
    echo "⚠️  يجب إنشاء Virtual Host يدوياً"
fi

echo ""
echo "9. التحقق من Apache Config..."
sudo apache2ctl configtest

echo ""
echo "10. إعادة تشغيل Apache..."
sudo systemctl restart apache2

echo ""
echo "=========================================="
echo "  ✅ تم الإعداد بنجاح!"
echo "=========================================="
echo ""
echo "التحقق من الحالة:"
echo "  - Reverb: ps aux | grep reverb"
echo "  - Apache: sudo systemctl status apache2"
echo "  - Logs: tail -50 reverb.log"
echo ""
echo "WebSocket URL:"
echo "  wss://linerscoot.com/app/m1k6cr5egrbe0p2eycaw"
echo ""
echo "⚠️  لا تنسى تفعيل WebSocket في Cloudflare:"
echo "  Dashboard → Network → WebSockets → ON"
echo ""

