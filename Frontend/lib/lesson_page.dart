import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'theme.dart';

class LessonPage extends StatelessWidget {
  const LessonPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundDark,
      body: Row(
        children: [
          const CourseSidebar(),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(60),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const VideoPlayerPlaceholder(),
                  const SizedBox(height: 48),
                  const LessonHeader(),
                  const SizedBox(height: 32),
                  const LessonNavigation(),
                  const SizedBox(height: 48),
                  const LessonTabs(),
                  const SizedBox(height: 32),
                  const Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 2,
                        child: LessonMainContent(),
                      ),
                      SizedBox(width: 48),
                      Expanded(
                        flex: 1,
                        child: LessonSidebarInfo(),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class CourseSidebar extends StatelessWidget {
  const CourseSidebar({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 280,
      color: const Color(0xFF070B1D),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: AppTheme.cardColor,
                  borderRadius: BorderRadius.circular(8),
                  image: const DecorationImage(
                    image: NetworkImage('https://i.pravatar.cc/150?u=tech'),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Technical Architecture',
                      style: GoogleFonts.outfit(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 4),
                    LinearProgressIndicator(
                      value: 0.68,
                      backgroundColor: Colors.white10,
                      color: AppTheme.accentBlue,
                      minHeight: 2,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '68% COMPLETE',
                      style: GoogleFonts.outfit(
                        fontSize: 9,
                        color: AppTheme.textSecondary,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 48),
          const SidebarItem(label: 'COURSE SUMMARY', icon: LucideIcons.bookOpen),
          const SidebarItem(label: 'MODULE 01: SETUP', icon: LucideIcons.settings),
          const SidebarItem(label: 'MODULE 02: LAYOUT', icon: LucideIcons.layoutPanelLeft, isActive: true),
          const SidebarItem(label: 'MODULE 03: LOGIC', icon: LucideIcons.cpu),
          const SidebarItem(label: 'PROJECT REVIEW', icon: LucideIcons.circleCheck),
          const Spacer(),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton.icon(
              onPressed: () {},
              icon: const Icon(LucideIcons.externalLink, size: 16),
              label: const Text('VIEW RESOURCES'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentBlue,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ),
          const SizedBox(height: 24),
          const SidebarItem(label: 'SUPPORT', icon: LucideIcons.info, isSmall: true),
          const SidebarItem(label: 'SETTINGS', icon: LucideIcons.settings, isSmall: true),
        ],
      ),
    );
  }
}

class SidebarItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isActive;
  final bool isSmall;

  const SidebarItem({
    super.key,
    required this.label,
    required this.icon,
    this.isActive = false,
    this.isSmall = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 4),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      decoration: BoxDecoration(
        color: isActive ? AppTheme.cardColor : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            size: isSmall ? 16 : 18,
            color: isActive ? Colors.white : AppTheme.textSecondary,
          ),
          const SizedBox(width: 16),
          Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: isSmall ? 11 : 12,
              fontWeight: isActive ? FontWeight.bold : FontWeight.w500,
              color: isActive ? Colors.white : AppTheme.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

class VideoPlayerPlaceholder extends StatelessWidget {
  const VideoPlayerPlaceholder({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      height: 450,
      decoration: BoxDecoration(
        color: Colors.black,
        borderRadius: BorderRadius.circular(16),
        image: const DecorationImage(
          image: NetworkImage('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop'),
          fit: BoxFit.cover,
          opacity: 0.4,
        ),
      ),
      child: Center(
        child: Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            color: AppTheme.accentBlue,
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: AppTheme.accentBlue.withValues(alpha: 0.4),
                blurRadius: 30,
                spreadRadius: 10,
              ),
            ],
          ),
          child: const Icon(LucideIcons.play, color: Colors.white, size: 32),
        ),
      ),
    );
  }
}

class LessonHeader extends StatelessWidget {
  const LessonHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white10,
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                'MODULE 02',
                style: GoogleFonts.jetBrainsMono(
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.textSecondary,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Text(
              '•  LESSON 3 OF 5',
              style: GoogleFonts.outfit(
                fontSize: 12,
                color: AppTheme.textSecondary,
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),
        Text(
          'Interface Layout &\nGrid Structures',
          style: GoogleFonts.outfit(
            fontSize: 56,
            fontWeight: FontWeight.bold,
            color: Colors.white,
            height: 1.1,
          ),
        ),
        const SizedBox(height: 24),
        ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 800),
          child: Text(
            'Master the foundational architecture of the interface. We explore the structural tokens and asymmetrical grid layouts that define the "Architectural Monolith" aesthetic, prioritizing spatial relationships.',
            style: GoogleFonts.outfit(
              fontSize: 18,
              height: 1.6,
              color: AppTheme.textSecondary,
            ),
          ),
        ),
      ],
    );
  }
}

