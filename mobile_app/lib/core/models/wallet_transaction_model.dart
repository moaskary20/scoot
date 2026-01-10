class WalletTransactionModel {
  final int id;
  final int userId;
  final int? tripId;
  final String type; // top_up, adjustment, refund, penalty, trip_payment, subscription
  final String transactionType; // credit, debit
  final double amount;
  final double balanceBefore;
  final double balanceAfter;
  final String? reference;
  final String? paymentMethod;
  final String status; // pending, completed, failed, cancelled
  final String? description;
  final String? notes;
  final DateTime createdAt;
  final DateTime? processedAt;

  WalletTransactionModel({
    required this.id,
    required this.userId,
    this.tripId,
    required this.type,
    required this.transactionType,
    required this.amount,
    required this.balanceBefore,
    required this.balanceAfter,
    this.reference,
    this.paymentMethod,
    required this.status,
    this.description,
    this.notes,
    required this.createdAt,
    this.processedAt,
  });

  factory WalletTransactionModel.fromJson(Map<String, dynamic> json) {
    return WalletTransactionModel(
      id: _toInt(json['id']),
      userId: _toInt(json['user_id'] ?? json['userId']),
      tripId: json['trip_id'] != null || json['tripId'] != null 
          ? _toInt(json['trip_id'] ?? json['tripId']) 
          : null,
      type: json['type']?.toString() ?? '',
      transactionType: json['transaction_type']?.toString() ?? json['transactionType']?.toString() ?? '',
      amount: _toDouble(json['amount']),
      balanceBefore: _toDouble(json['balance_before'] ?? json['balanceBefore']),
      balanceAfter: _toDouble(json['balance_after'] ?? json['balanceAfter']),
      reference: json['reference']?.toString(),
      paymentMethod: json['payment_method']?.toString() ?? json['paymentMethod']?.toString(),
      status: json['status']?.toString() ?? 'completed',
      description: json['description']?.toString(),
      notes: json['notes']?.toString(),
      createdAt: _parseDateTime(json['created_at'] ?? json['createdAt']),
      processedAt: json['processed_at'] != null || json['processedAt'] != null
          ? _parseDateTime(json['processed_at'] ?? json['processedAt']) 
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

  static DateTime _parseDateTime(dynamic value) {
    if (value == null) return DateTime.now();
    if (value is DateTime) return value;
    if (value is String) {
      try {
        return DateTime.parse(value);
      } catch (e) {
        print('❌ Error parsing DateTime: $value - $e');
        return DateTime.now();
      }
    }
    return DateTime.now();
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'trip_id': tripId,
      'type': type,
      'transaction_type': transactionType,
      'amount': amount,
      'balance_before': balanceBefore,
      'balance_after': balanceAfter,
      'reference': reference,
      'payment_method': paymentMethod,
      'status': status,
      'description': description,
      'notes': notes,
      'created_at': createdAt.toIso8601String(),
      'processed_at': processedAt?.toIso8601String(),
    };
  }
}

