# Android Studio / SDK (Windows)

Android Studio is **not** installed on this machine. Flutter cannot build APK or AAB until the official SDK exists.

Do **not** download SDK zip files from unofficial sites.

## Safest install

1. Open https://developer.android.com/studio
2. Download **Android Studio** from Google
3. Install with the default SDK location: `%LOCALAPPDATA%\Android\Sdk`
4. First launch: install Android SDK Platform, Platform-Tools, and a recent system image if you want an emulator
5. Accept licenses:

```powershell
flutter doctor --android-licenses
```

6. Point Flutter at the SDK (only if doctor still cannot find it):

```powershell
flutter config --android-sdk "$env:LOCALAPPDATA\Android\Sdk"
```

7. Confirm:

```powershell
flutter doctor -v
flutter devices
```

Then from `C:\laragon\www\be\mobile`:

```powershell
dart run flutter_launcher_icons
flutter build apk --release
flutter build appbundle --release
```
