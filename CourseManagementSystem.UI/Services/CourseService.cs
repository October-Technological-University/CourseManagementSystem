using System.Net.Http.Json;
using CourseManagementSystem.UI.Models;
using System.Text.Json;

namespace CourseManagementSystem.UI.Services
{
    public class CourseService : ICourseService
    {
        private readonly HttpClient _httpClient;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private const string BaseUrl = "https://course-management-system-axbne5a2chd6a3dz.southafricanorth-01.azurewebsites.net/";

        public CourseService(HttpClient httpClient, IHttpContextAccessor httpContextAccessor)
        {
            _httpClient = httpClient;
            _httpContextAccessor = httpContextAccessor;
            // Add a common User-Agent to avoid being blocked by WAFs
            _httpClient.DefaultRequestHeaders.Add("User-Agent", "CourseManagementSystem-UI/1.0");
        }

        private async Task<HttpRequestMessage> CreateRequestAsync(HttpMethod method, string url, object? body = null)
        {
            var request = new HttpRequestMessage(method, url);
            
            // Forward cookies from the browser to the PHP API
            if (_httpContextAccessor.HttpContext?.Request.Headers.TryGetValue("Cookie", out var clientCookies) == true)
            {
                request.Headers.Add("Cookie", clientCookies.ToString());
            }

            if (body != null)
            {
                if (body is HttpContent content)
                {
                    request.Content = content;
                }
                else
                {
                    request.Content = JsonContent.Create(body);
                }
            }

            return request;
        }

        private async Task<T?> SendAsync<T>(HttpRequestMessage request)
        {
            try
            {
                var response = await _httpClient.SendAsync(request);
                var content = await response.Content.ReadAsStringAsync();
                
                if (string.IsNullOrWhiteSpace(content))
                {
                    return default;
                }

                // Check if the content starts with JSON markers
                var trimmed = content.Trim();
                if (!(trimmed.StartsWith("{") && trimmed.EndsWith("}")) && !(trimmed.StartsWith("[") && trimmed.EndsWith("]")))
                {
                    // Log or handle non-JSON response (e.g., PHP Error page)
                    return default;
                }
                
                var options = new JsonSerializerOptions 
                { 
                    PropertyNameCaseInsensitive = true,
                    NumberHandling = System.Text.Json.Serialization.JsonNumberHandling.AllowReadingFromString,
                    PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
                };

                return JsonSerializer.Deserialize<T>(content, options);
            }
            catch
            {
                return default;
            }
        }

