import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'theme.dart';

class InstructorDashboardPage extends StatelessWidget {
  const InstructorDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundDark,
      body: Row(
        children: [
          const InstructorSidebar(),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(60),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const InstructorHeader(),
                  const SizedBox(height: 40),
                  const Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 2,
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Expanded(child: StatCard(
                                  label: 'TOTAL ENROLLMENT',
                                  value: '128',
                                  trend: '+12%',
                                  subLabel: 'Active Students',
                                  subValue: '115',
                                  icon: LucideIcons.users,
                                )),
                                SizedBox(width: 24),
                                Expanded(child: StatCard(
                                  label: 'ACTION REQUIRED',
                                  value: '14',
                                  subLabel: 'Pending Grades',
                                  subValue: 'Review',
                                  isAction: true,
                                  icon: LucideIcons.clipboardCheck,
                                  accentColor: Colors.orangeAccent,
                                )),
                              ],
                            ),
                            SizedBox(height: 24),
                            LiveModuleCard(),
                          ],
                        ),
                      ),
                      SizedBox(width: 32),
                      Expanded(
                        flex: 1,
                        child: ManagementTools(),
                      ),
                    ],
                  ),
                  const SizedBox(height: 60),
                  const EnrollmentRegistry(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class InstructorSidebar extends StatelessWidget {
  const InstructorSidebar({super.key});

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
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: AppTheme.accentBlue,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Icon(LucideIcons.draftingCompass, color: Colors.white, size: 16),
              ),
              const SizedBox(width: 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'build station',
                    style: GoogleFonts.outfit(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  Text(
                    'INSTITUTIONAL PORTAL',
                    style: GoogleFonts.outfit(
                      fontSize: 9,
                      letterSpacing: 0.5,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 32),
          
          // Create New Project Button (Top in this design)
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton.icon(
              onPressed: () {},
              icon: const Icon(LucideIcons.plus, size: 18),
              label: const Text('create new project'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentBlue,
                alignment: Alignment.centerLeft,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),
          const SizedBox(height: 48),
          
          const SidebarItem(label: 'DASHBOARD', icon: LucideIcons.layoutGrid, isActive: true),
          const SidebarItem(label: 'COURSE CATALOG', icon: LucideIcons.bookOpen),
          const SidebarItem(label: 'COMMUNITY', icon: LucideIcons.users),
          
          const Spacer(),
          
          const SidebarItem(label: 'HELP CENTER', icon: LucideIcons.info, isSmall: true),
          const SidebarItem(label: 'SIGN OUT', icon: LucideIcons.logOut, isSmall: true),
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
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
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
              height: 24,
              decoration: BoxDecoration(
                color: AppTheme.accentBlue,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ]
        ],
      ),
    );
  }
}

class InstructorHeader extends StatelessWidget {
  const InstructorHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'TEACHING WORKSHOP',
              style: GoogleFonts.outfit(
                fontSize: 14,
                letterSpacing: 2,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSecondary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Instructor Dashboard',
              style: GoogleFonts.outfit(
                fontSize: 48,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
          ],
        ),
        Container(
          width: 350,
          height: 48,
          decoration: BoxDecoration(
            color: AppTheme.cardColor,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
          ),
          child: TextField(
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: 'Search students, courses...',
              hintStyle: TextStyle(color: AppTheme.textSecondary, fontSize: 14),
              prefixIcon: Icon(LucideIcons.search, size: 18, color: AppTheme.textSecondary),
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(vertical: 14),
            ),
          ),
        ),
      ],
    );
  }
}

class StatCard extends StatelessWidget {
  final String label;
  final String value;
  final String? trend;
  final String subLabel;
  final String subValue;
  final IconData icon;
  final bool isAction;
  final Color? accentColor;

