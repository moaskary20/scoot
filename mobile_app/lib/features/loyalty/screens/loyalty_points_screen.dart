import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/models/loyalty_transaction_model.dart';
import '../../../core/services/api_service.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../../shared/theme/app_theme.dart';
import 'redeem_loyalty_points_screen.dart';

class LoyaltyPointsScreen extends StatefulWidget {
  const LoyaltyPointsScreen({super.key});

  @override
  State<LoyaltyPointsScreen> createState() => _LoyaltyPointsScreenState();
}

class _LoyaltyPointsScreenState extends State<LoyaltyPointsScreen> {
  final ApiService _apiService = ApiService();

  bool _isLoading = true;
  String? _errorMessage;

  int _points = 0;
  String _level = 'bronze';
  Map<String, dynamic> _thresholds = const {};
  Map<String, dynamic> _redeemSettings = const {};
  List<LoyaltyTransactionModel> _transactions = const [];

  @override
  void initState() {
    super.initState();
    _loadLoyaltyData();
  }

  Future<void> _loadLoyaltyData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _apiService.getLoyaltyData();

      setState(() {
        _points = result['points'] ?? 0;
        _level = result['level'] ?? 'bronze';
        _thresholds =
            (result['thresholds'] as Map<String, dynamic>? ?? const {});
        _redeemSettings =
            (result['redeem_settings'] as Map<String, dynamic>? ??
                const {});
        _transactions =
            (result['transactions'] as List<LoyaltyTransactionModel>? ??
                const []);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context);

    return Scaffold(
      appBar: AppBar(
        title: Text(
          loc?.loyaltyPoints ?? 'نقاط الولاء',
          style: AppTheme.tajawal(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: const Color(AppConstants.secondaryColor),
          ),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _loadLoyaltyData,
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : _errorMessage != null
                  ? _buildErrorState(loc)
                  : _buildContent(loc),
        ),
      ),
    );
  }

