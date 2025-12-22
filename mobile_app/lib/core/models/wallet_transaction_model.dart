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
      id: json['id'],
      userId: json['user_id'],
      tripId: json['trip_id'],
      type: json['type'] ?? '',
      transactionType: json['transaction_type'] ?? '',
      amount: _toDouble(json['amount']),
      balanceBefore: _toDouble(json['balance_before']),
      balanceAfter: _toDouble(json['balance_after']),
      reference: json['reference'],
      paymentMethod: json['payment_method'],
      status: json['status'] ?? 'completed',
      description: json['description'],
      notes: json['notes'],
      createdAt: DateTime.parse(json['created_at']),
      processedAt: json['processed_at'] != null 
          ? DateTime.parse(json['processed_at']) 
          : null,
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

