<?php
// Use dirname to go up to the main root
define('BASE_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

// Now the paths will resolve correctly as .../CourseManagementSystem/DAL/
require_once BASE_PATH . 'DAL/Database/DBContext.php';
require_once BASE_PATH . 'DAL/Database/InitialCreate.php';
require_once BASE_PATH . 'DAL/Database/Database.php';

require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Controllers/AuthController.php';
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
// ============================================================


$router = [
    'GET' => [
        'api/test' => function () {
            AuthMiddleware::requireAuth();
            AuthMiddleware::requireRole('admin');
            BaseController::success(['message' => 'Test route works!', 'timestamp' => time()]);
        },
        'api/testdatabase' => fn() => BaseController::success(['message' => 'DBContext connection active']),
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
        // Example: 'api/users' => fn() => (new UserController())->create(BaseController::getJsonInput())
    ],
    'PUT' => [
        // Example: 'api/users/{id}' => fn($id) => (new UserController())->update($id, BaseController::getJsonInput())
    ],
    'DELETE' => [
        // Example: 'api/users/{id}' => fn($id) => (new UserController())->delete($id)
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

// Route matching
if (isset($router[$method][$uri])) {
    $router[$method][$uri]();
} else {
    // For debugging later.
    // echo '<br>' . $method . $uri . ' not found <br> <br>';

    BaseController::error('Route not found', 404);
}

