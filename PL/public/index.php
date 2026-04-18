<?php
define('BASE_PATH', __DIR__ . '\\..\\..\\');

require_once BASE_PATH . 'DAL/Database/DBContext.php';
require_once BASE_PATH.'/DAL/Database/Database.php';
require_once __DIR__ . '/../Controllers/BaseController.php';

// CORS Headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
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

/* // Ensure database connection is initialized before handling any routes
DBContext::getInstance();
 */

// ============================================================
// ROUTER CONFIGURATION
// Register your routes here in the format:
// 'METHOD' => ['route/path' => callback]
// ============================================================

$dbContext = new Database();

$router = [
    'GET' => [
        'health' => fn() => BaseController::success('API is running'),
        'api/test' => fn() => BaseController::success(['message' => 'Test route works!', 'timestamp' => time()]),
        'api/testdatabase' => fn() => BaseController::success(['message'=> ''.$dbContext->testConnection()->data_seek(0),''=> time()]),
    ],
    'POST' => [
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
// ============================================================

// Route matching
if (isset($router[$method][$uri])) {
    $router[$method][$uri]();
} else {
    // For debugging later.
    // echo '<br>' . $method . $uri . ' not found <br> <br>';

    BaseController::error('Route not found', 404);
}
