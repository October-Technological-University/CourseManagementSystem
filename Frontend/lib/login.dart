import 'package:course_management_frontend/admin_dashboard.dart';
import 'package:course_management_frontend/dashboard.dart';
import 'package:course_management_frontend/instructor_dashboard.dart';
import 'package:course_management_frontend/services/api_service.dart';
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
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  final _api = ApiService();

  bool _isLoading = false;
  bool _obscurePassword = true;
  String? _errorMessage;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    // Clear previous error
    setState(() => _errorMessage = null);

    // Validate form
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final user = await _api.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );

      if (!mounted) return;

      // Navigate based on the role returned by the API
      Widget destination;
      switch (user.role) {
        case 'student':
          destination = const DashboardPage();
          break;
        case 'instructor':
          destination = const InstructorDashboardPage();
          break;
        case 'admin':
          destination = const AdminDashboardPage();
          break;
        default:
          destination = const DashboardPage();
      }

      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (context) => destination),
      );
    } on ApiException catch (e) {
      setState(() {
        _errorMessage = e.message;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Connection error. Please check your network.';
        _isLoading = false;
      });
    }
  }

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
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Center(
              child: Column(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: AppTheme.accentBlue.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: AppTheme.accentBlue.withValues(alpha: 0.3),
                      ),
                    ),
                    child: const Icon(
                      LucideIcons.lock,
                      color: AppTheme.accentBlue,
                      size: 22,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'SECURE LOGIN',
                    style: GoogleFonts.outfit(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      letterSpacing: 2,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),

            // Error Message
            if (_errorMessage != null)
              Container(
                width: double.infinity,
                margin: const EdgeInsets.only(bottom: 24),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.red.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.red.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    Icon(
                      LucideIcons.triangleAlert,
                      color: Colors.red[300],
                      size: 18,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: GoogleFonts.outfit(
                          fontSize: 13,
                          color: Colors.red[300],
                        ),
                      ),
                    ),
                  ],
                ),
              ).animate().fadeIn(duration: 300.ms).shakeX(
                    hz: 3,
                    amount: 4,
                    duration: 400.ms,
                  ),

            // Institutional Email
            const FieldLabel(label: 'INSTITUTIONAL EMAIL'),
            const SizedBox(height: 8),
            CustomInputField(
              hint: 'enter your email',
              icon: LucideIcons.mail,
              controller: _emailController,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Email is required';
                }
                if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value.trim())) {
                  return 'Enter a valid email address';
                }
                return null;
              },
              onFieldSubmitted: (_) => _handleLogin(),
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
            CustomInputField(
              hint: '••••••••',
              icon: LucideIcons.key,
              isPassword: true,
              obscureText: _obscurePassword,
              controller: _passwordController,
              onToggleObscure: () {
                setState(() => _obscurePassword = !_obscurePassword);
              },
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Password is required';
                }
                return null;
              },
              onFieldSubmitted: (_) => _handleLogin(),
            ),

            const SizedBox(height: 40),

            // Initialize Access Button
            SizedBox(
              width: double.infinity,
              height: 56,
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(8),
                  gradient: _isLoading
                      ? LinearGradient(
                          colors: [
                            const Color(0xFF2563EB).withValues(alpha: 0.6),
                            const Color(0xFF1E40AF).withValues(alpha: 0.6),
                          ],
                        )
                      : const LinearGradient(
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
                  onPressed: _isLoading ? null : _handleLogin,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.transparent,
                    disabledBackgroundColor: Colors.transparent,
                    shadowColor: Colors.transparent,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  child: _isLoading
                      ? Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Text(
                              'Authenticating...',
                              style: GoogleFonts.outfit(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                                color: Colors.white70,
                              ),
                            ),
                          ],
                        )
                      : Row(
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
      ),
    ).animate().fadeIn(delay: 200.ms).scale(begin: const Offset(0.95, 0.95));
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
  final bool obscureText;
  final TextEditingController? controller;
  final String? Function(String?)? validator;
  final VoidCallback? onToggleObscure;
  final void Function(String)? onFieldSubmitted;

  const CustomInputField({
    super.key,
    required this.hint,
    required this.icon,
    this.isPassword = false,
    this.obscureText = false,
    this.controller,
    this.validator,
    this.onToggleObscure,
    this.onFieldSubmitted,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(4),
      ),
      child: TextFormField(
        controller: controller,
        obscureText: isPassword && obscureText,
        validator: validator,
        onFieldSubmitted: onFieldSubmitted,
        style: const TextStyle(color: Colors.black, fontSize: 16),
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: Icon(icon, size: 18),
          suffixIcon: isPassword
              ? IconButton(
                  icon: Icon(
                    obscureText ? LucideIcons.eye : LucideIcons.eyeOff,
                    size: 18,
                  ),
                  onPressed: onToggleObscure,
                )
              : null,
          contentPadding: const EdgeInsets.symmetric(vertical: 18),
          errorStyle: GoogleFonts.outfit(
            fontSize: 11,
            color: Colors.red[700],
          ),
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