class LessonNavigation extends StatelessWidget {
  const LessonNavigation({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppTheme.cardColor,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
          ),
          child: Row(
            children: [
              Text(
                'MARK COMPLETE',
                style: GoogleFonts.outfit(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1.1,
                  color: AppTheme.textSecondary,
                ),
              ),
              const Spacer(),
              Container(
                width: 20,
                height: 20,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white24),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () {},
                icon: const Icon(LucideIcons.arrowLeft, size: 16),
                label: const Text('PREV'),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: Colors.white10),
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: OutlinedButton(
                onPressed: () {},
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: Colors.white10),
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('NEXT', style: TextStyle(color: Colors.white)),
                    SizedBox(width: 8),
                    Icon(LucideIcons.arrowRight, size: 16, color: Colors.white),
                  ],
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class LessonTabs extends StatelessWidget {
  const LessonTabs({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.white10)),
      ),
      child: Row(
        children: [
          _TabItem(label: 'ABOUT', isActive: true),
          _TabItem(label: 'RESOURCES (2)'),
          _TabItem(label: 'NOTES'),
          _TabItem(label: 'COMMUNITY'),
        ],
      ),
    );
  }
}

class _TabItem extends StatelessWidget {
  final String label;
  final bool isActive;

  const _TabItem({required this.label, this.isActive = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(right: 40),
      padding: const EdgeInsets.symmetric(vertical: 16),
      decoration: BoxDecoration(
        border: isActive ? const Border(bottom: BorderSide(color: Colors.white, width: 2)) : null,
      ),
      child: Text(
        label,
        style: GoogleFonts.outfit(
          fontSize: 12,
          fontWeight: isActive ? FontWeight.bold : FontWeight.w500,
          color: isActive ? Colors.white : AppTheme.textSecondary,
          letterSpacing: 1.1,
        ),
      ),
    );
  }
}

