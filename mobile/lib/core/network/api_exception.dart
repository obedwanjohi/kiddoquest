import 'package:dio/dio.dart';

/// One failure type for the whole app, so a screen can decide what to show
/// without knowing anything about HTTP.
class ApiException implements Exception {
  const ApiException({
    required this.code,
    required this.message,
    this.statusCode,
    this.fields = const {},
  });

  final String code;
  final String message;
  final int? statusCode;
  final Map<String, dynamic> fields;

  /// True when the request never reached the server. The child keeps playing;
  /// the outbox keeps the work.
  bool get isOffline => code == 'offline' || code == 'timeout';

  bool get isUnauthorized => statusCode == 401 || code == 'unauthenticated';

  bool get needsSubscription => statusCode == 402 || code == 'subscription_required';

  factory ApiException.fromDio(DioException error) {
    final response = error.response;
    final data = response?.data;

    if (data is Map && data['error'] is Map) {
      final payload = (data['error'] as Map).cast<String, dynamic>();
      return ApiException(
        code: payload['code']?.toString() ?? 'error',
        message: payload['message']?.toString() ?? 'Something went wrong.',
        statusCode: response?.statusCode,
        fields: (payload['fields'] as Map?)?.cast<String, dynamic>() ?? const {},
      );
    }

    // Laravel's own validation shape.
    if (data is Map && data['errors'] is Map) {
      final errors = (data['errors'] as Map).cast<String, dynamic>();
      final first = errors.values.first;
      return ApiException(
        code: 'validation',
        message: first is List && first.isNotEmpty ? first.first.toString() : 'Please check what you typed.',
        statusCode: response?.statusCode,
        fields: errors,
      );
    }

    final code = switch (error.type) {
      DioExceptionType.connectionTimeout || DioExceptionType.sendTimeout || DioExceptionType.receiveTimeout => 'timeout',
      DioExceptionType.connectionError => 'offline',
      DioExceptionType.badCertificate => 'bad_certificate',
      DioExceptionType.cancel => 'cancelled',
      _ => response?.statusCode == 401 ? 'unauthenticated' : 'error',
    };

    final message = switch (code) {
      'offline' => 'No internet right now. Your progress is safe and will upload later.',
      'timeout' => 'The connection is slow. We will try again shortly.',
      'unauthenticated' => 'Please sign in again.',
      _ => data is Map && data['message'] is String
          ? data['message'] as String
          : 'Something went wrong. Please try again.',
    };

    return ApiException(code: code, message: message, statusCode: response?.statusCode);
  }

  @override
  String toString() => 'ApiException($code, $statusCode): $message';
}
