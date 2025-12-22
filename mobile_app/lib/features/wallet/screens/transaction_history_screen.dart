import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/models/wallet_transaction_model.dart';
import '../../../core/l10n/app_localizations.dart';

class TransactionHistoryScreen extends StatefulWidget {
  const TransactionHistoryScreen({super.key});

  @override
  State<TransactionHistoryScreen> createState() => _TransactionHistoryScreenState();
}

class _TransactionHistoryScreenState extends State<TransactionHistoryScreen> {
  final ApiService _apiService = ApiService();
  List<WalletTransactionModel> _transactions = [];
  bool _isLoading = true;
  bool _hasMore = true;
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _loadTransactions();
  }

  Future<void> _loadTransactions({bool refresh = false}) async {
    if (refresh) {
      setState(() {
        _currentPage = 1;
        _transactions = [];
        _isLoading = true;
      });
    }

    try {
      final transactions = await _apiService.getWalletTransactions(
        page: _currentPage,
        perPage: 20,
      );

      if (mounted) {
        setState(() {
          if (refresh) {
            _transactions = transactions;
          } else {
            _transactions.addAll(transactions);
          }
          _hasMore = transactions.length >= 20;
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
            content: Text('${AppLocalizations.of(context)?.errorLoadingTransactions ?? 'حدث خطأ في تحميل المعاملات'}: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _refresh() async {
    await _loadTransactions(refresh: true);
  }

  void _loadMore() {
    if (!_isLoading && _hasMore) {
      setState(() {
        _currentPage++;
      });
      _loadTransactions();
    }
  }

  String _getPaymentMethodText(String? paymentMethod) {
    switch (paymentMethod) {
      case 'paymob':
        return 'Paymob';
      case 'visa':
        return 'فيزا';
      case 'mastercard':
        return 'ماستر كارد';
      case 'wallet':
        return 'المحفظة البنكية';
      case 'manual':
        return 'يدوي';
      default:
        return paymentMethod ?? 'غير محدد';
    }
  }

  String _getTransactionTypeText(String type) {
    switch (type) {
      case 'top_up':
        return 'شحن رصيد';
      case 'trip_payment':
        return 'دفع رحلة';
      case 'refund':
        return 'استرداد';
      case 'penalty':
        return 'غرامة';
      case 'adjustment':
        return 'تعديل';
      case 'subscription':
        return 'اشتراك';
      default:
        return type;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
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
    switch (status) {
      case 'completed':
        return 'مكتمل';
      case 'pending':
        return 'قيد الانتظار';
      case 'failed':
        return 'فشل';
      case 'cancelled':
        return 'ملغي';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'سجل المعاملات',
          style: TextStyle(
            color: Colors.black,
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
      ),
      body: RefreshIndicator(
          onRefresh: _refresh,
          child: _isLoading && _transactions.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : _transactions.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.receipt_long,
                            size: 64,
                            color: Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'لا توجد معاملات',
                            style: TextStyle(
                              fontSize: 18,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _transactions.length + (_hasMore ? 1 : 0),
                      itemBuilder: (context, index) {
                        if (index == _transactions.length) {
                          _loadMore();
                          return const Center(
                            child: Padding(
                              padding: EdgeInsets.all(16.0),
                              child: CircularProgressIndicator(),
                            ),
                          );
                        }

                        final transaction = _transactions[index];
                        return _buildTransactionCard(transaction);
                      },
                    ),
      ),
    );
  }

  Widget _buildTransactionCard(WalletTransactionModel transaction) {
    final dateFormat = DateFormat('yyyy-MM-dd HH:mm', 'ar');
    final isCredit = transaction.transactionType == 'credit';
    final statusColor = _getStatusColor(transaction.status);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Row
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Transaction Type
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: Color(AppConstants.primaryColor).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        _getTransactionTypeText(transaction.type),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: Color(AppConstants.primaryColor),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    // Status Badge
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _getStatusText(transaction.status),
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: statusColor,
                        ),
                      ),
                    ),
                  ],
                ),
                // Amount
                Text(
                  '${isCredit ? '+' : '-'}${transaction.amount.toStringAsFixed(2)} ج.م',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: isCredit ? Colors.green : Colors.red,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            // Description
            if (transaction.description != null && transaction.description!.isNotEmpty)
              Text(
                transaction.description!,
                style: const TextStyle(
                  fontSize: 14,
                  color: Colors.black87,
                ),
              ),
            const SizedBox(height: 12),
            // Details Row
            Row(
              children: [
                // Date
                Row(
                  children: [
                    Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                    const SizedBox(width: 4),
                    Text(
                      dateFormat.format(transaction.createdAt),
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 16),
                // Payment Method
                if (transaction.paymentMethod != null)
                  Row(
                    children: [
                      Icon(Icons.payment, size: 16, color: Colors.grey[600]),
                      const SizedBox(width: 4),
                      Text(
                        _getPaymentMethodText(transaction.paymentMethod),
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
              ],
            ),
            // Balance After
            if (transaction.balanceAfter != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Text(
                  'الرصيد بعد المعاملة: ${transaction.balanceAfter!.toStringAsFixed(2)} ج.م',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[500],
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
