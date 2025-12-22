import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:geolocator/geolocator.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/scooter_model.dart';
import '../../../core/models/user_model.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/location_service.dart';
import 'riding_guide_screen.dart';
import '../../wallet/screens/wallet_screen.dart';
import '../../wallet/screens/free_balance_screen.dart';
import '../../trips/screens/trips_screen.dart';

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
  
  Position? _currentPosition;
  List<ScooterModel> _scooters = [];
  bool _isLoading = true;
  Set<Marker> _markers = {};
  UserModel? _currentUser;
  bool _isLoadingUser = false;

  @override
  void initState() {
    super.initState();
    _initializeLocation();
    _loadUserData(); // Load user data on screen init
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
            content: Text('تعذر الحصول على موقعك. يتم استخدام موقع افتراضي.\n$e'),
            backgroundColor: Colors.orange,
            duration: const Duration(seconds: 4),
          ),
        );
        
        await _loadScooters();
      }
    }
  }

  Future<void> _loadScooters() async {
    if (_currentPosition == null || !mounted) return;

    if (mounted) {
      setState(() {
        _isLoading = true;
      });
    }

    try {
      final scooters = await _apiService.getNearbyScooters(
        _currentPosition!.latitude,
        _currentPosition!.longitude,
      );

      if (mounted) {
        setState(() {
          _scooters = scooters;
          _updateMarkers();
          _isLoading = false;
        });
        
        // Show message if no scooters found (but not an error)
        if (scooters.isEmpty && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('لا توجد سكوترات متاحة في المنطقة القريبة'),
              backgroundColor: Colors.blue,
              duration: Duration(seconds: 2),
            ),
          );
        }
      }
    } catch (e) {
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
              content: Text('حدث خطأ في تحميل السكوترات'),
              backgroundColor: Colors.orange,
              duration: const Duration(seconds: 3),
            ),
          );
        }
      }
    }
  }

  void _updateMarkers() {
    _markers.clear();

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
          infoWindow: const InfoWindow(title: 'موقعك الحالي'),
        ),
      );
    }

    // Add scooter markers
    for (var scooter in _scooters) {
      _markers.add(
        Marker(
          markerId: MarkerId('scooter_${scooter.id}'),
          position: LatLng(scooter.latitude, scooter.longitude),
          icon: BitmapDescriptor.defaultMarkerWithHue(
            scooter.isAvailable
                ? BitmapDescriptor.hueGreen
                : BitmapDescriptor.hueRed,
          ),
          infoWindow: InfoWindow(
            title: 'سكوتر ${scooter.code}',
            snippet: 'البطارية: ${scooter.batteryPercentage}%',
          ),
          onTap: () {
            _showScooterDetails(scooter);
          },
        ),
      );
    }
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
                        scooter.isAvailable ? 'متاح' : 'غير متاح',
                        style: TextStyle(
                          color: scooter.isAvailable ? Colors.green : Colors.red,
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
                  scooter.isLocked ? 'مقفول' : 'مفتوح',
                  scooter.isLocked ? Colors.orange : Colors.blue,
                ),
                if (scooter.distance != null) ...[
                  const SizedBox(width: 20),
                  _buildInfoItem(
                    Icons.location_on,
                    '${(scooter.distance! / 1000).toStringAsFixed(1)} كم',
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
                child: const Text(
                  'ابدأ الرحلة',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
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
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w500,
        ),
      ),
      onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
    );
  }

  void _startTrip(ScooterModel scooter) {
    // TODO: Implement start trip functionality
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('بدء الرحلة مع سكوتر ${scooter.code}'),
        backgroundColor: Colors.green,
      ),
    );
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
        title: const Text('المساعدة'),
        content: const Text('كيف يمكننا مساعدتك؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إغلاق'),
          ),
        ],
      ),
    );
  }

  void _openMenu() async {
    // Load user data first, then open drawer
    await _loadUserData();
    if (mounted) {
      _scaffoldKey.currentState?.openEndDrawer();
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
            content: Text('حدث خطأ في تحميل بيانات المستخدم: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 2),
          ),
        );
      }
    }
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

  void _startTripFromGuide() {
    // TODO: Implement QR scan and start trip
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('بدء مسح QR Code وبدء الرحلة'),
        backgroundColor: Colors.green,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
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
              myLocationEnabled: true,
              myLocationButtonEnabled: false,
              mapType: MapType.normal,
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
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Re-center Button
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

                    // Scan and Start Trip Button
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: ElevatedButton.icon(
                          onPressed: () {
                            _showRidingGuide();
                          },
                          icon: const Icon(Icons.qr_code_scanner),
                          label: const Text(
                            'سكان وابدأ الرحلة',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
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

                    // Menu Button
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
                  ],
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
      endDrawer: Directionality(
        textDirection: TextDirection.rtl,
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
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            const Text(
                                              'مستخدم',
                                              style: TextStyle(
                                                fontSize: 20,
                                                fontWeight: FontWeight.bold,
                                                color: Colors.black,
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                              'لا توجد بيانات متاحة',
                                              style: TextStyle(
                                                fontSize: 14,
                                                color: Colors.grey[600],
                                              ),
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
                                      child: _currentUser!.avatar != null &&
                                              _currentUser!.avatar!.isNotEmpty
                                          ? CachedNetworkImage(
                                              imageUrl: _getImageUrl(_currentUser!.avatar),
                                              width: 70,
                                              height: 70,
                                              fit: BoxFit.cover,
                                              placeholder: (context, url) => Container(
                                                width: 70,
                                                height: 70,
                                                color: Colors.grey[300],
                                                child: Icon(
                                                  Icons.person,
                                                  size: 40,
                                                  color: Colors.grey[600],
                                                ),
                                              ),
                                              errorWidget: (context, url, error) => Container(
                                                width: 70,
                                                height: 70,
                                                color: Colors.grey[300],
                                                child: Icon(
                                                  Icons.person,
                                                  size: 40,
                                                  color: Colors.grey[600],
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
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            _currentUser!.name.isNotEmpty 
                                                ? _currentUser!.name 
                                                : 'مستخدم',
                                            style: const TextStyle(
                                              fontSize: 20,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.black,
                                            ),
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
                                              Text(
                                                (_currentUser!.phone != null && _currentUser!.phone!.isNotEmpty)
                                                    ? _currentUser!.phone!
                                                    : 'لا يوجد رقم هاتف',
                                                style: TextStyle(
                                                  fontSize: 14,
                                                  color: Colors.grey[700],
                                                ),
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
                                        Color(AppConstants.primaryColor).withOpacity(0.8),
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
                                        // TODO: Navigate to wallet top-up screen
                                      },
                                      borderRadius: BorderRadius.circular(25),
                                      child: Padding(
                                        padding: const EdgeInsets.symmetric(
                                          vertical: 12,
                                          horizontal: 16,
                                        ),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            const Icon(
                                              Icons.star,
                                              color: Colors.white,
                                              size: 20,
                                            ),
                                            const SizedBox(width: 8),
                                            const Text(
                                              'اشحن رصيدك',
                                              style: TextStyle(
                                                color: Colors.white,
                                                fontSize: 16,
                                                fontWeight: FontWeight.bold,
                                              ),
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
                        title: 'رحلاتي',
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
                        title: 'محفظة',
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
                        icon: Icons.account_balance,
                        title: 'رصيد مجاني',
                        color: Color(AppConstants.primaryColor),
                        onTap: () {
                          Navigator.pop(context);
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const FreeBalanceScreen(),
                            ),
                          );
                        },
                      ),
                      _buildMenuItem(
                        icon: Icons.directions_bike,
                        title: 'إزاي تركب لينر سكوت',
                        color: Color(AppConstants.primaryColor),
                        onTap: () {
                          Navigator.pop(context);
                          _showRidingGuide();
                        },
                      ),
                      _buildMenuItem(
                        icon: Icons.language,
                        title: 'اللغة',
                        color: Color(AppConstants.primaryColor),
                        onTap: () {
                          Navigator.pop(context);
                          // TODO: Navigate to language settings screen
                        },
                      ),
                      const Divider(height: 1),
                      _buildMenuItem(
                        icon: Icons.logout,
                        title: 'تسجيل الخروج',
                        color: Colors.red,
                        onTap: () async {
                          Navigator.pop(context);
                          try {
                            await _apiService.logout();
                            if (mounted) {
                              Navigator.pushReplacementNamed(context, '/login');
                            }
                          } catch (e) {
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('حدث خطأ أثناء تسجيل الخروج: $e'),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
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
      ),
    );
  }

  @override
  void dispose() {
    _mapController?.dispose();
    super.dispose();
  }
}
