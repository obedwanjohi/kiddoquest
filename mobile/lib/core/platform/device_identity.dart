import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:uuid/uuid.dart';

import 'form_factor.dart';

/// Who this device is, from the server's point of view.
///
/// The id is generated once and kept, because sync ordering, rate limiting and
/// "last played on the living-room TV" all hang off it. It is a random UUID,
/// not a hardware identifier: nothing here identifies a person.
class DeviceIdentity {
  DeviceIdentity._();

  static final DeviceIdentity instance = DeviceIdentity._();

  static const _storageKey = 'kq_device_id';

  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  String? _deviceId;
  String? _model;
  String? _appVersion;

  String get deviceId => _deviceId ?? 'unknown';

  Future<void> load() async {
    _deviceId ??= await _readOrCreateId();

    try {
      final info = await PackageInfo.fromPlatform();
      _appVersion = info.version;
    } catch (_) {
      _appVersion = '0.1.0';
    }

    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
      try {
        final android = await DeviceInfoPlugin().androidInfo;
        _model = '${android.manufacturer} ${android.model}'.trim();
      } catch (_) {
        _model = 'Android';
      }
    }

    await TelevisionDetector.detect();
  }

  Future<String> _readOrCreateId() async {
    try {
      final existing = await _storage.read(key: _storageKey);
      if (existing != null && existing.isNotEmpty) return existing;

      final created = const Uuid().v4();
      await _storage.write(key: _storageKey, value: created);
      return created;
    } catch (_) {
      // Secure storage can fail on some very old devices; a session-scoped id
      // still lets the app work, it just looks like a new device each launch.
      return _deviceId ??= const Uuid().v4();
    }
  }

  String get platform {
    if (TelevisionDetector.isTelevision) return 'android_tv';
    if (kIsWeb) return 'web';

    return switch (defaultTargetPlatform) {
      TargetPlatform.android => 'android',
      TargetPlatform.iOS => 'ios',
      _ => 'other',
    };
  }

  Future<Map<String, String>> headers() async {
    _deviceId ??= await _readOrCreateId();

    return {
      'X-Device-Id': _deviceId!,
      'X-App-Version': _appVersion ?? '0.1.0',
      'X-Platform': platform,
      'X-Device-Model': ?_model,
    };
  }

  String get appVersion => _appVersion ?? '0.1.0';
}
