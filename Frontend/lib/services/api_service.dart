import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/browser_client.dart';
import 'package:course_management_frontend/models/user_model.dart';

/// Centralized API service for the Course Management System.
///
/// Uses session-based authentication. On web, the browser handles cookies
/// automatically when [withCredentials] is enabled on [BrowserClient].
class ApiService {
  // ── Configurations ──────────────────────────────────────────────────────
  static const String baseUrl = 'https://course-management-system-axbne5a2chd6a3dz.southafricanorth-01.azurewebsites.net';

  static ApiService? _instance;
  late final http.Client _client;

  /// The currently authenticated user, populated after a successful login.
  UserModel? currentUser;

  // ── Singleton ──────────────────────────────────────────────────────────
  factory ApiService() {
    _instance ??= ApiService._internal();
    return _instance!;
  }

  ApiService._internal() {
    if (kIsWeb) {
      // On web, BrowserClient supports withCredentials for cookie handling
      final browserClient = BrowserClient();
      browserClient.withCredentials = true;
      _client = browserClient;
    } else {
      _client = http.Client();
    }
  }

  // ── Helpers ────────────────────────────────────────────────────────────
  Map<String, String> get _jsonHeaders => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };

  Uri _uri(String path) => Uri.parse('$baseUrl$path');

  /// Parses the standard API response envelope.
  /// Returns the parsed JSON map. Throws [ApiException] on failure.
  Map<String, dynamic> _parseResponse(http.Response response) {
    final body = jsonDecode(response.body) as Map<String, dynamic>;
    return body;
  }

  // ── Auth Endpoints ─────────────────────────────────────────────────────

  /// POST /api/auth/login
  ///
  /// Returns a [UserModel] on success. Throws [ApiException] on failure.
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    final response = await _client.post(
      _uri('/api/auth/login'),
      headers: _jsonHeaders,
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    final body = _parseResponse(response);

    if (response.statusCode == 200 && body['success'] == true) {
      final userData = body['data'] as Map<String, dynamic>;
      currentUser = UserModel.fromJson(userData);
      return currentUser!;
    }

    // Extract error message from the API response
    final message = body['message'] as String? ?? 'Login failed';
    throw ApiException(message, response.statusCode);
  }

  /// POST /api/auth/logout
  Future<void> logout() async {
    await _client.post(
      _uri('/api/auth/logout'),
      headers: _jsonHeaders,
    );
    currentUser = null;
  }

  /// POST /api/auth/register
  Future<UserModel> register({
    required String name,
    required String email,
    required String password,
    required String role,
  }) async {
    final response = await _client.post(
      _uri('/api/auth/register'),
      headers: _jsonHeaders,
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'role': role,
      }),
    );

    final body = _parseResponse(response);

    if (response.statusCode == 201 && body['success'] == true) {
      final userData = body['data'] as Map<String, dynamic>;
      return UserModel.fromJson(userData);
    }

    final message = body['message'] as String? ?? 'Registration failed';
    throw ApiException(message, response.statusCode);
  }
}

/// Custom exception for API errors.
class ApiException implements Exception {
  final String message;
  final int statusCode;

  const ApiException(this.message, this.statusCode);

  @override
  String toString() => 'ApiException($statusCode): $message';
}