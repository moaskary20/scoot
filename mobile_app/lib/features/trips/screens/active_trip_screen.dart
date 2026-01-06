import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/location_service.dart';
import '../../../core/l10n/app_localizations.dart';

class ActiveTripScreen extends StatefulWidget {
  final int tripId;
  final String scooterCode;
  final DateTime startTime;

  const ActiveTripScreen({
    super.key,
    required this.tripId,
    required this.scooterCode,
    required this.startTime,
  });

  @override
  State<ActiveTripScreen> createState() => _ActiveTripScreenState();
}

class _ActiveTripScreenState extends State<ActiveTripScreen> {
  final ApiService _apiService = ApiService();
  final LocationService _locationService = LocationService();
  final ImagePicker _imagePicker = ImagePicker();
  
  Timer? _timer;
  Timer? _locationTimer;
  int _durationSeconds = 0;
  int _durationMinutes = 0;
  bool _isCompleting = false;
  Position? _currentPosition;
  Position? _previousPosition;
  DateTime _actualStartTime = DateTime.now(); // Initialize with current time to prevent errors
  int _batteryPercentage = 0;
  double _currentSpeed = 0.0; // Speed in m/s
  bool _isLoadingBattery = true;
  double _currentCost = 0.0; // Current trip cost
  bool _hasError = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    try {
      print('🚀 ActiveTripScreen initialized');
      print('📊 Trip ID: ${widget.tripId}');
      print('🛴 Scooter Code: ${widget.scooterCode}');
      print('⏰ Start Time from backend: ${widget.startTime}');
      
      // Use current time as actual start time to avoid timezone issues
      // Or use the backend time if it's recent (within 2 minutes)
      final now = DateTime.now();
      final backendTime = widget.startTime.isUtc ? widget.startTime.toLocal() : widget.startTime;
      final timeDiff = now.difference(backendTime).inMinutes.abs();
      
      print('🕐 Current time: $now');
      print('🕐 Backend time: $backendTime');
      print('🕐 Time difference: $timeDiff minutes');
      
      if (timeDiff > 2 || timeDiff < 0) {
        // If backend time is more than 2 minutes different or negative, use current time
        _actualStartTime = now;
        print('⚠️ Backend time seems incorrect (diff: $timeDiff min), using current time');
      } else {
        _actualStartTime = backendTime;
        print('✅ Using backend start time (diff: $timeDiff min)');
      }
      
      print('⏰ Actual Start Time: $_actualStartTime');
      
      // Initialize safely
      _startTimer();
      
      // Start location updates with error handling
      try {
        _startLocationUpdates();
      } catch (e) {
        print('⚠️ Error starting location updates: $e');
      }
      
      // Load battery with error handling
      try {
        _loadScooterBattery();
      } catch (e) {
        print('⚠️ Error loading battery: $e');
      }
      
      // Update battery and cost periodically (every 10 seconds for real-time updates)
      Timer.periodic(const Duration(seconds: 10), (timer) {
        if (mounted) {
          try {
            _loadScooterBattery();
          } catch (e) {
            print('⚠️ Error in periodic battery/cost update: $e');
          }
        } else {
          timer.cancel();
        }
      });
    } catch (e, stackTrace) {
      print('❌ Error in initState: $e');
      print('Stack trace: $stackTrace');
      // Set default values to prevent black screen
      _actualStartTime = DateTime.now();
      _hasError = true;
      _errorMessage = e.toString();
      try {
        _startTimer();
      } catch (timerError) {
        print('⚠️ Error starting timer: $timerError');
      }
      // Force rebuild to show error or content
      if (mounted) {
        setState(() {});
      }
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _locationTimer?.cancel();
    super.dispose();
  }

