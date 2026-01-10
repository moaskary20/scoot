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
    // If already masked or empty, return as is
    if (cardNumber.isEmpty || cardNumber.contains('*')) {
      return cardNumber.isNotEmpty ? cardNumber : '**** **** **** ****';
    }
    
    // Extract only digits
    final digitsOnly = cardNumber.replaceAll(RegExp(r'\D'), '');
    
    // If has less than 4 digits, return as is
    if (digitsOnly.length <= 4) {
      return digitsOnly.isNotEmpty ? digitsOnly : '**** **** **** ****';
    }
    
    // Format as masked: **** **** **** XXXX
    final last4 = digitsOnly.substring(digitsOnly.length - 4);
    return '**** **** **** $last4';
  }

  // Full expiry date (format: MM/YY)
  String get expiryDate {
    String month = expiryMonth.padLeft(2, '0');
    String year = expiryYear;
    
    // If year is 4 digits, take last 2
    if (year.length == 4) {
      year = year.substring(2);
    }
    
    return '$month/$year';
  }

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

