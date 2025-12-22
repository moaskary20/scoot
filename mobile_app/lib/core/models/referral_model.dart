class ReferralModel {
  final String referralCode;
  final String affiliateLink;
  final int referredFriendsCount;
  final double totalEarned;
  final int maxReferrals;
  final double rewardPerReferral;
  final bool isActive;

  ReferralModel({
    required this.referralCode,
    required this.affiliateLink,
    required this.referredFriendsCount,
    required this.totalEarned,
    this.maxReferrals = 5,
    this.rewardPerReferral = 30.0,
    this.isActive = true,
  });

  factory ReferralModel.fromJson(Map<String, dynamic> json) {
    return ReferralModel(
      referralCode: json['referral_code'] ?? json['referralCode'] ?? '',
      affiliateLink: json['affiliate_link'] ?? json['affiliateLink'] ?? '',
      referredFriendsCount: json['referred_friends_count'] ?? json['referredFriendsCount'] ?? 0,
      totalEarned: _toDouble(json['total_earned'] ?? json['totalEarned'] ?? 0),
      maxReferrals: json['max_referrals'] ?? json['maxReferrals'] ?? 5,
      rewardPerReferral: _toDouble(json['reward_per_referral'] ?? json['rewardPerReferral'] ?? 30.0),
      isActive: json['is_active'] ?? json['isActive'] ?? true,
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
      'referral_code': referralCode,
      'affiliate_link': affiliateLink,
      'referred_friends_count': referredFriendsCount,
      'total_earned': totalEarned,
      'max_referrals': maxReferrals,
      'reward_per_referral': rewardPerReferral,
      'is_active': isActive,
    };
  }

  // Check if user can still refer more friends
  bool get canReferMore => referredFriendsCount < maxReferrals && isActive;

  // Get remaining referrals
  int get remainingReferrals {
    final diff = maxReferrals - referredFriendsCount;
    return diff < 0 ? 0 : diff;
  }
}

