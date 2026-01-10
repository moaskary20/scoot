import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'dart:ui' as ui;
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/models/trip_model.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../../core/services/language_service.dart';

class TripDetailsScreen extends StatelessWidget {
  final TripModel trip;

  const TripDetailsScreen({
    super.key,
    required this.trip,
  });

  @override
  Widget build(BuildContext context) {
    final languageService = Provider.of<LanguageService>(context, listen: false);
    final localizations = AppLocalizations.of(context);
    final isArabic = languageService.isArabic;
    final dateFormat = DateFormat('dd/MM/yyyy - HH:mm', isArabic ? 'ar' : 'en');
    final statusColor = _getStatusColor(trip.status);
    final paymentStatusColor = _getPaymentStatusColor(trip.paymentStatus);

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
            AppLocalizations.of(context)?.tripDetails ?? 'تفاصيل الرحلة',
            style: const TextStyle(
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
                _buildHeaderCard(trip, dateFormat, statusColor, paymentStatusColor, localizations),
                const SizedBox(height: 16),
                // Trip Information Section
                _buildSectionTitle(localizations?.tripInformation ?? 'معلومات الرحلة', Icons.info_outline),
                const SizedBox(height: 12),
                _buildTripInfoCard(trip, dateFormat, localizations),
                // Penalty Section (if exists)
                if (trip.penalty != null || trip.penaltyAmount > 0) ...[
                  const SizedBox(height: 16),
                  _buildSectionTitle(localizations?.penaltyDetails ?? 'تفاصيل الغرامة', Icons.warning_amber_rounded),
                  const SizedBox(height: 12),
                  _buildPenaltyCard(trip, localizations, dateFormat),
                ],
                // Zone Exit Warning (if exists)
                if (trip.zoneExitDetected) ...[
                  const SizedBox(height: 16),
                  _buildSectionTitle(localizations?.warning ?? 'تحذير', Icons.location_off),
                  const SizedBox(height: 12),
                  _buildZoneExitCard(trip, localizations),
                ],
                // Payment Details Section
                const SizedBox(height: 16),
                _buildSectionTitle(localizations?.paymentDetails ?? 'تفاصيل الدفع', Icons.payment),
                const SizedBox(height: 12),
                _buildPaymentDetailsCard(trip, paymentStatusColor, localizations),
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
    AppLocalizations? loc,
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
                            ? '${loc?.scooter ?? 'سكوتر'} ${trip.scooterCode}'
                            : '${loc?.tripNumber ?? 'رحلة رقم'} ${trip.id}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${loc?.tripNumber ?? 'رحلة رقم'} ${trip.id}',
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
                    '${trip.cost.toStringAsFixed(2)} ${loc?.egp ?? 'ج.م'}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    trip.durationMinutes != null
                        ? _formatDuration(trip.durationMinutes!, loc)
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
                      _getStatusText(trip.status, loc),
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
                      _getPaymentStatusText(trip.paymentStatus, loc),
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

  Widget _buildTripInfoCard(TripModel trip, DateFormat dateFormat, AppLocalizations? loc) {
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
            label: loc?.startTime ?? 'وقت البدء',
            value: dateFormat.format(trip.startTime),
            iconColor: Colors.green,
          ),
          if (trip.endTime != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.check_circle_outline,
              label: loc?.endTime ?? 'وقت الانتهاء',
              value: dateFormat.format(trip.endTime!),
              iconColor: Colors.blue,
            ),
          ],
          if (trip.durationMinutes != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.timer,
              label: loc?.duration ?? 'المدة',
              value: _formatDuration(trip.durationMinutes!, loc),
              iconColor: Colors.orange,
            ),
          ],
          if (trip.startLatitude != null && trip.startLongitude != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.location_on,
              label: loc?.startPoint ?? 'نقطة البداية',
              value: '${trip.startLatitude!.toStringAsFixed(6)}, ${trip.startLongitude!.toStringAsFixed(6)}',
              iconColor: Colors.green,
            ),
          ],
          if (trip.endLatitude != null && trip.endLongitude != null) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.location_on,
              label: loc?.endPoint ?? 'نقطة النهاية',
              value: '${trip.endLatitude!.toStringAsFixed(6)}, ${trip.endLongitude!.toStringAsFixed(6)}',
              iconColor: Colors.red,
            ),
          ],
          if (trip.notes != null && trip.notes!.isNotEmpty) ...[
            const Divider(height: 24),
            _buildInfoRow(
              icon: Icons.note,
              label: loc?.notes ?? 'ملاحظات',
              value: trip.notes!,
              iconColor: Colors.grey,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPenaltyCard(TripModel trip, AppLocalizations? loc, DateFormat dateFormat) {
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
                          : (loc?.penaltyDefault ?? 'غرامة'),
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.red[900],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${trip.penaltyAmount.toStringAsFixed(2)} ${loc?.egp ?? 'ج.م'}',
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
                    loc?.penaltyDescription ?? 'وصف الغرامة:',
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
                  '${loc?.penaltyType ?? 'النوع:'} ${_getPenaltyTypeText(penalty.type, loc)}',
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
                    _getPenaltyStatusText(penalty.status, loc),
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
                    '${loc?.appliedDate ?? 'تاريخ التطبيق:'} ${dateFormat.format(penalty.appliedAt!)}',
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

  Widget _buildZoneExitCard(TripModel trip, AppLocalizations? loc) {
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
                  loc?.zoneExitMessage ?? 'تم اكتشاف خروج من المنطقة المسموحة',
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

  Widget _buildPaymentDetailsCard(TripModel trip, Color paymentStatusColor, AppLocalizations? loc) {
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
              label: loc?.discount ?? 'الخصم',
              amount: -trip.discountAmount,
              color: Colors.green[700]!,
              isDiscount: true,
              loc: loc,
            ),
            const Divider(height: 24),
          ],
          if (trip.penaltyAmount > 0) ...[
            _buildPaymentRow(
              label: loc?.penalty ?? 'الغرامة',
              amount: trip.penaltyAmount,
              color: Colors.red[700]!,
              isPenalty: true,
              loc: loc,
            ),
            const Divider(height: 24),
          ],
          _buildPaymentRow(
            label: loc?.totalCost ?? 'إجمالي التكلفة',
            amount: trip.cost,
            color: Colors.black,
            isTotal: true,
            loc: loc,
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
    AppLocalizations? loc,
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
          '${isDiscount ? '-' : (isPenalty ? '+' : '')}${amount.abs().toStringAsFixed(2)} ${loc?.egp ?? 'ج.م'}',
          style: TextStyle(
            fontSize: isTotal ? 18 : 14,
            fontWeight: isTotal ? FontWeight.bold : FontWeight.w500,
            color: color,
          ),
        ),
      ],
    );
  }

  String _formatDuration(int minutes, AppLocalizations? loc) {
    if (minutes <= 0) {
      if (loc != null) {
        return '0 ${loc.minutes}';
      }
      return '0 دقيقة';
    }
    final hours = minutes ~/ 60;
    final mins = minutes % 60;
    if (hours > 0) {
      if (loc != null) {
        return loc.formatDurationText(hours, mins);
      }
      return '${hours} ساعة ${mins} دقيقة';
    }
    if (loc != null) {
      return loc.formatMinutesText(mins);
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

  String _getStatusText(String status, AppLocalizations? loc) {
    switch (status) {
      case 'completed':
        return loc?.completed ?? 'مكتملة';
      case 'active':
        return loc?.active ?? 'نشطة';
      case 'cancelled':
        return loc?.cancelled ?? 'ملغاة';
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

  String _getPaymentStatusText(String status, AppLocalizations? loc) {
    switch (status) {
      case 'paid':
        return loc?.paid ?? 'مدفوع';
      case 'partially_paid':
        return loc?.partiallyPaid ?? 'مدفوع جزئياً';
      case 'unpaid':
        return loc?.unpaid ?? 'غير مدفوع';
      default:
        return status;
    }
  }

  String _getPenaltyTypeText(String type, AppLocalizations? loc) {
    switch (type) {
      case 'zone_exit':
        return loc?.penaltyTypeZoneExit ?? 'خروج من المنطقة';
      case 'forbidden_parking':
        return loc?.penaltyTypeForbiddenParking ?? 'ركن في مكان محظور';
      case 'unlocked_scooter':
        return loc?.penaltyTypeUnlockedScooter ?? 'عدم قفل السكوتر';
      case 'other':
        return loc?.penaltyTypeOther ?? 'أخرى';
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

  String _getPenaltyStatusText(String status, AppLocalizations? loc) {
    switch (status) {
      case 'paid':
        return loc?.paid ?? 'مدفوع';
      case 'pending':
        return loc?.penaltyStatusPending ?? 'قيد الانتظار';
      case 'waived':
        return loc?.penaltyStatusWaived ?? 'ملغاة';
      case 'cancelled':
        return loc?.cancelled ?? 'ملغاة';
      default:
        return status;
    }
  }
}
