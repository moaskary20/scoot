import 'dart:ui' as ui;
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import 'package:flutter/services.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:geolocator/geolocator.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/scooter_model.dart';
import '../../../core/models/user_model.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/location_service.dart';
import 'package:dio/dio.dart';
import 'riding_guide_screen.dart';
import '../../wallet/screens/wallet_screen.dart';
import '../../wallet/screens/top_up_screen.dart';
import '../../settings/screens/language_selection_screen.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../loyalty/screens/loyalty_points_screen.dart';
import 'package:provider/provider.dart';
import '../../../core/services/language_service.dart';
import '../../trips/screens/trips_screen.dart';
import '../../trips/screens/qr_scanner_screen.dart';
import '../../trips/screens/active_trip_screen.dart';
import '../../profile/screens/profile_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ApiService _apiService = ApiService();
  final LocationService _locationService = LocationService();
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  GoogleMapController? _mapController;
  MapType _currentMapType = MapType.normal;

  Position? _currentPosition;
  List<ScooterModel> _scooters = [];
  bool _isLoading = true;
  Set<Marker> _markers = {};
  Set<Polygon> _polygons = {};
  UserModel? _currentUser;
  bool _isLoadingUser = false;
  BitmapDescriptor? _availableScooterIcon;
  BitmapDescriptor? _unavailableScooterIcon;
  Timer? _scootersUpdateTimer;

  @override
  void initState() {
    super.initState();
    _createCustomMarkers();
    _initializeLocation();
    _loadUserData(); // Load user data on screen init
    _checkActiveTrip(); // Check for active trip on screen init
    _startScootersUpdateTimer(); // Start periodic update for scooters
  }

  void _startScootersUpdateTimer() {
    // Update scooters every 10 seconds to ensure rented scooters disappear immediately
    _scootersUpdateTimer = Timer.periodic(const Duration(seconds: 10), (timer) {
      if (mounted && _currentPosition != null) {
        print('🔄 Periodic update: Refreshing scooters list...');
        _loadScooters();
      } else {
        print('⚠️ Skipping periodic update: widget not mounted or no position');
      }
    });
    print('✅ Started periodic scooters update timer (10 seconds)');
  }

  Future<void> _createCustomMarkers() async {
    // Create available scooter icon (green)
    _availableScooterIcon = await _createScooterIcon(Colors.green);
    // Create unavailable scooter icon (red)
    _unavailableScooterIcon = await _createScooterIcon(Colors.red);
  }

  Future<BitmapDescriptor> _createScooterIcon(Color color) async {
    final pictureRecorder = ui.PictureRecorder();
    final canvas = Canvas(pictureRecorder);
    final size = 120.0;

    // Draw background circle with shadow
    final shadowPaint = Paint()
      ..color = Colors.black.withOpacity(0.3)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 4);
    canvas.drawCircle(
      Offset(size / 2 + 2, size / 2 + 2),
      size / 2 - 4,
      shadowPaint,
    );

    // Draw main circle
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;
    canvas.drawCircle(Offset(size / 2, size / 2), size / 2 - 4, paint);

    // Draw white border
    final borderPaint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = 5.0;
    canvas.drawCircle(Offset(size / 2, size / 2), size / 2 - 6, borderPaint);

    // Draw scooter emoji/icon using text
    final textPainter = TextPainter(
      text: TextSpan(
        text: '🛴', // Scooter emoji
        style: TextStyle(
          fontSize: size * 0.45,
          color: Colors.white,
          fontWeight: FontWeight.bold,
        ),
      ),
      textDirection: ui.TextDirection.ltr,
      textAlign: TextAlign.center,
    );
    textPainter.layout();
    textPainter.paint(
      canvas,
      Offset(
        (size - textPainter.width) / 2,
        (size - textPainter.height) / 2 - 5,
      ),
    );

    final picture = pictureRecorder.endRecording();
    final image = await picture.toImage(size.toInt(), size.toInt());
    final bytes = await image.toByteData(format: ui.ImageByteFormat.png);

    return BitmapDescriptor.fromBytes(bytes!.buffer.asUint8List());
  }

  Future<void> _initializeLocation() async {
    try {
      final position = await _locationService.getCurrentLocation();
      if (mounted) {
        setState(() {
          _currentPosition = position;
          _isLoading = false;
        });
        await _loadScooters();
        await _loadGeoZones();
      }
    } catch (e) {
      // Use default location (Cairo, Egypt) if location access fails
      if (mounted) {
        setState(() {
          _currentPosition = Position(
            latitude: 30.0444,
            longitude: 31.2357,
            timestamp: DateTime.now(),
            accuracy: 0,
            altitude: 0,
            heading: 0,
            speed: 0,
            speedAccuracy: 0,
            altitudeAccuracy: 0,
            headingAccuracy: 0,
          );
          _isLoading = false;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'تعذر الحصول على موقعك. يتم استخدام موقع افتراضي.\n$e',
            ),
            backgroundColor: Colors.orange,
            duration: const Duration(seconds: 4),
          ),
        );

        await _loadScooters();
        await _loadGeoZones();
      }
    }
  }

  Future<void> _loadScooters() async {
    if (_currentPosition == null || !mounted) {
      print(
        '⚠️ Cannot load scooters: _currentPosition is null or widget not mounted',
      );
      return;
    }

    if (mounted) {
      setState(() {
        _isLoading = true;
      });
    }

    print(
      '🛴 Loading scooters for location: ${_currentPosition!.latitude}, ${_currentPosition!.longitude}',
    );

    try {
      final scooters = await _apiService.getNearbyScooters(
        _currentPosition!.latitude,
        _currentPosition!.longitude,
      );

      print('✅ Loaded ${scooters.length} scooters');

      if (mounted) {
        setState(() {
          _scooters = scooters;
          _updateMarkers();
          _isLoading = false;
        });

        print('📍 Updated ${_markers.length} markers on map');

        // Show message if no scooters found (but not an error)
        if (scooters.isEmpty && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(AppLocalizations.of(context)?.noScootersAvailable ?? 'لا توجد سكوترات متاحة في المنطقة القريبة'),
              backgroundColor: Colors.blue,
              duration: const Duration(seconds: 2),
            ),
          );
        } else if (scooters.isNotEmpty) {
          print('✅ Successfully displayed ${scooters.length} scooters on map');
        }
      }
    } catch (e) {
      print('❌ Error loading scooters: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
          _scooters = []; // Set empty list on error
          _updateMarkers();
        });
        // Only show error for critical failures, not 404
        final errorMsg = e.toString();
        if (!errorMsg.contains('404') && !errorMsg.contains('empty')) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('${AppLocalizations.of(context)?.errorLoadingScooters ?? 'حدث خطأ في تحميل السكوترات'}: $e'),
              backgroundColor: Colors.orange,
              duration: const Duration(seconds: 3),
            ),
          );
        }
      }
    }
  }

  Future<void> _loadGeoZones() async {
    try {
      final zones = await _apiService.getGeoZones();
      final Set<Polygon> polygons = {};

      for (final zone in zones) {
        if (zone.polygon.isEmpty) continue;

        // Use zone color if provided, otherwise default green
        Color color;
        try {
          final hex = zone.color.replaceFirst('#', '');
          if (hex.length == 6) {
            color = Color(int.parse('0xFF$hex'));
          } else {
            color = const Color(0xFF00C853);
          }
        } catch (_) {
          color = const Color(0xFF00C853);
        }

        polygons.add(
          Polygon(
            polygonId: PolygonId('zone_${zone.id}'),
            points: zone.polygon,
            strokeColor: color,
            strokeWidth: 2,
            fillColor: color.withOpacity(0.25),
          ),
        );
      }

      if (mounted) {
        setState(() {
          _polygons
            ..clear()
            ..addAll(polygons);
        });
      }
    } catch (e) {
      print('❌ Error loading geo zones: $e');
      // لا نعرض SnackBar هنا حتى لا نزعج المستخدم؛ فقط لوج
    }
  }

  void _updateMarkers() {
    _markers.clear();
    print('🗺️ Updating markers...');

    // Add current location marker
    if (_currentPosition != null) {
      _markers.add(
        Marker(
          markerId: const MarkerId('current_location'),
          position: LatLng(
            _currentPosition!.latitude,
            _currentPosition!.longitude,
          ),
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueBlue),
          infoWindow: InfoWindow(title: AppLocalizations.of(context)?.yourLocation ?? 'موقعك الحالي'),
        ),
      );
      print('📍 Added current location marker');
    }

    // Add scooter markers - filter out rented scooters
    // Filter out rented scooters (status = 'rented') - they should not appear on map
    final availableScooters = _scooters.where((scooter) {
      final isRented = scooter.status?.toLowerCase() == 'rented';
      if (isRented) {
        print('🚫 Skipping rented scooter ${scooter.code} - status: ${scooter.status}');
      }
      return !isRented;
    }).toList();
    
    print('🛴 Adding ${availableScooters.length} available scooter markers (filtered ${_scooters.length - availableScooters.length} rented)...');
    for (var scooter in availableScooters) {
      if (scooter.latitude == 0.0 && scooter.longitude == 0.0) {
        print('⚠️ Skipping scooter ${scooter.code} - invalid coordinates');
        continue;
      }

      // Use custom scooter icon if available, otherwise use default
      final icon = scooter.isAvailable
          ? (_availableScooterIcon ??
                BitmapDescriptor.defaultMarkerWithHue(
                  BitmapDescriptor.hueGreen,
                ))
          : (_unavailableScooterIcon ??
                BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed));

      _markers.add(
        Marker(
          markerId: MarkerId('scooter_${scooter.id}'),
          position: LatLng(scooter.latitude, scooter.longitude),
          icon: icon,
          infoWindow: InfoWindow(
            title: '${AppLocalizations.of(context)?.scooter ?? 'سكوتر'} ${scooter.code}',
            snippet: '${AppLocalizations.of(context)?.battery ?? 'البطارية'}: ${scooter.batteryPercentage}%',
          ),
          onTap: () {
            _showScooterDetails(scooter);
          },
        ),
      );
      print(
        '✅ Added marker for scooter ${scooter.code} at (${scooter.latitude}, ${scooter.longitude})',
      );
    }

    print('✅ Total markers: ${_markers.length}');
  }

  void _showScooterDetails(ScooterModel scooter) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.electric_scooter,
                  color: Color(AppConstants.primaryColor),
                  size: 32,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'سكوتر ${scooter.code}',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                          scooter.isAvailable 
                              ? (AppLocalizations.of(context)?.available ?? 'متاح')
                              : (AppLocalizations.of(context)?.unavailable ?? 'غير متاح'),
                        style: TextStyle(
                          color: scooter.isAvailable
                              ? Colors.green
                              : Colors.red,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                _buildInfoItem(
                  Icons.battery_charging_full,
                  '${scooter.batteryPercentage}%',
                  Colors.green,
                ),
                const SizedBox(width: 20),
                _buildInfoItem(
                  scooter.isLocked ? Icons.lock : Icons.lock_open,
                  scooter.isLocked 
                      ? (AppLocalizations.of(context)?.locked ?? 'مقفول')
                      : (AppLocalizations.of(context)?.unlocked ?? 'مفتوح'),
                  scooter.isLocked ? Colors.orange : Colors.blue,
                ),
                if (scooter.distance != null) ...[
                  const SizedBox(width: 20),
                  _buildInfoItem(
                    Icons.location_on,
                    '${(scooter.distance! / 1000).toStringAsFixed(1)} ${AppLocalizations.of(context)?.km ?? 'كم'}',
                    Colors.grey,
                  ),
                ],
              ],
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: scooter.isAvailable
                    ? () {
                        Navigator.pop(context);
                        _startTrip(scooter);
                      }
                    : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Color(AppConstants.primaryColor),
                  foregroundColor: Color(AppConstants.secondaryColor),
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                  child: Text(
                    AppLocalizations.of(context)?.startTrip ?? 'ابدأ الرحلة',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoItem(IconData icon, String text, Color color) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 4),
        Text(
          text,
          style: TextStyle(
            fontSize: 14,
            color: color,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required Color color,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: color, size: 24),
      title: Text(
        title,
        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
      ),
      onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
    );
  }

  /// Parse start_time from backend with proper timezone handling
  DateTime _parseStartTime(dynamic startTimeValue) {
    if (startTimeValue == null) {
      print('⚠️ start_time is null, using current time');
      return DateTime.now();
    }

    final startTimeString = startTimeValue.toString();
    print('🕐 Parsing start_time: $startTimeString');

    try {
      // Try parsing as ISO 8601 first (with timezone)
      if (startTimeString.contains('T') || startTimeString.contains('Z') || startTimeString.contains('+')) {
        final parsed = DateTime.parse(startTimeString);
        final localTime = parsed.isUtc ? parsed.toLocal() : parsed;
        print('✅ Parsed as ISO 8601: $localTime (local)');
        return localTime;
      } else {
        // Legacy format without timezone - assume server timezone is UTC
        final parsed = DateTime.parse(startTimeString + 'Z');
        final localTime = parsed.toLocal();
        print('✅ Parsed as legacy format (assumed UTC): $localTime (local)');
        return localTime;
      }
    } catch (e) {
      // If parsing fails, try with DateFormat
      try {
        final parsed = DateFormat('yyyy-MM-dd HH:mm:ss').parse(startTimeString);
        final localTime = parsed.toLocal();
        print('✅ Parsed with DateFormat: $localTime (local)');
        return localTime;
      } catch (e2) {
        print('⚠️ Failed to parse start_time: $startTimeString, using current time');
        print('⚠️ Error: $e2');
        return DateTime.now();
      }
    }
  }

  Future<void> _startTrip(ScooterModel scooter) async {
    // Check if user account is active
    // Refresh user data first to get latest status
    await _loadUserData();
    
    if (_currentUser != null && !_currentUser!.isActive) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text(
              'حسابك غير مفعل. يرجى الانتظار حتى يتم تفعيله من قبل الإدارة',
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
          ),
        );
      }
      return;
    }

    // Check if user has negative wallet balance (debt)
    if (_currentUser != null && _currentUser!.walletBalance < 0) {
      final debtAmount = _currentUser!.walletBalance.abs();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'لا يمكنك بدء رحلة جديدة. لديك حساب مستحق بقيمة ${debtAmount.toStringAsFixed(2)} جنيه. يرجى تسديد المبلغ المستحق أولاً.',
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 5),
            action: SnackBarAction(
              label: 'المحفظة',
              textColor: Colors.white,
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const WalletScreen(),
                  ),
                );
              },
            ),
          ),
        );
      }
      return;
    }

    // Open QR scanner
    final qrCode = await Navigator.push<String>(
      context,
      MaterialPageRoute(
        builder: (context) => QRScannerScreen(
          onQRCodeScanned: (code) {
            Navigator.pop(context, code);
          },
        ),
      ),
    );

    if (qrCode == null || qrCode.isEmpty) {
      return; // User cancelled
    }

    // Show loading
    bool isLoadingDialogOpen = false;
    if (mounted) {
      try {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => PopScope(
            canPop: false,
            child: const Center(child: CircularProgressIndicator()),
          ),
        );
        isLoadingDialogOpen = true;
      } catch (e) {
        print('⚠️ Error showing loading dialog: $e');
        isLoadingDialogOpen = false;
      }
    }

    try {
      // Get current location
      final position = await _locationService.getCurrentLocation();

      // Start trip
      final tripData = await _apiService.startTrip(
        qrCode,
        position.latitude,
        position.longitude,
      );

      if (mounted) {
        print('✅ Trip started successfully, navigating to active trip screen');
        print('📊 Trip data: $tripData');

        // Close loading dialog safely before navigation
        await _closeLoadingDialogSafely(isLoadingDialogOpen);
        isLoadingDialogOpen = false;
        
        // Small delay to ensure dialog is fully closed
        await Future.delayed(const Duration(milliseconds: 100));
        
        if (!mounted) return;

        // Parse start_time and convert to local timezone
        final startTime = _parseStartTime(tripData['start_time']);
        print('📅 Parsed start time: $startTime (local)');

        // Update scooters immediately to remove the rented scooter from map
        // This ensures real-time update - the rented scooter disappears immediately
        print('🔄 Updating scooters list immediately after trip start...');
        await _loadScooters();

        if (!mounted) return;

        await _navigateToActiveTrip(
          tripData['trip_id'],
          tripData['scooter_code'] ?? scooter.code,
          startTime,
        );
      }
    } catch (e) {
      print('❌ Error in _startTrip: $e');
      print('❌ Error type: ${e.runtimeType}');
      
      // Close loading dialog first - use multiple strategies to ensure it closes
      await _closeLoadingDialogSafely(isLoadingDialogOpen);
      isLoadingDialogOpen = false;

      // Wait a bit to ensure dialog is fully closed before showing error
      await Future.delayed(const Duration(milliseconds: 200));

      if (!mounted) return;

      // Check if error is due to active trip
      final errorStr = e.toString();
      if (errorStr.contains('لديك رحلة نشطة بالفعل') ||
          errorStr.contains('active trip')) {
        // Try to extract trip_id from error message first
        int? tripId;
        if (errorStr.contains('trip_id:')) {
          try {
            final tripIdMatch = RegExp(
              r'trip_id:(\d+)',
            ).firstMatch(errorStr);
            if (tripIdMatch != null) {
              tripId = int.parse(tripIdMatch.group(1)!);
              print('📋 Extracted trip_id from error: $tripId');
            }
          } catch (parseError) {
            print('❌ Error parsing trip_id: $parseError');
          }
        }

        // Try to get active trip and navigate to it
        try {
          final activeTrip = await _apiService.getActiveTrip();
          if (activeTrip != null && mounted) {
            print('✅ Found active trip, navigating to active trip screen');
            await _navigateToActiveTrip(
              activeTrip['id'],
              activeTrip['scooter_code'] ?? 'غير معروف',
              _parseStartTime(activeTrip['start_time']),
            );
            return;
          }
        } catch (tripError) {
          print('❌ Error getting active trip: $tripError');
        }
      }

      // Extract and display user-friendly error message
      String errorMessage = 'حدث خطأ في بدء الرحلة';
      
      // Try to extract message from exception
      if (errorStr.contains('Exception: ')) {
        final parts = errorStr.split('Exception: ');
        if (parts.length > 1) {
          final message = parts[1].split('|')[0].trim(); // Remove any additional info after |
          errorMessage = message;
        }
      } else if (errorStr.contains('message')) {
        // Try to extract from JSON-like string
        final match = RegExp(r'"message"\s*:\s*"([^"]+)"').firstMatch(errorStr);
        if (match != null) {
          errorMessage = match.group(1)!;
        }
      }
      
      // Try to extract from DioException if available
      try {
        if (e is DioException && e.response?.data != null) {
          // Try to extract from DioException response
          final responseData = e.response!.data;
          if (responseData is Map && responseData['message'] != null) {
            errorMessage = responseData['message'].toString();
          }
        }
      } catch (dioError) {
        print('⚠️ Error extracting DioException message: $dioError');
      }

      if (mounted) {
        // Check if error message indicates scooter is unavailable (rented, maintenance, unlocked, etc.)
        final isScooterUnavailable = errorMessage.contains('مستأجر') || 
                                     errorMessage.contains('الصيانة') ||
                                     errorMessage.contains('غير متاح') ||
                                     errorMessage.contains('مفتوح') ||
                                     errorMessage.contains('مقفول') ||
                                     errorMessage.contains('UNLOCKED') ||
                                     errorMessage.contains('LOCKED') ||
                                     errorStr.contains('SCOOTER_UNLOCKED_RENTED') ||
                                     errorStr.contains('SCOOTER_NOT_AVAILABLE') ||
                                     errorStr.contains('SCOOTER_RENTED') ||
                                     errorStr.contains('SCOOTER_LOCKED') ||
                                     errorStr.contains('SCOOTER_MAINTENANCE');
        
        // Show error message in SnackBar
        try {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(errorMessage),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 5),
              action: isScooterUnavailable
                  ? SnackBarAction(
                      label: 'بحث عن سكوتر',
                      textColor: Colors.white,
                      onPressed: () {
                        // Refresh scooters list
                        _loadScooters();
                      },
                    )
                  : null,
            ),
          );
        } catch (snackBarError) {
          print('❌ Error showing SnackBar: $snackBarError');
          // Fallback: show dialog if SnackBar fails
          if (mounted) {
            showDialog(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('خطأ'),
                content: Text(errorMessage),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('حسناً'),
                  ),
                  if (isScooterUnavailable)
                    TextButton(
                      onPressed: () {
                        Navigator.pop(context);
                        _loadScooters();
                      },
                      child: const Text('بحث عن سكوتر'),
                    ),
                ],
              ),
            );
          }
        }
      }
    }
  }

  Future<void> _recenterMap() async {
    if (_currentPosition != null && _mapController != null) {
      await _mapController!.animateCamera(
        CameraUpdate.newLatLngZoom(
          LatLng(_currentPosition!.latitude, _currentPosition!.longitude),
          15.0,
        ),
      );
    }
  }

  void _openHelp() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(AppLocalizations.of(context)?.help ?? 'المساعدة'),
        content: Text(AppLocalizations.of(context)?.howCanWeHelp ?? 'كيف يمكننا مساعدتك؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(AppLocalizations.of(context)?.close ?? 'إغلاق'),
          ),
        ],
      ),
    );
  }

  void _openMenu() async {
    // Load user data first, then open drawer
    await _loadUserData();
    if (mounted) {
      _scaffoldKey.currentState?.openDrawer();
    }
  }

  Future<void> _loadUserData() async {
    setState(() {
      _isLoadingUser = true;
    });

    try {
      final user = await _apiService.getCurrentUser();
      if (mounted) {
        setState(() {
          _currentUser = user;
          _isLoadingUser = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingUser = false;
        });
        // Show error message
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)?.errorLoadingUserData ?? 'حدث خطأ في تحميل بيانات المستخدم'}: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 2),
          ),
        );
      }
    }
  }

  Future<void> _checkActiveTrip() async {
    try {
      final activeTrip = await _apiService.getActiveTrip();
      if (activeTrip != null && mounted) {
        // User has an active trip, navigate to active trip screen
        print('✅ Found active trip, navigating to active trip screen');
        print('📊 Active trip data: $activeTrip');

        // Wait a bit for the screen to fully load
        await Future.delayed(const Duration(milliseconds: 500));

        if (mounted) {
          await _navigateToActiveTrip(
            activeTrip['id'],
            activeTrip['scooter_code'] ?? 'غير معروف',
            _parseStartTime(activeTrip['start_time']),
          );
        }
      }
    } catch (e) {
      print('ℹ️ No active trip found or error: $e');
      // No active trip, continue normally
    }
  }

  /// Safely close loading dialog using multiple strategies
  Future<void> _closeLoadingDialogSafely(bool isDialogOpen) async {
    if (!isDialogOpen || !mounted) return;

    try {
      // Wait a bit to ensure dialog is fully rendered
      await Future.delayed(const Duration(milliseconds: 50));

      if (!mounted) return;

      // Strategy 1: Try rootNavigator first (most reliable for dialogs)
      try {
        final rootNavigator = Navigator.of(context, rootNavigator: true);
        if (rootNavigator.canPop()) {
          rootNavigator.pop();
          return;
        }
      } catch (e1) {
        print('⚠️ Strategy 1 failed: $e1');
      }

      // Strategy 2: Try regular navigator
      try {
        final navigator = Navigator.of(context, rootNavigator: false);
        if (navigator.canPop()) {
          navigator.pop();
          return;
        }
      } catch (e2) {
        print('⚠️ Strategy 2 failed: $e2');
      }

      // Strategy 3: Last resort - pop multiple times (but not more than 2 to avoid closing too much)
      try {
        int popCount = 0;
        final navigator = Navigator.of(context);
        while (navigator.canPop() && popCount < 2) {
          navigator.pop();
          popCount++;
          // Wait a bit between pops
          await Future.delayed(const Duration(milliseconds: 50));
        }
      } catch (e3) {
        print('⚠️ Strategy 3 failed: $e3');
      }
    } catch (e) {
      print('❌ All strategies failed to close loading dialog: $e');
      // Don't throw - we've done our best to close the dialog
      // As a last resort, try to ensure we're back on the home screen
      try {
        if (mounted) {
          // Use rootNavigator to ensure we're not stuck
          final rootNavigator = Navigator.of(context, rootNavigator: true);
          if (rootNavigator.canPop()) {
            rootNavigator.popUntil((route) => route.isFirst);
          }
        }
      } catch (finalError) {
        print('❌ Final fallback also failed: $finalError');
      }
    }
  }

  Future<void> _navigateToActiveTrip(
    int tripId,
    String scooterCode,
    DateTime startTime,
  ) async {
    if (!mounted) return;

    // Ensure any open dialogs are closed before navigation
    // Check if there's a dialog open by trying to pop once
    try {
      final navigator = Navigator.of(context, rootNavigator: true);
      if (navigator.canPop()) {
        // There might be a dialog open, try to close it safely
        await _closeLoadingDialogSafely(true);
      }
    } catch (e) {
      print('⚠️ Error closing dialogs before navigation: $e');
    }

    // Small delay to ensure camera is fully closed and Navigator is ready
    await Future.delayed(const Duration(milliseconds: 300));

    if (!mounted) return;

    // Use SchedulerBinding to ensure navigation happens after current frame
    SchedulerBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;

      try {
        final navigator = Navigator.of(context, rootNavigator: false);

        // Try to navigate - use pushReplacement if possible, otherwise push
        try {
          if (navigator.canPop()) {
            // Navigator has routes, use pushReplacement
            navigator.pushReplacement(
              MaterialPageRoute(
                builder: (context) => ActiveTripScreen(
                  tripId: tripId,
                  scooterCode: scooterCode,
                  startTime: startTime,
                ),
              ),
            );
          } else {
            // Navigator has no routes, use push
            navigator.push(
              MaterialPageRoute(
                builder: (context) => ActiveTripScreen(
                  tripId: tripId,
                  scooterCode: scooterCode,
                  startTime: startTime,
                ),
              ),
            );
          }
        } catch (navError) {
          print('⚠️ Navigation error: $navError');
          // Last resort: use push with rootNavigator
          if (mounted) {
            try {
              Navigator.of(context, rootNavigator: true).push(
                MaterialPageRoute(
                  builder: (context) => ActiveTripScreen(
                    tripId: tripId,
                    scooterCode: scooterCode,
                    startTime: startTime,
                  ),
                ),
              );
            } catch (rootNavError) {
              print('❌ RootNavigator navigation also failed: $rootNavError');
              // Show error message instead of black screen
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: const Text('حدث خطأ في الانتقال إلى شاشة الرحلة'),
                    backgroundColor: Colors.red,
                    duration: const Duration(seconds: 3),
                  ),
                );
              }
            }
          }
        }
      } catch (e) {
        print('❌ Critical navigation error: $e');
        // Show error message instead of black screen
        if (mounted) {
          try {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: const Text('حدث خطأ في الانتقال إلى شاشة الرحلة. يرجى المحاولة مرة أخرى.'),
                backgroundColor: Colors.red,
                duration: const Duration(seconds: 4),
              ),
            );
          } catch (snackBarError) {
            print('❌ Error showing error message: $snackBarError');
            // Last resort: show dialog
            if (mounted) {
              showDialog(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('خطأ'),
                  content: const Text('حدث خطأ في الانتقال إلى شاشة الرحلة. يرجى المحاولة مرة أخرى.'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('حسناً'),
                    ),
                  ],
                ),
              );
            }
          }
        }
      }
    });
  }

  String _getImageUrl(String? imagePath) {
    if (imagePath == null || imagePath.isEmpty) return '';
    if (imagePath.startsWith('http')) return imagePath;
    return '${ApiConstants.baseUrl.replaceAll('/api', '')}/storage/$imagePath';
  }

  void _showRidingGuide() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.65,
        minChildSize: 0.4,
        maxChildSize: 0.85,
        builder: (context, scrollController) => RidingGuideScreen(
          onStartTrip: () {
            Navigator.pop(context);
            _startTripFromGuide();
          },
          onCancel: () {
            Navigator.pop(context);
          },
        ),
      ),
    );
  }

  Future<void> _startTripFromGuide() async {
    // Check if user account is active
    // Refresh user data first to get latest status
    await _loadUserData();
    
    if (_currentUser != null && !_currentUser!.isActive) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text(
              'حسابك غير مفعل. يرجى الانتظار حتى يتم تفعيله من قبل الإدارة',
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
          ),
        );
      }
      return;
    }

    // Check if user has negative wallet balance (debt)
    if (_currentUser != null && _currentUser!.walletBalance < 0) {
      final debtAmount = _currentUser!.walletBalance.abs();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'لا يمكنك بدء رحلة جديدة. لديك حساب مستحق بقيمة ${debtAmount.toStringAsFixed(2)} جنيه. يرجى تسديد المبلغ المستحق أولاً.',
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 5),
            action: SnackBarAction(
              label: 'المحفظة',
              textColor: Colors.white,
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const WalletScreen(),
                  ),
                );
              },
            ),
          ),
        );
      }
      return;
    }

    // Open QR scanner
    final qrCode = await Navigator.push<String>(
      context,
      MaterialPageRoute(
        builder: (context) => QRScannerScreen(
          onQRCodeScanned: (code) {
            Navigator.pop(context, code);
          },
        ),
      ),
    );

    if (qrCode == null || qrCode.isEmpty) {
      return; // User cancelled
    }

    // Show loading
    bool isLoadingDialogOpen = false;
    if (mounted) {
      try {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => PopScope(
            canPop: false,
            child: const Center(child: CircularProgressIndicator()),
          ),
        );
        isLoadingDialogOpen = true;
      } catch (e) {
        print('⚠️ Error showing loading dialog: $e');
        isLoadingDialogOpen = false;
      }
    }

    try {
      // Get current location
      final position = await _locationService.getCurrentLocation();

      // Start trip
      final tripData = await _apiService.startTrip(
        qrCode,
        position.latitude,
        position.longitude,
      );

      // Close loading dialog safely
      await _closeLoadingDialogSafely(isLoadingDialogOpen);
      isLoadingDialogOpen = false;

      if (mounted) {
        print('✅ Trip started successfully, navigating to active trip screen');
        print('📊 Trip data: $tripData');

        // Validate trip data before navigation
        if (tripData['trip_id'] != null && tripData['start_time'] != null) {
          try {
            // Update scooters immediately to remove the rented scooter from map
            // This ensures real-time update - the rented scooter disappears immediately
            print('🔄 Updating scooters list immediately after trip start (from guide)...');
            await _loadScooters();

            await _navigateToActiveTrip(
              tripData['trip_id'],
              tripData['scooter_code'] ?? 'غير معروف',
              _parseStartTime(tripData['start_time']),
            );
          } catch (navError) {
            print('❌ Navigation error: $navError');
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('حدث خطأ في الانتقال إلى شاشة الرحلة: $navError'),
                  backgroundColor: Colors.orange,
                  duration: const Duration(seconds: 3),
                ),
              );
            }
          }
        } else {
          print('⚠️ Invalid trip data: missing trip_id or start_time');
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('حدث خطأ: بيانات الرحلة غير صحيحة'),
                backgroundColor: Colors.red,
                duration: Duration(seconds: 3),
              ),
            );
          }
        }
      }
    } catch (e) {
      print('❌ Error in _startTripFromGuide: $e');
      print('❌ Error type: ${e.runtimeType}');
      
      // Close loading dialog first - use multiple strategies to ensure it closes
      await _closeLoadingDialogSafely(isLoadingDialogOpen);
      isLoadingDialogOpen = false;

      // Wait a bit to ensure dialog is fully closed before showing error
      await Future.delayed(const Duration(milliseconds: 200));

      if (!mounted) return;

      // Check if error is due to active trip
      final errorStr = e.toString();
      if (errorStr.contains('لديك رحلة نشطة بالفعل') ||
          errorStr.contains('active trip')) {
        // Try to extract trip_id from error message first
        int? tripId;
        if (errorStr.contains('trip_id:')) {
          try {
            final tripIdMatch = RegExp(
              r'trip_id:(\d+)',
            ).firstMatch(errorStr);
            if (tripIdMatch != null) {
              tripId = int.parse(tripIdMatch.group(1)!);
              print('📋 Extracted trip_id from error: $tripId');
            }
          } catch (parseError) {
            print('❌ Error parsing trip_id: $parseError');
          }
        }

        // Try to get active trip and navigate to it
        try {
          final activeTrip = await _apiService.getActiveTrip();
          if (activeTrip != null && mounted) {
            print('✅ Found active trip, navigating to active trip screen');
            await _navigateToActiveTrip(
              activeTrip['id'],
              activeTrip['scooter_code'] ?? 'غير معروف',
              _parseStartTime(activeTrip['start_time']),
            );
            return;
          }
        } catch (tripError) {
          print('❌ Error getting active trip: $tripError');
        }
      }

      // Extract and display user-friendly error message
      String errorMessage = AppLocalizations.of(context)?.errorStartingTrip ?? 'حدث خطأ في بدء الرحلة';
      
      // Try to extract message from exception
      if (errorStr.contains('Exception: ')) {
        final parts = errorStr.split('Exception: ');
        if (parts.length > 1) {
          final message = parts[1].split('|')[0].trim(); // Remove any additional info after |
          errorMessage = message;
        }
      } else if (errorStr.contains('message')) {
        // Try to extract from JSON-like string
        final match = RegExp(r'"message"\s*:\s*"([^"]+)"').firstMatch(errorStr);
        if (match != null) {
          errorMessage = match.group(1)!;
        }
      }
      
      // Try to extract from DioException if available
      try {
        if (e is DioException && e.response?.data != null) {
          final responseData = e.response!.data;
          if (responseData is Map && responseData['message'] != null) {
            errorMessage = responseData['message'].toString();
          }
        }
      } catch (dioError) {
        print('⚠️ Error extracting DioException message: $dioError');
      }

      if (mounted) {
        // Check if error message indicates scooter is unavailable (rented, maintenance, unlocked, etc.)
        final isScooterUnavailable = errorMessage.contains('مستأجر') || 
                                     errorMessage.contains('الصيانة') ||
                                     errorMessage.contains('غير متاح') ||
                                     errorMessage.contains('مفتوح') ||
                                     errorMessage.contains('مقفول') ||
                                     errorMessage.contains('UNLOCKED') ||
                                     errorMessage.contains('LOCKED') ||
                                     errorStr.contains('SCOOTER_UNLOCKED_RENTED') ||
                                     errorStr.contains('SCOOTER_NOT_AVAILABLE') ||
                                     errorStr.contains('SCOOTER_RENTED') ||
                                     errorStr.contains('SCOOTER_LOCKED') ||
                                     errorStr.contains('SCOOTER_MAINTENANCE');
        
        // Show error message in SnackBar
        try {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(errorMessage),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 5),
              action: isScooterUnavailable
                  ? SnackBarAction(
                      label: 'بحث عن سكوتر',
                      textColor: Colors.white,
                      onPressed: () {
                        // Refresh scooters list
                        _loadScooters();
                      },
                    )
                  : null,
            ),
          );
        } catch (snackBarError) {
          print('❌ Error showing SnackBar: $snackBarError');
          // Fallback: show dialog if SnackBar fails
          if (mounted) {
            showDialog(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('خطأ'),
                content: Text(errorMessage),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('حسناً'),
                  ),
                  if (isScooterUnavailable)
                    TextButton(
                      onPressed: () {
                        Navigator.pop(context);
                        _loadScooters();
                      },
                      child: const Text('بحث عن سكوتر'),
                    ),
                ],
              ),
            );
          }
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final languageService = Provider.of<LanguageService>(context);
    return Directionality(
      textDirection: languageService.isArabic
          ? ui.TextDirection.rtl
          : ui.TextDirection.ltr,
      child: Scaffold(
        key: _scaffoldKey,
        body: Stack(
        children: [
          // Map
          if (_currentPosition != null)
            GoogleMap(
              initialCameraPosition: CameraPosition(
                target: LatLng(
                  _currentPosition!.latitude,
                  _currentPosition!.longitude,
                ),
                zoom: 15.0,
              ),
              markers: _markers,
              polygons: _polygons,
              myLocationEnabled: true,
              myLocationButtonEnabled: false,
              mapType: _currentMapType,
              onMapCreated: (controller) {
                _mapController = controller;
              },
              onCameraMoveStarted: () {
                // Handle camera movement
              },
              onCameraIdle: () {
                // Handle camera idle
              },
              minMaxZoomPreference: const MinMaxZoomPreference(10.0, 20.0),
            )
          else
            const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(
                  Color(AppConstants.primaryColor),
                ),
              ),
            ),

          // Top Help Button
          Positioned(
            top: MediaQuery.of(context).padding.top + 10,
            left: 10,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: IconButton(
                icon: const Icon(Icons.help_outline),
                color: Color(AppConstants.secondaryColor),
                onPressed: _openHelp,
              ),
            ),
          ),

          // Map type toggle (Normal / Satellite)
          Positioned(
            top: MediaQuery.of(context).padding.top + 10,
            right: 10,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: IconButton(
                icon: Icon(
                  _currentMapType == MapType.normal
                      ? Icons.satellite_alt
                      : Icons.map,
                ),
                tooltip: _currentMapType == MapType.normal
                    ? 'عرض القمر الصناعي'
                    : 'عرض الخريطة العادية',
                color: Color(AppConstants.secondaryColor),
                onPressed: () {
                  setState(() {
                    _currentMapType = _currentMapType == MapType.normal
                        ? MapType.hybrid
                        : MapType.normal;
                  });
                },
              ),
            ),
          ),

          // Bottom Action Bar
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: SafeArea(
                child: Builder(
                  builder: (context) {
                    final languageService = Provider.of<LanguageService>(context, listen: false);
                    final isRTL = languageService.isArabic;
                    return Row(
                      textDirection: isRTL ? ui.TextDirection.rtl : ui.TextDirection.ltr,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: isRTL
                          ? [
                              // In RTL: Menu Button on the right
                              Container(
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.1),
                                      blurRadius: 4,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: IconButton(
                                  icon: const Icon(Icons.menu),
                                  color: Color(AppConstants.secondaryColor),
                                  onPressed: _openMenu,
                                ),
                              ),

                              // Scan and Start Trip Button
                              Expanded(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: ElevatedButton.icon(
                                    onPressed: () {
                                      _showRidingGuide();
                                    },
                                    icon: const Icon(Icons.qr_code_scanner),
                                    label: Builder(
                                      builder: (context) {
                                        final loc = AppLocalizations.of(context);
                                        return Text(
                                          loc?.scanAndStartTrip ?? 'سكان وابدأ الرحلة',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        );
                                      },
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Color(AppConstants.primaryColor),
                                      foregroundColor: Color(AppConstants.secondaryColor),
                                      padding: const EdgeInsets.symmetric(vertical: 16),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                    ),
                                  ),
                                ),
                              ),

                              // Re-center Button on the left
                              Container(
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.1),
                                      blurRadius: 4,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: IconButton(
                                  icon: const Icon(Icons.my_location),
                                  color: Color(AppConstants.primaryColor),
                                  onPressed: _recenterMap,
                                ),
                              ),
                            ]
                          : [
                              // In LTR: Menu Button on the left
                              Container(
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.1),
                                      blurRadius: 4,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: IconButton(
                                  icon: const Icon(Icons.menu),
                                  color: Color(AppConstants.secondaryColor),
                                  onPressed: _openMenu,
                                ),
                              ),

                              // Scan and Start Trip Button
                              Expanded(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: ElevatedButton.icon(
                                    onPressed: () {
                                      _showRidingGuide();
                                    },
                                    icon: const Icon(Icons.qr_code_scanner),
                                    label: Builder(
                                      builder: (context) {
                                        final loc = AppLocalizations.of(context);
                                        return Text(
                                          loc?.scanAndStartTrip ?? 'سكان وابدأ الرحلة',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        );
                                      },
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Color(AppConstants.primaryColor),
                                      foregroundColor: Color(AppConstants.secondaryColor),
                                      padding: const EdgeInsets.symmetric(vertical: 16),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                    ),
                                  ),
                                ),
                              ),

                              // Re-center Button on the right
                              Container(
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.1),
                                      blurRadius: 4,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: IconButton(
                                  icon: const Icon(Icons.my_location),
                                  color: Color(AppConstants.primaryColor),
                                  onPressed: _recenterMap,
                                ),
                              ),
                            ],
                    );
                  },
                ),
              ),
            ),
          ),

          // Loading Indicator
          if (_isLoading)
            const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(
                  Color(AppConstants.primaryColor),
                ),
              ),
            ),
        ],
      ),
      drawer: Consumer<LanguageService>(
        builder: (context, languageService, _) {
          return Directionality(
            textDirection: languageService.isArabic
                ? ui.TextDirection.rtl
                : ui.TextDirection.ltr,
            child: Drawer(
              child: Container(
                color: Colors.white,
                child: Column(
                  children: [
                    // User Info Header
                    Container(
                      padding: EdgeInsets.only(
                        top: MediaQuery.of(context).padding.top + 20,
                        bottom: 20,
                        right: 20,
                        left: 20,
                      ),
                      child: _isLoadingUser
                          ? const Padding(
                              padding: EdgeInsets.all(20.0),
                              child: Center(child: CircularProgressIndicator()),
                            )
                          : _currentUser == null
                          ? Padding(
                              padding: const EdgeInsets.all(20.0),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        width: 70,
                                        height: 70,
                                        decoration: BoxDecoration(
                                          color: Colors.grey[300],
                                          shape: BoxShape.circle,
                                        ),
                                        child: Icon(
                                          Icons.person,
                                          size: 40,
                                          color: Colors.grey[600],
                                        ),
                                      ),
                                      const SizedBox(width: 16),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Builder(
                                              builder: (context) {
                                                final loc = AppLocalizations.of(context);
                                                return Text(
                                                  loc?.user ?? 'مستخدم',
                                                  style: const TextStyle(
                                                    fontSize: 20,
                                                    fontWeight: FontWeight.bold,
                                                    color: Colors.black,
                                                  ),
                                                );
                                              },
                                            ),
                                            const SizedBox(height: 4),
                                            Builder(
                                              builder: (context) {
                                                final loc = AppLocalizations.of(context);
                                                return Text(
                                                  loc?.noData ?? 'لا توجد بيانات متاحة',
                                                  style: TextStyle(
                                                    fontSize: 14,
                                                    color: Colors.grey[600],
                                                  ),
                                                );
                                              },
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            )
                          : Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    // User Avatar
                                    ClipOval(
                                      child:
                                          _currentUser!.avatar != null &&
                                              _currentUser!.avatar!.isNotEmpty
                                          ? CachedNetworkImage(
                                              imageUrl: _getImageUrl(
                                                _currentUser!.avatar,
                                              ),
                                              width: 70,
                                              height: 70,
                                              fit: BoxFit.cover,
                                              placeholder: (context, url) =>
                                                  Container(
                                                    width: 70,
                                                    height: 70,
                                                    color: Colors.grey[300],
                                                    child: Icon(
                                                      Icons.person,
                                                      size: 40,
                                                      color: Colors.grey[600],
                                                    ),
                                                  ),
                                              errorWidget:
                                                  (context, url, error) =>
                                                      Container(
                                                        width: 70,
                                                        height: 70,
                                                        color: Colors.grey[300],
                                                        child: Icon(
                                                          Icons.person,
                                                          size: 40,
                                                          color:
                                                              Colors.grey[600],
                                                        ),
                                                      ),
                                            )
                                          : Container(
                                              width: 70,
                                              height: 70,
                                              decoration: BoxDecoration(
                                                color: Colors.grey[300],
                                                shape: BoxShape.circle,
                                              ),
                                              child: Icon(
                                                Icons.person,
                                                size: 40,
                                                color: Colors.grey[600],
                                              ),
                                            ),
                                    ),
                                    const SizedBox(width: 16),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Builder(
                                            builder: (context) {
                                              final loc = AppLocalizations.of(context);
                                              return Text(
                                                _currentUser!.name.isNotEmpty
                                                    ? _currentUser!.name
                                                    : (loc?.user ?? 'مستخدم'),
                                                style: const TextStyle(
                                                  fontSize: 20,
                                                  fontWeight: FontWeight.bold,
                                                  color: Colors.black,
                                                ),
                                              );
                                            },
                                          ),
                                          const SizedBox(height: 4),
                                          Row(
                                            children: [
                                              const Icon(
                                                Icons.phone,
                                                size: 14,
                                                color: Colors.grey,
                                              ),
                                              const SizedBox(width: 4),
                                              Builder(
                                                builder: (context) {
                                                  final loc = AppLocalizations.of(context);
                                                  return Text(
                                                    (_currentUser!.phone != null &&
                                                            _currentUser!
                                                                .phone!
                                                                .isNotEmpty)
                                                        ? _currentUser!.phone!
                                                        : (loc?.noPhoneNumber ?? 'لا يوجد رقم هاتف'),
                                                    style: TextStyle(
                                                      fontSize: 14,
                                                      color: Colors.grey[700],
                                                    ),
                                                  );
                                                },
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 20),
                                // Charge Balance Button
                                Container(
                                  width: double.infinity,
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      colors: [
                                        Color(AppConstants.primaryColor),
                                        Color(
                                          AppConstants.primaryColor,
                                        ).withOpacity(0.8),
                                      ],
                                      begin: Alignment.centerLeft,
                                      end: Alignment.centerRight,
                                    ),
                                    borderRadius: BorderRadius.circular(25),
                                  ),
                                  child: Material(
                                    color: Colors.transparent,
                                    child: InkWell(
                                      onTap: () {
                                        Navigator.pop(context);
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (context) =>
                                                const TopUpScreen(),
                                          ),
                                        );
                                      },
                                      borderRadius: BorderRadius.circular(25),
                                      child: Padding(
                                        padding: const EdgeInsets.symmetric(
                                          vertical: 12,
                                          horizontal: 16,
                                        ),
                                        child: Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.center,
                                          children: [
                                            const Icon(
                                              Icons.star,
                                              color: Colors.white,
                                              size: 20,
                                            ),
                                            const SizedBox(width: 8),
                                            Builder(
                                              builder: (context) {
                                                final loc = AppLocalizations.of(context);
                                                return Text(
                                                  loc?.chargeBalance ?? 'اشحن رصيدك',
                                                  style: const TextStyle(
                                                    color: Colors.white,
                                                    fontSize: 16,
                                                    fontWeight: FontWeight.bold,
                                                  ),
                                                );
                                              },
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                    ),
                    const Divider(height: 1),
                    // Menu Items
                    Expanded(
                      child: ListView(
                        padding: EdgeInsets.zero,
                        children: [
                          _buildMenuItem(
                            icon: Icons.access_time,
                            title:
                                AppLocalizations.of(context)?.trips ?? 'رحلاتي',
                            color: Color(AppConstants.primaryColor),
                            onTap: () {
                              Navigator.pop(context);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TripsScreen(),
                                ),
                              );
                            },
                          ),
                          _buildMenuItem(
                            icon: Icons.account_balance_wallet,
                            title:
                                AppLocalizations.of(context)?.wallet ?? 'محفظة',
                            color: Color(AppConstants.primaryColor),
                            onTap: () {
                              Navigator.pop(context);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const WalletScreen(),
                                ),
                              );
                            },
                          ),
                          _buildMenuItem(
                            icon: Icons.stars_rounded,
                            title: AppLocalizations.of(context)
                                    ?.loyaltyPoints ??
                                'نقاط الولاء',
                            color: Color(AppConstants.primaryColor),
                            onTap: () {
                              Navigator.pop(context);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) =>
                                      const LoyaltyPointsScreen(),
                                ),
                              );
                            },
                          ),
                          _buildMenuItem(
                            icon: Icons.directions_bike,
                            title:
                                AppLocalizations.of(context)?.howToRide ??
                                'إزاي تركب لينر سكوت',
                            color: Color(AppConstants.primaryColor),
                            onTap: () {
                              Navigator.pop(context);
                              _showRidingGuide();
                            },
                          ),
                          _buildMenuItem(
                            icon: Icons.language,
                            title:
                                AppLocalizations.of(context)?.language ??
                                'اللغة',
                            color: Color(AppConstants.primaryColor),
                            onTap: () {
                              Navigator.pop(context);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) =>
                                      const LanguageSelectionScreen(),
                                ),
                              );
                            },
                          ),
                          _buildMenuItem(
                            icon: Icons.person,
                            title: AppLocalizations.of(context)?.profile ?? 'الملف الشخصي',
                            color: Color(AppConstants.primaryColor),
                            onTap: () {
                              Navigator.pop(context);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) =>
                                      const ProfileScreen(),
                                ),
                              );
                            },
                          ),
                          const Divider(height: 1),
                          _buildMenuItem(
                            icon: Icons.logout,
                            title:
                                AppLocalizations.of(context)?.logout ??
                                'تسجيل الخروج',
                            color: Colors.red,
                            onTap: () async {
                              Navigator.pop(context);
                              try {
                                // Logout will always clear local storage, even if API fails
                                await _apiService.logout();
                              } catch (e) {
                                // Even if logout API fails, local storage is cleared
                                print('⚠️ Logout error (ignored): $e');
                              }
                              
                              // Always navigate to login, regardless of API result
                              if (mounted) {
                                Navigator.pushReplacementNamed(
                                  context,
                                  '/login',
                                );
                              }
                            },
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
      ),
    );
  }

  @override
  void dispose() {
    _scootersUpdateTimer?.cancel();
    _mapController?.dispose();
    super.dispose();
  }
}
