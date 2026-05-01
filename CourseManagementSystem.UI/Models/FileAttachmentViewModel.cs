namespace CourseManagementSystem.UI.Models
{
    public class FileAttachmentViewModel
    {
        public int Id { get; set; }
        public string Filename { get; set; } = string.Empty;
        public string StoredName { get; set; } = string.Empty;
        public string FilePath { get; set; } = string.Empty;
        public string MimeType { get; set; } = string.Empty;
        public long FileSize { get; set; }
        public int UploadedBy { get; set; }
        public int? CourseId { get; set; }
        public string? Subtype { get; set; } // 'assignment' or 'resource'
        public string? CreatedAt { get; set; }
        public string? FileUrl { get; set; }

        public string HumanReadableSize
        {
            get
            {
                string[] units = { "B", "KB", "MB", "GB" };
                double size = FileSize;
                int unitIndex = 0;
                while (size >= 1024 && unitIndex < units.Length - 1)
                {
                    size /= 1024;
                    unitIndex++;
                }
                return $"{size:F2} {units[unitIndex]}";
            }
        }
    }
}
