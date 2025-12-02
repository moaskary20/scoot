#!/bin/bash
# سكريبت لإصلاح migrations على السيرفر

echo "Rolling back trips migration..."
php artisan migrate:rollback --step=1

echo "Running migrations again..."
php artisan migrate

echo "Done!"
