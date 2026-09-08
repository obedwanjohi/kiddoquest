import 'package:dio/dio.dart';

import '../platform/device_identity.dart';
import 'api_exception.dart';

/// Where the API lives. Overridden at build time:
///   flutter run --dart-define=API_BASE_URL=https://www.kiddoquest.co.ke/api/v1
///
/// The default points at the host machine as seen from an Android emulator,
/// which is what a developer wants nine times out of ten.
const String kApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000/api/v1',
);

/// The single door to the server.
///
/// Everything about it assumes the network is unreliable: short timeouts, one
/// automatic retry for reads, and errors that come back as something a screen
/// can show a parent rather than a stack trace.
class ApiClient {
  ApiClient({Dio? dio, this.tokenProvider, DeviceIdentity? identity})
      : _identity = identity ?? DeviceIdentity.instance,
        _dio = dio ?? Dio() {
    _dio.options = _dio.options.copyWith(
      baseUrl: kApiBaseUrl,
      connectTimeout: const Duration(seconds: 12),
      receiveTimeout: const Duration(seconds: 30),
      sendTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json'},
      // 304 is a success for us: it means the cached copy is still good.
      validateStatus: (status) => status != null && status >= 200 && status < 400,
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await tokenProvider?.call();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          options.headers.addAll(await _identity.headers());

          if (activeChildId != null) {
            options.headers['X-Child-Id'] = '$activeChildId';
          }

          handler.next(options);
        },
        onError: (error, handler) => handler.reject(error),
      ),
    );
  }

  final Dio _dio;
  final DeviceIdentity _identity;

  /// Called before every request; returns the current access token or null.
  final Future<String?> Function()? tokenProvider;

  /// Which child the request is about. Set when a child is playing.
  int? activeChildId;

  Dio get raw => _dio;

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? query,
    Map<String, String>? headers,
  }) async {
    final response = await _send(() => _dio.get(path, queryParameters: query, options: Options(headers: headers)));

    return _asMap(response);
  }

  /// Like [get] but tells you when the server said "nothing has changed".
  Future<({Map<String, dynamic>? body, String? etag, bool notModified})> getCached(
    String path, {
    Map<String, dynamic>? query,
    String? etag,
  }) async {
    final response = await _send(
      () => _dio.get(
        path,
        queryParameters: query,
        options: Options(headers: etag == null ? null : {'If-None-Match': etag}),
      ),
    );

    if (response.statusCode == 304) {
      return (body: null, etag: etag, notModified: true);
    }

    return (
      body: _asMap(response),
      etag: response.headers.value('etag'),
      notModified: false,
    );
  }

  Future<Map<String, dynamic>> post(String path, {Object? body}) async {
    final response = await _send(() => _dio.post(path, data: body));

    return _asMap(response);
  }

  Future<Map<String, dynamic>> patch(String path, {Object? body}) async {
    final response = await _send(() => _dio.patch(path, data: body));

    return _asMap(response);
  }

  Future<Map<String, dynamic>> delete(String path) async {
    final response = await _send(() => _dio.delete(path));

    return _asMap(response);
  }

  /// Fetch a file. Used for pack documents and media.
  Future<List<int>> getBytes(String url) async {
    try {
      final response = await _dio.get<List<int>>(
        url,
        options: Options(responseType: ResponseType.bytes, headers: {'Accept': '*/*'}),
      );

      return response.data ?? const [];
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<Response<dynamic>> _send(Future<Response<dynamic>> Function() request) async {
    try {
      return await request();
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Map<String, dynamic> _asMap(Response<dynamic> response) {
    final data = response.data;

    if (data is Map) return data.cast<String, dynamic>();
    if (data == null) return const {};

    return {'data': data};
  }
}