        public async Task<ApiResponse<List<CourseViewModel>>> GetAllCoursesAsync(string? keyword = null)
        {
            try
            {
                var url = $"{BaseUrl}api/courses";
                if (!string.IsNullOrEmpty(keyword)) url += $"?keyword={Uri.EscapeDataString(keyword)}";
                
                var request = await CreateRequestAsync(HttpMethod.Get, url);
                return await SendAsync<ApiResponse<List<CourseViewModel>>>(request) ?? new ApiResponse<List<CourseViewModel>> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<List<CourseViewModel>> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<CourseViewModel>> GetCourseByIdAsync(int id)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/courses/{id}");
                return await SendAsync<ApiResponse<CourseViewModel>>(request) ?? new ApiResponse<CourseViewModel> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<CourseViewModel> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<List<CourseViewModel>>> GetCoursesByInstructorAsync(int instructorId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/courses/instructor/{instructorId}");
                return await SendAsync<ApiResponse<List<CourseViewModel>>>(request) ?? new ApiResponse<List<CourseViewModel>> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<List<CourseViewModel>> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<List<CourseViewModel>>> GetCoursesByStudentAsync(int studentId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/enrollments/student/{studentId}/courses");
                return await SendAsync<ApiResponse<List<CourseViewModel>>>(request) ?? new ApiResponse<List<CourseViewModel>> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<List<CourseViewModel>> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<bool>> EnrollInCourseAsync(int courseId, int studentId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Post, $"{BaseUrl}api/enrollments", new { course_id = courseId, student_id = studentId });
                var result = await SendAsync<ApiResponse<JsonElement>>(request);

                return new ApiResponse<bool>
                {
                    Success = result?.Success ?? false,
                    Message = result?.Message,
                    Error = result?.Error,
                    Data = result?.Success ?? false
                };
            }
            catch (Exception ex)
            {
                return new ApiResponse<bool> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<CourseViewModel>> CreateCourseAsync(CreateCourseViewModel model)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Post, $"{BaseUrl}api/courses", new
                {
                    name = model.Name,
                    code = model.Code,
                    description = model.Description,
                    capacity = model.Capacity,
                    start_date = model.StartDate.ToString("yyyy-MM-dd"),
                    end_date = model.EndDate.ToString("yyyy-MM-dd")
                });
                return await SendAsync<ApiResponse<CourseViewModel>>(request) ?? new ApiResponse<CourseViewModel> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<CourseViewModel> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<string>> GenerateInviteCodeAsync(int courseId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Post, $"{BaseUrl}api/courses/{courseId}/generate-code");
                return await SendAsync<ApiResponse<string>>(request) ?? new ApiResponse<string> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<string> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<bool>> UploadCourseImageAsync(int courseId, IFormFile file)
        {
            return await UploadFileAsync($"{BaseUrl}api/courses/{courseId}/course-image", file);
        }

        public async Task<ApiResponse<bool>> UploadCourseMaterialAsync(int courseId, IFormFile file, string subtype)
        {
            return await UploadFileAsync($"{BaseUrl}api/files/upload/course", file, courseId, subtype);
        }

        public async Task<ApiResponse<List<UserViewModel>>> GetEnrolledStudentsAsync(int courseId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/enrollments/course/{courseId}/students");
                return await SendAsync<ApiResponse<List<UserViewModel>>>(request) ?? new ApiResponse<List<UserViewModel>> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<List<UserViewModel>> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<List<FileAttachmentViewModel>>> GetCourseFilesAsync(int courseId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/files/course/{courseId}");
                return await SendAsync<ApiResponse<List<FileAttachmentViewModel>>>(request) ?? new ApiResponse<List<FileAttachmentViewModel>> { Success = false };
            }
            catch (Exception ex)
            {
                return new ApiResponse<List<FileAttachmentViewModel>> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<bool>> DeleteCourseAsync(int courseId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Delete, $"{BaseUrl}api/courses/{courseId}");
                var result = await SendAsync<ApiResponse<JsonElement>>(request);
                return new ApiResponse<bool> { Success = result?.Success ?? false, Message = result?.Message, Error = result?.Error };
            }
            catch (Exception ex)
            {
                return new ApiResponse<bool> { Success = false, Error = ex.Message };
            }
        }

        public async Task<ApiResponse<bool>> KickStudentAsync(int courseId, int studentId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Delete, $"{BaseUrl}api/enrollments/drop", new { course_id = courseId, student_id = studentId });
                var result = await SendAsync<ApiResponse<JsonElement>>(request);
                return new ApiResponse<bool> { Success = result?.Success ?? false, Message = result?.Message, Error = result?.Error };
            }
            catch (Exception ex)
            {
                return new ApiResponse<bool> { Success = false, Error = ex.Message };
            }
        }

        public async Task<(Stream Content, string ContentType, string FileName)?> DownloadFileAsync(int fileId)
        {
            try
            {
                var request = await CreateRequestAsync(HttpMethod.Get, $"{BaseUrl}api/files/download/{fileId}");
                var response = await _httpClient.SendAsync(request);

                if (response.IsSuccessStatusCode)
                {
                    var content = await response.Content.ReadAsStreamAsync();
                    var contentType = response.Content.Headers.ContentType?.ToString() ?? "application/octet-stream";
                    var fileName = response.Content.Headers.ContentDisposition?.FileName?.Trim('"') ?? $"file_{fileId}";

                    return (content, contentType, fileName);
                }

                return null;
            }
            catch
            {
                return null;
            }
        }

        private async Task<ApiResponse<bool>> UploadFileAsync(string url, IFormFile file, int? courseId = null, string? subtype = null)
        {
            try
            {
                var content = new MultipartFormDataContent();
                var fileStream = file.OpenReadStream();
                var streamContent = new StreamContent(fileStream);
                streamContent.Headers.ContentType = new System.Net.Http.Headers.MediaTypeHeaderValue(file.ContentType);
                content.Add(streamContent, "file", file.FileName);

                if (courseId.HasValue) content.Add(new StringContent(courseId.Value.ToString()), "course_id");
                if (!string.IsNullOrEmpty(subtype)) content.Add(new StringContent(subtype), "subtype");

                var request = await CreateRequestAsync(HttpMethod.Post, url, content);
                var result = await SendAsync<ApiResponse<JsonElement>>(request);

                return new ApiResponse<bool> { Success = result?.Success ?? false, Message = result?.Message, Error = result?.Error };
            }
            catch (Exception ex)
            {
                return new ApiResponse<bool> { Success = false, Error = ex.Message };
            }
        }
    }
}
