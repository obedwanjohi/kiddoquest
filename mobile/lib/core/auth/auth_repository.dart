import 'dart:convert';

import 'package:crypto/crypto.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/child.dart';
import '../network/api_client.dart';
import '../platform/device_identity.dart';

/// Who is signed in on this device.
///
/// The token lives in secure storage, never in the database, and it is the only
/// credential the app keeps. Children never have accounts: a parent signs in
/// once and then chooses who is playing.
class AuthRepository {
  AuthRepository({required ApiClient api, FlutterSecureStorage? storage})
      : _api = api,
        _storage = storage ?? const FlutterSecureStorage();

  static const _tokenKey = 'kq_access_token';
  static const _expiryKey = 'kq_token_expires';
  static const _guardianKey = 'kq_guardian';
  static const _pinHashKey = 'kq_pin_hash';

  final ApiClient _api;
  final FlutterSecureStorage _storage;

  String? _cachedToken;
  Guardian? _guardian;

  Guardian? get guardian => _guardian;

  bool get isSignedIn => _cachedToken != null && _cachedToken!.isNotEmpty;

  Future<String?> token() async {
    if (_cachedToken != null) return _cachedToken;

    try {
      return _cachedToken = await _storage.read(key: _tokenKey);
    } catch (_) {
      return null;
    }
  }

  /// Read what we already know at startup so the app can go straight to the
  /// profile picker without waiting for the network.
  Future<void> restore() async {
    _cachedToken = await token();

    try {
      final raw = await _storage.read(key: _guardianKey);
      if (raw != null && raw.isNotEmpty) {
        _guardian = Guardian.fromJson(jsonDecode(raw) as Map<String, dynamic>);
      }
    } catch (_) {
      _guardian = null;
    }
  }

  Future<({Guardian guardian, List<Child> children})> signIn({
    required String email,
    required String password,
  }) async {
    final response = await _api.post('/auth/parent/login', body: {
      'email': email.trim(),
      'password': password,
    });

    return _persist(response);
  }

  Future<({Guardian guardian, List<Child> children})> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    final response = await _api.post('/auth/parent/register', body: {
      'name': name.trim(),
      'email': email.trim(),
      'password': password,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
    });