  const StatCard({
    super.key,
    required this.label,
    required this.value,
    this.trend,
    required this.subLabel,
    required this.subValue,
    required this.icon,
    this.isAction = false,
    this.accentColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: accentColor?.withValues(alpha: 0.3) ?? Colors.white.withValues(alpha: 0.05)),
        boxShadow: [
          if (accentColor != null)
            BoxShadow(
              color: accentColor!.withValues(alpha: 0.1),
              blurRadius: 20,
              offset: const Offset(0, 10),
            ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: GoogleFonts.outfit(
                  fontSize: 12,
                  letterSpacing: 1.1,
                  fontWeight: FontWeight.w600,
                  color: accentColor ?? AppTheme.textSecondary,
                ),
              ),
              Icon(icon, size: 40, color: Colors.white.withValues(alpha: 0.1)),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                value,
                style: GoogleFonts.outfit(
                  fontSize: 48,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              if (trend != null) ...[
                const SizedBox(width: 8),
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Row(
                    children: [
                      const Icon(LucideIcons.trendingUp, size: 14, color: Colors.orangeAccent),
                      const SizedBox(width: 4),
                      Text(
                        trend!,
                        style: GoogleFonts.outfit(
                          fontSize: 14,
                          color: Colors.orangeAccent,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ]
            ],
          ),
          const SizedBox(height: 32),
          const Divider(color: Colors.white10),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                subLabel,
                style: GoogleFonts.outfit(
                  fontSize: 14,
                  color: AppTheme.textSecondary,
                ),
              ),
              Text(
                subValue,
                style: GoogleFonts.outfit(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: isAction ? AppTheme.textSecondary : Colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class LiveModuleCard extends StatelessWidget {
  const LiveModuleCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.orangeAccent.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        'LIVE NOW',
                        style: GoogleFonts.outfit(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: Colors.orangeAccent,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      'Module 4',
                      style: GoogleFonts.outfit(
                        fontSize: 14,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                Text(
                  'Advanced Prototyping II',
                  style: GoogleFonts.outfit(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'Students are currently working on high-fidelity interactive models. Review submission guidelines and prepare for tomorrow\'s critique session.',
                  style: GoogleFonts.outfit(
                    fontSize: 16,
                    height: 1.5,
                    color: AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(height: 40),
                Row(
                  children: [
                    ElevatedButton(
                      onPressed: () {},
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.accentBlue,
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text('Open Workshop'),
                    ),
                    const SizedBox(width: 16),
                    OutlinedButton(
                      onPressed: () {},
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Colors.white24),
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text('View Syllabus', style: TextStyle(color: Colors.white)),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 40),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Image.network(
              'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop',
              width: 250,
              height: 250,
              fit: BoxFit.cover,
            ),
          ),
        ],
      ),
    );
  }
}

class ManagementTools extends StatelessWidget {
  const ManagementTools({super.key});

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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'MANAGEMENT TOOLS',
            style: GoogleFonts.outfit(
              fontSize: 12,
              letterSpacing: 1.1,
              fontWeight: FontWeight.w600,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: 32),
          const ToolItem(label: 'Schedule Lecture', icon: LucideIcons.calendar),
          const ToolItem(label: 'Post Announcement', icon: LucideIcons.megaphone),
          const ToolItem(label: 'Generate Report', icon: LucideIcons.fileText),
        ],
      ),
    );
  }
}

class ToolItem extends StatelessWidget {
  final String label;
  final IconData icon;

  const ToolItem({super.key, required this.label, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.2),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.white),
          const SizedBox(width: 16),
          Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.white,
            ),
          ),
          const Spacer(),
          const Icon(LucideIcons.arrowRight, size: 16, color: Colors.white24),
        ],
      ),
    );
  }
}

class EnrollmentRegistry extends StatelessWidget {
  const EnrollmentRegistry({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Active Enrollment Registry',
                  style: GoogleFonts.outfit(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Current cohort performance and status tracking.',
                  style: GoogleFonts.outfit(
                    fontSize: 14,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
            ),
            TextButton(
              onPressed: () {},
              child: Row(
                children: [
                  Text('View All', style: TextStyle(color: AppTheme.textSecondary)),
                  const SizedBox(width: 4),
                  Icon(LucideIcons.chevronRight, size: 14, color: AppTheme.textSecondary),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 32),
        Container(
          decoration: BoxDecoration(
            color: AppTheme.cardColor,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
          ),
          child: Table(
            columnWidths: const {
              0: FlexColumnWidth(2),
              1: FlexColumnWidth(1),
              2: FlexColumnWidth(2),
              3: FlexColumnWidth(2),
              4: FlexColumnWidth(1.5),
              5: FlexColumnWidth(1),
            },
            children: [
              TableRow(
                decoration: const BoxDecoration(
                  border: Border(bottom: BorderSide(color: Colors.white10)),
                ),
                children: [
                  _buildHeaderCell('STUDENT'),
                  _buildHeaderCell('ID'),
                  _buildHeaderCell('FOCUS TRACK'),
                  _buildHeaderCell('PROGRESS'),
                  _buildHeaderCell('STATUS'),
                  _buildHeaderCell('ACTIONS'),
                ],
              ),
              _buildDataRow('Elena Rodriguez', 'ST - 8492', 'Parametric Design', 0.88, 'On Track', 'EL'),
              _buildDataRow('Marcus Chen', 'ST - 7311', 'Urban Systems', 0.42, 'Needs Review', 'MC', statusColor: Colors.orangeAccent),
              _buildDataRow('Sarah Jenkins', 'ST - 9024', 'Material Science', 0.95, 'On Track', 'SJ'),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildHeaderCell(String label) {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Text(
        label,
        style: GoogleFonts.outfit(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          letterSpacing: 1.1,
          color: AppTheme.textSecondary,
        ),
      ),
    );
  }

  TableRow _buildDataRow(String name, String id, String track, double progress, String status, String initials, {Color? statusColor}) {
    return TableRow(
      children: [
        Padding(
          padding: const EdgeInsets.all(24),
          child: Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: AppTheme.accentBlue.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Center(
                  child: Text(initials, style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(width: 16),
              Text(name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500)),
            ],
          ),
        ),
        _buildDataCell(id),
        _buildDataCell(track),
        Padding(
          padding: const EdgeInsets.all(24),
          child: Row(
            children: [
              Expanded(
                child: LinearProgressIndicator(
                  value: progress,
                  backgroundColor: Colors.white10,
                  color: AppTheme.accentBlue,
                  minHeight: 4,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 16),
              Text('${(progress * 100).toInt()}%', style: const TextStyle(color: Colors.white, fontSize: 12)),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(24),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: (statusColor ?? Colors.white).withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(4),
              border: Border.all(color: (statusColor ?? Colors.white).withValues(alpha: 0.1)),
            ),
            child: Text(
              status,
              style: GoogleFonts.outfit(
                fontSize: 11,
                color: statusColor ?? AppTheme.textSecondary,
              ),
            ),
          ),
        ),
        const Padding(
          padding: EdgeInsets.all(24),
          child: Icon(LucideIcons.ellipsis, color: Colors.white24, size: 18),
        ),
      ],
    );
  }

  Widget _buildDataCell(String text) {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Text(text, style: TextStyle(color: AppTheme.textSecondary, fontSize: 13)),
    );
  }
}
