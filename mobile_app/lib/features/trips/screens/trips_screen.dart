import 'package:flutter/material.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/models/trip_model.dart';
import '../../../core/l10n/app_localizations.dart';
import 'trip_details_screen.dart';

class TripsScreen extends StatefulWidget {
  const TripsScreen({super.key});

  @override
  State<TripsScreen> createState() => _TripsScreenState();
}

class _TripsScreenState extends State<TripsScreen> {
  final ApiService _apiService = ApiService();
  List<TripModel> _trips = [];
  bool _isLoading = true;
  bool _hasMore = true;
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _loadTrips();
  }

  Future<void> _loadTrips({bool refresh = false}) async {
    if (refresh) {
      setState(() {
        _currentPage = 1;
        _trips = [];
        _isLoading = true;
      });
    }

    try {
      final trips = await _apiService.getUserTrips(
        page: _currentPage,
        perPage: 20,
      );

      if (mounted) {
        setState(() {
          if (refresh) {
            _trips = trips;
          } else {
            _trips.addAll(trips);
          }
          _hasMore = trips.length >= 20;
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
            content: Text('${AppLocalizations.of(context)?.errorLoadingTransactions ?? 'حدث خطأ في تحميل الرحلات'}: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _refresh() async {
    await _loadTrips(refresh: true);
  }

  void _loadMore() {
    if (!_isLoading && _hasMore) {
      setState(() {
        _currentPage++;
      });
      _loadTrips();
    }
  }

  String _formatDate(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
  }

  String _formatDuration(int? minutes) {
    if (minutes == null || minutes <= 0) return '0 دقيقة';
    final hours = minutes ~/ 60;
    final mins = minutes % 60;
    if (hours > 0) {
      return '${hours} س ${mins} د';
    }
    return '$mins دقيقة';
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'completed':
        return Colors.green;
      case 'active':
        return Colors.blue;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getStatusText(String status) {
    final localizations = AppLocalizations.of(context);
    switch (status) {
      case 'completed':
        return localizations?.completed ?? 'مكتملة';
      case 'active':
        return localizations?.active ?? 'نشطة';
      case 'cancelled':
        return localizations?.cancelled ?? 'ملغاة';
      default:
        return status;
    }
  }

  Color _getPaymentStatusColor(String status) {
    switch (status) {
      case 'paid':
        return Colors.green;
      case 'partially_paid':
        return Colors.orange;
      case 'unpaid':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getPaymentStatusText(String status) {
    final localizations = AppLocalizations.of(context);
    switch (status) {
      case 'paid':
        return localizations?.paid ?? 'مدفوع';
      case 'partially_paid':
        return localizations?.partiallyPaid ?? 'مدفوع جزئياً';
      case 'unpaid':
        return localizations?.unpaid ?? 'غير مدفوع';
      default:
        return status;
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
          title: Text(
            AppLocalizations.of(context)?.myTrips ?? 'رحلاتي',
            style: const TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          centerTitle: true,
        ),
        body: RefreshIndicator(
          onRefresh: _refresh,
          child: _isLoading && _trips.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : _trips.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.electric_scooter,
                            size: 64,
                            color: Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            AppLocalizations.of(context)?.noTripsYet ?? 'لم تقم بأي رحلات بعد',
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
                      itemCount: _trips.length + (_hasMore ? 1 : 0),
                      itemBuilder: (context, index) {
                        if (index == _trips.length) {
                          _loadMore();
                          return const Center(
                            child: Padding(
                              padding: EdgeInsets.all(16.0),
                              child: CircularProgressIndicator(),
                            ),
                          );
                        }

                        final trip = _trips[index];
                        return _buildTripCard(trip);
                      },
                    ),
        ),
      ),
    );
  }

  Widget _buildTripCard(TripModel trip) {
    final statusColor = _getStatusColor(trip.status);
    final paymentStatusColor = _getPaymentStatusColor(trip.paymentStatus);
    
    // Check if trip is not fully paid
    final bool isNotFullyPaid = trip.paymentStatus == 'unpaid' || trip.paymentStatus == 'partially_paid';
    final bool hasDebt = trip.remainingAmount > 0;

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => TripDetailsScreen(trip: trip),
          ),
        );
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: isNotFullyPaid ? Colors.red.withOpacity(0.03) : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isNotFullyPaid ? Colors.red.withOpacity(0.5) : Colors.grey[200]!,
            width: isNotFullyPaid ? 2 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: isNotFullyPaid 
                  ? Colors.red.withOpacity(0.1)
                  : Colors.black.withOpacity(0.05),
              blurRadius: isNotFullyPaid ? 8 : 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
            // Warning Banner for unpaid trips
            if (isNotFullyPaid) ...[
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.red.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.red.withOpacity(0.3)),
                ),
                child: Row(
                  children: [
                    Icon(Icons.warning_amber_rounded, color: Colors.red, size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        trip.paymentStatus == 'unpaid'
                            ? 'غير مدفوع بالكامل - يرجى سداد المبلغ المتبقي'
                            : 'مدفوع جزئياً - يرجى سداد المبلغ المتبقي: ${trip.remainingAmount.toStringAsFixed(2)} ج.م',
                        style: const TextStyle(
                          fontSize: 13,
                          color: Colors.red,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],
            // Header Row (Scooter + Status + Cost)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: isNotFullyPaid 
                            ? Colors.red.withOpacity(0.1)
                            : Color(AppConstants.primaryColor).withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        Icons.electric_scooter,
                        size: 20,
                        color: isNotFullyPaid ? Colors.red : Colors.black87,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          trip.scooterCode != null && trip.scooterCode!.isNotEmpty
                              ? '${AppLocalizations.of(context)?.scooter ?? 'سكوتر'} ${trip.scooterCode}'
                              : '${AppLocalizations.of(context)?.tripNumber ?? 'رحلة رقم'} ${trip.id}',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: isNotFullyPaid ? Colors.red[900] : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _formatDate(trip.startTime),
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '${trip.cost.toStringAsFixed(2)} ${AppLocalizations.of(context)?.egp ?? 'ج.م'}',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: isNotFullyPaid ? Colors.red[900] : Colors.black,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _formatDuration(trip.durationMinutes),
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),
            // Status Row
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.trip_origin,
                        size: 14,
                        color: statusColor,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _getStatusText(trip.status),
                        style: TextStyle(
                          fontSize: 12,
                          color: statusColor,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: paymentStatusColor.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(8),
                    border: isNotFullyPaid ? Border.all(color: paymentStatusColor, width: 1.5) : null,
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isNotFullyPaid ? Icons.payment_outlined : Icons.payment,
                        size: 14,
                        color: paymentStatusColor,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _getPaymentStatusText(trip.paymentStatus),
                        style: TextStyle(
                          fontSize: 12,
                          color: paymentStatusColor,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                if (trip.zoneExitDetected) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.red.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.warning_amber_rounded,
                          size: 14,
                          color: Colors.red,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          AppLocalizations.of(context)?.outsideZone ?? 'خارج المنطقة',
                          style: const TextStyle(
                            fontSize: 12,
                            color: Colors.red,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
            if (hasDebt) ...[
              const SizedBox(height: 12),
              // Debt Warning Section (highlighted)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.red.withOpacity(0.1),
                      Colors.orange.withOpacity(0.05),
                    ],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  ),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.red.withOpacity(0.3)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.account_balance_wallet, color: Colors.red, size: 18),
                        const SizedBox(width: 8),
                        const Text(
                          'تفاصيل الدفع',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: Colors.red,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'المدفوع:',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey[700],
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${trip.paidAmount.toStringAsFixed(2)} ج.م',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.green[700],
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          width: 1,
                          height: 40,
                          color: Colors.grey[300],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'المتبقي:',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey[700],
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${trip.remainingAmount.toStringAsFixed(2)} ج.م',
                              style: const TextStyle(
                                fontSize: 14,
                                color: Colors.red,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          width: 1,
                          height: 40,
                          color: Colors.grey[300],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'الإجمالي:',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey[700],
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${trip.cost.toStringAsFixed(2)} ج.م',
                              style: const TextStyle(
                                fontSize: 14,
                                color: Colors.black87,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ] else ...[
              const SizedBox(height: 12),
              // Payment details for fully paid trips (simplified)
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${AppLocalizations.of(context)?.paidAmount ?? 'المدفوع'}: ${trip.paidAmount.toStringAsFixed(2)} ${AppLocalizations.of(context)?.egp ?? 'ج.م'}',
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.green,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  if (trip.discountAmount > 0 || trip.penaltyAmount > 0)
                    Row(
                      children: [
                        if (trip.discountAmount > 0)
                          Text(
                            '${AppLocalizations.of(context)?.discount ?? 'الخصم'}: -${trip.discountAmount.toStringAsFixed(2)} ',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.green[700],
                            ),
                          ),
                        if (trip.penaltyAmount > 0)
                          Text(
                            '${AppLocalizations.of(context)?.penalty ?? 'الغرامة'}: +${trip.penaltyAmount.toStringAsFixed(2)} ',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.red[700],
                            ),
                          ),
                      ],
                    ),
                ],
              ),
            ],
              // Show "اضغط لعرض التفاصيل" hint
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Icon(
                    Icons.arrow_back_ios,
                    size: 14,
                    color: Colors.grey[400],
                  ),
                  Text(
                    'اضغط لعرض التفاصيل',
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey[500],
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}


