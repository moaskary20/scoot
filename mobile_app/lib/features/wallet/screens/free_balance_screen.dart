import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/models/referral_model.dart';

class FreeBalanceScreen extends StatefulWidget {
  const FreeBalanceScreen({super.key});

  @override
  State<FreeBalanceScreen> createState() => _FreeBalanceScreenState();
}

class _FreeBalanceScreenState extends State<FreeBalanceScreen> {
  final ApiService _apiService = ApiService();
  ReferralModel? _referralData;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadReferralData();
  }

  Future<void> _loadReferralData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final data = await _apiService.getReferralData();
      print('📊 Referral data received: Code=${data.referralCode}, Link=${data.affiliateLink}');
      if (mounted) {
        setState(() {
          _referralData = data;
          _isLoading = false;
        });
        
        // Show warning if referral code is empty
        if (data.referralCode.isEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('لم يتم العثور على كود إحالة. يرجى المحاولة مرة أخرى.'),
              backgroundColor: Colors.orange,
              duration: Duration(seconds: 3),
            ),
          );
        }
      }
    } catch (e) {
      print('❌ Error in _loadReferralData: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في تحميل البيانات: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _shareReferralCode() async {
    // Reload data if null or empty
    if (_referralData == null || _referralData!.referralCode.isEmpty) {
      // Try to reload data first
      await _loadReferralData();
      
      // Check again after reload
      if (_referralData == null || _referralData!.referralCode.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('لا يوجد كود إحالة متاح. يرجى المحاولة مرة أخرى.'),
            backgroundColor: Colors.orange,
            duration: Duration(seconds: 3),
          ),
        );
        return;
      }
    }

    // Use affiliate link if available, otherwise use referral code
    final shareText = _referralData!.affiliateLink.isNotEmpty
        ? '''
🎉 انضم إلى Liner Scoot واحصل على رصيد مجاني!

استخدم رابط الإحالة الخاص بي:
${_referralData!.affiliateLink}

أو استخدم كود الإحالة: ${_referralData!.referralCode}

احصل على 30 جنيه رصيد مجاني عند إتمام رحلتك الأولى! 🛴

#LinerScoot
'''
        : '''
🎉 انضم إلى Liner Scoot واحصل على رصيد مجاني!

استخدم كود الإحالة الخاص بي: ${_referralData!.referralCode}

احصل على 30 جنيه رصيد مجاني عند إتمام رحلتك الأولى! 🛴

#LinerScoot
''';

    try {
      await Share.share(shareText);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في المشاركة: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.black),
            onPressed: () => Navigator.pop(context),
          ),
          title: const Text(
            'رصيد مجاني',
            style: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          centerTitle: true,
          actions: [
            IconButton(
              icon: const Icon(Icons.help_outline, color: Colors.black),
              onPressed: () {
                // TODO: Show help dialog
              },
            ),
          ],
        ),
        body: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : RefreshIndicator(
                onRefresh: _loadReferralData,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    children: [
                      const SizedBox(height: 20),
                      // Scooter Image
                      _buildScooterImage(),
                      const SizedBox(height: 30),
                      // Main Title
                      Text(
                        'احصل على ${_referralData?.rewardPerReferral ?? 30} ج.م رصيد!',
                        style: const TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          color: Colors.black,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      // Description
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: Text(
                          'رشح بحد أقصي ${_referralData?.maxReferrals ?? 5} من اصدقاءك لتحصل على ${_referralData?.rewardPerReferral ?? 30} ج.م رصيد لما يكملوا رحلتهم الأولى! 🐰 ✌️',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[700],
                            height: 1.5,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                      const SizedBox(height: 40),
                      // Referral Code Display (if available)
                      if (_referralData != null && _referralData!.referralCode.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.grey[100],
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.grey[300]!),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Text(
                                'كود الإحالة: ',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.black87,
                                ),
                              ),
                              Text(
                                _referralData!.referralCode,
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: Color(AppConstants.primaryColor),
                                  letterSpacing: 2,
                                ),
                              ),
                            ],
                          ),
                        ),
                      if (_referralData != null && _referralData!.referralCode.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.orange[50],
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.orange[200]!),
                          ),
                          child: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.warning_amber_rounded, color: Colors.orange),
                              SizedBox(width: 8),
                              Text(
                                'لا يوجد كود إحالة متاح',
                                style: TextStyle(
                                  fontSize: 16,
                                  color: Colors.orange,
                                ),
                              ),
                            ],
                          ),
                        ),
                      const SizedBox(height: 20),
                      // Share Button
                      SizedBox(
                        width: double.infinity,
                        height: 56,
                        child: ElevatedButton.icon(
                          onPressed: _shareReferralCode,
                          icon: const Icon(Icons.share, size: 24),
                          label: const Text(
                            'شارك الكود بتاعك',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Color(AppConstants.primaryColor),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                            elevation: 2,
                          ),
                        ),
                      ),
                      const SizedBox(height: 40),
                      // Status Cards
                      Row(
                        children: [
                          Expanded(
                            child: _buildStatusCard(
                              icon: Icons.people,
                              title: '${_referralData?.referredFriendsCount ?? 0} أصدقاء',
                              subtitle: 'تم استخدام الكود',
                              color: Color(AppConstants.primaryColor),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildStatusCard(
                              icon: Icons.account_balance_wallet,
                              title: '${(_referralData?.totalEarned ?? 0.0).toStringAsFixed(0)} ج.م',
                              subtitle: 'تم استلامه بالكامل',
                              color: Colors.green,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
      ),
    );
  }

  Widget _buildScooterImage() {
    return Container(
      width: 250,
      height: 250,
      decoration: BoxDecoration(
        color: Color(AppConstants.primaryColor),
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: Color(AppConstants.primaryColor).withOpacity(0.3),
            blurRadius: 20,
            spreadRadius: 5,
          ),
        ],
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          // Decorative circle outline
          Container(
            width: 280,
            height: 280,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: Color(AppConstants.primaryColor).withOpacity(0.2),
                width: 2,
              ),
            ),
          ),
          // Main image
          ClipOval(
            child: Image.asset(
              'assets/images/onbording1.png',
              width: 200,
              height: 200,
              fit: BoxFit.cover,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey[200]!),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(
            icon,
            size: 32,
            color: color,
          ),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.black,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            subtitle,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

