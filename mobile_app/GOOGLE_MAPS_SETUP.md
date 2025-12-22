# إعداد Google Maps API

## الخطوات المطلوبة:

1. **الحصول على Google Maps API Key:**
   - اذهب إلى [Google Cloud Console](https://console.cloud.google.com/)
   - أنشئ مشروع جديد أو اختر مشروع موجود
   - اذهب إلى "APIs & Services" > "Credentials"
   - اضغط "Create Credentials" > "API Key"
   - انسخ الـ API Key

2. **تفعيل APIs المطلوبة:**
   - Maps SDK for Android
   - Maps SDK for iOS (إذا كنت تطور لـ iOS)

3. **إضافة API Key إلى التطبيق:**

   **لـ Android:**
   - افتح `android/app/src/main/AndroidManifest.xml`
   - ابحث عن `YOUR_GOOGLE_MAPS_API_KEY`
   - استبدله بـ API Key الخاص بك

   **لـ iOS:**
   - افتح `ios/Runner/AppDelegate.swift`
   - أضف: `GMSServices.provideAPIKey("YOUR_API_KEY_HERE")`

4. **تقييد API Key (اختياري لكن موصى به):**
   - في Google Cloud Console
   - اختر API Key
   - اضغط "Restrict key"
   - قيد الاستخدام بـ "Android apps" أو "iOS apps"
   - أضف package name و SHA-1 fingerprint

## ملاحظات:
- لا ترفع API Key إلى GitHub مباشرة
- استخدم environment variables أو secure storage
- في الإنتاج، استخدم API Key مقيد

