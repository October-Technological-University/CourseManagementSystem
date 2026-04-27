import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'theme.dart';

class ResourceHubPage extends StatelessWidget {
  const ResourceHubPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundDark,
      body: Row(
        children: [
          const Sidebar(),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(60),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const ResourceHeader(),
                  const SizedBox(height: 48),
                  const Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(flex: 3, child: UploadAndPinnedSection()),
                      SizedBox(width: 32),
                      Expanded(flex: 1, child: StorageOverview()),
                    ],
                  ),
                  const SizedBox(height: 48),
                  const AllDocumentsSection(),
                ],
              ),
            ),
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
                  Text('build station', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                  Text('INSTITUTIONAL PORTAL', style: GoogleFonts.outfit(fontSize: 9, color: AppTheme.textSecondary)),
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
              label: const Text('create new project'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentBlue,
                alignment: Alignment.centerLeft,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ),
          const SizedBox(height: 48),
          const SidebarItem(label: 'DASHBOARD', icon: LucideIcons.layoutGrid),
          const SidebarItem(label: 'COURSE CATALOG', icon: LucideIcons.bookOpen, isActive: true),
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

  const SidebarItem({super.key, required this.label, required this.icon, this.isActive = false, this.isSmall = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          Icon(icon, size: isSmall ? 18 : 20, color: isActive ? Colors.white : AppTheme.textSecondary),
          const SizedBox(width: 16),
          Text(label, style: GoogleFonts.outfit(fontSize: isSmall ? 12 : 13, fontWeight: isActive ? FontWeight.bold : FontWeight.w500, color: isActive ? Colors.white : AppTheme.textSecondary)),
          if (isActive) ...[
            const Spacer(),
            Container(width: 3, height: 24, decoration: BoxDecoration(color: AppTheme.accentBlue, borderRadius: BorderRadius.circular(2))),
          ]
        ],
      ),
    );
  }
}

class ResourceHeader extends StatelessWidget {
  const ResourceHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('ARC-402 • RESOURCE HUB', style: GoogleFonts.outfit(fontSize: 12, letterSpacing: 1.5, color: AppTheme.textSecondary)),
            const SizedBox(height: 12),
            Text('Structural Dynamics', style: GoogleFonts.outfit(fontSize: 48, fontWeight: FontWeight.bold, color: Colors.white)),
            const SizedBox(height: 12),
            Text('Central repository for project calculations, inspection reports, and reference materials.', style: GoogleFonts.outfit(fontSize: 16, color: AppTheme.textSecondary)),
          ],
        ),
        Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(color: AppTheme.cardColor, borderRadius: BorderRadius.circular(8), border: Border.all(color: Colors.white10)),
              child: Row(
                children: [
                  const Icon(LucideIcons.listFilter, size: 16, color: Colors.white),
                  const SizedBox(width: 12),
                  Text('Filter', style: GoogleFonts.outfit(color: Colors.white, fontSize: 14)),
                ],
              ),
            ),
            const SizedBox(width: 16),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(color: AppTheme.cardColor, borderRadius: BorderRadius.circular(8), border: Border.all(color: Colors.white10)),
              child: Row(
                children: [
                  const Icon(LucideIcons.search, size: 16, color: AppTheme.textSecondary),
                  const SizedBox(width: 12),
                  Text('Search Files', style: GoogleFonts.outfit(color: AppTheme.textSecondary, fontSize: 14)),
                ],
              ),
            ),
            const SizedBox(width: 32),
            const Icon(LucideIcons.bell, color: AppTheme.textSecondary, size: 20),
            const SizedBox(width: 24),
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                image: const DecorationImage(
                  image: NetworkImage('https://i.pravatar.cc/150?u=alex'),
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

class UploadAndPinnedSection extends StatelessWidget {
  const UploadAndPinnedSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Upload Box
        Expanded(
          child: Container(
            height: 300,
            decoration: BoxDecoration(
              color: AppTheme.cardColor.withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(LucideIcons.cloudUpload, size: 40, color: Colors.white24),
                const SizedBox(height: 24),
                Text('Upload Document', style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                const SizedBox(height: 8),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 40),
                  child: Text('Drag and drop files here, or click to browse.', textAlign: TextAlign.center, style: GoogleFonts.outfit(fontSize: 14, color: AppTheme.textSecondary)),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () {},
                  style: ElevatedButton.styleFrom(backgroundColor: AppTheme.accentBlue, padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
                  child: const Text('Select Files'),
                ),
                const SizedBox(height: 16),
                Text('Supports PDF, DOCX, XLSX, JPEG (Max 50MB)', style: GoogleFonts.outfit(fontSize: 11, color: AppTheme.textSecondary)),
              ],
            ),
          ),
        ),
        const SizedBox(width: 24),
        // Pinned Files
        Expanded(
          child: Column(
            children: const [
              PinnedFileCard(
                title: 'Seismic_Load_Calc.pdf',
                subtitle: 'Updated 2 hours ago by Sarah Jenkins',
                size: '4.2 MB',
                icon: LucideIcons.fileText,
                iconColor: Colors.redAccent,
              ),
              SizedBox(height: 16),
              PinnedFileCard(
                title: 'Material_Stres_Q3.xlsx',
                subtitle: 'Updated yesterday by Marcus Chen',
                size: '1.8 MB',
                icon: LucideIcons.table,
                iconColor: Colors.orangeAccent,
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class PinnedFileCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final String size;
  final IconData icon;
  final Color iconColor;

  const PinnedFileCard({super.key, required this.title, required this.subtitle, required this.size, required this.icon, required this.iconColor});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(color: AppTheme.cardColor, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.white.withValues(alpha: 0.05))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: iconColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
                child: Icon(icon, color: iconColor, size: 24),
              ),
              const Spacer(),
              const Icon(LucideIcons.pin, size: 14, color: AppTheme.textSecondary),
            ],
          ),
          const SizedBox(height: 24),
          Text(title, style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
          const SizedBox(height: 8),
          Text(subtitle, style: GoogleFonts.outfit(fontSize: 12, color: AppTheme.textSecondary)),
          const SizedBox(height: 16),
          Row(
            children: [
              const Icon(LucideIcons.pin, size: 12, color: AppTheme.textSecondary),
              const SizedBox(width: 4),
              Text('Pinned', style: GoogleFonts.outfit(fontSize: 12, color: AppTheme.textSecondary)),
              const SizedBox(width: 12),
              Text('•', style: TextStyle(color: AppTheme.textSecondary)),
              const SizedBox(width: 12),
              Text(size, style: GoogleFonts.outfit(fontSize: 12, color: AppTheme.textSecondary)),
            ],
          ),
        ],
      ),
    );
  }
}

