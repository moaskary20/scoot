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
  });

  factory TripModel.fromJson(Map<String, dynamic> json) {
    return TripModel(
      id: json['id'] as int,
      status: json['status'] ?? 'active',
      startTime: DateTime.parse(json['start_time']),
      endTime: json['end_time'] != null && json['end_time'] != ''
          ? DateTime.parse(json['end_time'])
          : null,
      durationMinutes: json['duration_minutes'],
      cost: _toDouble(json['cost']),
      baseCost: _toDouble(json['base_cost']),
      discountAmount: _toDouble(json['discount_amount']),
      penaltyAmount: _toDouble(json['penalty_amount']),
      zoneExitDetected: json['zone_exit_detected'] ?? false,
      zoneExitDetails: json['zone_exit_details'],
      paidAmount: _toDouble(json['paid_amount']),
      remainingAmount: _toDouble(json['remaining_amount']),
      paymentStatus: json['payment_status'] ?? 'unpaid',
      scooterCode: json['scooter_code'] ?? json['scooter']?['code'],
    );
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


