using System.Net.Http.Json;
using CourseManagementSystem.UI.Models;
using System.Text.Json;
using Microsoft.AspNetCore.Http;
using System.Net;

namespace CourseManagementSystem.UI.Services
{
    public class AuthService : IAuthService
    {
        private readonly HttpClient _httpClient;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private const string BaseUrl = "https://course-management-system-axbne5a2chd6a3dz.southafricanorth-01.azurewebsites.net/";

        public AuthService(HttpClient httpClient, IHttpContextAccessor httpContextAccessor)
        {
            _httpClient = httpClient;
            _httpContextAccessor = httpContextAccessor;
            _httpClient.DefaultRequestHeaders.Add("User-Agent", "CourseManagementSystem-UI/1.0");
        }

        private async Task<HttpRequestMessage> CreateRequestAsync(HttpMethod method, string url, object? body = null)
        {
            var request = new HttpRequestMessage(method, url);
            
            if (_httpContextAccessor.HttpContext?.Request.Headers.TryGetValue("Cookie", out var clientCookies) == true)
            {
                request.Headers.Add("Cookie", clientCookies.ToString());
            }

            if (body != null)
            {
                request.Content = JsonContent.Create(body);
            }

            return request;
        }

        private async Task<T?> SendAsync<T>(HttpRequestMessage request)
        {
            var response = await _httpClient.SendAsync(request);
            
            // Extract cookies from PHP response and set them in our response
            if (response.Headers.TryGetValues("Set-Cookie", out var cookies))
            {
                foreach (var cookie in cookies)
                {
                    _httpContextAccessor.HttpContext?.Response.Headers.Append("Set-Cookie", cookie);
                }
            }

            var content = await response.Content.ReadAsStringAsync();
            var options = new JsonSerializerOptions 
            { 
                PropertyNameCaseInsensitive = true,
                NumberHandling = System.Text.Json.Serialization.JsonNumberHandling.AllowReadingFromString,
                PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
            };

            return JsonSerializer.Deserialize<T>(content, options);
        }

        public async Task<ApiResponse<UserViewModel>> LoginAsync(LoginViewModel model)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Post, $"{BaseUrl}api/auth/login", new
                {
                    email = model.Email,
                    password = model.Password
                });

                var result = await SendAsync<ApiResponse<JsonElement>>(request);

                if (result != null && result.Success)
                {
                    var userJson = result.Data.GetProperty("user").ToString();
                    var user = JsonSerializer.Deserialize<UserViewModel>(userJson, new JsonSerializerOptions 
                    { 
                        PropertyNameCaseInsensitive = true,
                        NumberHandling = System.Text.Json.Serialization.JsonNumberHandling.AllowReadingFromString,
                        PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
                    });
                    return new ApiResponse<UserViewModel>
                    {
                        Success = true,
                        Message = result.Message,
                        Data = user
                    };
                }

                return new ApiResponse<UserViewModel>
                {
                    Success = false,
                    Error = result?.Error ?? "Invalid login attempt"
                };
            }
            catch (Exception ex)
            {
                return new ApiResponse<UserViewModel> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<UserViewModel>> RegisterAsync(RegisterViewModel model)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Post, $"{BaseUrl}api/auth/register", new
                {
                    email = model.Email,
                    password = model.Password,
                    first_name = model.FirstName,
                    last_name = model.LastName,
                    role = model.Role
                });

                return await SendAsync<ApiResponse<UserViewModel>>(request) ?? new ApiResponse<UserViewModel> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<UserViewModel> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<UserViewModel>> GetUserInfoAsync()
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/auth/me");
                return await SendAsync<ApiResponse<UserViewModel>>(request) ?? new ApiResponse<UserViewModel> { Success = false };
            }
            catch
            {
                return new ApiResponse<UserViewModel> { Success = false };
            }
        }

        public async Task<bool> LogoutAsync()
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Post, $"{BaseUrl}api/auth/logout");
                var response = await _httpClient.SendAsync(request);
                
                if (response.Headers.TryGetValues("Set-Cookie", out var cookies))
                {
                    foreach (var cookie in cookies)
                    {
                        _httpContextAccessor.HttpContext?.Response.Headers.Append("Set-Cookie", cookie);
                    }
                }

                return response.IsSuccessStatusCode;
            }
            catch
            {
                return false;
            }
        }
    }
}
