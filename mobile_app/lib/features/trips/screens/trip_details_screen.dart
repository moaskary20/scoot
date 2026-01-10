import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'dart:ui' as ui;
import '../../../core/constants/app_constants.dart';
import '../../../core/models/trip_model.dart';
import '../../../core/l10n/app_localizations.dart';

class TripDetailsScreen extends StatelessWidget {
  final TripModel trip;

  const TripDetailsScreen({
    super.key,
    required this.trip,
  });

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('dd/MM/yyyy - HH:mm', 'ar');
    final statusColor = _getStatusColor(trip.status);
    final paymentStatusColor = _getPaymentStatusColor(trip.paymentStatus);

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
            'تفاصيل الرحلة',
            style: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          centerTitle: true,
        ),
        body: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Card - Trip Overview
                _buildHeaderCard(trip, dateFormat, statusColor, paymentStatusColor),
                const SizedBox(height: 16),
                // Trip Information Section
                _buildSectionTitle('معلومات الرحلة', Icons.info_outline),
                const SizedBox(height: 12),
                _buildTripInfoCard(trip, dateFormat),
                // Penalty Section (if exists)
                if (trip.penalty != null || trip.penaltyAmount > 0) ...[
                  const SizedBox(height: 16),
                  _buildSectionTitle('تفاصيل الغرامة', Icons.warning_amber_rounded),
                  const SizedBox(height: 12),
                  _buildPenaltyCard(trip),
                ],
                // Zone Exit Warning (if exists)
                if (trip.zoneExitDetected) ...[
                  const SizedBox(height: 16),
                  _buildSectionTitle('تحذير', Icons.location_off),
                  const SizedBox(height: 12),
                  _buildZoneExitCard(trip),
                ],
                // Payment Details Section
                const SizedBox(height: 16),
                _buildSectionTitle('تفاصيل الدفع', Icons.payment),
                const SizedBox(height: 12),
                _buildPaymentDetailsCard(trip, paymentStatusColor),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title, IconData icon) {
    return Row(
      children: [
        Icon(
          icon,
          color: Color(AppConstants.primaryColor),
          size: 20,
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(AppConstants.primaryColor),
          ),
        ),
      ],
    );
  }

  Widget _buildHeaderCard(
    TripModel trip,
    DateFormat dateFormat,
    Color statusColor,
    Color paymentStatusColor,
  ) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Color(AppConstants.primaryColor),
            Color(AppConstants.primaryColor).withOpacity(0.8),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Color(AppConstants.primaryColor).withOpacity(0.3),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.electric_scooter,
                      color: Colors.white,
                      size: 28,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        trip.scooterCode != null && trip.scooterCode!.isNotEmpty
                            ? 'سكوتر ${trip.scooterCode}'
                            : 'رحلة رقم ${trip.id}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'رحلة رقم ${trip.id}',
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.9),
                          fontSize: 14,
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
                    '${trip.cost.toStringAsFixed(2)} ج.م',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    trip.durationMinutes != null
                        ? _formatDuration(trip.durationMinutes!)
                        : '-',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.9),
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.trip_origin,
                      size: 14,
                      color: Colors.white,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      _getStatusText(trip.status),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.payment,
                      size: 14,
                      color: Colors.white,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      _getPaymentStatusText(trip.paymentStatus),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTripInfoCard(TripModel trip, DateFormat dateFormat) {
    return Container(
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
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildInfoRow(
            icon: Icons.access_time,
            label: 'وقت البدء',
            value: dateFormat.format(trip.startTime),
            iconColor: Colors.green,
          ),
          if (trip.endTime != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.check_circle_outline,
              label: 'وقت الانتهاء',
              value: dateFormat.format(trip.endTime!),
              iconColor: Colors.blue,
            ),
          ],
          if (trip.durationMinutes != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.timer,
              label: 'المدة',
              value: _formatDuration(trip.durationMinutes!),
              iconColor: Colors.orange,
            ),
          ],
          if (trip.startLatitude != null && trip.startLongitude != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.location_on,
              label: 'نقطة البداية',
              value: '${trip.startLatitude!.toStringAsFixed(6)}, ${trip.startLongitude!.toStringAsFixed(6)}',
              iconColor: Colors.green,
            ),
          ],
          if (trip.endLatitude != null && trip.endLongitude != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.location_on,
              label: 'نقطة النهاية',
              value: '${trip.endLatitude!.toStringAsFixed(6)}, ${trip.endLongitude!.toStringAsFixed(6)}',
              iconColor: Colors.red,
            ),
          ],
          if (trip.notes != null && trip.notes!.isNotEmpty) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.note,
              label: 'ملاحظات',
              value: trip.notes!,
              iconColor: Colors.grey,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPenaltyCard(TripModel trip) {
    final penalty = trip.penalty;
    final hasPenalty = penalty != null || trip.penaltyAmount > 0;

    if (!hasPenalty) return const SizedBox.shrink();

    return Container(
      decoration: BoxDecoration(
        color: Colors.red[50],
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.red[200]!, width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.red[100]!.withOpacity(0.3),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.red[100],
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  Icons.warning_amber_rounded,
                  color: Colors.red[700],
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Show title - always show if penalty exists
                    Text(
                      (penalty != null && penalty!.title.trim().isNotEmpty)
                          ? penalty!.title
                          : 'غرامة',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.red[900],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${trip.penaltyAmount.toStringAsFixed(2)} ج.م',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.red[700],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          // Show description if available
          if (penalty != null && penalty!.description != null && penalty!.description!.toString().trim().isNotEmpty) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.red[200]!),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'وصف الغرامة:',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Colors.red[900],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    penalty!.description!,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[800],
                      height: 1.5,
                    ),
                  ),
                ],
              ),
            ),
          ],
          if (penalty != null) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.category, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  'النوع: ${_getPenaltyTypeText(penalty.type)}',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[700],
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _getPenaltyStatusColor(penalty.status).withOpacity(0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _getPenaltyStatusText(penalty.status),
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: _getPenaltyStatusColor(penalty.status),
                    ),
                  ),
                ),
              ],
            ),
            if (penalty.appliedAt != null) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    'تاريخ التطبيق: ${DateFormat('dd/MM/yyyy - HH:mm', 'ar').format(penalty.appliedAt!)}',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[700],
                    ),
                  ),
                ],
              ),
            ],
          ],
        ],
      ),
    );
  }

  Widget _buildZoneExitCard(TripModel trip) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.orange[50],
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.orange[200]!, width: 1.5),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.location_off,
                color: Colors.orange[700],
                size: 24,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'تم اكتشاف خروج من المنطقة المسموحة',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.orange[900],
                  ),
                ),
              ),
            ],
          ),
          if (trip.zoneExitDetails != null && trip.zoneExitDetails!.isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                trip.zoneExitDetails!,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[800],
                  height: 1.5,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPaymentDetailsCard(TripModel trip, Color paymentStatusColor) {
    return Container(
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
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          if (trip.discountAmount > 0) ...[
            _buildPaymentRow(
              label: 'الخصم',
              amount: -trip.discountAmount,
              color: Colors.green[700]!,
              isDiscount: true,
            ),
            const Divider(height: 24),
          ],
          if (trip.penaltyAmount > 0) ...[
            _buildPaymentRow(
              label: 'الغرامة',
              amount: trip.penaltyAmount,
              color: Colors.red[700]!,
              isPenalty: true,
            ),
            const Divider(height: 24),
          ],
          _buildPaymentRow(
            label: 'إجمالي التكلفة',
            amount: trip.cost,
            color: Colors.black,
            isTotal: true,
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
    required Color iconColor,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: iconColor, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Colors.black87,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildPaymentRow({
    required String label,
    required double amount,
    required Color color,
    bool isDiscount = false,
    bool isPenalty = false,
    bool isTotal = false,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: isTotal ? 16 : 14,
            fontWeight: isTotal ? FontWeight.bold : FontWeight.w500,
            color: isTotal ? Colors.black : Colors.grey[700],
          ),
        ),
        Text(
          '${isDiscount ? '-' : (isPenalty ? '+' : '')}${amount.abs().toStringAsFixed(2)} ج.م',
          style: TextStyle(
            fontSize: isTotal ? 18 : 14,
            fontWeight: isTotal ? FontWeight.bold : FontWeight.w500,
            color: color,
          ),
        ),
      ],
    );
  }

  String _formatDuration(int minutes) {
    if (minutes <= 0) return '0 دقيقة';
    final hours = minutes ~/ 60;
    final mins = minutes % 60;
    if (hours > 0) {
      return '${hours} ساعة ${mins} دقيقة';
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
    switch (status) {
      case 'completed':
        return 'مكتملة';
      case 'active':
        return 'نشطة';
      case 'cancelled':
        return 'ملغاة';
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
    switch (status) {
      case 'paid':
        return 'مدفوع';
      case 'partially_paid':
        return 'مدفوع جزئياً';
      case 'unpaid':
        return 'غير مدفوع';
      default:
        return status;
    }
  }

  String _getPenaltyTypeText(String type) {
    switch (type) {
      case 'zone_exit':
        return 'خروج من المنطقة';
      case 'forbidden_parking':
        return 'ركن في مكان محظور';
      case 'unlocked_scooter':
        return 'عدم قفل السكوتر';
      case 'other':
        return 'أخرى';
      default:
        return type;
    }
  }

  Color _getPenaltyStatusColor(String status) {
    switch (status) {
      case 'paid':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'waived':
        return Colors.blue;
      case 'cancelled':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  String _getPenaltyStatusText(String status) {
    switch (status) {
      case 'paid':
        return 'مدفوعة';
      case 'pending':
        return 'قيد الانتظار';
      case 'waived':
        return 'ملغاة';
      case 'cancelled':
        return 'ملغاة';
      default:
        return status;
    }
  }
}
