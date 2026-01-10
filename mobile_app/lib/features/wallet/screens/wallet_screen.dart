import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'dart:ui' as ui;
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/models/user_model.dart';
import '../../../core/models/wallet_transaction_model.dart';
import '../../../core/l10n/app_localizations.dart';
import 'top_up_screen.dart';
import 'transaction_history_screen.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  final ApiService _apiService = ApiService();
  UserModel? _currentUser;
  List<WalletTransactionModel> _transactions = [];
  bool _isLoading = true;
  bool _isLoadingTransactions = false;
  final TextEditingController _promoCodeController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadWalletData();
  }

  Future<void> _loadWalletData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final user = await _apiService.getCurrentUser();
      // جلب جميع المعاملات المالية المتعلقة بالشحن والمحفظة
      // بما فيها المعاملات التي تمت من admin panel (top_up, adjustment, refund, subscription)
      final transactions = await _apiService.getWalletTransactions(
        // لا نستخدم type filter لجلب جميع المعاملات المالية
        perPage: 20, // جلب آخر 20 معاملة
      );
      
      if (mounted) {
        setState(() {
          _currentUser = user;
          _transactions = transactions;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)?.errorLoadingWallet ?? 'حدث خطأ في تحميل بيانات المحفظة'}: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _refreshData() async {
    await _loadWalletData();
  }

  Future<void> _topUpWallet() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const TopUpScreen(),
      ),
    );
    
    // Refresh wallet data if payment was initiated
    if (result == true) {
      _refreshData();
    }
  }

  Future<void> _activatePromoCode() async {
    final code = _promoCodeController.text.trim();
    if (code.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context)?.enterPromoCode ?? 'يرجى إدخال كود البرومو'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isLoadingTransactions = true;
    });

    try {
      final result = await _apiService.validatePromoCode(code);
      if (mounted) {
        setState(() {
          _isLoadingTransactions = false;
        });
        
        if (result['success'] == true) {
          _promoCodeController.clear();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'تم تفعيل الكود بنجاح'),
              backgroundColor: Colors.green,
            ),
          );
          _refreshData();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'كود غير صحيح'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingTransactions = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)?.errorOccurred ?? 'حدث خطأ'}: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _showTransactionHistory() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const TransactionHistoryScreen(),
      ),
    );
  }

  @override
  void dispose() {
    _promoCodeController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: ui.TextDirection.rtl,
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
            'محفظة',
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
                onRefresh: _refreshData,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Available Balance Section with Transaction History
                        _buildBalanceSection(),
                        const SizedBox(height: 24),
                        const Divider(height: 1),
                        const SizedBox(height: 24),
                        // Promo Code Section
                        _buildPromoCodeSection(),
                      ],
                    ),
                  ),
                ),
              ),
      ),
    );
  }

  Widget _buildBalanceSection() {
    final balance = _currentUser?.walletBalance ?? 0.0;
    final recentTransactions = _transactions.take(5).toList();
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Balance Header
        const Text(
          'الرصيد المتاح',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w500,
            color: Colors.black,
          ),
        ),
        const SizedBox(height: 12),
        // Balance and Top Up Button
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              '${balance.toStringAsFixed(2)} ج.م',
              style: const TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Color(AppConstants.primaryColor),
                    Color(AppConstants.primaryColor).withOpacity(0.8),
                  ],
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                ),
                borderRadius: BorderRadius.circular(25),
                boxShadow: [
                  BoxShadow(
                    color: Color(AppConstants.primaryColor).withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: _topUpWallet,
                  borderRadius: BorderRadius.circular(25),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      vertical: 14,
                      horizontal: 24,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.account_balance_wallet,
                          color: Colors.white,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        const Text(
                          'إشحن',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),
        // Transaction History Section
        _buildTransactionHistorySection(recentTransactions),
      ],
    );
  }

  Widget _buildTransactionHistorySection(List<WalletTransactionModel> transactions) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Color(AppConstants.primaryColor).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    Icons.account_balance,
                    color: Color(AppConstants.primaryColor),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                const Text(
                  'المعاملات المالية',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black,
                  ),
                ),
              ],
            ),
            TextButton.icon(
              onPressed: _showTransactionHistory,
              icon: const Icon(Icons.arrow_forward_ios, size: 14),
              label: const Text(
                'سجل المعاملات',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              style: TextButton.styleFrom(
                foregroundColor: Color(AppConstants.primaryColor),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        // Transactions List
        if (transactions.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.grey[50],
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.grey[200]!),
            ),
            child: Column(
              children: [
                Icon(
                  Icons.receipt_long_outlined,
                  size: 56,
                  color: Colors.grey[400],
                ),
                const SizedBox(height: 12),
                Text(
                  'لا توجد معاملات مالية بعد',
                  style: TextStyle(
                    fontSize: 16,
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'ستظهر جميع المعاملات المالية المتعلقة بالمحفظة هنا',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[500],
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          )
        else
          ...transactions.map((transaction) => _buildTransactionItem(transaction)),
      ],
    );
  }

  Widget _buildTransactionItem(WalletTransactionModel transaction) {
    final dateFormat = DateFormat('dd/MM/yyyy - HH:mm', 'ar');
    final isCredit = transaction.transactionType == 'credit';
    final statusColor = _getStatusColor(transaction.status);
    final typeIcon = _getTransactionTypeIcon(transaction.type);
    
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Colors.white,
            (isCredit ? Colors.green[50] : Colors.red[50]) ?? Colors.white,
          ],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: (isCredit ? Colors.green[200] : Colors.red[200]) ?? Colors.grey[200]!,
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: (isCredit ? Colors.green[100] : Colors.red[100])?.withOpacity(0.3) ?? Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: _showTransactionHistory,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                // Icon Container
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        (isCredit ? Colors.green : Colors.red)[400]!,
                        (isCredit ? Colors.green : Colors.red)[600]!,
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: (isCredit ? Colors.green : Colors.red)[300]!.withOpacity(0.4),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Icon(
                    typeIcon,
                    color: Colors.white,
                    size: 28,
                  ),
                ),
                const SizedBox(width: 16),
                // Transaction Details
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              _getTransactionTypeText(transaction.type),
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Colors.black87,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: statusColor.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: statusColor.withOpacity(0.3),
                                width: 1,
                              ),
                            ),
                            child: Text(
                              _getStatusText(transaction.status),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: statusColor,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        dateFormat.format(transaction.createdAt),
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[600],
                        ),
                      ),
                      if (transaction.description != null && transaction.description!.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          transaction.description!,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[700],
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                // Amount
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '${isCredit ? '+' : '-'} ${transaction.amount.toStringAsFixed(2)}',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: isCredit ? Colors.green[700] : Colors.red[700],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'ج.م',
                      style: TextStyle(
                        fontSize: 10,
                        color: Colors.grey[600],
                        fontWeight: FontWeight.w500,
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

  IconData _getTransactionTypeIcon(String type) {
    switch (type) {
      case 'top_up':
        return Icons.add_circle;
      case 'trip_payment':
        return Icons.two_wheeler;
      case 'refund':
        return Icons.refresh;
      case 'adjustment':
        return Icons.tune;
      case 'penalty':
        return Icons.warning;
      case 'subscription':
        return Icons.credit_card;
      default:
        return Icons.receipt;
    }
  }

  String _getTransactionTypeText(String type) {
    switch (type) {
      case 'top_up':
        return 'شحن المحفظة';
      case 'trip_payment':
        return 'دفع رحلة';
      case 'refund':
        return 'استرجاع';
      case 'adjustment':
        return 'تعديل';
      case 'penalty':
        return 'غرامة';
      case 'subscription':
        return 'اشتراك';
      default:
        return 'معاملة';
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'failed':
        return Colors.red;
      case 'cancelled':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  String _getStatusText(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return 'مكتملة';
      case 'pending':
        return 'قيد الانتظار';
      case 'failed':
        return 'فاشلة';
      case 'cancelled':
        return 'ملغاة';
      default:
        return status;
    }
  }

  Widget _buildPromoCodeSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'ضيف البروموكود',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w500,
            color: Colors.black,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _promoCodeController,
                decoration: InputDecoration(
                  hintText: 'بروموكود',
                  hintStyle: TextStyle(
                    color: Colors.grey[400],
                  ),
                  filled: true,
                  fillColor: Colors.grey[100],
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 14,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Container(
              decoration: BoxDecoration(
                color: Color(AppConstants.primaryColor),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: _isLoadingTransactions ? null : _activatePromoCode,
                  borderRadius: BorderRadius.circular(12),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 14,
                    ),
                    child: _isLoadingTransactions
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.check,
                                color: Colors.white,
                                size: 20,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'تفعيل',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

