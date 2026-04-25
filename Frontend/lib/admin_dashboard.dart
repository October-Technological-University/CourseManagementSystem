import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'theme.dart';

class AdminDashboardPage extends StatelessWidget {
  const AdminDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundDark,
      body: Row(
        children: [
          const AdminSidebar(),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(60),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const AdminHeader(),
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
                                Expanded(child: SimpleStatCard(
                                  label: 'ACTIVE NODES',
                                  value: '1,284',
                                  trend: '+12%',
                                  icon: LucideIcons.network,
                                )),
                                SizedBox(width: 24),
                                Expanded(child: SimpleStatCard(
                                  label: 'CPU LOAD',
                                  value: '78.2%',
                                  subValue: 'High',
                                  icon: LucideIcons.cpu,
                                )),
                              ],
                            ),
                            SizedBox(height: 32),
                            UserManagementSection(),
                          ],
                        ),
                      ),
                      SizedBox(width: 32),
                      Expanded(
                        flex: 1,
                        child: Column(
                          children: [
                            SystemControls(),
                            SizedBox(height: 32),
                            AuditTrail(),
                          ],
                        ),
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

class AdminSidebar extends StatelessWidget {
  const AdminSidebar({super.key});

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
                    'Build Station',
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
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 32),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton.icon(
              onPressed: () {},
              icon: const Icon(LucideIcons.plus, size: 18),
              label: const Text('CREATE NEW PROJECT'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentBlue,
                alignment: Alignment.centerLeft,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ),
          const SizedBox(height: 48),
          const SidebarNavItem(label: 'DASHBOARD', icon: LucideIcons.layoutGrid, isActive: true),
          const SidebarNavItem(label: 'COURSE CATALOG', icon: LucideIcons.bookOpen),
          const SidebarNavItem(label: 'COMMUNITY', icon: LucideIcons.users),
          const Spacer(),
          const SidebarNavItem(label: 'HELP CENTER', icon: LucideIcons.info, isSmall: true),
          const SidebarNavItem(label: 'SIGN OUT', icon: LucideIcons.logOut, isSmall: true),
        ],
      ),
    );
  }
}

class SidebarNavItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isActive;
  final bool isSmall;

  const SidebarNavItem({
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

class AdminHeader extends StatelessWidget {
  const AdminHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Command Center',
              style: GoogleFonts.outfit(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'System Overview & Administration',
              style: GoogleFonts.outfit(
                fontSize: 14,
                color: AppTheme.textSecondary,
              ),
            ),
          ],
        ),
        Row(
          children: [
            const Icon(LucideIcons.bell, color: AppTheme.textSecondary, size: 20),
            const SizedBox(width: 24),
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                image: const DecorationImage(
                  image: NetworkImage('https://i.pravatar.cc/150?u=admin'),
                  fit: BoxFit.cover,
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class SimpleStatCard extends StatelessWidget {
  final String label;
  final String value;
  final String? trend;
  final String? subValue;
  final IconData icon;

  const SimpleStatCard({
    super.key,
    required this.label,
    required this.value,
    this.trend,
    this.subValue,
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: GoogleFonts.outfit(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1.1,
                  color: AppTheme.textSecondary,
                ),
              ),
              Icon(icon, size: 24, color: Colors.white.withValues(alpha: 0.5)),
            ],
          ),
          const SizedBox(height: 24),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                value,
                style: GoogleFonts.outfit(
                  fontSize: 40,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              if (trend != null) ...[
                const SizedBox(width: 8),
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Text(
                    trend!,
                    style: GoogleFonts.outfit(fontSize: 14, color: AppTheme.textSecondary),
                  ),
                ),
              ],
              if (subValue != null) ...[
                const SizedBox(width: 8),
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Text(
                    subValue!,
                    style: GoogleFonts.outfit(fontSize: 14, color: Colors.orangeAccent),
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

class UserManagementSection extends StatelessWidget {
  const UserManagementSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'User Management',
                style: GoogleFonts.outfit(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const Icon(LucideIcons.listFilter, color: AppTheme.textSecondary, size: 18),
            ],
          ),
          const SizedBox(height: 32),
          Table(
            columnWidths: const {
              0: FlexColumnWidth(2),
              1: FlexColumnWidth(1),
              2: FlexColumnWidth(1),
              3: FlexColumnWidth(0.5),
            },
            children: [
              TableRow(
                decoration: const BoxDecoration(
                  border: Border(bottom: BorderSide(color: Colors.white10)),
                ),
                children: [
                  _headerCell('USER'),
                  _headerCell('ROLE'),
                  _headerCell('STATUS'),
                  _headerCell('ACTIONS'),
                ],
              ),
              _userRow('John Doe', 'john@example.com', 'Admin', 'ACTIVE', true),
              _userRow('Alice Smith', 'alice@example.com', 'Editor', 'OFFLINE', false),
              _userRow('Robert Jones', 'robert@example.com', 'Viewer', 'ACTIVE', true),
            ],
          ),
        ],
      ),
    );
  }

  Widget _headerCell(String label) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Text(
        label,
        style: GoogleFonts.outfit(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: AppTheme.textSecondary,
        ),
      ),
    );
  }

  TableRow _userRow(String name, String email, String role, String status, bool isActive) {
    return TableRow(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 20),
          child: Row(
            children: [
              CircleAvatar(
                radius: 16,
                backgroundColor: Colors.white.withValues(alpha: 0.1),
                child: Text(
                  name.split(' ').map((e) => e[0]).join(),
                  style: const TextStyle(fontSize: 10, color: Colors.white),
                ),
              ),
              const SizedBox(width: 16),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500)),
                  Text(email, style: TextStyle(color: AppTheme.textSecondary, fontSize: 12)),
                ],
              ),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 24),
          child: Text(role, style: TextStyle(color: AppTheme.textSecondary, fontSize: 13)),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 24),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: isActive ? AppTheme.accentBlue.withValues(alpha: 0.1) : Colors.white.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Text(
              status,
              style: TextStyle(
                fontSize: 10,
                color: isActive ? AppTheme.accentBlue : AppTheme.textSecondary,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ),
        const Padding(
          padding: EdgeInsets.symmetric(vertical: 24),
          child: Icon(LucideIcons.ellipsis, color: Colors.white24, size: 18),
        ),
      ],
    );
  }
}

