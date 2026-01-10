import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:ui' as ui;
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/language_service.dart';
import '../../../core/l10n/app_localizations.dart';

class TopUpScreen extends StatefulWidget {
  const TopUpScreen({super.key});

  @override
  State<TopUpScreen> createState() => _TopUpScreenState();
}

class _TopUpScreenState extends State<TopUpScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _amountController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = false;

  @override
  void dispose() {
    _amountController.dispose();
    super.dispose();
  }

  Future<void> _processPayment() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final amount = double.tryParse(_amountController.text.trim());
    if (amount == null || amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context)?.pleaseEnterValidAmount ?? 'يرجى إدخال مبلغ صحيح'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    if (amount < 1) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context)?.minimumCharge ?? 'الحد الأدنى للشحن هو 1 جنيه'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      // Create payment and get Paymob URL
      final result = await _apiService.topUpWallet(
        amount: amount,
        paymentMethod: 'paymob',
      );

      if (mounted) {
        setState(() {
          _isLoading = false;
        });

        if (result['success'] == true) {
          final paymentUrl = result['data']['payment_url'];
          
          // Show confirmation dialog
          final shouldProceed = await showDialog<bool>(
            context: context,
            builder: (context) => AlertDialog(
              title: Text(AppLocalizations.of(context)?.confirmPayment ?? 'تأكيد الدفع'),
              content: Text(
                '${AppLocalizations.of(context)?.paymentPageWillOpen ?? 'سيتم فتح صفحة الدفع لشحن رصيدك بمبلغ'} ${amount.toStringAsFixed(2)} ${AppLocalizations.of(context)?.egp ?? 'جنيه'}',
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context, false),
                  child: Text(AppLocalizations.of(context)?.cancel ?? 'إلغاء'),
                ),
                ElevatedButton(
                  onPressed: () => Navigator.pop(context, true),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Color(AppConstants.primaryColor),
                    foregroundColor: Colors.white,
                  ),
                  child: Text(AppLocalizations.of(context)?.continueText ?? 'متابعة'),
                ),
              ],
            ),
          );

          if (shouldProceed == true && mounted) {
            // Open Paymob payment page
            final uri = Uri.parse(paymentUrl);
            if (await canLaunchUrl(uri)) {
              await launchUrl(
                uri,
                mode: LaunchMode.externalApplication,
              );
              
              // Show success message and wait a bit before going back
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(AppLocalizations.of(context)?.paymentPageOpened ?? 'تم فتح صفحة الدفع. بعد إتمام الدفع، سيتم تحديث رصيدك تلقائياً'),
                    backgroundColor: Colors.green,
                    duration: Duration(seconds: 4),
                  ),
                );
                
                // Wait a bit then go back
                Future.delayed(const Duration(seconds: 2), () {
                  if (mounted) {
                    Navigator.pop(context, true); // Return true to indicate payment initiated
                  }
                });
              }
            } else {
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(AppLocalizations.of(context)?.cannotOpenPaymentPage ?? 'لا يمكن فتح صفحة الدفع'),
                    backgroundColor: Colors.red,
                  ),
                );
              }
            }
          }
        } else {
          final localizations = AppLocalizations.of(context);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? (localizations?.paymentError ?? 'حدث خطأ في إنشاء عملية الدفع')),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
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

  @override
  Widget build(BuildContext context) {
    final languageService = Provider.of<LanguageService>(context, listen: false);
    final localizations = AppLocalizations.of(context);
    final isArabic = languageService.isArabic;
    
    return Directionality(
      textDirection: isArabic ? ui.TextDirection.rtl : ui.TextDirection.ltr,
      child: Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.black),
            onPressed: () => Navigator.pop(context),
          ),
          title: Text(
            localizations?.topUpBalance ?? 'شحن الرصيد',
            style: const TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          centerTitle: true,
        ),
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 20),
                  // Icon
                  Center(
                    child: Container(
                      width: 100,
                      height: 100,
                      decoration: BoxDecoration(
                        color: Color(AppConstants.primaryColor).withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        Icons.account_balance_wallet,
                        size: 50,
                        color: Color(AppConstants.primaryColor),
                      ),
                    ),
                  ),
                  const SizedBox(height: 40),
                  // Title
                  Text(
                    localizations?.enterAmountToCharge ?? 'أدخل المبلغ المراد شحنه',
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.black,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    localizations?.minimumChargeAmount ?? 'الحد الأدنى للشحن: 1 جنيه',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 40),
                  // Amount Input
                  TextFormField(
                    controller: _amountController,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    decoration: InputDecoration(
                      labelText: localizations?.amountLabel ?? 'المبلغ (جنيه)',
                      hintText: localizations?.amountHint ?? '0.00',
                      prefixIcon: const Icon(Icons.attach_money),
                      suffixText: localizations?.egp ?? 'ج.م',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(
                          color: Color(AppConstants.primaryColor),
                          width: 2,
                        ),
                      ),
                      filled: true,
                      fillColor: Colors.grey[50],
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return localizations?.pleaseEnterAmount ?? 'يرجى إدخال المبلغ';
                      }
                      final amount = double.tryParse(value.trim());
                      if (amount == null) {
                        return localizations?.pleaseEnterValidNumber ?? 'يرجى إدخال رقم صحيح';
                      }
                      if (amount < 1) {
                        return localizations?.minimumChargeRequired ?? 'الحد الأدنى للشحن هو 1 جنيه';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 40),
                  // Payment Methods Info
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.blue[50],
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.blue[200]!),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.info_outline, color: Colors.blue[700], size: 20),
                            const SizedBox(width: 8),
                            Text(
                              localizations?.availablePaymentMethods ?? 'طرق الدفع المتاحة',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Colors.blue[900],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _buildPaymentMethodItem(
                          icon: Icons.credit_card,
                          title: localizations?.visaMastercard ?? 'الفيزا / الماستر كارد',
                          color: Colors.blue,
                        ),
                        const SizedBox(height: 8),
                        _buildPaymentMethodItem(
                          icon: Icons.account_balance,
                          title: localizations?.bankWallet ?? 'المحفظة البنكية',
                          color: Colors.green,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 40),
                  // Pay Button
                  SizedBox(
                    height: 56,
                    child: ElevatedButton(
                      onPressed: _isLoading ? null : _processPayment,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Color(AppConstants.primaryColor),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 2,
                      ),
                      child: _isLoading
                          ? const SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                              ),
                            )
                          : Text(
                              localizations?.pay ?? 'دفع',
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPaymentMethodItem({
    required IconData icon,
    required String title,
    required Color color,
  }) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 12),
        Text(
          title,
          style: TextStyle(
            fontSize: 14,
            color: Colors.grey[800],
          ),
        ),
      ],
    );
  }
}

