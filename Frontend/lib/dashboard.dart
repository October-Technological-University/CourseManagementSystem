import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:course_management_frontend/lesson_page.dart';
import 'theme.dart';

class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundDark,
      body: Row(
        children: [
          const Sidebar(),
          Expanded(
            child: MainContent(),
          ),
        ],
      ),
    );
  }
}

class Sidebar extends StatelessWidget {
  const Sidebar({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 280,
      color: const Color(0xFF070B1D),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Logo Section
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: AppTheme.accentBlue,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(LucideIcons.draftingCompass, color: Colors.white, size: 20),
              ),
              const SizedBox(width: 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Build Station',
                    style: GoogleFonts.outfit(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  Text(
                    'Institutional Portal',
                    style: GoogleFonts.outfit(
                      fontSize: 10,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 60),
          
          // Navigation Items
          const NavItem(label: 'DASHBOARD', icon: LucideIcons.layoutGrid, isActive: true),
          const NavItem(label: 'COURSE CATALOG', icon: LucideIcons.bookOpen),
          const NavItem(label: 'COMMUNITY', icon: LucideIcons.users),
          
          const Spacer(),
          
          // Create New Project Button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: () {},
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentBlue,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: Text(
                'CREATE NEW PROJECT',
                style: GoogleFonts.outfit(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(height: 40),
          
          // Footer Items
          NavItem(label: 'HELP CENTER', icon: LucideIcons.info, isSmall: true),
          NavItem(label: 'SIGN OUT', icon: LucideIcons.logOut, isSmall: true),
        ],
      ),
    );
  }
}

class NavItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isActive;
  final bool isSmall;

  const NavItem({
    super.key,
    required this.label,
    required this.icon,
    this.isActive = false,
    this.isSmall = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: InkWell(
        onTap: () {},
        child: Row(
          children: [
            Icon(
              icon,
              size: isSmall ? 18 : 20,
              color: isActive ? Colors.white : AppTheme.textSecondary,
            ),
            const SizedBox(width: 16),
            Text(
              label,
              style: GoogleFonts.outfit(
                fontSize: isSmall ? 12 : 13,
                fontWeight: isActive ? FontWeight.bold : FontWeight.w500,
                color: isActive ? Colors.white : AppTheme.textSecondary,
                letterSpacing: 1.1,
              ),
            ),
            if (isActive) ...[
              const Spacer(),
              Container(
                width: 3,
                height: 20,
                decoration: BoxDecoration(
                  color: AppTheme.accentBlue,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ]
          ],
        ),
      ),
    );
  }
}

class MainContent extends StatelessWidget {
  const MainContent({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(60),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'STAY HARD',
                    style: GoogleFonts.outfit(
                      fontSize: 14,
                      letterSpacing: 2,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textSecondary,
                    ),
                  ).animate().fadeIn(duration: 600.ms).slideY(begin: 0.1),
                  const SizedBox(height: 12),
                  Text(
                    'Welcome back, Alex.',
                    style: GoogleFonts.outfit(
                      fontSize: 56,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ).animate().fadeIn(delay: 200.ms, duration: 800.ms),
                ],
              ),
              Row(
                children: [
                  const IconContainer(icon: LucideIcons.bell),
                  const SizedBox(width: 16),
                  const UserAvatar(),
                ],
              ).animate().fadeIn(delay: 400.ms),
            ],
          ),
          const SizedBox(height: 60),
          
          // Course Grid
          GridView.count(
            crossAxisCount: 3,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 32,
            crossAxisSpacing: 32,
            childAspectRatio: 1.1,
            children: const [
              CourseCard(
                title: 'Applied Cryptography',
                code: 'SEC-201',
                description: 'Foundations of modern encryption protocols and secur...',
                isEnrolled: false,
              ),
              CourseCard(
                title: 'Applied Cryptography',
                code: 'SEC-201',
                description: 'Foundations of modern encryption protocols and secur...',
                isEnrolled: false,
              ),
              CourseCard(
                title: 'Applied Cryptography',
                code: 'SEC-201',
                description: 'Foundations of modern encryption protocols and secur...',
                isEnrolled: false,
              ),
              CourseCard(
                title: 'Applied Cryptography',
                code: 'SEC-201',
                description: 'Foundations of modern encryption protocols and secur...',
                isEnrolled: false,
              ),
              CourseCard(
                title: 'Applied Cryptography',
                code: 'SEC-201',
                description: 'Foundations of modern encryption protocols and secur...',
                isEnrolled: false,
              ),
              CourseCard(
                title: 'Applied Cryptography',
                code: 'SEC-201',
                description: 'Foundations of modern encryption protocols and secur...',
                isEnrolled: false,
              ),
            ],
          ).animate().fadeIn(delay: 600.ms).slideY(begin: 0.05),
        ],
      ),
    );
  }
}

class CourseCard extends StatelessWidget {
  final String title;
  final String code;
  final String description;
  final bool isEnrolled;

  const CourseCard({
    super.key,
    required this.title,
    required this.code,
    required this.description,
    required this.isEnrolled,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(builder: (context) => const LessonPage()),
        );
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.cardColor,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Card Header with Pattern
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                  image: const DecorationImage(
                    image: NetworkImage('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop'),
                    fit: BoxFit.cover,
                    opacity: 0.3,
                  ),
                ),
                child: Stack(
                  children: [
                    Positioned(
                      top: 16,
                      left: 16,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.5),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          code,
                          style: GoogleFonts.jetBrainsMono(
                            fontSize: 10,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
            // Card Content
            Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: GoogleFonts.outfit(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    description,
                    style: GoogleFonts.outfit(
                      fontSize: 14,
                      color: AppTheme.textSecondary,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: isEnrolled ? Colors.green.withValues(alpha: 0.1) : Colors.blue.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          isEnrolled ? 'ENROLLED' : 'AVAILABLE',
                          style: GoogleFonts.outfit(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isEnrolled ? Colors.greenAccent : Colors.blueAccent,
                          ),
                        ),
                      ),
                      const AvatarsGroup(),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class AvatarsGroup extends StatelessWidget {
  const AvatarsGroup({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        const CircleAvatar(radius: 12, backgroundImage: NetworkImage('https://i.pravatar.cc/150?u=1')),
        const SizedBox(width: -8),
        const CircleAvatar(radius: 12, backgroundImage: NetworkImage('https://i.pravatar.cc/150?u=2')),
        const SizedBox(width: 8),
        Text(
          '+12',
          style: GoogleFonts.outfit(
            fontSize: 12,
            color: AppTheme.textSecondary,
          ),
        ),
      ],
    );
  }
}

class IconContainer extends StatelessWidget {
  final IconData icon;
  const IconContainer({super.key, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 40,
      height: 40,
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
      ),
      child: Icon(icon, color: Colors.white, size: 20),
    );
  }
}

class UserAvatar extends StatelessWidget {
  const UserAvatar({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 40,
      height: 40,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8),
        image: const DecorationImage(
          image: NetworkImage('https://i.pravatar.cc/150?u=alex'),
          fit: BoxFit.cover,
        ),
      ),
    );
  }
}

