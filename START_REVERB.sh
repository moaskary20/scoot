#!/bin/bash

# Script to start Laravel Reverb Server
# Usage: ./START_REVERB.sh

cd /var/www/scoot || cd "$(dirname "$0")"

echo "🚀 Starting Laravel Reverb Server..."
echo "📍 Host: 0.0.0.0"
echo "📍 Port: 8080"
echo ""
echo "Press Ctrl+C to stop"
echo ""

php artisan reverb:start --host=0.0.0.0 --port=8080

