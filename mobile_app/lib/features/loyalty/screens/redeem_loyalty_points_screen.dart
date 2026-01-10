import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'dart:ui' as ui;
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../../shared/theme/app_theme.dart';

class RedeemLoyaltyPointsScreen extends StatefulWidget {
  final int currentPoints;
  final Map<String, dynamic> redeemSettings;

  const RedeemLoyaltyPointsScreen({
    super.key,
    required this.currentPoints,
    required this.redeemSettings,
  });

  @override
  State<RedeemLoyaltyPointsScreen> createState() =>
      _RedeemLoyaltyPointsScreenState();
}

class _RedeemLoyaltyPointsScreenState
    extends State<RedeemLoyaltyPointsScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _pointsController = TextEditingController();
  final FocusNode _pointsFocusNode = FocusNode();

  bool _isRedeeming = false;
  bool _isCalculating = false;
  double? _calculatedAmount;
  String? _errorMessage;

  int get _minPoints => widget.redeemSettings['min_points_to_redeem'] ?? 100;
  int get _pointsToEgpRate =>
      widget.redeemSettings['points_to_egp_rate'] ?? 100;
  bool get _isEnabled =>
      widget.redeemSettings['enabled'] ?? true;

  @override
  void initState() {
    super.initState();
    _pointsController.addListener(_calculateAmount);
  }

  @override
  void dispose() {
    _pointsController.removeListener(_calculateAmount);
    _pointsController.dispose();
    _pointsFocusNode.dispose();
    super.dispose();
  }

  void _calculateAmount() {
    final pointsText = _pointsController.text.trim();
    if (pointsText.isEmpty) {
      setState(() {
        _calculatedAmount = null;
        _errorMessage = null;
      });
      return;
    }

    final points = int.tryParse(pointsText);
    if (points == null || points <= 0) {
      setState(() {
        _calculatedAmount = null;
        _errorMessage = 'يرجى إدخال رقم صحيح';
      });
      return;
    }

    if (points < _minPoints) {
      setState(() {
        _calculatedAmount = null;
        _errorMessage = 'الحد الأدنى لاستبدال النقاط هو $_minPoints نقطة';
      });
      return;
    }

    if (points > widget.currentPoints) {
      setState(() {
        _calculatedAmount = null;
        _errorMessage = 'ليس لديك نقاط كافية. النقاط المتاحة: ${widget.currentPoints}';
      });
      return;
    }

    setState(() {
      _isCalculating = true;
      _errorMessage = null;
    });

    // Calculate amount (points / rate)
    final amount = points / _pointsToEgpRate;
    
    Future.delayed(const Duration(milliseconds: 300), () {
      if (mounted) {
        setState(() {
          _calculatedAmount = amount;
          _isCalculating = false;
        });
      }
    });
  }

  Future<void> _redeemPoints() async {
    final pointsText = _pointsController.text.trim();
    if (pointsText.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('يرجى إدخال عدد النقاط المراد استبدالها'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final points = int.tryParse(pointsText);
    if (points == null || points <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('يرجى إدخال رقم صحيح'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (points < _minPoints) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('الحد الأدنى لاستبدال النقاط هو $_minPoints نقطة'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (points > widget.currentPoints) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('ليس لديك نقاط كافية. النقاط المتاحة: ${widget.currentPoints}'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    // Show confirmation dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(Icons.confirmation_number, color: Colors.amber[700], size: 28),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                'تأكيد الاستبدال',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.amber[900],
                ),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'هل أنت متأكد من استبدال:',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.amber.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.amber.withOpacity(0.3)),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('النقاط المستبدلة:'),
                      Text(
                        '$points نقطة',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Colors.amber[900],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('المبلغ المضاف للمحفظة:'),
                      Text(
                        '${_calculatedAmount?.toStringAsFixed(2) ?? (points / _pointsToEgpRate).toStringAsFixed(2)} ج.م',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Colors.green[700],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.amber,
              foregroundColor: Colors.white,
            ),
            child: const Text(
              'تأكيد',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() {
      _isRedeeming = true;
      _errorMessage = null;
    });

    try {
      final result = await _apiService.redeemLoyaltyPoints(points);

      if (mounted) {
        setState(() {
          _isRedeeming = false;
        });

        // Show success dialog
        await showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green, size: 28),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'تم الاستبدال بنجاح',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.green[900],
                    ),
                  ),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  result['message'] ?? 'تم استبدال النقاط بنجاح',
                  style: const TextStyle(fontSize: 15),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.green.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.green.withOpacity(0.3)),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('النقاط الجديدة:'),
                          Text(
                            '${result['data']['new_points_balance']} نقطة',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.amber[900],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('رصيد المحفظة الجديد:'),
                          Text(
                            '${result['data']['new_wallet_balance'].toStringAsFixed(2)} ج.م',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.green[700],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            actions: [
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context); // Close success dialog
                  Navigator.pop(context, true); // Return to previous screen with refresh flag
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                ),
                child: const Text(
                  'حسناً',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isRedeeming = false;
          _errorMessage = e.toString().replaceAll('Exception: ', '');
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(_errorMessage ?? 'حدث خطأ في استبدال النقاط'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context);

    if (!_isEnabled) {
      return Scaffold(
        appBar: AppBar(
          title: Text(
            loc?.loyaltyRedeem ?? 'استبدال نقاط الولاء',
            style: AppTheme.tajawal(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: const Color(AppConstants.secondaryColor),
            ),
          ),
          centerTitle: true,
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.block,
                  size: 64,
                  color: Colors.grey[400],
                ),
                const SizedBox(height: 16),
                Text(
                  'استبدال النقاط معطل حالياً',
                  style: AppTheme.tajawal(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey[700],
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Directionality(
      textDirection: ui.TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: Text(
            loc?.loyaltyRedeem ?? 'استبدال نقاط الولاء',
            style: AppTheme.tajawal(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: const Color(AppConstants.secondaryColor),
            ),
          ),
          centerTitle: true,
        ),
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Info Card
                _buildInfoCard(),
                const SizedBox(height: 24),
                // Points Input Section
                _buildPointsInputSection(),
                const SizedBox(height: 24),
                // Calculated Amount Section
                if (_calculatedAmount != null || _isCalculating)
                  _buildCalculatedAmountSection(),
                if (_errorMessage != null) ...[
                  const SizedBox(height: 16),
                  _buildErrorMessage(),
                ],
                const SizedBox(height: 24),
                // Redeem Button
                _buildRedeemButton(),
                const SizedBox(height: 24),
                // Terms and Info
                _buildTermsInfo(),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard() {
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
              Icon(Icons.info_outline, color: Colors.white, size: 24),
              const SizedBox(width: 8),
              Text(
                'نقاطك الحالية',
                style: AppTheme.tajawal(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            widget.currentPoints.toString(),
            style: AppTheme.tajawal(
              fontSize: 36,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'نقطة',
            style: AppTheme.tajawal(
              fontSize: 14,
              color: Colors.white70,
            ),
          ),
          const SizedBox(height: 16),
          Divider(color: Colors.white.withOpacity(0.3)),
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(Icons.swap_horiz, color: Colors.white, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  '${_pointsToEgpRate} نقطة = 1 جنيه',
                  style: AppTheme.tajawal(
                    fontSize: 13,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              Icon(Icons.minimize, color: Colors.white, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'الحد الأدنى للاستبدال: $_minPoints نقطة',
                  style: AppTheme.tajawal(
                    fontSize: 13,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPointsInputSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'عدد النقاط المراد استبدالها',
          style: AppTheme.tajawal(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: Colors.black87,
          ),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _pointsController,
          focusNode: _pointsFocusNode,
          keyboardType: TextInputType.number,
          inputFormatters: [
            FilteringTextInputFormatter.digitsOnly,
          ],
          decoration: InputDecoration(
            hintText: 'أدخل عدد النقاط',
            prefixIcon: Icon(Icons.confirmation_number,
                color: Color(AppConstants.primaryColor)),
            suffixIcon: _pointsController.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear),
                    onPressed: () {
                      _pointsController.clear();
                      _pointsFocusNode.unfocus();
                    },
                  )
                : null,
            filled: true,
            fillColor: Colors.grey[50],
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey[300]!),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey[300]!),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: Color(AppConstants.primaryColor),
                width: 2,
              ),
            ),
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          ),
        ),
        const SizedBox(height: 12),
        // Quick select buttons
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            if (widget.currentPoints >= _minPoints)
              _buildQuickSelectButton(
                _minPoints.toString(),
                _minPoints <= widget.currentPoints,
              ),
            if (widget.currentPoints >= _minPoints * 2)
              _buildQuickSelectButton(
                (_minPoints * 2).toString(),
                (_minPoints * 2) <= widget.currentPoints,
              ),
            if (widget.currentPoints >= _minPoints * 5)
              _buildQuickSelectButton(
                (_minPoints * 5).toString(),
                (_minPoints * 5) <= widget.currentPoints,
              ),
            if (widget.currentPoints > _minPoints)
              _buildQuickSelectButton(
                'الكل',
                true,
                isAll: true,
              ),
          ],
        ),
      ],
    );
  }

  Widget _buildQuickSelectButton(String label, bool enabled,
      {bool isAll = false}) {
    return OutlinedButton(
      onPressed: enabled
          ? () {
              if (isAll) {
                _pointsController.text = widget.currentPoints.toString();
              } else {
                _pointsController.text = label;
              }
              _calculateAmount();
            }
          : null,
      style: OutlinedButton.styleFrom(
        foregroundColor: Color(AppConstants.primaryColor),
        side: BorderSide(
          color: enabled
              ? Color(AppConstants.primaryColor)
              : Colors.grey[300]!,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
      child: Text(label),
    );
  }

  Widget _buildCalculatedAmountSection() {
    if (_isCalculating) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.blue[50],
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.blue[200]!),
        ),
        child: Row(
          children: [
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation<Color>(
                  Color(AppConstants.primaryColor),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Text(
              'جاري الحساب...',
              style: AppTheme.tajawal(
                fontSize: 14,
                color: Colors.blue[900],
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Colors.green[50]!,
            Colors.green[100]!,
          ],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.green[300]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.account_balance_wallet, color: Colors.green[700], size: 24),
              const SizedBox(width: 8),
              Text(
                'المبلغ المضاف للمحفظة',
                style: AppTheme.tajawal(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.green[900],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${_pointsController.text} نقطة',
                style: AppTheme.tajawal(
                  fontSize: 14,
                  color: Colors.amber[900],
                  fontWeight: FontWeight.w600,
                ),
              ),
              Text(
                '=',
                style: AppTheme.tajawal(
                  fontSize: 20,
                  color: Colors.grey[600],
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                '${_calculatedAmount?.toStringAsFixed(2) ?? '0.00'} ج.م',
                style: AppTheme.tajawal(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.green[700],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildErrorMessage() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.red[50],
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.red[300]!),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, color: Colors.red[700], size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              _errorMessage ?? '',
              style: AppTheme.tajawal(
                fontSize: 13,
                color: Colors.red[900],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRedeemButton() {
    final pointsText = _pointsController.text.trim();
    final points = int.tryParse(pointsText);
    final canRedeem = points != null &&
        points >= _minPoints &&
        points <= widget.currentPoints &&
        !_isRedeeming &&
        _calculatedAmount != null;

    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: canRedeem ? _redeemPoints : null,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.amber,
          foregroundColor: Colors.white,
          disabledBackgroundColor: Colors.grey[300],
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: canRedeem ? 4 : 0,
        ),
        child: _isRedeeming
            ? SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : Text(
                'استبدال النقاط',
                style: AppTheme.tajawal(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
      ),
    );
  }

  Widget _buildTermsInfo() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.info_outline, color: Colors.grey[700], size: 18),
              const SizedBox(width: 8),
              Text(
                'ملاحظات مهمة:',
                style: AppTheme.tajawal(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey[800],
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            '• الحد الأدنى لاستبدال النقاط هو $_minPoints نقطة\n'
            '• كل $_pointsToEgpRate نقطة = 1 جنيه\n'
            '• سيتم إضافة المبلغ مباشرة إلى المحفظة\n'
            '• لا يمكن استرجاع النقاط بعد الاستبدال',
            style: AppTheme.tajawal(
              fontSize: 12,
              color: Colors.grey[700],
            ),
          ),
        ],
      ),
    );
  }
}