  void _startTimer() {
    _updateDuration();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      _updateDuration();
    });
  }

  void _updateDuration() {
    final now = DateTime.now();
    final duration = now.difference(_actualStartTime);
    final totalSeconds = duration.inSeconds;
    final minutes = totalSeconds ~/ 60;
    final seconds = totalSeconds % 60;
    
    // Ensure duration is not negative
    if (totalSeconds < 0) {
      print('⚠️ Negative duration detected, resetting start time');
      _actualStartTime = now;
      setState(() {
        _durationSeconds = 0;
        _durationMinutes = 0;
      });
      return;
    }
    
    setState(() {
      _durationSeconds = totalSeconds;
      _durationMinutes = minutes;
    });
  }

  void _startLocationUpdates() {
    _getCurrentLocation();
    // Update location every 3 seconds to get speed
    _locationTimer = Timer.periodic(const Duration(seconds: 3), (timer) {
      _getCurrentLocation();
    });
  }

  Future<void> _getCurrentLocation() async {
    try {
      final position = await _locationService.getCurrentLocation();
      
      // Calculate speed if we have previous position
      double speed = 0.0;
      if (_previousPosition != null && position.speed > 0) {
        // Use GPS speed if available and valid
        speed = position.speed; // Speed in m/s
      } else if (_previousPosition != null) {
        // Calculate speed from distance and time
        final distance = Geolocator.distanceBetween(
          _previousPosition!.latitude,
          _previousPosition!.longitude,
          position.latitude,
          position.longitude,
        );
        final timeDiff = position.timestamp.difference(_previousPosition!.timestamp).inSeconds;
        if (timeDiff > 0) {
          speed = distance / timeDiff; // m/s
        }
      }
      
      setState(() {
        _previousPosition = _currentPosition;
        _currentPosition = position;
        _currentSpeed = speed;
      });
    } catch (e) {
      print('Error getting location: $e');
    }
  }

  Future<void> _loadScooterBattery() async {
    try {
      print('🔋 Loading scooter battery and cost from backend...');
      final activeTrip = await _apiService.getActiveTrip();
      
      if (activeTrip != null) {
        print('📦 Active trip data: $activeTrip');
        print('📦 Full response keys: ${activeTrip.keys.toList()}');
        
        // Get current cost
        final currentCost = (activeTrip['current_cost'] ?? 0.0).toDouble();
        
        // Check if scooter data exists
        if (activeTrip['scooter'] != null) {
          final scooterData = activeTrip['scooter'];
          print('📦 Scooter data: $scooterData');
          print('📦 Scooter data keys: ${scooterData.keys.toList()}');
          
          final battery = scooterData['battery_percentage'] ?? 0;
          final scooterId = scooterData['id'];
          final scooterCode = scooterData['code'];
          
          print('✅ Battery and cost data from backend:');
          print('   - Scooter ID: $scooterId');
          print('   - Scooter Code: $scooterCode');
          print('   - Battery Percentage: $battery% (type: ${battery.runtimeType})');
          print('   - Current Cost: $currentCost ج.م');
          
          if (mounted) {
            setState(() {
              _batteryPercentage = battery is int ? battery : (battery is String ? int.tryParse(battery) ?? 0 : 0);
              _currentCost = currentCost;
              _isLoadingBattery = false;
            });
          }
        } else {
          print('⚠️ Scooter data not found in active trip response');
          print('⚠️ Available keys in activeTrip: ${activeTrip.keys.toList()}');
          if (mounted) {
            setState(() {
              _currentCost = currentCost;
              _isLoadingBattery = false;
            });
          }
        }
      } else {
        print('⚠️ No active trip found');
        if (mounted) {
          setState(() {
            _isLoadingBattery = false;
          });
        }
      }
    } catch (e, stackTrace) {
      print('❌ Error loading battery and cost from backend: $e');
      print('❌ Stack trace: $stackTrace');
      if (mounted) {
        setState(() {
          _isLoadingBattery = false;
        });
      }
    }
  }

  Future<void> _completeTrip() async {
    if (_isCompleting) return;

    // Show confirmation dialog
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(AppLocalizations.of(context)?.closeTrip ?? 'إغلاق الرحلة'),
        content: Text(
          '${AppLocalizations.of(context)?.confirmCloseTrip ?? 'هل أنت متأكد من إغلاق الرحلة؟'}\n\n'
          '${AppLocalizations.of(context)?.duration ?? 'المدة'}: $_durationMinutes ${AppLocalizations.of(context)?.minutes ?? 'دقيقة'}\n'
          '${AppLocalizations.of(context)?.costCalculationMessage ?? 'سيتم حساب التكلفة حسب المنطقة الجغرافية'}',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(AppLocalizations.of(context)?.cancel ?? 'إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: Text(AppLocalizations.of(context)?.closeTrip ?? 'إغلاق الرحلة'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    // Stop timer when starting completion
    _timer?.cancel();

    setState(() {
      _isCompleting = true;
    });

    try {
      // Get current location
      await _getCurrentLocation();
      
      // Take photo
      final XFile? image = await _imagePicker.pickImage(
        source: ImageSource.camera,
        imageQuality: 85,
      );

      if (image == null) {
        // User cancelled photo, still complete trip
        await _submitCompletion(null);
      } else {
        await _submitCompletion(image.path);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)?.errorOccurred ?? 'حدث خطأ'}: $e'),
            backgroundColor: Colors.red,
          ),
        );
        setState(() {
          _isCompleting = false;
        });
      }
    }
  }

  Future<void> _submitCompletion(String? imagePath) async {
    try {
      final result = await _apiService.completeTrip(
        widget.tripId,
        _currentPosition?.latitude,
        _currentPosition?.longitude,
        imagePath,
      );

      // Get cost from backend response
      final cost = result['cost'] ?? 0.0;
      final durationMinutes = result['duration_minutes'] ?? _durationMinutes;

      // Reset completing state
      if (mounted) {
        setState(() {
          _isCompleting = false;
        });
      }

      // Show completion dialog with cost and return button
      if (mounted) {
        // Store navigator before showing dialog
        final navigator = Navigator.of(context);
        
        await showDialog(
          context: context,
          barrierDismissible: false,
          builder: (dialogContext) => AlertDialog(
            title: const Text(
              'تم إغلاق الرحلة بنجاح',
              style: TextStyle(
                color: Colors.green,
                fontWeight: FontWeight.bold,
              ),
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 10),
                _buildInfoRow('المدة', '$durationMinutes دقيقة'),
                const SizedBox(height: 10),
                _buildInfoRow('التكلفة', '${cost.toStringAsFixed(2)} ج.م'),
              ],
            ),
            actions: [
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    // Close dialog first
                    Navigator.of(dialogContext).pop();
                    
                    // Wait a bit to ensure dialog is closed
                    await Future.delayed(const Duration(milliseconds: 300));
                    
                    // Navigate back to home screen using stored navigator
                    if (mounted) {
                      try {
                        // Use stored navigator to pop until home screen
                        navigator.popUntil((route) {
                          print('📍 Checking route: ${route.settings.name}, isFirst: ${route.isFirst}');
                          return route.isFirst;
                        });
                        print('✅ Successfully navigated to home screen');
                      } catch (e) {
                        print('⚠️ Navigation failed: $e');
                        // Fallback: try with rootNavigator
                        if (mounted) {
                          try {
                            Navigator.of(context, rootNavigator: true).popUntil((route) => route.isFirst);
                            print('✅ Successfully navigated using rootNavigator');
                          } catch (e2) {
                            print('⚠️ RootNavigator also failed: $e2');
                            // Last resort: pop current route multiple times
                            if (mounted) {
                              int popCount = 0;
                              while (navigator.canPop() && popCount < 10) {
                                navigator.pop();
                                popCount++;
                              }
                              print('⚠️ Popped $popCount routes as last resort');
                            }
                          }
                        }
                      }
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Color(AppConstants.primaryColor),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text(
                    'العودة إلى الصفحة الرئيسية',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      print('❌ Error in _submitCompletion: $e');
      if (mounted) {
        // Restart timer if error occurs
        if (_timer == null || !_timer!.isActive) {
          _startTimer();
        }
        
        setState(() {
          _isCompleting = false;
        });
        
        // Show error dialog with option to go back anyway
        final shouldGoBack = await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('خطأ في إغلاق الرحلة'),
            content: Text(
              'حدث خطأ أثناء إغلاق الرحلة:\n\n$e\n\n'
              'هل تريد العودة إلى الصفحة الرئيسية؟',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('البقاء هنا'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.orange,
                ),
                child: const Text('العودة للصفحة الرئيسية'),
              ),
            ],
          ),
        );
        
        if (shouldGoBack == true && mounted) {
          // Navigate back to home screen even if there was an error
          Navigator.of(context).popUntil((route) => route.isFirst);
        } else {
          // Show error snackbar
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('${AppLocalizations.of(context)?.tripCompletionErrorMessage ?? 'حدث خطأ في إغلاق الرحلة'}: $e'),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 5),
            ),
          );
        }
      }
    }
  }

  String _formatDuration(int totalSeconds) {
    final minutes = totalSeconds ~/ 60;
    final seconds = totalSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w500,
            color: Colors.grey,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(AppConstants.primaryColor),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoCard({
    required IconData icon,
    required String value,
    required String label,
    required Color color,
    String? subtitle,
    Widget? child,
  }) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: color.withOpacity(0.3),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: color.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(
            icon,
            size: 32,
            color: color,
          ),
          const SizedBox(height: 12),
          if (child != null)
            child
          else
            Text(
              value,
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.bold,
                color: color,
                height: 1.2,
              ),
            ),
          const SizedBox(height: 8),
          Text(
            subtitle ?? label,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBatteryIndicator(int percentage) {
    Color batteryColor = _getBatteryColor(percentage);
    return Stack(
      alignment: Alignment.center,
      children: [
        SizedBox(
          width: 60,
          height: 30,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: percentage / 100,
              backgroundColor: Colors.grey[200],
              valueColor: AlwaysStoppedAnimation<Color>(batteryColor),
              minHeight: 30,
            ),
          ),
        ),
        Text(
          '$percentage%',
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.bold,
            color: percentage > 50 ? Colors.white : Colors.black87,
          ),
        ),
      ],
    );
  }

  Color _getBatteryColor(int percentage) {
    if (percentage >= 50) {
      return Colors.green;
    } else if (percentage >= 20) {
      return Colors.orange;
    } else {
      return Colors.red;
    }
  }

  @override
  Widget build(BuildContext context) {
    // Show error widget if there's a critical error
    if (_hasError && _errorMessage != null) {
      return Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.error_outline,
                    size: 64,
                    color: Colors.orange,
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'تحذير: حدث خطأ في التهيئة',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _errorMessage!,
                    style: const TextStyle(
                      fontSize: 14,
                      color: Colors.grey,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: () {
                      // Try to continue anyway
                      setState(() {
                        _hasError = false;
                      });
                    },
                    child: const Text('المحاولة مرة أخرى'),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    try {
      return Directionality(
        textDirection: TextDirection.rtl,
        child: PopScope(
          canPop: false,
          onPopInvoked: (didPop) {
            if (didPop) return;
            // Show warning when user tries to go back
            try {
              showDialog(
                context: context,
                builder: (context) => AlertDialog(
                  title: Text(AppLocalizations.of(context)?.warning ?? 'تحذير'),
                  content: Text(
                    AppLocalizations.of(context)?.cannotCloseTripMessage ?? 'لا يمكنك إغلاق هذه الشاشة أثناء الرحلة. استخدم زر "إغلاق الرحلة" لإتمام الرحلة.',
                  ),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: Text(AppLocalizations.of(context)?.ok ?? 'حسناً'),
                    ),
                  ],
                ),
              );
            } catch (e) {
              print('⚠️ Error showing dialog: $e');
            }
          },
          child: Scaffold(
            backgroundColor: Colors.white,
            body: SafeArea(
              child: Column(
                children: [
                // Header
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Color(AppConstants.primaryColor),
                    borderRadius: const BorderRadius.only(
                      bottomLeft: Radius.circular(30),
                      bottomRight: Radius.circular(30),
                    ),
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.close, color: Colors.white),
                            onPressed: () {
                              // Show warning
                              showDialog(
                                context: context,
                                builder: (context) => AlertDialog(
                                  title: Text(AppLocalizations.of(context)?.warning ?? 'تحذير'),
                                  content: Text(
                                    AppLocalizations.of(context)?.cannotCancelTripMessage ?? 'لا يمكنك إلغاء الرحلة من هنا. يرجى استخدام زر "إغلاق الرحلة" لإتمامها.',
                                  ),
                                  actions: [
                                    TextButton(
                                      onPressed: () => Navigator.pop(context),
                                      child: Text(AppLocalizations.of(context)?.ok ?? 'حسناً'),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                          const Spacer(),
                          const Text(
                            'رحلة نشطة',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const Spacer(),
                          const SizedBox(width: 48),
                        ],
                      ),
                      const SizedBox(height: 20),
                      // Scooter code
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 12,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(
                              Icons.electric_scooter,
                              color: Colors.white,
                              size: 24,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'سكوتر ${widget.scooterCode}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // Content
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      children: [
                        // Duration Card
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                Color(AppConstants.primaryColor).withOpacity(0.1),
                                Color(AppConstants.primaryColor).withOpacity(0.05),
                              ],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: Color(AppConstants.primaryColor).withOpacity(0.2),
                              width: 1,
                            ),
                          ),
                          child: Column(
                            children: [
                              const Icon(
                                Icons.timer_outlined,
                                size: 48,
                                color: Color(AppConstants.primaryColor),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                _formatDuration(_durationSeconds),
                                style: TextStyle(
                                  fontSize: 56,
                                  fontWeight: FontWeight.bold,
                                  color: Color(AppConstants.primaryColor),
                                  letterSpacing: 2,
                                  height: 1.2,
                                ),
                              ),
                              const SizedBox(height: 8),
                              const Text(
                                'مدة الرحلة',
                                style: TextStyle(
                                  fontSize: 16,
                                  color: Colors.grey,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 20),

                        // Battery, Speed, and Cost Row
                        Row(
                          children: [
                            // Battery Card
                            Expanded(
                              child: _buildInfoCard(
                                icon: Icons.battery_charging_full,
                                value: _isLoadingBattery ? '--' : '$_batteryPercentage%',
                                label: 'البطارية',
                                color: _getBatteryColor(_batteryPercentage),
                                child: _isLoadingBattery
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(strokeWidth: 2),
                                      )
                                    : _buildBatteryIndicator(_batteryPercentage),
                              ),
                            ),
                            const SizedBox(width: 12),
                            // Speed Card
                            Expanded(
                              child: _buildInfoCard(
                                icon: Icons.speed,
                                value: '${(_currentSpeed * 3.6).toStringAsFixed(0)}',
                                label: 'كم/س',
                                color: Colors.blue,
                                subtitle: 'السرعة',
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 12),

                        // Cost Card
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                Colors.green.withOpacity(0.1),
                                Colors.green.withOpacity(0.05),
                              ],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: Colors.green.withOpacity(0.3),
                              width: 1.5,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.green.withOpacity(0.1),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(
                                Icons.attach_money,
                                size: 32,
                                color: Colors.green,
                              ),
                              const SizedBox(width: 12),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'التكلفة الحالية',
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: Colors.grey,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${_currentCost.toStringAsFixed(2)} ج.م',
                                    style: const TextStyle(
                                      fontSize: 28,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.green,
                                      height: 1.2,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 30),

                        // Complete button
                        SizedBox(
                          width: double.infinity,
                          height: 56,
                          child: ElevatedButton(
                            onPressed: _isCompleting ? null : _completeTrip,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              foregroundColor: Colors.white,
                              elevation: 4,
                              shadowColor: Colors.red.withOpacity(0.4),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                            ),
                            child: _isCompleting
                                ? const SizedBox(
                                    width: 24,
                                    height: 24,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                    ),
                                  )
                                : const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.stop_circle, size: 24),
                                      SizedBox(width: 8),
                                      Text(
                                        'إغلاق الرحلة',
                                        style: TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
    } catch (e, stackTrace) {
      print('❌ Error in build method: $e');
      print('Stack trace: $stackTrace');
      // Return error widget instead of black screen
      return Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(
                  Icons.error_outline,
                  size: 64,
                  color: Colors.red,
                ),
                const SizedBox(height: 16),
                const Text(
                  'حدث خطأ في تحميل الشاشة',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Text(
                    e.toString(),
                    style: const TextStyle(
                      fontSize: 14,
                      color: Colors.grey,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                  },
                  child: const Text('العودة'),
                ),
              ],
            ),
          ),
        ),
      );
    }
  }
}

