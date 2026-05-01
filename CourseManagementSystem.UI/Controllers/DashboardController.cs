using Microsoft.AspNetCore.Mvc;
using CourseManagementSystem.UI.Services;
using CourseManagementSystem.UI.Models;

namespace CourseManagementSystem.UI.Controllers
{
    public class DashboardController : Controller
    {
        private readonly IAuthService _authService;
        private readonly ICourseService _courseService;

        public DashboardController(IAuthService authService, ICourseService courseService)
        {
            _authService = authService;
            _courseService = courseService;
        }

        public async Task<IActionResult> Index()
        {
            var userInfo = await _authService.GetUserInfoAsync();
            if (userInfo == null || !userInfo.Success)
            {
                return RedirectToAction("Login", "Account");
            }

            if (string.Equals(userInfo.Data.Role, "instructor", StringComparison.OrdinalIgnoreCase))
            {
                return RedirectToAction(nameof(Instructor));
            }

            return RedirectToAction(nameof(Student));
        }

        public async Task<IActionResult> Student()
        {
            var userInfo = await _authService.GetUserInfoAsync();
            if (userInfo == null || !userInfo.Success || !string.Equals(userInfo.Data.Role, "student", StringComparison.OrdinalIgnoreCase))
            {
                return RedirectToAction("Index", "Home");
            }

            var result = await _courseService.GetCoursesByStudentAsync(userInfo.Data.Id);
            ViewBag.User = userInfo.Data;
            return View(result.Data ?? new List<CourseViewModel>());
        }

        public async Task<IActionResult> Instructor()
        {
            var userInfo = await _authService.GetUserInfoAsync();
            if (userInfo == null || !userInfo.Success || !string.Equals(userInfo.Data.Role, "instructor", StringComparison.OrdinalIgnoreCase))
            {
                return RedirectToAction("Index", "Home");
            }

            var result = await _courseService.GetCoursesByInstructorAsync(userInfo.Data.Id);
            ViewBag.User = userInfo.Data;
            return View(result.Data ?? new List<CourseViewModel>());
        }
    }
}
