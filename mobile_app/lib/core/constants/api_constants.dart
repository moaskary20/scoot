class ApiConstants {
  // Base URL - Update this with your Laravel backend URL
  // IMPORTANT: For Android emulator, use: http://10.0.2.2:8000/api
  // For Android device on same network, use your computer's IP: http://192.168.x.x:8000/api
  // To find your IP: On Linux/Mac run: ip addr show | grep "inet " | grep -v 127.0.0.1
  // On Windows run: ipconfig and look for IPv4 Address
  // For production, use: static const String baseUrl = 'https://your-domain.com/api';
  
  // TODO: Replace with your actual server IP address
  // For Android Emulator, use: http://10.0.2.2:8000/api
  // For Android Device on same network, use your computer's IP: http://192.168.x.x:8000/api
  // Current server IP: 192.168.1.44
  // static const String baseUrl = 'http://10.0.2.2:8000/api'; // Android Emulator
  static const String baseUrl = 'http://192.168.1.44:8000/api'; // Android Device (replace with your IP)
  
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
  
  // Wallet
  static const String wallet = '/wallet';
  static const String walletTransactions = '/wallet/transactions';
  static const String walletTopUp = '/wallet/top-up';
  
  // Subscriptions
  static const String subscriptions = '/subscriptions';
  
  // Coupons
  static const String coupons = '/coupons';
  static const String validateCoupon = '/coupons/validate';
  
  // WebSocket
  static const String wsUrl = 'ws://localhost:6001';
  // For production, use: static const String wsUrl = 'wss://your-domain.com/ws';
}

