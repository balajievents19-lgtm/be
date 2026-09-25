# Android release signing (Balaji Royal Events)

Never commit `android/key.properties`, `*.jks`, or `*.keystore`.

## Play Store upload key

Google Play requires a **release/upload keystore**. This repo does **not** contain a production keystore. Do not treat a locally generated debug key as the Play upload key.

1. Create the upload keystore on the owner’s machine (keep a secure backup):

```powershell
keytool -genkey -v -keystore C:\secure\balaji-royal-events-upload.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

2. Copy `android/key.properties.example` to `android/key.properties` and fill in real values:

```
storePassword=...
keyPassword=...
keyAlias=upload
storeFile=C:\\secure\\balaji-royal-events-upload.jks
```

`storeFile` may be an absolute path. Do not put the path in Dart source.

3. Print the SHA-256 fingerprint for App Links / Play Console:

```powershell
keytool -list -v -keystore C:\secure\balaji-royal-events-upload.jks -alias upload
```

Copy the `SHA256:` value into `store/well-known/assetlinks.json`.

4. Build:

```powershell
cd C:\laragon\www\be\mobile
flutter build appbundle --release
```

Artifact: `build/app/outputs/bundle/release/app-release.aab`

If `android/key.properties` is missing, Gradle signs the **release** build with the **debug** key. That is for local testing only and must not be uploaded to Play production.

## After SDK is installed

```powershell
flutter doctor -v
flutter config --android-sdk %LOCALAPPDATA%\Android\Sdk
flutter build apk --release
flutter build appbundle --release
```