class SystemControls extends StatefulWidget {
  const SystemControls({super.key});

  @override
  State<SystemControls> createState() => _SystemControlsState();
}

class _SystemControlsState extends State<SystemControls> {
  bool maintenanceMode = false;
  bool autoScaling = true;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'System Controls',
            style: GoogleFonts.outfit(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 32),
          _controlRow('Maintenance Mode', 'SYSTEM-WIDE LOCK', maintenanceMode, (v) => setState(() => maintenanceMode = v)),
          const SizedBox(height: 24),
          _controlRow('Auto-Scaling', 'NODE ALLOCATION', autoScaling, (v) => setState(() => autoScaling = v)),
          const SizedBox(height: 32),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: OutlinedButton.icon(
              onPressed: () {},
              icon: const Icon(LucideIcons.settings, size: 16),
              label: const Text('ADVANCED CONFIG'),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.white10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _controlRow(String title, String sub, bool value, ValueChanged<bool> onChanged) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500)),
            Text(sub, style: TextStyle(color: AppTheme.textSecondary, fontSize: 10, letterSpacing: 1.1)),
          ],
        ),
        Switch(
          value: value,
          onChanged: onChanged,
          activeTrackColor: AppTheme.accentBlue,
          activeThumbColor: Colors.white,
        ),
      ],
    );
  }
}

class AuditTrail extends StatelessWidget {
  const AuditTrail({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppTheme.cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Audit Trail',
            style: GoogleFonts.outfit(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 32),
          _auditItem('Node deployment successful', 'SYSTEM • 2 MINS AGO', AppTheme.accentBlue),
          _auditItem('High memory usage detected', 'ALERT • 15 MINS AGO', Colors.orangeAccent),
          _auditItem('Admin login (John Doe)', 'AUTH • 1 HR AGO', AppTheme.textSecondary),
          _auditItem('Configuration updated', 'SYSTEM • 3 HRS AGO', AppTheme.textSecondary),
        ],
      ),
    );
  }

  Widget _auditItem(String title, String sub, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 24),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 4),
            width: 8,
            height: 8,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(color: Colors.white, fontSize: 14)),
              const SizedBox(height: 4),
              Text(sub, style: TextStyle(color: AppTheme.textSecondary, fontSize: 10, letterSpacing: 0.5)),
            ],
          ),
        ],
      ),
    );
  }
}
