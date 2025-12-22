class CardModel {
  final int? id;
  final String cardNumber;
  final String cardHolderName;
  final String expiryMonth;
  final String expiryYear;
  final String cvv;
  final String? token; // Paymob token for saved card
  final bool isDefault;
  final DateTime? createdAt;

  CardModel({
    this.id,
    required this.cardNumber,
    required this.cardHolderName,
    required this.expiryMonth,
    required this.expiryYear,
    required this.cvv,
    this.token,
    this.isDefault = false,
    this.createdAt,
  });

  // Masked card number for display (shows only last 4 digits)
  String get maskedCardNumber {
    if (cardNumber.length <= 4) return cardNumber;
    return '**** **** **** ${cardNumber.substring(cardNumber.length - 4)}';
  }

  // Full expiry date
  String get expiryDate => '$expiryMonth/$expiryYear';

  factory CardModel.fromJson(Map<String, dynamic> json) {
    return CardModel(
      id: json['id'],
      cardNumber: json['card_number'] ?? json['cardNumber'] ?? '',
      cardHolderName: json['card_holder_name'] ?? json['cardHolderName'] ?? '',
      expiryMonth: json['expiry_month'] ?? json['expiryMonth'] ?? '',
      expiryYear: json['expiry_year'] ?? json['expiryYear'] ?? '',
      cvv: json['cvv'] ?? '',
      token: json['token'],
      isDefault: json['is_default'] ?? json['isDefault'] ?? false,
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'card_number': cardNumber,
      'card_holder_name': cardHolderName,
      'expiry_month': expiryMonth,
      'expiry_year': expiryYear,
      'cvv': cvv,
      'token': token,
      'is_default': isDefault,
      'created_at': createdAt?.toIso8601String(),
    };
  }

  // For local storage (without sensitive data)
  Map<String, dynamic> toLocalJson() {
    return {
      'id': id,
      'card_number': maskedCardNumber,
      'card_holder_name': cardHolderName,
      'expiry_month': expiryMonth,
      'expiry_year': expiryYear,
      'token': token,
      'is_default': isDefault,
      'created_at': createdAt?.toIso8601String(),
    };
  }
}

