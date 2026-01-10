# إعداد Release Signing - دليل سريع

## 🚀 طريقة سريعة (مُوصى بها)

### تشغيل السكريبت التلقائي:

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app/android
./create_keystore.sh
```

السكريبت سيقوم بـ:
1. إنشاء keystore تلقائياً
2. إنشاء ملف key.properties
3. إعداد كل شيء بشكل صحيح

---

## 📝 طريقة يدوية

إذا كنت تفضل القيام بذلك يدوياً:

### 1. إنشاء Keystore:

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app/android
keytool -genkey -v -keystore ~/upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

**أدخل المعلومات المطلوبة:**
- كلمة مرور keystore (احفظها!)
- اسمك
- اسم الشركة/المنظمة
- المدينة، المحافظة، رمز البلد

### 2. إنشاء ملف key.properties:

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app/android
nano key.properties
```

**أضف المحتوى التالي (استبدل كلمة المرور بكلمة المرور التي أدخلتها):**

```properties
storePassword=YOUR_PASSWORD_HERE
keyPassword=YOUR_PASSWORD_HERE
keyAlias=upload
storeFile=/home/mohamed/upload-keystore.jks
```

**للعثور على مسار home:**
```bash
echo $HOME
```

---

## ✅ التحقق من الإعداد

بعد إنشاء الملفات، تحقق:

```bash
# التحقق من keystore
ls -lh ~/upload-keystore.jks

# التحقق من key.properties
ls -lh android/key.properties
```

---

## 🔨 بناء التطبيق

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app
flutter clean
flutter build appbundle --release
```

الملف سيكون في: `build/app/outputs/bundle/release/app-release.aab`

---

## 🔒 الأمان

- ✅ تم إضافة `key.properties` و `*.jks` إلى `.gitignore`
- ✅ لا ترفع هذه الملفات على GitHub
- ✅ احفظ نسخة احتياطية من keystore في مكان آمن
- ✅ احفظ كلمة المرور في مكان آمن

---

## ❓ حل المشاكل

### خطأ: "keystore file not found"
- تحقق من مسار `storeFile` في `key.properties`
- استخدم المسار الكامل (مثل: `/home/mohamed/upload-keystore.jks`)

### خطأ: "password incorrect"
- تأكد من أن `storePassword` و `keyPassword` في `key.properties` صحيحة

### خطأ: "alias not found"
- تأكد من أن `keyAlias=upload` في `key.properties`

---

**بعد إكمال هذه الخطوات، يمكنك رفع التطبيق على Google Play!**

