using System.ComponentModel.DataAnnotations;

namespace CourseManagementSystem.UI.Models
{
    public class CreateCourseViewModel
    {
        [Required]
        [Display(Name = "Course Name")]
        public string Name { get; set; } = string.Empty;

        [Required]
        [Display(Name = "Course Code")]
        public string Code { get; set; } = string.Empty;

        [Display(Name = "Description")]
        public string? Description { get; set; }

        [Required]
        [Range(1, 1000)]
        public int Capacity { get; set; } = 30;

        [Required]
        [DataType(DataType.Date)]
        [Display(Name = "Start Date")]
        public DateTime StartDate { get; set; } = DateTime.Now;

        [Required]
        [DataType(DataType.Date)]
        [Display(Name = "End Date")]
        public DateTime EndDate { get; set; } = DateTime.Now.AddMonths(3);
    }

    public class FileUploadModel
    {
        [Required]
        public IFormFile File { get; set; }
        public int CourseId { get; set; }
        public string? Subtype { get; set; } // 'assignment' or 'resource'
    }
}