class LessonMainContent extends StatelessWidget {
  const LessonMainContent({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(48),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(width: 24, height: 2, color: Colors.white38),
              const SizedBox(width: 16),
              Text(
                'Lesson Objectives',
                style: GoogleFonts.outfit(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          const SizedBox(height: 48),
          _ObjectiveItem(
            text: 'Understand the philosophical shift from strict grid systems to asymmetrical, whitespace-driven layouts.',
            isChecked: true,
          ),
          _ObjectiveItem(
            text: 'Implement the "No-Line Rule" using nested container levels (from surface-container-low to high).',
            isChecked: true,
          ),
          _ObjectiveItem(
            text: 'Construct a high-fidelity dashboard frame mimicking physical layers of state and light.',
            isChecked: false,
          ),
          const SizedBox(height: 60),
          Text(
            'KEY TERMINOLOGY',
            style: GoogleFonts.outfit(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              letterSpacing: 1.5,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: 32),
          const Row(
            children: [
              Expanded(
                child: TerminologyCard(
                  title: 'TONAL DENSITY',
                  description: 'The perceived weight of a component based on its background luminance relative to the overbase surface.',
                  icon: LucideIcons.layers,
                ),
              ),
              SizedBox(width: 24),
              Expanded(
                child: TerminologyCard(
                  title: 'GHOST BORDER',
                  description: 'A highly transparent stroke (10% opacity) used sparingly to uphold structural accessibility.',
                  icon: LucideIcons.square,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ObjectiveItem extends StatelessWidget {
  final String text;
  final bool isChecked;

  const _ObjectiveItem({required this.text, required this.isChecked});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 32),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: isChecked ? AppTheme.accentBlue.withValues(alpha: 0.1) : Colors.transparent,
              borderRadius: BorderRadius.circular(4),
              border: Border.all(color: isChecked ? AppTheme.accentBlue : Colors.white24),
            ),
            child: Icon(
              LucideIcons.check,
              size: 14,
              color: isChecked ? AppTheme.accentBlue : Colors.transparent,
            ),
          ),
          const SizedBox(width: 24),
          Expanded(
            child: Text(
              text,
              style: GoogleFonts.outfit(
                fontSize: 16,
                height: 1.5,
                color: isChecked ? Colors.white70 : AppTheme.textSecondary,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class TerminologyCard extends StatelessWidget {
  final String title;
  final String description;
  final IconData icon;

  const TerminologyCard({
    super.key,
    required this.title,
    required this.description,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.2),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: AppTheme.textSecondary),
          const SizedBox(height: 24),
          Text(
            title,
            style: GoogleFonts.outfit(
              fontSize: 13,
              fontWeight: FontWeight.bold,
              letterSpacing: 1.1,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            description,
            style: GoogleFonts.outfit(
              fontSize: 13,
              height: 1.5,
              color: AppTheme.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

class LessonSidebarInfo extends StatelessWidget {
  const LessonSidebarInfo({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const InstructorCard(),
        const SizedBox(height: 32),
        const Row(
          children: [
            Expanded(child: InfoStat(label: 'DURATION', value: '45m', icon: LucideIcons.clock)),
            SizedBox(width: 16),
            Expanded(child: InfoStat(label: 'RATING', value: '4.9', icon: LucideIcons.star)),
          ],
        ),
        const SizedBox(height: 32),
        const QuestionBox(),
      ],
    );
  }
}

class InstructorCard extends StatelessWidget {
  const InstructorCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        children: [
          Text(
            'INSTRUCTOR',
            style: GoogleFonts.outfit(
              fontSize: 10,
              fontWeight: FontWeight.bold,
              letterSpacing: 1.1,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: 32),
          const CircleAvatar(
            radius: 32,
            backgroundImage: NetworkImage('https://i.pravatar.cc/150?u=instructor'),
          ),
          const SizedBox(height: 16),
          Text(
            'Alex Mercer',
            style: GoogleFonts.outfit(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          Text(
            'LEAD ARCHITECT',
            style: GoogleFonts.outfit(
              fontSize: 12,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: 32),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              onPressed: () {},
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.white10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: const Text('VIEW PROFILE', style: TextStyle(color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }
}

class InfoStat extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const InfoStat({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 18, color: AppTheme.accentBlue),
          const SizedBox(height: 16),
          Text(
            value,
            style: GoogleFonts.outfit(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: 10,
              color: AppTheme.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

class QuestionBox extends StatelessWidget {
  const QuestionBox({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(LucideIcons.messageSquare, color: Colors.orangeAccent, size: 24),
          const SizedBox(height: 16),
          Text(
            'Have a question?',
            style: GoogleFonts.outfit(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Join our community discussion on Module 02 and ask anything, within 24h.',
            style: GoogleFonts.outfit(
              fontSize: 13,
              height: 1.5,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: 24),
          TextButton(
            onPressed: () {},
            child: Row(
              children: [
                Text(
                  'OPEN THREAD',
                  style: GoogleFonts.outfit(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(LucideIcons.arrowRight, size: 14, color: Colors.white),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
