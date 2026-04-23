<?php
// Use dirname to go up to the main root
define('BASE_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

// Now the paths will resolve correctly as .../CourseManagementSystem/DAL/
require_once BASE_PATH . 'DAL/Database/DBContext.php';
require_once BASE_PATH . 'DAL/Database/InitialCreate.php';
require_once BASE_PATH . 'DAL/Database/Database.php';

require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Controllers/AuthController.php';
require_once __DIR__ . '/../Controllers/FileAttachmentController.php';
require_once __DIR__ . '/../Controllers/UserController.php';
require_once __DIR__ . '/../Controllers/CourseController.php';
require_once __DIR__ . '/../Controllers/EnrollmentController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

foreach (glob(BASE_PATH . "DAL/Repository/*.php") as $filename) {
    require_once $filename;
}


// CORS Headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://your-frontend-origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse URI and remove base path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#/CourseManagementSystem/PL/public/(index(\.php)?)?#', '', $uri);
$uri = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Initialize database connection and create tables
try {
    // Ensure DBContext singleton is initialized
    DBContext::getInstance();

    // Create tables if they don't exist (idempotent - safe to run on every request)
    $initial = new InitialCreate();
    $initial->createTables();
} catch (Exception $e) {
    BaseController::error('Database initialization failed: ' . $e->getMessage(), 500);
    exit;
} catch (Error $e) {
    BaseController::error('Database error: ' . $e->getMessage(), 500);
    exit;
}

// ============================================================
// ROUTER CONFIGURATION
// Register your routes here in the format:
// 'METHOD' => ['route/path' => callback]
// For parameterized routes, use {param} syntax
// ============================================================

$router = [
    'GET' => [
        'api/test' => function () {
            AuthMiddleware::requireAuth();
            AuthMiddleware::requireRole('admin');
            BaseController::success(['message' => 'Test route works!', 'timestamp' => time()]);
        },
        'api/testdatabase' => fn() => BaseController::success(['message' => 'DBContext connection active']),
        'api/files/download/{id}' => fn($id) => (new FileAttachmentController())->download($id),
        'api/files/course/{courseId}/assignments' => fn($courseId) => (new FileAttachmentController())->getCourseAssignments($courseId),
        'api/files/course/{courseId}/resources' => fn($courseId) => (new FileAttachmentController())->getCourseResources($courseId),
        'api/files/course/{courseId}' => fn($courseId) => (new FileAttachmentController())->getCourseFiles($courseId),
        'api/files/{id}' => fn($id) => (new FileAttachmentController())->getById($id),
        'api/users' => fn() => (new UserController())->index(),
        'api/users/students' => fn() => (new UserController())->listStudents(),
        'api/users/instructors' => fn() => (new UserController())->listInstructors(),
        'api/courses' => fn() => (new CourseController())->index(),
        'api/courses/instructor/{instructorId}' => fn($id) => (new CourseController())->getByInstructor((int)$id),
        'api/courses/{id}' => fn($id) => (new CourseController())->show((int)$id),
        'api/enrollments/course/{courseId}/students' => fn($id) => (new EnrollmentController())->getStudentsByCourse((int)$id),
        'api/enrollments/student/{studentId}/courses' => fn($id) => (new EnrollmentController())->getCoursesByStudent((int)$id),
    ],
    'POST' => [
        'api/auth/register' => fn() => (new AuthController())->register(),
        'api/auth/login' => fn() => (new AuthController())->login(),
        'api/auth/logout' => function () {
            AuthMiddleware::requireAuth();
            (new AuthController())->logout();
        },
        'api/auth/changepassword' => function () {
            AuthMiddleware::requireAuth();
            (new AuthController())->changePassword();
        },
        'api/files/upload/course' => fn() => (new FileAttachmentController())->uploadCourseFile(),
        'api/files/upload/profile' => fn() => (new FileAttachmentController())->uploadProfilePicture(),
        'api/courses' => fn() => (new CourseController())->create(),
        'api/enrollments' => fn() => (new EnrollmentController())->enroll(),
    ],
    'PUT' => [
        'api/courses/{id}' => fn($id) => (new CourseController())->update((int)$id),
    ],
    'DELETE' => [
        'api/files/{id}' => fn($id) => (new FileAttachmentController())->delete($id),
        'api/courses/{id}' => fn($id) => (new CourseController())->delete((int)$id),
        'api/enrollments/drop' => fn() => (new EnrollmentController())->drop(),
    ]
];

$routePatterns = [
    'GET' => [
        'api/users/{id}' => fn($id) => (new UserController())->show((int)$id),
        'api/users/student/{id}' => fn($id) => (new UserController())->showStudent((int)$id),
        'api/users/instructor/{id}' => fn($id) => (new UserController())->showInstructor((int)$id),
    ],
    'POST' => [
        'api/users/{id}/profile-picture' => fn($id) => (new UserController())->uploadProfilePicture((int)$id),
    ],
    'DELETE' => [
        'api/users/{id}/profile-picture' => fn($id) => (new UserController())->removeProfilePicture((int)$id),
    ]
];

// ============================================================
// HOW TO REGISTER CONTROLLERS (Pattern for later):
// ============================================================
//
// 1. Import the controller:
//    require_once __DIR__ . '/../Controllers/UserController.php';
//
// 2. Add route to router array:
//    'GET' => [
//        'api/users' => fn() => (new UserController())->index()
//    ],
//    'POST' => [
//        'api/users' => fn() => (new UserController())->create(BaseController::getJsonInput())
//    ]
//
// 3. For routes with parameters (like /api/users/5):
//    Use pattern matching and extract ID from URI
//
// NOTE: Controller methods should call exit() after sending response
// to prevent further execution
// ============================================================

// Route matching with parameter support
$matched = false;
if (isset($router[$method])) {
    foreach ($router[$method] as $routePattern => $callback) {
        $params = [];
        if (matchRoute($routePattern, $uri, $params)) {
            call_user_func_array($callback, $params);
            $matched = true;
            break;
        }
    }
}

// Fallback: match against $routePatterns (segment-based matching)
if (!$matched) {
    foreach ($routePatterns[$method] ?? [] as $routePattern => $handler) {
        $patternSegments = explode('/', trim($routePattern, '/'));
        $uriSegments = explode('/', trim($uri, '/'));

        if (count($patternSegments) !== count($uriSegments)) {
            continue;
        }

        $params = [];
        $segmentMatched = true;
        foreach ($patternSegments as $index => $segment) {
            if (preg_match('/^\{(.+)\}$/', $segment, $matches)) {
                $paramName = $matches[1];
                if ($paramName === 'id' && !ctype_digit($uriSegments[$index])) {
                    $segmentMatched = false;
                    break;
                }
                $params[] = $uriSegments[$index];
                continue;
            }

            if ($segment !== $uriSegments[$index]) {
                $segmentMatched = false;
                break;
            }
        }

        if ($segmentMatched) {
            $handler(...$params);
            $matched = true;
            break;
        }
    }
}

if (!$matched) {
    BaseController::error('Route not found', 404);
}

function matchRoute($pattern, $uri, &$params)
{
    $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (preg_match($regex, $uri, $matches)) {
        $params = array_slice($matches, 1);
        return true;
    }
    return false;
}

