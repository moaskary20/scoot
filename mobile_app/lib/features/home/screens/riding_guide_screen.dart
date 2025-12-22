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
                      'إزاي تركب الاسكوتر',
                      style: GoogleFonts.tajawal(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Color(AppConstants.secondaryColor),
                      ),
                      textAlign: TextAlign.right,
                    ),
                  ),
                ),
                Row(
                  textDirection: TextDirection.rtl,
                  children: [
                    Text(
                      'دليل الركوب',
                      style: GoogleFonts.tajawal(
                        fontSize: 16,
                        color: Color(AppConstants.secondaryColor),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Icon(
                      Icons.description,
                      color: Color(AppConstants.primaryColor),
                      size: 20,
                    ),
                  ],
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
                    'لازم يكون عندك 16 سنة أو أكثر',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'الركوب لشخص واحد بس',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'خليك دايما جوا المنطقة الخضرا',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'امشي على يمين الطريق',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'متطلعش بالسكوتر على الرصيف، ولا تمشي عكس الاتجاه',
                    Icons.check_circle,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    'اركن في مكان آمن وواضح، بعيد عن ممرات الناس',
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

