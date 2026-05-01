namespace CourseManagementSystem.UI.Models
{
    public class UserViewModel
    {
        public int Id { get; set; }
        public string Email { get; set; } = string.Empty;
        public string FirstName { get; set; } = string.Empty;
        public string LastName { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public int? ProfilePictureId { get; set; }
        public string? CreatedAt { get; set; }
        public string? ProfilePictureUrl { get; set; }

        public string FullName => $"{FirstName} {LastName}";
    }
}
