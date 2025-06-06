// File generated by FlutterFire CLI.
// ignore_for_file: type=lint
import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

/// Default [FirebaseOptions] for use with your Firebase apps.
///
/// Example:
/// ```dart
/// import 'firebase_options.dart';
/// // ...
/// await Firebase.initializeApp(
///   options: DefaultFirebaseOptions.currentPlatform,
/// );
/// ```
class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for ios - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      case TargetPlatform.macOS:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for macos - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      case TargetPlatform.windows:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for windows - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      case TargetPlatform.linux:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for linux - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions are not supported for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyAdxTLDnNq_nlrUB7gzCqlthKuIztzBhXg',
    appId: '1:102104939012:web:787f91cd639c0df3fe9351',
    messagingSenderId: '102104939012',
    projectId: 'fitdine',
    authDomain: 'fitdine.firebaseapp.com',
    storageBucket: 'fitdine.firebasestorage.app',
    measurementId: 'G-X3W67FND49',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyDGRaRg4U3UNoMpQulvUFvgm7ZirSQ8uEs',
    appId: '1:102104939012:android:90f295b871c16110fe9351',
    messagingSenderId: '102104939012',
    projectId: 'fitdine',
    storageBucket: 'fitdine.firebasestorage.app',
  );
}
