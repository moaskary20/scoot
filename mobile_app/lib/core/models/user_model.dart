class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final int? age;
  final String? universityId;
  final String? nationalIdPhoto;
  final String? avatar;
  final bool isActive;
  final double walletBalance;
  final int loyaltyPoints;
  final String loyaltyLevel;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.age,
    this.universityId,
    this.nationalIdPhoto,
    this.avatar,
    required this.isActive,
    required this.walletBalance,
    required this.loyaltyPoints,
    required this.loyaltyLevel,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      age: json['age'],
      universityId: json['university_id'],
      nationalIdPhoto: json['national_id_photo'],
      avatar: json['avatar'],
      isActive: json['is_active'] ?? false,
      walletBalance: _toDouble(json['wallet_balance']),
      loyaltyPoints: json['loyalty_points'] ?? 0,
      loyaltyLevel: json['loyalty_level'] ?? 'bronze',
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
      'name': name,
      'email': email,
      'phone': phone,
      'age': age,
      'university_id': universityId,
      'national_id_photo': nationalIdPhoto,
      'avatar': avatar,
      'is_active': isActive,
      'wallet_balance': walletBalance,
      'loyalty_points': loyaltyPoints,
      'loyalty_level': loyaltyLevel,
    };
  }
}

