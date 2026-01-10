import java.util.Properties
import java.io.FileInputStream

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "com.linerscoot.mobile_app"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    defaultConfig {
        // TODO: Specify your own unique Application ID (https://developer.android.com/studio/build/application-id.html).
        applicationId = "com.linerscoot.mobile_app"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
        
        // Support for 16 KB page size devices (required for Google Play)
        // Flutter and modern Android Gradle Plugin (8.1+) automatically handle
        // 16 KB page size compatibility. No additional configuration needed.
    }

    signingConfigs {
        create("release") {
            // Release signing will be configured via key.properties file
            // See: https://docs.flutter.dev/deployment/android#signing-the-app
            // For now, you can use:
            // 1. Generate keystore: keytool -genkey -v -keystore ~/upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
            // 2. Create key.properties file in android/ folder with:
            //    storePassword=YOUR_STORE_PASSWORD
            //    keyPassword=YOUR_KEY_PASSWORD
            //    keyAlias=upload
            //    storeFile=YOUR_KEYSTORE_PATH
            val keystorePropertiesFile = rootProject.file("key.properties")
            if (keystorePropertiesFile.exists()) {
                val keystoreProperties = Properties()
                keystoreProperties.load(FileInputStream(keystorePropertiesFile))
                keyAlias = keystoreProperties["keyAlias"]?.toString() ?: ""
                keyPassword = keystoreProperties["keyPassword"]?.toString() ?: ""
                val storeFileStr = keystoreProperties["storeFile"]?.toString() ?: ""
                if (storeFileStr.isNotEmpty()) {
                    storeFile = file(storeFileStr)
                }
                storePassword = keystoreProperties["storePassword"]?.toString() ?: ""
            }
        }
    }

    buildTypes {
        release {
            // Enable code shrinking, obfuscation, and optimization
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
            // REQUIRED: Use release signing config for Google Play
            // If keystore is not found, the build will fail (this is intentional)
            val releaseSigning = signingConfigs.findByName("release")
            if (releaseSigning?.storeFile?.exists() == true) {
                signingConfig = releaseSigning
            } else {
                throw GradleException(
                    """
                    ============================================
                    RELEASE SIGNING CONFIGURATION MISSING!
                    ============================================
                    
                    To build a release APK/AAB for Google Play, you need to:
                    
                    1. Create a keystore file:
                       keytool -genkey -v -keystore ~/upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
                    
                    2. Create android/key.properties file with:
                       storePassword=YOUR_STORE_PASSWORD
                       keyPassword=YOUR_KEY_PASSWORD
                       keyAlias=upload
                       storeFile=/path/to/upload-keystore.jks
                    
                    See GOOGLE_PLAY_SETUP.md for detailed instructions.
                    ============================================
                    """.trimIndent()
                )
            }
        }
    }
}

flutter {
    source = "../.."
}

// Note: Google Play Core libraries are not needed for this app
// They are only required for Flutter deferred components, which we don't use.
// Removing them to avoid compatibility issues with SDK 34.
