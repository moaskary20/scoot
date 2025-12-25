# ✅ فحص إعدادات .env للإنتاج

## 🔍 التحليل

### ✅ إعدادات صحيحة:
- `APP_URL=https://linerscoot.com` ✅
- `MQTT_HOST=localhost` ✅ (إذا كان Mosquitto على نفس السيرفر)
- `MQTT_PORT=1883` ✅
- `MQTT_QOS=1` ✅
- `MQTT_RETAIN=true` ✅
- `REVERB_SERVER_HOST=0.0.0.0` ✅
- `REVERB_SERVER_PORT=8080` ✅

### ⚠️ إعدادات تحتاج تعديل:

#### 1. APP_ENV و APP_DEBUG
```env
APP_ENV=local          # ❌ يجب أن يكون production
APP_DEBUG=true         # ❌ يجب أن يكون false
```

**التعديل المطلوب:**
```env
APP_ENV=production
APP_DEBUG=false
```

#### 2. REVERB_HOST
```env
REVERB_HOST=localhost  # ❌ يجب أن يكون domain name للإنتاج
REVERB_SCHEME=http     # ❌ يجب أن يكون https للإنتاج
```

**التعديل المطلوب:**
```env
REVERB_HOST=linerscoot.com
REVERB_SCHEME=https
REVERB_PORT=443
```

**ملاحظة:** إذا كنت تستخدم reverse proxy (nginx/apache) على port 8080، يمكنك الاحتفاظ بـ `REVERB_PORT=8080` و `REVERB_SCHEME=https`.

#### 3. MQTT_HOST (إذا كان ESP32 يتصل من خارج السيرفر)
```env
MQTT_HOST=localhost    # ⚠️ إذا كان ESP32 على نفس السيرفر: صحيح
                       # ❌ إذا كان ESP32 خارج السيرفر: يجب أن يكون IP أو domain
```

**إذا كان ESP32 يتصل من خارج السيرفر:**
```env
MQTT_HOST=linerscoot.com  # أو IP السيرفر
```

---

## 📋 ملف .env الموصى به للإنتاج

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:3jrdLEHuRFyz/5X1EqxJQV3NhxFNiVJZnVl5G/NID4E=
APP_DEBUG=false
APP_URL=https://linerscoot.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scooter_app
DB_USERNAME=mohamed
DB_PASSWORD=password123

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
GOOGLE_MAPS_API_KEY=AIzaSyAYx3NYuaW2KfRt28bdfC-g37i9B-6rVgA

REVERB_APP_ID=672193
REVERB_APP_KEY=m1k6cr5egrbe0p2eycaw
REVERB_APP_SECRET=meazymdqwetpjhangtyp
REVERB_HOST=linerscoot.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# MQTT Configuration
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=laravel-scooter-app
MQTT_QOS=1
MQTT_RETAIN=true
MQTT_KEEP_ALIVE=60
MQTT_TIMEOUT=10
MQTT_RECONNECT_DELAY=5
```

---

## 🔧 التعديلات المطلوبة

### 1. تعديل APP_ENV و APP_DEBUG
```bash
# على السيرفر
nano .env
```

غيّر:
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

### 2. تعديل REVERB_HOST
```env
REVERB_HOST=linerscoot.com
REVERB_SCHEME=https
REVERB_PORT=443
```

**ملاحظة:** إذا كنت تستخدم reverse proxy على port 8080:
- يمكنك الاحتفاظ بـ `REVERB_PORT=8080`
- لكن `REVERB_SCHEME` يجب أن يكون `https`
- و `REVERB_HOST` يجب أن يكون `linerscoot.com`

### 3. MQTT_HOST
إذا كان ESP32 يتصل من خارج السيرفر:
```env
MQTT_HOST=linerscoot.com  # أو IP السيرفر
```

إذا كان ESP32 على نفس السيرفر أو نفس الشبكة:
```env
MQTT_HOST=localhost  # صحيح
```

### 4. مسح Cache بعد التعديل
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## ✅ التحقق من الإعدادات

### 1. التحقق من MQTT
```bash
# على السيرفر
mosquitto_sub -h localhost -t test/topic
```

### 2. التحقق من Reverb
```bash
# على السيرفر
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 3. التحقق من Laravel Config
```bash
php artisan tinker
```

```php
config('app.env');        // يجب أن يكون 'production'
config('app.debug');      // يجب أن يكون false
config('reverb.apps.apps.0.options.host');  // يجب أن يكون 'linerscoot.com'
config('mqtt.host');      // يجب أن يكون 'localhost' أو 'linerscoot.com'
```

---

## 🔒 أمان إضافي (اختياري)

### 1. MQTT Authentication
```env
MQTT_USERNAME=your_username
MQTT_PASSWORD=your_strong_password
```

### 2. MQTT TLS (للإنتاج)
```env
MQTT_TLS_ENABLED=true
MQTT_PORT=8883
```

---

## 📝 ملخص التعديلات المطلوبة

1. ✅ `APP_ENV=production`
2. ✅ `APP_DEBUG=false`
3. ✅ `LOG_LEVEL=error`
4. ✅ `REVERB_HOST=linerscoot.com`
5. ✅ `REVERB_SCHEME=https`
6. ✅ `REVERB_PORT=443` (أو 8080 إذا كان reverse proxy)
7. ⚠️ `MQTT_HOST` - حسب موقع ESP32

بعد التعديل:
```bash
php artisan config:clear
```

