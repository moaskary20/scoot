class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final int? age;
  final String? universityId;
  final String? nationalIdPhoto;
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
    required this.isActive,
    required this.walletBalance,
    required this.loyaltyPoints,
    required this.loyaltyLevel,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      age: json['age'],
      universityId: json['university_id'],
      nationalIdPhoto: json['national_id_photo'],
      isActive: json['is_active'] ?? false,
      walletBalance: (json['wallet_balance'] ?? 0).toDouble(),
      loyaltyPoints: json['loyalty_points'] ?? 0,
      loyaltyLevel: json['loyalty_level'] ?? 'bronze',
    );
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
      'is_active': isActive,
      'wallet_balance': walletBalance,
      'loyalty_points': loyaltyPoints,
      'loyalty_level': loyaltyLevel,
    };
  }
}

