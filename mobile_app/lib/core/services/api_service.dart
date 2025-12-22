import 'dart:io';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';
import '../constants/app_constants.dart';
import '../models/user_model.dart';

class ApiService {
  late Dio _dio;

  ApiService() {
    _dio = Dio(BaseOptions(
      baseUrl: ApiConstants.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        // Add token to headers if available
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString(AppConstants.tokenKey);
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (error, handler) {
        // Handle errors
        return handler.next(error);
      },
    ));
  }

  // Login with phone and password
  Future<Map<String, dynamic>> login(String phone, String password) async {
    try {
      print('🔐 Attempting login to: ${ApiConstants.baseUrl}${ApiConstants.login}');
      print('📱 Phone: $phone');
      
      final response = await _dio.post(
        ApiConstants.login,
        data: {
          'phone': phone,
          'password': password,
        },
      );
      
      print('✅ Login response: ${response.statusCode}');
      print('📦 Response data: ${response.data}');
      
      return response.data;
    } catch (e) {
      print('❌ Login error: $e');
      if (e is DioException) {
        print('🔍 Error type: ${e.type}');
        print('🔍 Error message: ${e.message}');
        if (e.response != null) {
          print('🔍 Response status: ${e.response?.statusCode}');
          print('🔍 Response data: ${e.response?.data}');
        }
      }
      throw _handleError(e);
    }
  }

  // Register new user
  Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    required String email,
    required String password,
    required int age,
    required String universityId,
    required File? nationalIdPhoto,
  }) async {
    try {
      final formData = FormData.fromMap({
        'name': name,
        'phone': phone,
        'email': email,
        'password': password,
        'password_confirmation': password,
        'age': age,
        'university_id': universityId,
        'is_active': false, // All mobile accounts are inactive by default
      });

      if (nationalIdPhoto != null) {
        formData.files.add(
          MapEntry(
            'national_id_photo',
            await MultipartFile.fromFile(
              nationalIdPhoto.path,
              filename: 'national_id_photo.jpg',
            ),
          ),
        );
      }

      final response = await _dio.post(
        ApiConstants.register,
        data: formData,
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Forgot password
  Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final response = await _dio.post(
        '/auth/forgot-password',
        data: {'email': email},
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get current user
  Future<UserModel> getCurrentUser() async {
    try {
      final response = await _dio.get(ApiConstants.user);
      // API returns: { success: true, data: { ... } }
      final userData = response.data['data'] ?? response.data;
      return UserModel.fromJson(userData);
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Save token
  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.tokenKey, token);
  }

  // Get token
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(AppConstants.tokenKey);
  }

  // Logout
  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } catch (e) {
      // Ignore errors on logout
    } finally {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(AppConstants.tokenKey);
      await prefs.remove(AppConstants.userKey);
    }
  }

  String _handleError(dynamic error) {
    if (error is DioException) {
      // Handle connection errors
      if (error.type == DioExceptionType.connectionTimeout ||
          error.type == DioExceptionType.receiveTimeout ||
          error.type == DioExceptionType.sendTimeout) {
        return 'انتهت مهلة الاتصال. يرجى التحقق من اتصال الإنترنت';
      }
      
      if (error.type == DioExceptionType.connectionError) {
        return 'فشل الاتصال بالخادم. يرجى التحقق من:\n1. أن الخادم يعمل\n2. عنوان IP صحيح\n3. اتصال الإنترنت';
      }
      
      // Handle response errors
      if (error.response != null) {
        final data = error.response?.data;
        if (data is Map) {
          if (data.containsKey('message')) {
            return data['message'] as String;
          }
          if (data.containsKey('error')) {
            return data['error'] as String;
          }
          // Handle validation errors
          if (data.containsKey('errors')) {
            final errors = data['errors'] as Map<String, dynamic>;
            final firstError = errors.values.first;
            if (firstError is List && firstError.isNotEmpty) {
              return firstError.first as String;
            }
          }
        }
        final statusCode = error.response?.statusCode;
        if (statusCode == 401) {
          return 'بيانات الدخول غير صحيحة';
        } else if (statusCode == 404) {
          return 'الرابط غير موجود. يرجى التحقق من إعدادات API';
        } else if (statusCode == 500) {
          return 'خطأ في الخادم. يرجى المحاولة لاحقاً';
        }
        return 'حدث خطأ: $statusCode';
      }
      
      // Network error
      return 'فشل الاتصال بالخادم. يرجى التحقق من:\n1. أن الخادم يعمل على ${ApiConstants.baseUrl}\n2. أنك متصل بنفس الشبكة\n3. اتصال الإنترنت';
    }
    return error.toString();
  }
}

