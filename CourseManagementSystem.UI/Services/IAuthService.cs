using CourseManagementSystem.UI.Models;

namespace CourseManagementSystem.UI.Services
{
    public interface IAuthService
    {
        Task<ApiResponse<UserViewModel>> LoginAsync(LoginViewModel model);
        Task<ApiResponse<UserViewModel>> RegisterAsync(RegisterViewModel model);
        Task<ApiResponse<UserViewModel>> GetUserInfoAsync();
        Task<bool> LogoutAsync();
    }
}
