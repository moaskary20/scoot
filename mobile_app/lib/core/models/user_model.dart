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
  final String? reviewNotes;
  final String accountStatus; // 'active', 'pending', 'rejected'
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
    this.reviewNotes,
    required this.accountStatus,
    required this.walletBalance,
    required this.loyaltyPoints,
    required this.loyaltyLevel,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    // Parse age - handle both int and string
    int? parsedAge;
    if (json['age'] != null) {
      if (json['age'] is int) {
        parsedAge = json['age'];
      } else if (json['age'] is num) {
        parsedAge = (json['age'] as num).toInt();
      } else if (json['age'] is String) {
        parsedAge = int.tryParse(json['age']);
      }
    }
    
    // Parse university_id - handle both string and null
    String? parsedUniversityId;
    if (json['university_id'] != null) {
      parsedUniversityId = json['university_id'].toString();
      if (parsedUniversityId.isEmpty) {
        parsedUniversityId = null;
      }
    }
    
    print('📋 Parsing UserModel:');
    print('  - age: ${json['age']} (type: ${json['age']?.runtimeType}) -> parsed: $parsedAge');
    print('  - university_id: ${json['university_id']} (type: ${json['university_id']?.runtimeType}) -> parsed: $parsedUniversityId');
    
    // Determine account status
    String accountStatus = 'pending'; // قيد التفعيل
    final isActive = json['is_active'] == true || json['is_active'] == 1 || json['is_active'] == '1';
    final reviewNotes = json['review_notes']?.toString();
    
    if (isActive) {
      accountStatus = 'active'; // مفعل
    } else if (reviewNotes != null && reviewNotes.isNotEmpty && reviewNotes.trim().isNotEmpty) {
      accountStatus = 'rejected'; // مرفوض
    }
    
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString(),
      age: parsedAge,
      universityId: parsedUniversityId,
      nationalIdPhoto: json['national_id_photo']?.toString(),
      avatar: json['avatar']?.toString(),
      isActive: isActive,
      reviewNotes: reviewNotes,
      accountStatus: json['account_status']?.toString() ?? accountStatus,
      walletBalance: _toDouble(json['wallet_balance']),
      loyaltyPoints: json['loyalty_points'] is int ? json['loyalty_points'] : int.tryParse(json['loyalty_points']?.toString() ?? '0') ?? 0,
      loyaltyLevel: json['loyalty_level']?.toString() ?? 'bronze',
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
      'review_notes': reviewNotes,
      'account_status': accountStatus,
      'wallet_balance': walletBalance,
      'loyalty_points': loyaltyPoints,
      'loyalty_level': loyaltyLevel,
    };
  }
}

