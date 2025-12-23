class ApiConstants {
  // Base URL - Production server
  // For development/testing, you can use:
  // - Android Emulator: http://10.0.2.2:8000/api
  // - Android Device on same network: http://192.168.x.x:8000/api
  
  // Production server
  static const String baseUrl = 'https://linerscoot.com/api';
  
  // API Endpoints
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String logout = '/auth/logout';
  static const String user = '/auth/user';
  static const String forgotPassword = '/auth/forgot-password';
  
  // Scooters
  static const String scooters = '/scooters';
  static const String scootersNearby = '/scooters/nearby';
  static const String scooterLock = '/scooters/{id}/lock';
  static const String scooterUnlock = '/scooters/{id}/unlock';
  
  // Trips
  static const String trips = '/trips';
  static const String startTrip = '/trips/start';
  static const String completeTrip = '/trips/{id}/complete';
  static const String cancelTrip = '/trips/{id}/cancel';
  static const String activeTrip = '/trips/active';
  
  // Wallet
  static const String wallet = '/wallet';
  static const String walletTransactions = '/wallet/transactions';
  static const String walletTopUp = '/wallet/top-up';
  
  // Subscriptions
  static const String subscriptions = '/subscriptions';
  
  // Coupons
  static const String coupons = '/coupons';
  static const String validateCoupon = '/wallet/validate-promo';
  
  // Cards
  static const String saveCard = '/wallet/cards';
  static const String getCards = '/wallet/cards';
  
  // Referral
  static const String referral = '/referral';
  
  // Geo Zones
  static const String geoZones = '/geo-zones';
  
  // WebSocket - Production server
  // Note: Update port if your WebSocket server uses a different port
  static const String wsUrl = 'wss://linerscoot.com:8080';
}

