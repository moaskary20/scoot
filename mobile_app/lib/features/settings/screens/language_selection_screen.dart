import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/services/language_service.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../../core/constants/app_constants.dart';
import '../../../shared/theme/app_theme.dart';

class LanguageSelectionScreen extends StatelessWidget {
  const LanguageSelectionScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final languageService = Provider.of<LanguageService>(context);
    final localizations = AppLocalizations.of(context);

    return Directionality(
      textDirection: languageService.isArabic ? TextDirection.rtl : TextDirection.ltr,
      child: Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: Icon(
              Icons.arrow_back,
              color: Colors.black,
            ),
            onPressed: () => Navigator.pop(context),
          ),
          title: Text(
            localizations?.selectLanguage ?? 'اختر اللغة',
            style: AppTheme.tajawal(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          centerTitle: true,
        ),
        body: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            children: [
              const SizedBox(height: 20),
              // Arabic Option
              _buildLanguageOption(
                context: context,
                languageService: languageService,
                languageCode: 'ar',
                languageName: localizations?.arabic ?? 'العربية',
                flag: '🇸🇦',
                isSelected: languageService.isArabic,
              ),
              const SizedBox(height: 16),
              // English Option
              _buildLanguageOption(
                context: context,
                languageService: languageService,
                languageCode: 'en',
                languageName: localizations?.english ?? 'English',
                flag: '🇬🇧',
                isSelected: languageService.isEnglish,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLanguageOption({
    required BuildContext context,
    required LanguageService languageService,
    required String languageCode,
    required String languageName,
    required String flag,
    required bool isSelected,
  }) {
    return InkWell(
      onTap: () async {
        if (languageCode == 'ar') {
          await languageService.setArabic();
        } else {
          await languageService.setEnglish();
        }
        // Restart app to apply language change
        if (context.mounted) {
          Navigator.of(context).pop();
        }
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: isSelected
              ? Color(AppConstants.primaryColor).withOpacity(0.1)
              : Colors.grey[100],
          border: Border.all(
            color: isSelected
                ? Color(AppConstants.primaryColor)
                : Colors.grey[300]!,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            Text(
              flag,
              style: const TextStyle(fontSize: 32),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                languageName,
                style: AppTheme.tajawal(
                  fontSize: 18,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected
                      ? Color(AppConstants.primaryColor)
                      : Colors.black,
                ),
              ),
            ),
            if (isSelected)
              Icon(
                Icons.check_circle,
                color: Color(AppConstants.primaryColor),
                size: 24,
              ),
          ],
        ),
      ),
    );
  }
}

