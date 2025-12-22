class AppConstants {
  // App Info
  static const String appName = 'LinerScoot';
  static const String appVersion = '1.0.0';
  
  // Colors
  static const int primaryColor = 0xFFFFA836; // Orange
  static const int secondaryColor = 0xFF000000; // Black
  static const int backgroundColor = 0xFFFFFFFF; // White
  
  // Map Settings
  static const double defaultZoom = 15.0;
  static const double nearbyRadius = 5000; // 5km in meters
  
  // Trip Settings
  static const int maxTripDurationMinutes = 120;
  static const double maxTripCost = 500.0;
  
  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String languageKey = 'language';
  
  // Languages
  static const String defaultLanguage = 'ar';
  static const List<String> supportedLanguages = ['ar', 'en'];
}

