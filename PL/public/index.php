<?php

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
$uri = str_replace('/CourseManagementSystem/PL/public', '', $uri);
$uri = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// ============================================================
// ROUTER CONFIGURATION
// Register your routes here in the format:
// 'METHOD' => ['route/path' => callback]
// ============================================================

$router = [
    'GET' => [
        'health' => fn() => BaseController::success('API is running'),
        'api/test' => fn() => BaseController::success(['message' => 'Test route works!', 'timestamp' => time()])
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
    BaseController::error('Route not found', 404);
}
