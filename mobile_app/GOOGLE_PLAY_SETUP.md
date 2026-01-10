# إعداد التطبيق لرفعه على Google Play Store

## 📋 المتطلبات الأساسية

### 1. إنشاء Keystore للتوقيع

قبل رفع التطبيق على Google Play، يجب إنشاء **keystore** لتوقيع التطبيق:

```bash
cd mobile_app/android
keytool -genkey -v -keystore ~/upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

**ملاحظات مهمة:**
- احفظ كلمة المرور في مكان آمن (ستحتاجها في كل تحديث)
- احفظ ملف `upload-keystore.jks` في مكان آمن (لا تفقده أبداً!)
- إذا فقدت الـ keystore، لن تتمكن من تحديث التطبيق على Google Play

### 2. إنشاء ملف key.properties

أنشئ ملف `mobile_app/android/key.properties` وأضف:

```properties
storePassword=YOUR_STORE_PASSWORD
keyPassword=YOUR_KEY_PASSWORD
keyAlias=upload
storeFile=/path/to/upload-keystore.jks
```

**مثال:**
```properties
storePassword=MySecurePassword123
keyPassword=MySecurePassword123
keyAlias=upload
storeFile=/home/username/upload-keystore.jks
```

### 3. إضافة key.properties إلى .gitignore

تأكد من أن ملف `key.properties` موجود في `.gitignore`:

```bash
echo "android/key.properties" >> mobile_app/.gitignore
echo "android/*.jks" >> mobile_app/.gitignore
echo "android/*.keystore" >> mobile_app/.gitignore
```

## 🔧 بناء التطبيق للـ Release

### بناء APK (للتجربة)

```bash
cd mobile_app
flutter build apk --release
```

الملف سيكون في: `build/app/outputs/flutter-apk/app-release.apk`

### بناء App Bundle (للرفع على Google Play)

```bash
cd mobile_app
flutter build appbundle --release
```

الملف سيكون في: `build/app/outputs/bundle/release/app-release.aab`

**ملاحظة:** Google Play يتطلب **App Bundle (.aab)** وليس APK.

## ✅ التحقق من الإعدادات

### 1. التحقق من versionCode و versionName

في `pubspec.yaml`:
```yaml
version: 1.0.0+1
```
- `1.0.0` = versionName (يظهر للمستخدم)
- `+1` = versionCode (يجب أن يزيد مع كل تحديث)

### 2. التحقق من targetSdkVersion

يجب أن يكون `targetSdkVersion` 33 أو أعلى (مطلوب من Google Play).

### 3. التحقق من Permissions

تأكد من أن جميع الـ permissions المطلوبة موجودة في `AndroidManifest.xml`:
- ✅ Location permissions
- ✅ Camera permissions
- ✅ Internet permission
- ✅ Storage permissions (للصور)
- ✅ Foreground service permissions (للموقع)

## 📤 رفع التطبيق على Google Play

### 1. إنشاء حساب Google Play Developer

- اذهب إلى [Google Play Console](https://play.google.com/console)
- ادفع رسوم التسجيل ($25 - مرة واحدة)

### 2. إنشاء التطبيق

1. اضغط على "Create app"
2. أدخل:
   - **App name**: Liner Scoot
   - **Default language**: Arabic (العربية)
   - **App or game**: App
   - **Free or paid**: Free

### 3. رفع App Bundle

1. اذهب إلى **Production** → **Create new release**
2. ارفع ملف `app-release.aab`
3. املأ **Release notes** (ملاحظات الإصدار)
4. اضغط **Save** ثم **Review release**

### 4. إكمال معلومات التطبيق

#### Content rating (تقييم المحتوى)
- املأ استبيان التقييم
- احصل على التقييم (عادة PEGI 3 أو Everyone)

#### Store listing (قائمة المتجر)
- **App name**: Liner Scoot
- **Short description**: تطبيق لتأجير السكوترات الذكية
- **Full description**: وصف كامل للتطبيق
- **App icon**: 512x512 pixels
- **Feature graphic**: 1024x500 pixels
- **Screenshots**: على الأقل 2 صورة (مطلوب)
  - Phone: 16:9 أو 9:16
  - Tablet (اختياري): 16:9 أو 9:16

#### Privacy Policy (سياسة الخصوصية)
- **مطلوب!** يجب أن يكون لديك رابط لسياسة الخصوصية
- يمكنك استخدام [Privacy Policy Generator](https://www.privacypolicygenerator.info/)

### 5. إعدادات الأمان

#### Data safety (أمان البيانات)
- حدد البيانات التي يجمعها التطبيق:
  - ✅ Location (موقع)
  - ✅ Photos and videos (الصور)
  - ✅ Personal info (المعلومات الشخصية)

#### App access (وصول التطبيق)
- حدد إذا كان التطبيق محدود العمر أو متاح للجميع

## 🚨 المشاكل الشائعة وحلولها

### 1. "You uploaded an APK or Android App Bundle that is signed with the debug certificate"

**الحل:** تأكد من أنك أنشأت `key.properties` وأن الـ keystore موجود.

### 2. "Your app targets API level 29. You need to target at least API level 33"

**الحل:** تأكد من أن `targetSdkVersion` في `build.gradle.kts` هو 33 أو أعلى.

### 3. "Missing privacy policy"

**الحل:** أضف رابط لسياسة الخصوصية في Store listing.

### 4. "App bundle contains native code, but you haven't uploaded debug symbols"

**الحل:** أضف `--split-debug-info` عند البناء:
```bash
flutter build appbundle --release --split-debug-info=build/app/outputs/symbols
```

### 5. "Version code 1 has already been used"

**الحل:** زد `versionCode` في `pubspec.yaml`:
```yaml
version: 1.0.1+2  # زد الرقم بعد +
```

## 📝 قائمة التحقق قبل الرفع

- [ ] تم إنشاء keystore و key.properties
- [ ] تم إضافة key.properties إلى .gitignore
- [ ] versionCode و versionName محدثين
- [ ] targetSdkVersion >= 33
- [ ] تم بناء App Bundle بنجاح
- [ ] تم اختبار التطبيق على أجهزة حقيقية
- [ ] تم إعداد Store listing (اسم، وصف، صور)
- [ ] تم إضافة Privacy Policy
- [ ] تم إكمال Content rating
- [ ] تم إكمال Data safety form

## 🔗 روابط مفيدة

- [Flutter - Deploying to Google Play](https://docs.flutter.dev/deployment/android)
- [Google Play Console](https://play.google.com/console)
- [Android App Bundle](https://developer.android.com/guide/app-bundle)
- [Privacy Policy Generator](https://www.privacypolicygenerator.info/)

## 📞 الدعم

إذا واجهت مشاكل، راجع:
- [Flutter Documentation](https://docs.flutter.dev/)
- [Google Play Console Help](https://support.google.com/googleplay/android-developer)

---

**ملاحظة:** احتفظ بنسخة احتياطية من `upload-keystore.jks` في مكان آمن!

