import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/storage_service.dart';
import '../../../core/services/api_service.dart';
import '../screens/onboarding_screen.dart';
import '../../home/screens/home_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  VideoPlayerController? _controller;
  bool _isVideoInitialized = false;
  bool _hasError = false;
  final _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _initializeVideo();
  }

  Future<void> _initializeVideo() async {
    try {
      _controller = VideoPlayerController.asset('assets/images/splash.mp4');
      await _controller!.initialize();
      if (mounted) {
        setState(() {
          _isVideoInitialized = true;
        });
        _controller!.setLooping(false);
        _controller!.play();
        _controller!.addListener(_videoListener);
        
        // Fallback: Navigate after max 5 seconds even if video doesn't finish
        Future.delayed(const Duration(seconds: 5), () {
          if (mounted && _controller != null && _controller!.value.isPlaying) {
            _navigateToNext();
          }
        });
      }
    } catch (e) {
      // If video fails to load, show logo and continue
      if (mounted) {
        setState(() {
          _hasError = true;
        });
        // Wait 2 seconds then navigate
        Future.delayed(const Duration(seconds: 2), () {
          if (mounted) {
            _navigateToNext();
          }
        });
      }
    }
  }

  void _videoListener() {
    if (_controller != null &&
        _controller!.value.position >= _controller!.value.duration &&
        _controller!.value.duration.inMilliseconds > 0) {
      if (!_controller!.value.isPlaying) {
        _navigateToNext();
      }
    }
  }

  Future<void> _navigateToNext() async {
    // Remove listener to prevent multiple calls
    if (_controller != null) {
      _controller!.removeListener(_videoListener);
    }

    // Check if user is logged in (has valid token)
    final token = await _apiService.getToken();
    bool isLoggedIn = false;

    if (token != null && token.isNotEmpty) {
      // Verify token by trying to get user data
      try {
        await _apiService.getCurrentUser();
        isLoggedIn = true;
      } catch (e) {
        // Token is invalid, clear it
        await _apiService.logout();
        isLoggedIn = false;
      }
    }

    if (!mounted) return;

    if (isLoggedIn) {
      // User is logged in, go directly to home
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const HomeScreen()),
      );
      return;
    }

    // Check if onboarding is completed
    final bool onboardingCompleted = await StorageService.isOnboardingCompleted();

    if (!mounted) return;

    if (onboardingCompleted) {
      // Navigate to login
      Navigator.pushReplacementNamed(context, '/login');
    } else {
      // Navigate to onboarding
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const OnboardingScreen()),
      );
    }
  }

  @override
  void dispose() {
    if (_controller != null) {
      _controller!.removeListener(_videoListener);
      _controller!.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white, // White background
      body: Center(
        child: _hasError
            ? Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Show logo if video fails
                  Image.asset(
                    'assets/images/logo.png',
                    height: 120,
                    width: 120,
                  ),
                  const SizedBox(height: 24),
                  const CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      Color(AppConstants.primaryColor),
                    ),
                  ),
                ],
              )
            : _isVideoInitialized && _controller != null
                ? AspectRatio(
                    aspectRatio: _controller!.value.aspectRatio,
                    child: VideoPlayer(_controller!),
                  )
                : const CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      Color(AppConstants.primaryColor),
                    ),
                  ),
      ),
    );
  }
}
