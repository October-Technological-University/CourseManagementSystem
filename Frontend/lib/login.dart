import 'package:course_management_frontend/dashboard.dart';
import 'package:course_management_frontend/instructor_dashboard.dart';
import 'package:course_management_frontend/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

class LoginPage extends StatelessWidget {
  const LoginPage({super.key});

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isDesktop = size.width > 900;

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: RadialGradient(
            center: Alignment.bottomRight,
            radius: 1.5,
            colors: [
              Color(0xFF0D1630),
              AppTheme.backgroundDark,
            ],
          ),
        ),
        child: Stack(
          children: [
            // Background decoration lines
            Positioned.fill(
              child: CustomPaint(
                painter: BackgroundPainter(),
              ),
            ),
            Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(40),
                child: isDesktop
                    ? Row(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          const Expanded(child: HeroSection()),
                          const SizedBox(width: 80),
                          const Expanded(child: Center(child: LoginCard())),
                        ],
                      )
                    : Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          HeroSection(),
                          SizedBox(height: 60),
                          LoginCard(),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class HeroSection extends StatelessWidget {
  const HeroSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(
              'Build Station',
              style: GoogleFonts.outfit(
                fontSize: 28,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
          ],
        ).animate().fadeIn(duration: 600.ms).slideX(begin: -0.2),
        const SizedBox(height: 8),
        Text(
          'INSTITUTIONAL PORTAL',
          style: GoogleFonts.outfit(
            fontSize: 12,
            letterSpacing: 2,
            fontWeight: FontWeight.w600,
            color: AppTheme.textSecondary,
          ),
        ).animate().fadeIn(delay: 200.ms, duration: 600.ms),
        const SizedBox(height: 48),
        Text(
          'Mastering the\ndigital landscape',
          style: Theme.of(context).textTheme.displayLarge,
        ).animate().fadeIn(delay: 400.ms, duration: 800.ms).slideY(begin: 0.1),
        const SizedBox(height: 32),
        const SizedBox(
          width: 400,
          child: Text(
            'Secure access to your project environments, course catalogs, and collaborative workspaces.',
            style: TextStyle(
              fontSize: 18,
              color: AppTheme.textSecondary,
              height: 1.5,
            ),
          ),
        ).animate().fadeIn(delay: 600.ms, duration: 800.ms),
        const SizedBox(height: 48),
        Container(
          width: 120,
          height: 4,
          decoration: BoxDecoration(
            color: AppTheme.accentBlue,
            borderRadius: BorderRadius.circular(2),
            boxShadow: [
              BoxShadow(
                color: AppTheme.accentBlue.withValues(alpha: 0.5),
                blurRadius: 10,
                spreadRadius: 2,
              ),
            ],
          ),
        ).animate().fadeIn(delay: 800.ms).scaleX(begin: 0, alignment: Alignment.centerLeft),
      ],
    );
  }
}

class LoginCard extends StatefulWidget {
  const LoginCard({super.key});

  @override
  State<LoginCard> createState() => _LoginCardState();
}

class _LoginCardState extends State<LoginCard> {
  bool isStudent = true;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 450,
      padding: const EdgeInsets.all(40),
      decoration: BoxDecoration(
        color: AppTheme.cardColor.withValues(alpha: 0.8),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.4),
            blurRadius: 40,
            offset: const Offset(0, 20),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Role Selector
          Container(
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.3),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                Expanded(
                  child: RoleTab(
                    label: 'STUDENT',
                    isActive: isStudent,
                    onTap: () => setState(() => isStudent = true),
                  ),
                ),
                Expanded(
                  child: RoleTab(
                    label: 'INSTRUCTOR',
                    isActive: !isStudent,
                    onTap: () => setState(() => isStudent = false),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 40),
          
          // Institutional Email
          const FieldLabel(label: 'INSTITUTIONAL EMAIL'),
          const SizedBox(height: 8),
          const CustomInputField(
            hint: 'enter your email',
            icon: LucideIcons.mail,
          ),
          
          const SizedBox(height: 24),
          
          // Authentication Key
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const FieldLabel(label: 'AUTHENTICATION KEY'),
              TextButton(
                onPressed: () {},
                child: Text(
                  'Forgot Key?',
                  style: GoogleFonts.outfit(
                    fontSize: 12,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          const CustomInputField(
            hint: '••••••••',
            icon: LucideIcons.key,
            isPassword: true,
          ),
          
          const SizedBox(height: 40),
          
          // Initialize Access Button
          SizedBox(
            width: double.infinity,
            height: 56,
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                gradient: const LinearGradient(
                  colors: [
                    Color(0xFF2563EB),
                    Color(0xFF1E40AF),
                  ],
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF2563EB).withValues(alpha: 0.3),
                    blurRadius: 15,
                    offset: const Offset(0, 5),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (context) => isStudent 
                        ? const DashboardPage() 
                        : const InstructorDashboardPage(),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Initialize Access',
                      style: GoogleFonts.outfit(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Icon(LucideIcons.arrowRight, size: 18, color: Colors.white),
                  ],
                ),
              ),
            ),
          ).animate(onPlay: (controller) => controller.repeat(reverse: true))
           .shimmer(delay: 3.seconds, duration: 2.seconds, color: Colors.white.withValues(alpha: 0.1)),
        ],
      ),
    ).animate().fadeIn(delay: 200.ms).scale(begin: const Offset(0.95, 0.95));
  }
}

class RoleTab extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const RoleTab({
    super.key,
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: 200.ms,
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: isActive ? AppTheme.cardColor : Colors.transparent,
          borderRadius: BorderRadius.circular(6),
        ),
        child: Center(
          child: Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: 12,
              fontWeight: isActive ? FontWeight.bold : FontWeight.w500,
              letterSpacing: 1.2,
              color: isActive ? Colors.white : AppTheme.textSecondary,
            ),
          ),
        ),
      ),
    );
  }
}

class FieldLabel extends StatelessWidget {
  final String label;
  const FieldLabel({super.key, required this.label});

  @override
  Widget build(BuildContext context) {
    return Text(
      label,
      style: GoogleFonts.outfit(
        fontSize: 11,
        fontWeight: FontWeight.w600,
        letterSpacing: 1.2,
        color: AppTheme.textSecondary,
      ),
    );
  }
}

class CustomInputField extends StatelessWidget {
  final String hint;
  final IconData icon;
  final bool isPassword;

  const CustomInputField({
    super.key,
    required this.hint,
    required this.icon,
    this.isPassword = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(4),
      ),
      child: TextField(
        obscureText: isPassword,
        style: const TextStyle(color: Colors.black, fontSize: 16),
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: Icon(icon, size: 18),
          suffixIcon: isPassword ? const Icon(LucideIcons.eye, size: 18) : null,
          contentPadding: const EdgeInsets.symmetric(vertical: 18),
        ),
      ),
    );
  }
}

class BackgroundPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withValues(alpha: 0.03)
      ..strokeWidth = 1;

    // Draw diagonal lines matching the design
    for (var i = -size.width; i < size.width; i += 60) {
      canvas.drawLine(
        Offset(i.toDouble(), 0),
        Offset(i + size.height * 0.5, size.height),
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