    return _persist(response);
  }

  /// Ask the server for a code to put on the television screen.
  ///
  /// Typing an email and a password with a remote control is miserable, so the
  /// TV never asks for either: it shows six characters and waits for a phone
  /// that is already signed in to vouch for it.
  Future<DeviceCode> requestDeviceCode() async {
    return DeviceCode.fromJson(await _api.post('/auth/device/code'));
  }

  /// Called by the television on a timer.
  ///
  /// Returns null while the parent has not approved yet, which is the ordinary
  /// case and not worth an exception. A code that expired or was already
  /// claimed throws, because the TV must stop asking and show a fresh one.
  Future<({Guardian guardian, List<Child> children})?> claimDeviceCode(String code) async {
    final response = await _api.get('/auth/device/poll', query: {'code': code});
    final status = response['status'] as String? ?? 'pending';

    return switch (status) {
      'approved' => await _persist(response),
      'pending'  => null,
      _          => throw DeviceCodeExpired(status),
    };
  }

  /// Called by the phone: yes, that television is mine.
  Future<void> approveDeviceCode(String code) async {
    await _api.post('/auth/device/approve', body: {'code': code.trim().toUpperCase()});
  }

  Future<List<Child>> refreshChildren() async {
    final response = await _api.get('/auth/me');

    if (response['guardian'] is Map) {
      _guardian = Guardian.fromJson((response['guardian'] as Map).cast<String, dynamic>());
      await _storage.write(key: _guardianKey, value: jsonEncode(_guardian!.toJson()));
    }

    return ((response['children'] as List?) ?? const [])
        .map((e) => Child.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  Future<Child> addChild({
    required String name,
    required String avatar,
    String? birthdate,
    String? favoriteColor,
    String? level,
  }) async {
    final response = await _api.post('/children', body: {
      'name': name.trim(),
      'avatar': avatar,
      'birthdate': ?birthdate,
      'favorite_color': ?favoriteColor,
      'level': ?level,
    });

    return Child.fromJson((response['child'] as Map).cast<String, dynamic>());
  }

  /// Check the parent PIN with the server, and remember enough to check it
  /// again offline.
  ///
  /// The server stores a bcrypt hash, which is no use on the device without a
  /// bcrypt implementation. So on a successful check we keep our own digest of
  /// the PIN salted with the guardian and device ids. It never leaves the
  /// device, it cannot be replayed anywhere else, and it means a parent can
  /// open the parent zone on a plane.
  Future<bool> verifyPin(String pin) async {
    final response = await _api.post('/parent/pin/verify', body: {'pin': pin});
    final ok = response['token'] != null;

    if (ok) {
      try {
        await _storage.write(key: _pinHashKey, value: await _localPinDigest(pin));
      } catch (_) {
        // Remembering the PIN for offline use is a convenience. If the device's
        // secure store refuses, the parent has still proved who they are and
        // must not be turned away; they will simply need a network next time.
      }
    }

    return ok;
  }

  /// Record a PIN for offline checks after it has been changed.
  ///
  /// Without this the device would keep gating on the old PIN whenever it is
  /// offline, which is exactly when a parent would least expect it.
  Future<void> rememberPin(String pin) async {
    try {
      await _storage.write(key: _pinHashKey, value: await _localPinDigest(pin));
    } catch (_) {
      // Same reasoning as in verifyPin: this is a convenience, not a gate.
    }
  }

  /// Check the PIN with no network, against what the last successful online
  /// check recorded. Returns null when this device has never verified a PIN and
  /// therefore cannot judge.
  Future<bool?> verifyPinOffline(String pin) async {
    final stored = await _storage.read(key: _pinHashKey);

    if (stored == null || stored.isEmpty) return null;

    return stored == await _localPinDigest(pin);
  }

  Future<bool> get hasCachedPin async {
    final stored = await _storage.read(key: _pinHashKey);
    return stored != null && stored.isNotEmpty;
  }

  Future<String> _localPinDigest(String pin) async {
    final salt = '${_guardian?.id ?? 0}:${DeviceIdentity.instance.deviceId}';

    return sha256.convert(utf8.encode('$salt:$pin')).toString();
  }

  Future<void> signOut() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {
      // Signing out locally must work even with no network.
    }

    _cachedToken = null;
    _guardian = null;

    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _expiryKey);
    await _storage.delete(key: _guardianKey);
    await _storage.delete(key: _pinHashKey);
  }

  Future<({Guardian guardian, List<Child> children})> _persist(Map<String, dynamic> response) async {
    final tokenPayload = (response['token'] as Map?)?.cast<String, dynamic>() ?? const {};
    final accessToken = tokenPayload['access_token'] as String?;

    if (accessToken == null || accessToken.isEmpty) {
      throw StateError('The server did not return a token.');
    }

    _cachedToken = accessToken;
    await _storage.write(key: _tokenKey, value: accessToken);

    if (tokenPayload['expires_at'] is String) {
      await _storage.write(key: _expiryKey, value: tokenPayload['expires_at'] as String);
    }

    final guardian = Guardian.fromJson((response['guardian'] as Map).cast<String, dynamic>());
    _guardian = guardian;
    await _storage.write(key: _guardianKey, value: jsonEncode(guardian.toJson()));

    final children = ((response['children'] as List?) ?? const [])
        .map((e) => Child.fromJson((e as Map).cast<String, dynamic>()))
        .toList();

    return (guardian: guardian, children: children);
  }
}

/// A code the television shows, and how long it is good for.
class DeviceCode {
  const DeviceCode({
    required this.code,
    required this.expiresAt,
    this.pollSeconds = 3,
    this.approveUrl,
  });

  final String code;
  final DateTime expiresAt;
  final int pollSeconds;
  final String? approveUrl;

  bool get isExpired => DateTime.now().isAfter(expiresAt);

  Duration get remaining {
    final left = expiresAt.difference(DateTime.now());
    return left.isNegative ? Duration.zero : left;
  }

  factory DeviceCode.fromJson(Map<String, dynamic> json) => DeviceCode(
        code: json['code'] as String? ?? '',
        expiresAt: DateTime.tryParse(json['expires_at'] as String? ?? '') ??
            DateTime.now().add(const Duration(minutes: 10)),
        pollSeconds: (json['poll_seconds'] as num?)?.toInt() ?? 3,
        approveUrl: json['approve_url'] as String?,
      );
}

/// The code can no longer be claimed: it ran out of time, or somebody already
/// used it. Either way the television needs a new one.
class DeviceCodeExpired implements Exception {
  const DeviceCodeExpired(this.status);

  final String status;

  @override
  String toString() => 'DeviceCodeExpired($status)';
}