class StorageOverview extends StatelessWidget {
  const StorageOverview({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(color: AppTheme.cardColor, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.white.withValues(alpha: 0.05))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('STORAGE OVERVIEW', style: GoogleFonts.outfit(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white)),
          const SizedBox(height: 32),
          const StorageItem(label: 'Calculations (PDF)', size: '4.2 GB', progress: 0.7, color: Colors.blueAccent),
          const SizedBox(height: 24),
          const StorageItem(label: 'Inspection Photos', size: '2.8 GB', progress: 0.4, color: Colors.orangeAccent),
          const SizedBox(height: 24),
          const StorageItem(label: 'Spreadsheets', size: '800 MB', progress: 0.2, color: Colors.purpleAccent),
          const SizedBox(height: 48),
          const Divider(color: Colors.white10),
          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Total Used', style: GoogleFonts.outfit(fontSize: 14, color: AppTheme.textSecondary)),
              RichText(
                text: TextSpan(
                  children: [
                    TextSpan(text: '5.8 GB ', style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white)),
                    TextSpan(text: '/ 15 GB', style: GoogleFonts.outfit(fontSize: 14, color: AppTheme.textSecondary)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class StorageItem extends StatelessWidget {
  final String label;
  final String size;
  final double progress;
  final Color color;

  const StorageItem({super.key, required this.label, required this.size, required this.progress, required this.color});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: GoogleFonts.outfit(fontSize: 13, color: Colors.white)),
            Text(size, style: GoogleFonts.outfit(fontSize: 13, color: AppTheme.textSecondary)),
          ],
        ),
        const SizedBox(height: 12),
        LinearProgressIndicator(value: progress, backgroundColor: Colors.white10, color: color, minHeight: 4, borderRadius: BorderRadius.circular(2)),
      ],
    );
  }
}

class AllDocumentsSection extends StatelessWidget {
  const AllDocumentsSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text('ALL DOCUMENTS', style: GoogleFonts.outfit(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textSecondary)),
            const Spacer(),
            const Icon(LucideIcons.list, size: 18, color: Colors.white),
            const SizedBox(width: 16),
            const Icon(LucideIcons.layoutGrid, size: 18, color: AppTheme.textSecondary),
          ],
        ),
        const SizedBox(height: 24),
        const FileListItem(title: 'Foundation_Settlement_Analysis.pdf', subtitle: 'PDF Document • 2.4 MB • Oct 24, 2023', icon: LucideIcons.fileText, iconColor: Colors.redAccent),
        const FileListItem(title: 'Site_Inspection_Pillar_B4.jpg', subtitle: 'Image • 4.1 MB • Oct 22, 2023', icon: LucideIcons.image, iconColor: Colors.greenAccent),
        const FileListItem(title: 'Wind_Load_Deflection_Models.xlsx', subtitle: 'Spreadsheet • 856 KB • Oct 15, 2023', icon: LucideIcons.table, iconColor: Colors.orangeAccent),
        const FileListItem(title: 'Archived_Drafts_2022', subtitle: 'Folder • 14 Items • Jan 10, 2023', icon: LucideIcons.folder, iconColor: Colors.blueAccent),
        const SizedBox(height: 32),
        Center(
          child: TextButton.icon(
            onPressed: () {},
            icon: const Icon(LucideIcons.chevronDown, size: 16, color: AppTheme.textSecondary),
            label: Text('Load More Files', style: GoogleFonts.outfit(color: AppTheme.textSecondary, fontSize: 14)),
          ),
        ),
      ],
    );
  }
}

class FileListItem extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color iconColor;

  const FileListItem({super.key, required this.title, required this.subtitle, required this.icon, required this.iconColor});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: AppTheme.cardColor, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.white.withValues(alpha: 0.05))),
      child: Row(
        children: [
          Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: iconColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)), child: Icon(icon, color: iconColor, size: 20)),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.w600, color: Colors.white)),
              const SizedBox(height: 4),
              Text(subtitle, style: GoogleFonts.outfit(fontSize: 12, color: AppTheme.textSecondary)),
            ],
          ),
          const Spacer(),
          const Icon(LucideIcons.ellipsis, color: Colors.white24, size: 18),
        ],
      ),
    );
  }
}