  Widget _buildErrorState(AppLocalizations? loc) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        const SizedBox(height: 80),
        Center(
          child: Text(
            loc?.errorLoadingData ?? 'حدث خطأ في تحميل البيانات',
            style: AppTheme.tajawal(
              fontSize: 16,
              fontWeight: FontWeight.w500,
              color: Colors.red,
            ),
            textAlign: TextAlign.center,
          ),
        ),
        if (_errorMessage != null) ...[
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Text(
              _errorMessage!,
              style: AppTheme.tajawal(
                fontSize: 12,
                color: Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildContent(AppLocalizations? loc) {
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        _buildHeaderCard(loc),
        const SizedBox(height: 16),
        _buildProgressCard(loc),
        const SizedBox(height: 16),
        _buildRedeemCard(loc),
        const SizedBox(height: 16),
        _buildTransactionsSection(loc),
      ],
    );
  }

  Widget _buildHeaderCard(AppLocalizations? loc) {
    final levelLabel = _mapLevelLabel(_level, loc);
    final levelColor = _mapLevelColor(_level);

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Color(AppConstants.primaryColor),
            Color(AppConstants.primaryColor).withOpacity(0.8),
          ],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Color(AppConstants.primaryColor).withOpacity(0.2),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.stars_rounded,
                color: Colors.amber[400],
                size: 32,
              ),
              const SizedBox(width: 12),
              Text(
                loc?.loyaltyPoints ?? 'نقاط الولاء',
                style: AppTheme.tajawal(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const Spacer(),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: levelColor.background,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.military_tech,
                      size: 14,
                      color: levelColor.foreground,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      levelLabel,
                      style: AppTheme.tajawal(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: levelColor.foreground,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Text(
            _points.toString(),
            style: AppTheme.tajawal(
              fontSize: 40,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            loc?.pointsLabel ?? 'نقاط',
            style: AppTheme.tajawal(
              fontSize: 14,
              color: Colors.white70,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProgressCard(AppLocalizations? loc) {
    final thresholds = _thresholds.map(
      (key, value) => MapEntry(key, int.tryParse(value.toString()) ?? 0),
    );

    final bronzeThreshold = thresholds['bronze'] ?? 0;
    final silverThreshold = thresholds['silver'] ?? 500;
    final goldThreshold = thresholds['gold'] ?? 1000;

    int currentLevelMin = 0;
    int nextLevelThreshold = silverThreshold;

    switch (_level) {
      case 'gold':
        currentLevelMin = goldThreshold;
        nextLevelThreshold = goldThreshold;
        break;
      case 'silver':
        currentLevelMin = silverThreshold;
        nextLevelThreshold = goldThreshold;
        break;
      default:
        currentLevelMin = bronzeThreshold;
        nextLevelThreshold = silverThreshold;
        break;
    }

    final range = (nextLevelThreshold - currentLevelMin).clamp(1, 1 << 31);
    final clampedPoints = _points.clamp(currentLevelMin, nextLevelThreshold);
    final progress = (clampedPoints - currentLevelMin) / range;

    final pointsToNext =
        (nextLevelThreshold > _points) ? (nextLevelThreshold - _points) : 0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            loc?.loyaltyLevel ?? 'مستوى الولاء',
            style: AppTheme.tajawal(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            loc?.loyaltyProgressDescription ??
                'كل ما تكمل رحلات أكتر، تجمع نقاط أكتر وتطلع لمستوى أعلى.',
            style: AppTheme.tajawal(
              fontSize: 13,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 16),
          ClipRRect(
            borderRadius: BorderRadius.circular(20),
            child: LinearProgressIndicator(
              value: progress.clamp(0.0, 1.0),
              minHeight: 10,
              backgroundColor: Colors.grey[200],
              valueColor: AlwaysStoppedAnimation<Color>(
                Color(AppConstants.primaryColor),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${loc?.currentLevel ?? 'المستوى الحالي'}: ${_mapLevelLabel(_level, loc)}',
                style: AppTheme.tajawal(
                  fontSize: 12,
                  color: Colors.grey[700],
                ),
              ),
              if (pointsToNext > 0)
                Text(
                  loc?.pointsToNextLevel(pointsToNext) ??
                      'متبقي $pointsToNext نقطة للمستوى التالي',
                  style: AppTheme.tajawal(
                    fontSize: 12,
                    color: Color(AppConstants.primaryColor),
                    fontWeight: FontWeight.w600,
                  ),
                )
              else
                Text(
                  loc?.maxLevelReached ?? 'أعلى مستوى ولاء 🎉',
                  style: AppTheme.tajawal(
                    fontSize: 12,
                    color: Color(AppConstants.primaryColor),
                    fontWeight: FontWeight.w600,
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRedeemCard(AppLocalizations? loc) {
    final redeemSettings = _redeemSettings;
    final isEnabled = redeemSettings['enabled'] ?? true;
    final minPoints = redeemSettings['min_points_to_redeem'] ?? 100;
    final canRedeem = _points >= minPoints && isEnabled;

    if (!isEnabled) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Colors.amber[400]!,
            Colors.orange[400]!,
          ],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.amber.withOpacity(0.3),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.card_giftcard, color: Colors.white, size: 28),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'استبدال النقاط',
                  style: AppTheme.tajawal(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (canRedeem) ...[
            Text(
              'يمكنك استبدال نقاط الولاء برصيد في المحفظة',
              style: AppTheme.tajawal(
                fontSize: 14,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () async {
                  final result = await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => RedeemLoyaltyPointsScreen(
                        currentPoints: _points,
                        redeemSettings: _redeemSettings,
                      ),
                    ),
                  );
                  
                  // Refresh data if redemption was successful
                  if (result == true) {
                    _loadLoyaltyData();
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: Colors.orange[700],
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: Text(
                  'استبدال الآن',
                  style: AppTheme.tajawal(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ] else ...[
            Text(
              'تحتاج ${minPoints - _points} نقطة إضافية للاستبدال',
              style: AppTheme.tajawal(
                fontSize: 14,
                color: Colors.white70,
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white.withOpacity(0.3),
                  foregroundColor: Colors.white70,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: Text(
                  'النقاط غير كافية',
                  style: AppTheme.tajawal(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTransactionsSection(AppLocalizations? loc) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          loc?.loyaltyTransactions ?? 'معاملات نقاط الولاء',
          style: AppTheme.tajawal(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: Colors.black87,
          ),
        ),
        const SizedBox(height: 8),
        if (_transactions.isEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 24),
            child: Center(
              child: Text(
                loc?.noLoyaltyTransactionsYet ?? 'لا توجد معاملات نقاط حتى الآن',
                style: AppTheme.tajawal(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
                textAlign: TextAlign.center,
              ),
            ),
          )
        else
          ListView.separated(
            itemCount: _transactions.length,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (context, index) {
              final tx = _transactions[index];
              return _buildTransactionItem(tx, loc);
            },
          ),
      ],
    );
  }

  Widget _buildTransactionItem(
    LoyaltyTransactionModel tx,
    AppLocalizations? loc,
  ) {
    final isPositive = tx.points >= 0;
    final icon = switch (tx.type) {
      'earned' => Icons.add_circle,
      'redeemed' => Icons.remove_circle,
      'adjusted' => Icons.tune,
      'expired' => Icons.hourglass_empty,
      _ => Icons.brightness_1,
    };

    final iconColor = switch (tx.type) {
      'earned' => Colors.green,
      'redeemed' => Colors.red,
      'expired' => Colors.orange,
      _ => Colors.blueGrey,
    };

    final title = switch (tx.type) {
      'earned' => loc?.loyaltyEarned ?? 'نقاط مكتسبة',
      'redeemed' => loc?.loyaltyRedeemed ?? 'نقاط مستخدمة',
      'adjusted' => loc?.loyaltyAdjusted ?? 'تعديل نقاط',
      'expired' => loc?.loyaltyExpired ?? 'نقاط منتهية',
      _ => loc?.loyaltyPoints ?? 'نقاط الولاء',
    };

    final dateText = DateFormat('yyyy-MM-dd HH:mm').format(tx.createdAt);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: AppTheme.tajawal(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Colors.black87,
                  ),
                ),
                if (tx.description != null && tx.description!.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 2),
                    child: Text(
                      tx.description!,
                      style: AppTheme.tajawal(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(
                      Icons.schedule,
                      size: 12,
                      color: Colors.grey[500],
                    ),
                    const SizedBox(width: 4),
                    Text(
                      dateText,
                      style: AppTheme.tajawal(
                        fontSize: 11,
                        color: Colors.grey[500],
                      ),
                    ),
                    const Spacer(),
                    Text(
                      '${loc?.balanceAfter ?? 'الرصيد بعد'}: ${tx.balanceAfter}',
                      style: AppTheme.tajawal(
                        fontSize: 11,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '${isPositive ? '+' : ''}${tx.points}',
            style: AppTheme.tajawal(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: isPositive ? Colors.green : Colors.red,
            ),
          ),
        ],
      ),
    );
  }

  _LevelColor _mapLevelColor(String level) {
    switch (level) {
      case 'gold':
        return _LevelColor(
          background: const Color(0xFFFFF7D6),
          foreground: const Color(0xFFB58B00),
        );
      case 'silver':
        return _LevelColor(
          background: const Color(0xFFF4F4F5),
          foreground: const Color(0xFF4B5563),
        );
      default:
        return _LevelColor(
          background: const Color(0xFFFFF4E5),
          foreground: const Color(0xFFB45309),
        );
    }
  }

  String _mapLevelLabel(String level, AppLocalizations? loc) {
    switch (level) {
      case 'gold':
        return loc?.gold ?? 'ذهبي';
      case 'silver':
        return loc?.silver ?? 'فضي';
      default:
        return loc?.bronze ?? 'برونزي';
    }
  }
}

class _LevelColor {
  final Color background;
  final Color foreground;

  const _LevelColor({
    required this.background,
    required this.foreground,
  });
}


