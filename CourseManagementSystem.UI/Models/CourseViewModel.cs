namespace CourseManagementSystem.UI.Models
{
    public class CourseViewModel
    {
        public int Id { get; set; }
        public string Name { get; set; } = string.Empty;
        public string Code { get; set; } = string.Empty;
        public string? Description { get; set; }
        public int InstructorId { get; set; }
        public string? InstructorName { get; set; }
        public int Capacity { get; set; }
        public int EnrolledCount { get; set; }
        public string? StartDate { get; set; }
        public string? EndDate { get; set; }
        public int? CourseImageId { get; set; }
        public string? CourseImageUrl { get; set; }
        public string? CreatedAt { get; set; }
    }
}
