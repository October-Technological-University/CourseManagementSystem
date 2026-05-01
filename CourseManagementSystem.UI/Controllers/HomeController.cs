using Microsoft.AspNetCore.Mvc;
using CourseManagementSystem.UI.Models;
using CourseManagementSystem.UI.Services;
using System.Diagnostics;

namespace CourseManagementSystem.UI.Controllers
{
    public class HomeController : Controller
    {
        private readonly ILogger<HomeController> _logger;
        private readonly ICourseService _courseService;
        private readonly IAuthService _authService;

        public HomeController(ILogger<HomeController> logger, ICourseService courseService, IAuthService authService)
        {
            _logger = logger;
            _courseService = courseService;
            _authService = authService;
        }

        public async Task<IActionResult> Index(string? keyword)
        {
            var result = await _courseService.GetAllCoursesAsync(keyword);
            
            // Check if user is authenticated and get their info for the view
            var userInfo = await _authService.GetUserInfoAsync();
            ViewBag.IsAuthenticated = userInfo != null && userInfo.Success;
            ViewBag.UserInfo = userInfo?.Data;

            return View(result.Data ?? new List<CourseViewModel>());
        }

        [HttpPost]
        public async Task<IActionResult> Enroll(int courseId)
        {
            var userInfo = await _authService.GetUserInfoAsync();
            if (userInfo == null || !userInfo.Success)
            {
                return RedirectToAction("Login", "Account");
            }

            var result = await _courseService.EnrollInCourseAsync(courseId, userInfo.Data.Id);
            if (result.Success)
            {
                TempData["SuccessMessage"] = "Successfully enrolled in the course!";
            }
            else
            {
                TempData["ErrorMessage"] = result.Error ?? "Failed to enroll in the course.";
            }

            return RedirectToAction(nameof(Index));
        }

        public IActionResult Privacy()
        {
            return View();
        }

        [ResponseCache(Duration = 0, Location = ResponseCacheLocation.None, NoStore = true)]
        public IActionResult Error()
        {
            return View(new ErrorViewModel { RequestId = Activity.Current?.Id ?? HttpContext.TraceIdentifier });
        }
    }
}
