using Microsoft.AspNetCore.Mvc;
using CourseManagementSystem.UI.Models;
using CourseManagementSystem.UI.Services;

namespace CourseManagementSystem.UI.Controllers
{
    public class AccountController : Controller
    {
        private readonly IAuthService _authService;

        public AccountController(IAuthService authService)
        {
            _authService = authService;
        }

        [HttpGet]
        public IActionResult Login()
        {
            if (User.Identity?.IsAuthenticated == true)
            {
                return RedirectToAction("Index", "Home");
            }
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Login(LoginViewModel model)
        {
            if (!ModelState.IsValid)
            {
                return View(model);
            }

            var result = await _authService.LoginAsync(model);

            if (result.Success)
            {
                // In a real app, we might want to also sign in locally using Cookie Authentication
                // but since we are proxying PHP cookies, the browser will have the PHP session cookie.
                // However, ASP.NET Core won't know the user is authenticated unless we also sign in locally
                // OR we implement a custom AuthenticationHandler that reads the PHP cookie.
                
                // For now, let's just redirect and assume the PHP cookie is enough for the PHP API.
                // To make User.Identity.IsAuthenticated work in C#, we'd need more work.
                
                return RedirectToAction("Index", "Home");
            }

            ModelState.AddModelError(string.Empty, result.Error ?? "Invalid login attempt.");
            return View(model);
        }

        [HttpGet]
        public IActionResult Register()
        {
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Register(RegisterViewModel model)
        {
            if (!ModelState.IsValid)
            {
                return View(model);
            }

            var result = await _authService.RegisterAsync(model);

            if (result.Success)
            {
                return RedirectToAction(nameof(Login));
            }

            ModelState.AddModelError(string.Empty, result.Error ?? "Registration failed.");
            return View(model);
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Logout()
        {
            await _authService.LogoutAsync();
            return RedirectToAction(nameof(Login));
        }
    }
}
