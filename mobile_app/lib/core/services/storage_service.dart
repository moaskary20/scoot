import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const String _firstLaunchKey = 'first_launch';
  static const String _onboardingCompletedKey = 'onboarding_completed';

  static Future<SharedPreferences> get _prefs async =>
      await SharedPreferences.getInstance();

  // Check if this is the first launch
  static Future<bool> isFirstLaunch() async {
    final prefs = await _prefs;
    return prefs.getBool(_firstLaunchKey) ?? true;
  }

  // Mark first launch as completed
  static Future<void> setFirstLaunchCompleted() async {
    final prefs = await _prefs;
    await prefs.setBool(_firstLaunchKey, false);
  }

  // Check if onboarding is completed
  static Future<bool> isOnboardingCompleted() async {
    final prefs = await _prefs;
    return prefs.getBool(_onboardingCompletedKey) ?? false;
  }

  // Mark onboarding as completed
  static Future<void> setOnboardingCompleted() async {
    final prefs = await _prefs;
    await prefs.setBool(_onboardingCompletedKey, true);
  }

  // Reset onboarding (for testing)
  static Future<void> resetOnboarding() async {
    final prefs = await _prefs;
    await prefs.remove(_onboardingCompletedKey);
    await prefs.remove(_firstLaunchKey);
  }
}

