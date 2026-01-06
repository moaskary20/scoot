#!/bin/bash

# Check for errors in logs
# Run on server: ssh root@38.242.251.149 "bash -s" < check_error.sh

cd /var/www/scoot

echo "=========================================="
echo "🔍 Checking for Errors in Logs"
echo "=========================================="
echo ""

echo "1️⃣ Last 50 lines with ERROR or Exception:"
echo "-----------------------------------"
tail -200 storage/logs/laravel.log | grep -i "error\|exception" | tail -20
echo ""

echo "2️⃣ Last 100 lines of log file:"
echo "-----------------------------------"
tail -100 storage/logs/laravel.log
echo ""

echo "3️⃣ Checking for broadcast/command related errors:"
echo "-----------------------------------"
tail -200 storage/logs/laravel.log | grep -i "broadcast\|command\|websocket\|reverb" | tail -20
echo ""






