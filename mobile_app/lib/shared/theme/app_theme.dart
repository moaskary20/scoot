import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_constants.dart';

class AppTheme {
  // Helper method to get Tajawal text style
  static TextStyle tajawal({
    double? fontSize,
    FontWeight? fontWeight,
    Color? color,
    double? height,
  }) {
    return GoogleFonts.tajawal(
      fontSize: fontSize,
      fontWeight: fontWeight,
      color: color,
      height: height,
    );
  }
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: Color(AppConstants.primaryColor),
        brightness: Brightness.light,
      ),
      primaryColor: Color(AppConstants.primaryColor),
      scaffoldBackgroundColor: Color(AppConstants.backgroundColor),
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(AppConstants.primaryColor),
        foregroundColor: Color(AppConstants.secondaryColor),
        elevation: 0,
        centerTitle: true,
      ),
      // Apply Tajawal font to all text in the app using Google Fonts
      textTheme: GoogleFonts.tajawalTextTheme(
        TextTheme(
          displayLarge: GoogleFonts.tajawal(
            fontSize: 32,
            fontWeight: FontWeight.bold,
            color: const Color(AppConstants.secondaryColor),
          ),
          displayMedium: GoogleFonts.tajawal(
            fontSize: 28,
            fontWeight: FontWeight.bold,
            color: const Color(AppConstants.secondaryColor),
          ),
          displaySmall: GoogleFonts.tajawal(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: const Color(AppConstants.secondaryColor),
          ),
          headlineMedium: GoogleFonts.tajawal(
            fontSize: 20,
            fontWeight: FontWeight.w600,
            color: const Color(AppConstants.secondaryColor),
          ),
          bodyLarge: GoogleFonts.tajawal(
            fontSize: 16,
            color: const Color(AppConstants.secondaryColor),
          ),
          bodyMedium: GoogleFonts.tajawal(
            fontSize: 14,
            color: const Color(AppConstants.secondaryColor),
          ),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: Color(AppConstants.primaryColor),
          foregroundColor: Color(AppConstants.secondaryColor),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        filled: true,
        fillColor: Colors.grey[100],
      ),
    );
  }
}

