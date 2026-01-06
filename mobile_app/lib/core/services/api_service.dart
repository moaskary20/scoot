import 'dart:io';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';
import '../constants/app_constants.dart';
import '../models/user_model.dart';
import '../models/scooter_model.dart';
import '../models/wallet_transaction_model.dart';
import '../models/trip_model.dart';
import '../models/card_model.dart';
import '../models/referral_model.dart';
import '../models/loyalty_transaction_model.dart';
import '../models/geo_zone_model.dart';

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
  // age هنا هو تاريخ الميلاد بصيغة YYYY/MM/DD
  Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    required String email,
    required String password,
    required String age,
    required String universityId,
    required File? nationalIdFrontPhoto,
    required File? nationalIdBackPhoto,
  }) async {
    try {
      final formData = FormData.fromMap({
        'name': name,
        'phone': phone,
        'email': email,
        'password': password,
        'password_confirmation': password,
        // تاريخ الميلاد بصيغة YYYY/MM/DD
        'age': age,
        'university_id': universityId,
        'is_active': false, // All mobile accounts are inactive by default
      });

      if (nationalIdFrontPhoto != null) {
        formData.files.add(
          MapEntry(
            'national_id_front',
            await MultipartFile.fromFile(
              nationalIdFrontPhoto.path,
              filename: 'national_id_front.jpg',
            ),
          ),
        );
      }

      if (nationalIdBackPhoto != null) {
        formData.files.add(
          MapEntry(
            'national_id_back',
            await MultipartFile.fromFile(
              nationalIdBackPhoto.path,
              filename: 'national_id_back.jpg',
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
      print('📱 User API Response: ${response.statusCode}');
      print('📦 Response data: ${response.data}');
      
      // API returns: { success: true, data: { ... } }
      final userData = response.data['data'] ?? response.data;
      print('👤 Parsed user data: $userData');
      
      final user = UserModel.fromJson(userData);
      print('✅ User model created - Name: ${user.name}, Phone: ${user.phone}');
      
      return user;
    } catch (e) {
      print('❌ Error getting user: $e');
      throw _handleError(e);
    }
  }

  // Update password
  Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    try {
      final response = await _dio.post(
        '/auth/update-password',
        data: {
          'current_password': currentPassword,
          'new_password': newPassword,
          'new_password_confirmation': newPassword,
        },
      );

      if (response.data['success'] != true) {
        throw Exception(response.data['message'] ?? 'فشل تحديث كلمة المرور');
      }
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Update avatar
  Future<UserModel> updateAvatar(File avatarFile) async {
    try {
      final formData = FormData.fromMap({});

      formData.files.add(
        MapEntry(
          'avatar',
          await MultipartFile.fromFile(
            avatarFile.path,
            filename: 'avatar.jpg',
          ),
        ),
      );

      final response = await _dio.post(
        '/auth/update-avatar',
        data: formData,
      );

      if (response.data['success'] == true) {
        final userData = response.data['data'] ?? response.data;
        return UserModel.fromJson(userData);
      }

      throw Exception(response.data['message'] ?? 'فشل تحديث الصورة الشخصية');
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

  // Get nearby scooters
  Future<List<ScooterModel>> getNearbyScooters(double latitude, double longitude) async {
    try {
      final url = '${ApiConstants.baseUrl}${ApiConstants.scootersNearby}';
      print('🛴 Fetching nearby scooters from: $url');
      print('📍 Location: lat=$latitude, lng=$longitude, radius=${AppConstants.nearbyRadius}');
      
      final response = await _dio.get(
        ApiConstants.scootersNearby,
        queryParameters: {
          'latitude': latitude,
          'longitude': longitude,
          'radius': AppConstants.nearbyRadius,
        },
      );
      
      print('📦 Scooters API Response Status: ${response.statusCode}');
      print('📦 Scooters API Response Data: ${response.data}');
      
      if (response.data != null) {
        // Handle different response formats
        if (response.data['success'] == true && response.data['data'] != null) {
          final List<dynamic> scooters = response.data['data'];
          print('✅ Found ${scooters.length} scooters');
          final result = scooters.map((json) => ScooterModel.fromJson(json)).toList();
          print('✅ Parsed ${result.length} scooter models');
          return result;
        } else if (response.data is List) {
          // If response is directly a list
          final List<dynamic> scooters = response.data as List;
          print('✅ Found ${scooters.length} scooters (direct list)');
          return scooters.map((json) => ScooterModel.fromJson(json)).toList();
        } else {
          print('⚠️ Unexpected response format: ${response.data}');
        }
      }
      
      print('⚠️ No scooters found or empty response');
      return [];
    } on DioException catch (e) {
      print('❌ DioException fetching scooters: ${e.message}');
      print('📡 Status Code: ${e.response?.statusCode}');
      print('📡 Response Data: ${e.response?.data}');
      
      // Handle 404 or other API errors gracefully
      if (e.response?.statusCode == 404) {
        print('⚠️ Scooters endpoint not found (404). Returning empty list.');
        return [];
      }
      return [];
    } catch (e) {
      print('❌ Error fetching scooters: $e');
      return [];
    }
  }

  // Get loyalty summary and recent transactions
  Future<Map<String, dynamic>> getLoyaltyData({int page = 1}) async {
    try {
      final response = await _dio.get(
        '/loyalty',
        queryParameters: {
          'page': page,
        },
      );

      if (response.data['success'] != true) {
        throw Exception(response.data['message'] ?? 'فشل في جلب بيانات نقاط الولاء');
      }

      final data = response.data['data'] as Map<String, dynamic>;

      // Parse transactions list into models
      final transactionsWrapper =
          (data['transactions'] as Map<String, dynamic>? ?? {});
      final List<dynamic> txList =
          (transactionsWrapper['data'] as List<dynamic>? ?? []);

      final transactions = txList
          .map((json) => LoyaltyTransactionModel.fromJson(json))
          .toList();

      return {
        'points': data['points'] ?? 0,
        'level': data['level'] ?? 'bronze',
        'thresholds': data['thresholds'] ?? const {},
        'redeem_settings': data['redeem_settings'] ?? const {},
        'transactions': transactions,
        'pagination': {
          'current_page': transactionsWrapper['current_page'] ?? 1,
          'last_page': transactionsWrapper['last_page'] ?? 1,
          'per_page': transactionsWrapper['per_page'] ?? txList.length,
          'total': transactionsWrapper['total'] ?? txList.length,
        },
      };
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get active allowed geo zones
  Future<List<GeoZoneModel>> getGeoZones() async {
    try {
      final response = await _dio.get(ApiConstants.geoZones);

      if (response.data['success'] == true &&
          response.data['data'] != null) {
        final List<dynamic> zones = response.data['data'];
        return zones.map((j) => GeoZoneModel.fromJson(j)).toList();
      }

      return [];
    } catch (e) {
      print('❌ Error fetching geo zones: $e');
      return [];
    }
  }

  // Get all available scooters
  Future<List<ScooterModel>> getAvailableScooters() async {
    try {
      final response = await _dio.get(ApiConstants.scooters);
      
      if (response.data['success'] == true && response.data['data'] != null) {
        final List<dynamic> scooters = response.data['data'];
        return scooters
            .map((json) => ScooterModel.fromJson(json))
            .where((scooter) => scooter.isAvailable)
            .toList();
      }
      
      return [];
    } catch (e) {
      print('Error fetching scooters: $e');
      return [];
    }
  }

  // Get scooter details
  Future<ScooterModel> getScooterDetails(int scooterId) async {
    try {
      final response = await _dio.get('${ApiConstants.scooters}/$scooterId');
      return ScooterModel.fromJson(response.data['data'] ?? response.data);
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get wallet balance (from user data)
  Future<double> getWalletBalance() async {
    try {
      final user = await getCurrentUser();
      return user.walletBalance;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get wallet transactions
  Future<List<WalletTransactionModel>> getWalletTransactions({
    int page = 1,
    int perPage = 20,
  }) async {
    try {
      final response = await _dio.get(
        ApiConstants.walletTransactions,
        queryParameters: {
          'page': page,
          'per_page': perPage,
        },
      );
      
      if (response.data['success'] == true && response.data['data'] != null) {
        final List<dynamic> transactions = response.data['data']['data'] ?? response.data['data'] ?? [];
        return transactions
            .map((json) => WalletTransactionModel.fromJson(json))
            .toList();
      } else if (response.data is List) {
        final List<dynamic> transactions = response.data as List;
        return transactions
            .map((json) => WalletTransactionModel.fromJson(json))
            .toList();
      }
      
      return [];
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        print('Wallet transactions endpoint not found (404). Returning empty list.');
        return [];
      }
      print('Error fetching wallet transactions: ${e.message}');
      return [];
    } catch (e) {
      print('Error fetching wallet transactions: $e');
      return [];
    }
  }

  // Get user trips
  Future<List<TripModel>> getUserTrips({
    int page = 1,
    int perPage = 20,
  }) async {
    try {
      final response = await _dio.get(
        ApiConstants.trips,
        queryParameters: {
          'page': page,
          'per_page': perPage,
        },
      );

      if (response.data != null) {
        final data = response.data['data'];
        if (data is Map && data['data'] != null) {
          final List<dynamic> trips = data['data'];
          return trips.map((json) => TripModel.fromJson(json)).toList();
        } else if (response.data['success'] == true && response.data['data'] is List) {
          final List<dynamic> trips = response.data['data'];
          return trips.map((json) => TripModel.fromJson(json)).toList();
        } else if (response.data is List) {
          final List<dynamic> trips = response.data as List;
          return trips.map((json) => TripModel.fromJson(json)).toList();
        }
      }

      return [];
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        print('Trips endpoint not found (404). Returning empty list.');
        return [];
      }
      print('Error fetching trips: ${e.message}');
      return [];
    } catch (e) {
      print('Error fetching trips: $e');
      return [];
    }
  }

  // Top up wallet
  Future<Map<String, dynamic>> topUpWallet({
    required double amount,
    String? paymentMethod,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.walletTopUp,
        data: {
          'amount': amount,
          'payment_method': paymentMethod ?? 'paymob',
        },
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Validate promo code
  Future<Map<String, dynamic>> validatePromoCode(String code) async {
    try {
      final response = await _dio.post(
        ApiConstants.validateCoupon,
        data: {'code': code},
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Save card
  Future<Map<String, dynamic>> saveCard(CardModel card) async {
    try {
      final response = await _dio.post(
        ApiConstants.saveCard,
        data: card.toJson(),
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get saved cards
  Future<List<CardModel>> getSavedCards() async {
    try {
      final response = await _dio.get(ApiConstants.getCards);
      
      if (response.data['success'] == true && response.data['data'] != null) {
        final List<dynamic> cards = response.data['data'];
        return cards.map((json) => CardModel.fromJson(json)).toList();
      }
      
      return [];
    } catch (e) {
      print('Error fetching cards: $e');
      return [];
    }
  }

  // Delete card
  Future<void> deleteCard(int cardId) async {
    try {
      await _dio.delete('${ApiConstants.getCards}/$cardId');
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get referral data
  Future<ReferralModel> getReferralData() async {
    try {
      print('🔗 Fetching referral data from: ${ApiConstants.baseUrl}${ApiConstants.referral}');
      final response = await _dio.get(ApiConstants.referral);
      
      print('📦 Referral API Response: ${response.data}');
      
      if (response.data['success'] == true && response.data['data'] != null) {
        final referralData = ReferralModel.fromJson(response.data['data']);
        print('✅ Referral data loaded: Code=${referralData.referralCode}, Link=${referralData.affiliateLink}');
        return referralData;
      }
      
      print('⚠️ No referral data in response');
      // Return default if no data
      return ReferralModel(
        referralCode: '',
        affiliateLink: '',
        referredFriendsCount: 0,
        totalEarned: 0.0,
      );
    } catch (e) {
      print('❌ Error fetching referral data: $e');
      if (e is DioException) {
        print('📡 Dio Error: ${e.response?.data}');
        print('📡 Status Code: ${e.response?.statusCode}');
      }
      // Return default on error
      return ReferralModel(
        referralCode: '',
        affiliateLink: '',
        referredFriendsCount: 0,
        totalEarned: 0.0,
      );
    }
  }

  // Start trip by scanning QR code
  Future<Map<String, dynamic>> startTrip(String qrCode, double? latitude, double? longitude) async {
    try {
      print('🚀 Starting trip with QR code: $qrCode');
      print('📍 Location: lat=$latitude, lng=$longitude');
      
      final response = await _dio.post(
        ApiConstants.startTrip,
        data: {
          'qr_code': qrCode,
          'latitude': latitude,
          'longitude': longitude,
        },
      );

      print('📦 Start trip response: ${response.statusCode}');
      print('📦 Response data: ${response.data}');

      if (response.data['success'] == true) {
        return response.data['data'];
      }

      final errorMessage = response.data['message'] ?? 'فشل بدء الرحلة';
      print('❌ Start trip failed: $errorMessage');
      throw Exception(errorMessage);
    } catch (e) {
      print('❌ Error starting trip: $e');
      if (e is DioException) {
        print('📡 Status Code: ${e.response?.statusCode}');
        print('📡 Response Data: ${e.response?.data}');
        if (e.response?.data != null) {
          final responseData = e.response!.data;
          if (responseData['message'] != null) {
            // If there's an active trip, include trip_id in the exception message
            if (responseData['trip_id'] != null) {
              throw Exception('${responseData['message']}|trip_id:${responseData['trip_id']}');
            }
            throw Exception(responseData['message']);
          }
        }
      }
      throw _handleError(e);
    }
  }

  // Get active trip
  Future<Map<String, dynamic>?> getActiveTrip() async {
    try {
      final response = await _dio.get('${ApiConstants.trips}/active');

      if (response.data['success'] == true) {
        return response.data['data'];
      }

      return null;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        return null; // No active trip
      }
      print('Error getting active trip: $e');
      return null;
    }
  }

  // Complete trip
  Future<Map<String, dynamic>> completeTrip(
    int tripId,
    double? endLatitude,
    double? endLongitude,
    String? imagePath,
  ) async {
    try {
      print('🔄 Completing trip: $tripId');
      print('📍 End location: lat=$endLatitude, lng=$endLongitude');
      print('📷 Image path: $imagePath');
      
      final formData = FormData.fromMap({
        'end_latitude': endLatitude,
        'end_longitude': endLongitude,
      });

      if (imagePath != null) {
        try {
          formData.files.add(
            MapEntry(
              'end_image',
              await MultipartFile.fromFile(
                imagePath,
                filename: 'trip_end_${tripId}_${DateTime.now().millisecondsSinceEpoch}.jpg',
              ),
            ),
          );
          print('✅ Image added to form data');
        } catch (imageError) {
          print('⚠️ Error adding image to form: $imageError');
          // Continue without image if there's an error
        }
      }

      final response = await _dio.post(
        '${ApiConstants.trips}/$tripId/complete',
        data: formData,
      );

      print('📦 Complete trip response: ${response.statusCode}');
      print('📦 Response data: ${response.data}');

      if (response.data['success'] == true) {
        return response.data['data'];
      }

      final errorMessage = response.data['message'] ?? 'فشل إغلاق الرحلة';
      print('❌ Complete trip failed: $errorMessage');
      throw Exception(errorMessage);
    } catch (e) {
      print('❌ Error completing trip: $e');
      if (e is DioException) {
        print('📡 Status Code: ${e.response?.statusCode}');
        print('📡 Response Data: ${e.response?.data}');
        if (e.response?.data != null && e.response?.data['message'] != null) {
          throw Exception(e.response!.data['message']);
        }
        if (e.response?.data != null && e.response?.data['error'] != null) {
          throw Exception('${e.response!.data['message'] ?? 'حدث خطأ'}: ${e.response!.data['error']}');
        }
      }
      throw _handleError(e);
    }
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

