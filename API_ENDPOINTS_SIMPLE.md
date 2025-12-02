# روابط API لـ ESP32 - ملخص سريع

## 🔗 Base URL
```
http://localhost:8000/api/v1/scooter
```

---

## 📋 قائمة الروابط

### 1. المصادقة (Authentication)
```
POST /api/v1/scooter/authenticate
Body: {"imei": "YOUR_IMEI"}
```

### 2. تحديث الموقع (Update Location)
```
POST /api/v1/scooter/update-location
Body: {
  "imei": "YOUR_IMEI",
  "latitude": 30.0444,
  "longitude": 31.2357,
  "battery_percentage": 85,
  "lock_status": true
}
```

### 3. الحصول على الأوامر (Get Commands)
```
POST /api/v1/scooter/get-commands
Body: {"imei": "YOUR_IMEI"}
```

### 4. تحديث حالة القفل (Update Lock Status)
```
POST /api/v1/scooter/update-lock-status
Body: {
  "imei": "YOUR_IMEI",
  "lock_status": true
}
```

### 5. تحديث البطارية (Update Battery)
```
POST /api/v1/scooter/update-battery
Body: {
  "imei": "YOUR_IMEI",
  "battery_percentage": 85
}
```

---

## ⚡ الاستخدام الموصى به

1. **عند بدء التشغيل:** استدعي `authenticate`
2. **كل 5-10 ثواني:** استدعي `update-location` أو `get-commands`
3. **بعد تنفيذ أمر:** استدعي `update-lock-status`

---

## 📖 للتفاصيل الكاملة
راجع ملف: `API_DOCUMENTATION_AR.md`

