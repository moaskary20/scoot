import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_constants.dart';

class RidingGuideScreen extends StatelessWidget {
  final VoidCallback? onStartTrip;
  final VoidCallback? onCancel;

  const RidingGuideScreen({
    super.key,
    this.onStartTrip,
    this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Handle bar
          Container(
            margin: const EdgeInsets.only(top: 12, bottom: 8),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            child: Row(
              textDirection: TextDirection.rtl,
              children: [
                Expanded(
                  child: Align(
                    alignment: Alignment.centerRight,
                    child: Text(
                      'دليل الركوب الآمن',
                      style: GoogleFonts.tajawal(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Color(AppConstants.secondaryColor),
                      ),
                      textAlign: TextAlign.right,
                    ),
                  ),
                ),
                Icon(
                  Icons.description,
                  color: Color(AppConstants.primaryColor),
                  size: 24,
                ),
              ],
            ),
          ),

          // Icons
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              textDirection: TextDirection.rtl,
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Color(AppConstants.primaryColor).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    Icons.pedal_bike,
                    color: Color(AppConstants.primaryColor),
                    size: 32,
                  ),
                ),
                const SizedBox(width: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Color(AppConstants.primaryColor).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    Icons.electric_scooter,
                    color: Color(AppConstants.primaryColor),
                    size: 32,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Guidelines List
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                children: [
                  _buildGuidelineItem(
                    'يجب أن يكون عمرك 16 سنة أو أكثر.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'الركوب لشخص واحد فقط على السكوتر.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'ارتدِ خوذة الحماية دائمًا أثناء الركوب.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'التزم بالقيادة داخل المنطقة البرتقالية (جامعة الجلالة) فقط.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'سر دائمًا على يمين الطريق وبعيدًا عن السيارات.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'ممنوع صعود السكوتر على الرصيف أو السير عكس الاتجاه.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'أركن السكوتر داخل المنطقة الخضراء وفي الأماكن المخصصة فقط.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'تأكد من أن القفل مغلق بإحكام.',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'التقط صورة واضحة للسكوتر بعد الركن.',
                    Icons.check_circle,
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 20),

          // Action Buttons
          Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              textDirection: TextDirection.rtl,
              children: [
                // Start Trip Button
                Expanded(
                  child: ElevatedButton(
                    onPressed: onStartTrip,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Color(AppConstants.primaryColor),
                      foregroundColor: Color(AppConstants.secondaryColor),
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      elevation: 0,
                    ),
                    child: Text(
                      'ابدأ الرحلة',
                      style: GoogleFonts.tajawal(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // Cancel Button
                Expanded(
                  child: OutlinedButton(
                    onPressed: onCancel ?? () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.grey[700],
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      side: BorderSide(color: Colors.grey[300]!),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: Text(
                      'ألغ',
                      style: GoogleFonts.tajawal(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      ),
    );
  }

  Widget _buildGuidelineItem(String text, IconData icon) {
    return Row(
      textDirection: TextDirection.rtl,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(
          icon,
          color: Color(AppConstants.primaryColor),
          size: 24,
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            text,
            style: GoogleFonts.tajawal(
              fontSize: 14,
              color: Color(AppConstants.secondaryColor),
              height: 1.5,
            ),
            textAlign: TextAlign.right,
          ),
        ),
      ],
    );
  }
}

