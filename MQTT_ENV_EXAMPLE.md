# MQTT Environment Variables

أضف هذه المتغيرات إلى ملف `.env`:

```env
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

## شرح المتغيرات

- `MQTT_HOST`: عنوان MQTT broker (localhost للتطوير، أو IP/domain للإنتاج)
- `MQTT_PORT`: منفذ MQTT broker (1883 للـ TCP، 8883 لـ TLS)
- `MQTT_USERNAME`: اسم المستخدم (اختياري)
- `MQTT_PASSWORD`: كلمة المرور (اختياري)
- `MQTT_CLIENT_ID`: معرف العميل الفريد
- `MQTT_QOS`: مستوى جودة الخدمة (0, 1, أو 2)
- `MQTT_RETAIN`: الاحتفاظ بالرسائل للعملاء offline (true/false)
- `MQTT_KEEP_ALIVE`: فترة keep-alive بالثواني
- `MQTT_TIMEOUT`: timeout الاتصال بالثواني
- `MQTT_RECONNECT_DELAY`: فترة الانتظار قبل إعادة الاتصال بالثواني

