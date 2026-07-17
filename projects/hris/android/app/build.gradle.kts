import java.util.Properties

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

val appId: String = (project.findProperty("APP_ID") as String?) ?: "com.example.absensi"

fun loadMapsApiKeyAndroid(projectRootDir: File): String {
    // Prefer an untracked file: `android/maps.properties`
    val propertiesFile = File(projectRootDir, "maps.properties")
    if (propertiesFile.exists()) {
        val props = Properties()
        props.load(propertiesFile.inputStream())
        val fromFile = props.getProperty("MAPS_API_KEY_ANDROID")?.trim()
        if (!fromFile.isNullOrEmpty()) return fromFile
    }

    // Fallback to environment variable (useful for CI)
    val fromEnv = System.getenv("MAPS_API_KEY_ANDROID")?.trim()
    if (!fromEnv.isNullOrEmpty()) return fromEnv

    return ""
}

android {
    namespace = appId
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
        isCoreLibraryDesugaringEnabled = true
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_11.toString()
    }

    defaultConfig {
        // TODO: Specify your own unique Application ID (https://developer.android.com/studio/build/application-id.html).
        applicationId = appId
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName

        manifestPlaceholders["MAPS_API_KEY_ANDROID"] = loadMapsApiKeyAndroid(project.rootDir)
    }

    buildTypes {
        release {
            // TODO: Add your own signing config for the release build.
            // Signing with the debug keys for now, so `flutter run --release` works.
            signingConfig = signingConfigs.getByName("debug")
        }
    }
}

flutter {
    source = "../.."
}

dependencies {
    coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.1.4")
}
