import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../../core/services/language_service.dart';
import 'package:provider/provider.dart';

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
    final languageService = Provider.of<LanguageService>(context, listen: false);
    final localizations = AppLocalizations.of(context);
    final isArabic = languageService.isArabic;
    
    return Directionality(
      textDirection: isArabic ? TextDirection.rtl : TextDirection.ltr,
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
              textDirection: isArabic ? TextDirection.rtl : TextDirection.ltr,
              children: [
                Icon(
                  Icons.description,
                  color: Color(AppConstants.primaryColor),
                  size: 24,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Align(
                    alignment: isArabic ? Alignment.centerRight : Alignment.centerLeft,
                    child: Text(
                      localizations?.safeRidingGuide ?? 'دليل الركوب الآمن',
                      style: GoogleFonts.tajawal(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Color(AppConstants.secondaryColor),
                      ),
                      textAlign: isArabic ? TextAlign.right : TextAlign.left,
                    ),
                  ),
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
                    localizations?.ridingGuideLine1 ?? 'يجب أن يكون عمرك 16 سنة أو أكثر.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine2 ?? 'الركوب لشخص واحد فقط على السكوتر.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine3 ?? 'ارتدِ خوذة الحماية دائمًا أثناء الركوب.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine4 ?? 'التزم بالقيادة داخل المنطقة البرتقالية (جامعة الجلالة) فقط.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine5 ?? 'سر دائمًا على يمين الطريق وبعيدًا عن السيارات.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine6 ?? 'ممنوع صعود السكوتر على الرصيف أو السير عكس الاتجاه.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine7 ?? 'أركن السكوتر داخل المنطقة الخضراء وفي الأماكن المخصصة فقط.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine8 ?? 'تأكد من أن القفل مغلق بإحكام.',
                    Icons.check_circle,
                    isArabic,
                  ),
                  const SizedBox(height: 16),
                  _buildGuidelineItem(
                    localizations?.ridingGuideLine9 ?? 'التقط صورة واضحة للسكوتر بعد الركن.',
                    Icons.check_circle,
                    isArabic,
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
                      localizations?.startTrip ?? 'ابدأ الرحلة',
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
                      localizations?.cancel ?? 'إلغاء',
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

  Widget _buildGuidelineItem(String text, IconData icon, bool isArabic) {
    return Row(
      textDirection: isArabic ? TextDirection.rtl : TextDirection.ltr,
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
            textAlign: isArabic ? TextAlign.right : TextAlign.left,
          ),
        ),
      ],
    );
  }
}

