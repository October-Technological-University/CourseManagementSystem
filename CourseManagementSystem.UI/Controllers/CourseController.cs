using Microsoft.AspNetCore.Mvc;
using CourseManagementSystem.UI.Models;
using CourseManagementSystem.UI.Services;

namespace CourseManagementSystem.UI.Controllers
{
    public class CourseController : Controller
    {
        private readonly ICourseService _courseService;
        private readonly IAuthService _authService;
        private readonly ILogger<CourseController> _logger;

        public CourseController(ICourseService courseService, IAuthService authService, ILogger<CourseController> logger)
        {
            _courseService = courseService;
            _authService = authService;
            _logger = logger;
        }

        [HttpGet]
        public async Task<IActionResult> Create()
        {
            var userInfo = await _authService.GetUserInfoAsync();
            if (userInfo == null || !userInfo.Success || !string.Equals(userInfo.Data.Role, "instructor", StringComparison.OrdinalIgnoreCase))
            {
                return RedirectToAction("Index", "Home");
            }
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Create(CreateCourseViewModel model)
        {
            if (!ModelState.IsValid) return View(model);

            var result = await _courseService.CreateCourseAsync(model);
            if (result.Success)
            {
                TempData["SuccessMessage"] = "Course created successfully!";
                return RedirectToAction("Instructor", "Dashboard");
            }

            ModelState.AddModelError(string.Empty, result.Error ?? "Failed to create course.");
            return View(model);
        }

        [HttpGet]
        public async Task<IActionResult> Details(int id)
        {
            var userInfo = await _authService.GetUserInfoAsync();
            var courseResult = await _courseService.GetCourseByIdAsync(id);

            _logger.LogInformation("Details Debug: UserSuccess={UserSuccess}, UserID={UserID}, UserRole={UserRole}", 
                userInfo?.Success, userInfo?.Data?.Id, userInfo?.Data?.Role);

            if (!courseResult.Success)
            {
                _logger.LogWarning("Details Debug: Course fetch failed. Error={Error}", courseResult.Error);
                return NotFound();
            }

            var course = courseResult.Data;
            _logger.LogInformation("Details Debug: CourseID={CourseID}, InstructorID={InstructorID}", course.Id, course.InstructorId);

            ViewBag.IsInstructor = userInfo?.Success == true && userInfo.Data.Id == course.InstructorId;
            ViewBag.IsStudent = userInfo?.Success == true && string.Equals(userInfo.Data.Role, "student", StringComparison.OrdinalIgnoreCase);
            ViewBag.User = userInfo?.Data;

            if (ViewBag.IsInstructor || string.Equals(userInfo?.Data?.Role, "admin", StringComparison.OrdinalIgnoreCase))
            {
                var studentsResult = await _courseService.GetEnrolledStudentsAsync(id);
                ViewBag.Students = studentsResult.Data;
                _logger.LogInformation("Details Debug: Students count={Count}", studentsResult.Data?.Count);
            }

            var filesResult = await _courseService.GetCourseFilesAsync(id);
            ViewBag.Files = filesResult.Data ?? new List<FileAttachmentViewModel>();

            return View(course);
        }

        [HttpPost]
        public async Task<IActionResult> GenerateCode(int id)
        {
            var result = await _courseService.GenerateInviteCodeAsync(id);
            if (result.Success) TempData["InviteCode"] = result.Data;
            else TempData["ErrorMessage"] = result.Error;

            return RedirectToAction(nameof(Details), new { id });
        }

        [HttpPost]
        public async Task<IActionResult> UploadImage(int courseId, IFormFile file)
        {
            if (file == null) return BadRequest();
            var result = await _courseService.UploadCourseImageAsync(courseId, file);
            if (result.Success) TempData["SuccessMessage"] = "Image uploaded!";
            else TempData["ErrorMessage"] = result.Error;

            return RedirectToAction(nameof(Details), new { id = courseId });
        }

        [HttpPost]
        public async Task<IActionResult> UploadMaterial(int courseId, IFormFile file, string subtype)
        {
            if (file == null) return BadRequest();
            var result = await _courseService.UploadCourseMaterialAsync(courseId, file, subtype);
            if (result.Success) TempData["SuccessMessage"] = $"{subtype} uploaded!";
            else TempData["ErrorMessage"] = result.Error;

            return RedirectToAction(nameof(Details), new { id = courseId });
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> KickStudent(int courseId, int studentId)
        {
            var result = await _courseService.KickStudentAsync(courseId, studentId);
            if (result.Success) TempData["SuccessMessage"] = "Student kicked from course.";
            else TempData["ErrorMessage"] = result.Error;

            return RedirectToAction(nameof(Details), new { id = courseId });
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Delete(int id)
        {
            var result = await _courseService.DeleteCourseAsync(id);
            if (result.Success)
            {
                TempData["SuccessMessage"] = "Course deleted successfully.";
                return RedirectToAction("Instructor", "Dashboard");
            }

            TempData["ErrorMessage"] = result.Error ?? "Failed to delete course.";
            return RedirectToAction(nameof(Details), new { id });
        }

        [HttpGet]
        public async Task<IActionResult> Download(int fileId)
        {
            var fileData = await _courseService.DownloadFileAsync(fileId);
            if (fileData == null)
            {
                return NotFound("File not found or unauthorized.");
            }

            return File(fileData.Value.Content, fileData.Value.ContentType, fileData.Value.FileName);
        }
    }
}
