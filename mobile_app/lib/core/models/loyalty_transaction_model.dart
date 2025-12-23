class LoyaltyTransactionModel {
  final int id;
  final String type; // earned, redeemed, adjusted, expired
  final int points;
  final int balanceAfter;
  final String? description;
  final int? tripId;
  final DateTime createdAt;

  const LoyaltyTransactionModel({
    required this.id,
    required this.type,
    required this.points,
    required this.balanceAfter,
    this.description,
    this.tripId,
    required this.createdAt,
  });

  factory LoyaltyTransactionModel.fromJson(Map<String, dynamic> json) {
    return LoyaltyTransactionModel(
      id: json['id'] as int,
      type: json['type'] ?? 'earned',
      points: _toInt(json['points']),
      balanceAfter: _toInt(json['balance_after']),
      description: json['description'],
      tripId: json['trip_id'],
      createdAt: DateTime.parse(json['created_at']),
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
}


