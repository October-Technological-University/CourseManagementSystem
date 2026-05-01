<?php
/**
 * Bootstrap file for the Course Management System
 * Handles autoloading and environment variable loading
 */

// Define the root path of the project (one level up from config/)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Load Composer autoloader
$autoloadPath = BASE_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die("Composer autoloader not found. Please run 'composer install'.");
}

// Load environment variables from config/.env
$dotenvPath = BASE_PATH . 'config';
if (file_exists($dotenvPath . DIRECTORY_SEPARATOR . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}
// Note: If .env doesn't exist, we assume environment variables are set via the server/Docker.
