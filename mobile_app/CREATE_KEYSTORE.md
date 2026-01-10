# إنشاء Keystore للتطبيق

## ⚠️ مهم جداً

**احفظ keystore و key.properties في مكان آمن!** إذا فقدتهما، لن تتمكن من تحديث التطبيق على Google Play.

---

## خطوات إنشاء Keystore

### 1. إنشاء Keystore File

افتح Terminal وانتقل إلى مجلد المشروع:

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app/android
```

ثم قم بتشغيل الأمر التالي (استبدل المعلومات بين الأقواس بمعلوماتك):

```bash
keytool -genkey -v -keystore ~/upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

**ستُطلب منك إدخال:**
- **Keystore password**: اختر كلمة مرور قوية واحفظها
- **Re-enter password**: أعد إدخال نفس كلمة المرور
- **Your name**: اسمك (مثل: Mohamed)
- **Organizational Unit**: القسم (مثل: Development)
- **Organization**: اسم الشركة (مثل: LinerScoot)
- **City**: المدينة
- **State**: المحافظة
- **Country code**: رمز البلد (مثل: EG لمصر)

**مثال:**
```
Enter keystore password: MySecurePass123!
Re-enter new password: MySecurePass123!
What is your first and last name?
  [Unknown]:  Mohamed Ahmed
What is the name of your organizational unit?
  [Unknown]:  Development
What is the name of your organization?
  [Unknown]:  LinerScoot
What is the name of your City or Locality?
  [Unknown]:  Cairo
What is the name of your State or Province?
  [Unknown]:  Cairo
What is the two-letter country code for this unit?
  [Unknown]:  EG
Is CN=Mohamed Ahmed, OU=Development, O=LinerScoot, L=Cairo, ST=Cairo, C=EG correct?
  [no]:  yes
```

**بعد الانتهاء، سيتم إنشاء ملف `~/upload-keystore.jks` في مجلد home الخاص بك.**

---

### 2. إنشاء ملف key.properties

أنشئ ملف `key.properties` في مجلد `android/`:

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app/android
nano key.properties
```

أو استخدم محرر النصوص المفضل لديك.

**أضف المحتوى التالي (استبدل القيم بقيمك الفعلية):**

```properties
storePassword=MySecurePass123!
keyPassword=MySecurePass123!
keyAlias=upload
storeFile=/home/mohamed/upload-keystore.jks
```

**ملاحظات:**
- `storePassword`: كلمة مرور keystore التي أدخلتها في الخطوة 1
- `keyPassword`: نفس كلمة المرور (أو كلمة مرور مختلفة إذا استخدمتها)
- `keyAlias`: `upload` (نفس الاسم الذي استخدمته في الأمر)
- `storeFile`: المسار الكامل لملف keystore (استبدل `/home/mohamed/` بمسار home الخاص بك)

**للعثور على مسار home الخاص بك:**
```bash
echo $HOME
```

**مثال:**
إذا كان `$HOME` يعطي `/home/mohamed`، فاستخدم:
```properties
storeFile=/home/mohamed/upload-keystore.jks
```

---

### 3. التحقق من الملفات

تأكد من وجود الملفات:

```bash
# التحقق من keystore
ls -lh ~/upload-keystore.jks

# التحقق من key.properties
ls -lh android/key.properties
```

---

### 4. بناء App Bundle

بعد إنشاء keystore و key.properties، قم ببناء التطبيق:

```bash
cd /media/mohamed/3E16609616605147/linerscoot/mobile_app
flutter clean
flutter pub get
flutter build appbundle --release
```

**الملف سيكون في:**
```
build/app/outputs/bundle/release/app-release.aab
```

---

## 🔒 الأمان

### حماية الملفات

1. **لا ترفع keystore أو key.properties على GitHub!**
   - تم إضافتها إلى `.gitignore` تلقائياً

2. **احفظ نسخة احتياطية:**
   ```bash
   # نسخ keystore إلى مكان آمن
   cp ~/upload-keystore.jks /path/to/secure/backup/location/
   
   # نسخ key.properties (لكن احذفه من النسخة الاحتياطية بعد حفظ المعلومات في مكان آمن)
   ```

3. **احفظ المعلومات في مكان آمن:**
   - كلمة مرور keystore
   - مسار keystore
   - معلومات keyAlias

---

## ❓ حل المشاكل

### خطأ: "keystore file not found"

**الحل:** تحقق من مسار `storeFile` في `key.properties`:
```bash
# تحقق من المسار
cat android/key.properties
# تأكد من أن المسار صحيح
ls -lh /path/in/key.properties
```

### خطأ: "password incorrect"

**الحل:** تأكد من أن `storePassword` و `keyPassword` في `key.properties` صحيحة.

### خطأ: "alias not found"

**الحل:** تأكد من أن `keyAlias` في `key.properties` هو `upload` (نفس الاسم المستخدم عند إنشاء keystore).

---

## 📝 ملاحظات إضافية

- **Keystore صالح لمدة 10000 يوم** (حوالي 27 سنة)
- يمكنك استخدام نفس keystore لجميع تحديثات التطبيق
- **لا تفقد keystore!** إذا فقدته، ستحتاج إلى إنشاء تطبيق جديد على Google Play

---

## ✅ قائمة التحقق

- [ ] تم إنشاء keystore بنجاح
- [ ] تم إنشاء ملف key.properties
- [ ] تم التحقق من مسار keystore
- [ ] تم حفظ كلمات المرور في مكان آمن
- [ ] تم بناء app-release.aab بنجاح
- [ ] تم التحقق من أن التطبيق موقّع بـ release signing

---

**بعد إكمال هذه الخطوات، يمكنك رفع `app-release.aab` على Google Play!**

