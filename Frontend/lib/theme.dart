import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  static const Color backgroundDark = Color(0xFF050A18);
  static const Color backgroundLight = Color(0xFF0A1128);
  static const Color cardColor = Color(0xFF161B22);
  static const Color accentBlue = Color(0xFF2361EE);
  static const Color textPrimary = Colors.white;
  static const Color textSecondary = Color(0xFF8B949E);
  static const Color inputBackground = Color(0xFF0D1117);
  static const Color borderColor = Color(0xFF30363D);

  static ThemeData get darkTheme {
    return ThemeData(
      brightness: Brightness.dark,
      scaffoldBackgroundColor: backgroundDark,
      primaryColor: accentBlue,
      textTheme: GoogleFonts.outfitTextTheme().copyWith(
        displayLarge: GoogleFonts.outfit(
          fontSize: 64,
          fontWeight: FontWeight.bold,
          color: textPrimary,
          height: 1.1,
        ),
        displayMedium: GoogleFonts.outfit(
          fontSize: 48,
          fontWeight: FontWeight.bold,
          color: textPrimary,
        ),
        bodyLarge: GoogleFonts.outfit(
          fontSize: 18,
          color: textSecondary,
          height: 1.5,
        ),
        labelLarge: GoogleFonts.outfit(
          fontSize: 14,
          fontWeight: FontWeight.w600,
          letterSpacing: 1.2,
          color: textSecondary,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white, // In the design the background of input is white
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(4),
          borderSide: BorderSide.none,
        ),
        hintStyle: GoogleFonts.outfit(
          color: Colors.grey[400],
          fontSize: 16,
        ),
        prefixIconColor: Colors.grey[600],
        suffixIconColor: Colors.grey[600],
      ),
    );
  }
}
