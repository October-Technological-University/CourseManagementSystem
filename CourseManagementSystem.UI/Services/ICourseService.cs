using CourseManagementSystem.UI.Models;

namespace CourseManagementSystem.UI.Services
{
    public interface ICourseService
    {
        Task<ApiResponse<List<CourseViewModel>>> GetAllCoursesAsync(string? keyword = null);
        Task<ApiResponse<CourseViewModel>> GetCourseByIdAsync(int id);
        Task<ApiResponse<List<CourseViewModel>>> GetCoursesByInstructorAsync(int instructorId);
        Task<ApiResponse<List<CourseViewModel>>> GetCoursesByStudentAsync(int studentId);
        Task<ApiResponse<bool>> EnrollInCourseAsync(int courseId, int studentId);
        Task<ApiResponse<CourseViewModel>> CreateCourseAsync(CreateCourseViewModel model);
        Task<ApiResponse<string>> GenerateInviteCodeAsync(int courseId);
        Task<ApiResponse<bool>> UploadCourseImageAsync(int courseId, IFormFile file);
        Task<ApiResponse<bool>> UploadCourseMaterialAsync(int courseId, IFormFile file, string subtype);
        Task<ApiResponse<List<UserViewModel>>> GetEnrolledStudentsAsync(int courseId);
        Task<ApiResponse<List<FileAttachmentViewModel>>> GetCourseFilesAsync(int courseId);
        Task<ApiResponse<bool>> DeleteCourseAsync(int courseId);
        Task<ApiResponse<bool>> KickStudentAsync(int courseId, int studentId);
        Task<(Stream Content, string ContentType, string FileName)?> DownloadFileAsync(int fileId);
    }
}
