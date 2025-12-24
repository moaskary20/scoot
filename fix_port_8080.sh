#!/bin/bash

# Fix Port 8080 issue
# Run on server: ssh root@38.242.251.149 "bash -s" < fix_port_8080.sh

echo "=========================================="
echo "🔍 Checking Port 8080 Usage"
echo "=========================================="
echo ""

echo "1️⃣ What's using Port 8080:"
echo "-----------------------------------"
lsof -i :8080 || netstat -tuln | grep 8080
echo ""

echo "2️⃣ Reverb Server Processes:"
echo "-----------------------------------"
ps aux | grep reverb | grep -v grep
echo ""

echo "3️⃣ Killing old Reverb processes (if needed):"
echo "-----------------------------------"
pkill -f "reverb:start" || echo "No processes to kill"
sleep 2
echo ""

echo "4️⃣ Starting Reverb Server:"
echo "-----------------------------------"
cd /var/www/scoot
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
sleep 3
echo ""

echo "5️⃣ Verifying Reverb Server is running:"
echo "-----------------------------------"
ps aux | grep reverb | grep -v grep
echo ""

echo "6️⃣ Checking Port 8080 again:"
echo "-----------------------------------"
lsof -i :8080 || netstat -tuln | grep 8080
echo ""

echo "=========================================="
echo "✅ Done"
echo "=========================================="

