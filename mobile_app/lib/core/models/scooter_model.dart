class ScooterModel {
  final int id;
  final String code;
  final double latitude;
  final double longitude;
  final int batteryPercentage;
  final bool isLocked;
  final bool isAvailable;
  final String? status;
  final double? distance; // Distance from user in meters

  ScooterModel({
    required this.id,
    required this.code,
    required this.latitude,
    required this.longitude,
    required this.batteryPercentage,
    required this.isLocked,
    required this.isAvailable,
    this.status,
    this.distance,
  });

  factory ScooterModel.fromJson(Map<String, dynamic> json) {
    // Scooter is available if it's locked AND status is available
    final isLocked = json['is_locked'] ?? json['lock_status'] ?? false;
    final status = json['status']?.toString() ?? '';
    final isAvailableFromJson = json['is_available'];
    
    // If is_available is explicitly provided, use it; otherwise calculate based on is_locked and status
    final isAvailable = isAvailableFromJson != null 
        ? (isAvailableFromJson == true || isAvailableFromJson == 'true')
        : (isLocked == true && status == 'available');
    
    return ScooterModel(
      id: json['id'] ?? json['scooter_id'] ?? 0,
      code: json['code'] ?? json['scooter_code'] ?? '',
      latitude: (json['latitude'] ?? json['lat'] ?? 0.0).toDouble(),
      longitude: (json['longitude'] ?? json['lng'] ?? json['lon'] ?? 0.0).toDouble(),
      batteryPercentage: json['battery_percentage'] ?? json['battery'] ?? 0,
      isLocked: isLocked,
      isAvailable: isAvailable,
      status: status.isNotEmpty ? status : null,
      distance: json['distance']?.toDouble(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'code': code,
      'latitude': latitude,
      'longitude': longitude,
      'battery_percentage': batteryPercentage,
      'is_locked': isLocked,
      'is_available': isAvailable,
      'status': status,
      'distance': distance,
    };
  }
}

