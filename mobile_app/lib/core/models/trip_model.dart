class PenaltyModel {
  final int id;
  final String type;
  final String title;
  final String? description;
  final double amount;
  final String status;
  final DateTime? appliedAt;

  PenaltyModel({
    required this.id,
    required this.type,
    required this.title,
    this.description,
    required this.amount,
    required this.status,
    this.appliedAt,
  });

  factory PenaltyModel.fromJson(Map<String, dynamic> json) {
    final title = json['title']?.toString() ?? '';
    final description = json['description']?.toString();
    
    // Log penalty data for debugging
    print('📋 Parsing PenaltyModel:');
    print('  - id: ${json['id']}');
    print('  - title: $title (type: ${title.runtimeType})');
    print('  - description: $description (type: ${description?.runtimeType})');
    print('  - type: ${json['type']}');
    print('  - amount: ${json['amount']}');
    
    return PenaltyModel(
      id: _toInt(json['id']),
      type: json['type']?.toString() ?? '',
      title: title,
      description: description,
      amount: _toDouble(json['amount']),
      status: json['status']?.toString() ?? 'pending',
      appliedAt: json['applied_at'] != null && json['applied_at'].toString().isNotEmpty
          ? DateTime.parse(json['applied_at'].toString())
          : null,
    );
  }

  static int _toInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) {
      return int.tryParse(value) ?? 0;
    }
    return 0;
  }

  static double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) {
      return double.tryParse(value) ?? 0.0;
    }
    return 0.0;
  }
}

class TripModel {
  final int id;
  final String status;
  final DateTime startTime;
  final DateTime? endTime;
  final int? durationMinutes;
  final double cost;
  final double baseCost;
  final double discountAmount;
  final double penaltyAmount;
  final bool zoneExitDetected;
  final String? zoneExitDetails;
  final double paidAmount;
  final double remainingAmount;
  final String paymentStatus;
  final String? scooterCode;
  final PenaltyModel? penalty; // Penalty details
  final double? startLatitude;
  final double? startLongitude;
  final double? endLatitude;
  final double? endLongitude;
  final String? notes;

  TripModel({
    required this.id,
    required this.status,
    required this.startTime,
    this.endTime,
    this.durationMinutes,
    required this.cost,
    required this.baseCost,
    required this.discountAmount,
    required this.penaltyAmount,
    required this.zoneExitDetected,
    this.zoneExitDetails,
    required this.paidAmount,
    required this.remainingAmount,
    required this.paymentStatus,
    this.scooterCode,
    this.penalty,
    this.startLatitude,
    this.startLongitude,
    this.endLatitude,
    this.endLongitude,
    this.notes,
  });

  factory TripModel.fromJson(Map<String, dynamic> json) {
    return TripModel(
      id: _toInt(json['id']),
      status: json['status']?.toString() ?? 'active',
      startTime: DateTime.parse(json['start_time']),
      endTime: json['end_time'] != null && json['end_time'] != '' && json['end_time'].toString().isNotEmpty
          ? DateTime.parse(json['end_time'])
          : null,
      durationMinutes: json['duration_minutes'] != null ? _toInt(json['duration_minutes']) : null,
      cost: _toDouble(json['cost']),
      baseCost: _toDouble(json['base_cost']),
      discountAmount: _toDouble(json['discount_amount']),
      penaltyAmount: _toDouble(json['penalty_amount']),
      zoneExitDetected: json['zone_exit_detected'] ?? false,
      zoneExitDetails: json['zone_exit_details']?.toString(),
      paidAmount: _toDouble(json['paid_amount']),
      remainingAmount: _toDouble(json['remaining_amount']),
      paymentStatus: json['payment_status']?.toString() ?? 'unpaid',
      scooterCode: json['scooter_code']?.toString() ?? json['scooter']?['code']?.toString(),
      penalty: json['penalty'] != null && json['penalty'] is Map<String, dynamic>
          ? PenaltyModel.fromJson(json['penalty'] as Map<String, dynamic>)
          : null,
      startLatitude: json['start_latitude'] != null ? _toDouble(json['start_latitude']) : null,
      startLongitude: json['start_longitude'] != null ? _toDouble(json['start_longitude']) : null,
      endLatitude: json['end_latitude'] != null ? _toDouble(json['end_latitude']) : null,
      endLongitude: json['end_longitude'] != null ? _toDouble(json['end_longitude']) : null,
      notes: json['notes']?.toString(),
    );
  }

  static int _toInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) {
      return int.tryParse(value) ?? 0;
    }
    return 0;
  }

  static double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) {
      return double.tryParse(value) ?? 0.0;
    }
    return 0.0;
  }
}


