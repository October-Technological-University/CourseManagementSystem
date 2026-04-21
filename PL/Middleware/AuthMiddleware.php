<?php

require_once __DIR__ . '/../../utils/ResponseHelper.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';

/**
 * Authentication middleware using PHP sessions
 * Simplified approach for college project - no remember tokens needed
 */
class AuthMiddleware
{
    private static $authenticatedUser = null;

    /**
     * Start session if not already started
     */
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate request using session
     * Returns user object if authenticated, null otherwise
     */
    public static function authenticate()
    {
        if (self::$authenticatedUser !== null) {
            return self::$authenticatedUser;
        }

        self::startSession();

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userRepo = new UserRepository();
        $user = $userRepo->getById($_SESSION['user_id']);

        if (!$user) {
            self::logout();
            return null;
        }

        self::$authenticatedUser = $user;
        return $user;
    }

    /**
     * Require authentication - sends error response if not authenticated
     */
    public static function requireAuth()
    {
        $user = self::authenticate();
        if (!$user) {
            ResponseHelper::error('Unauthorized. Please log in.', 401);
            exit;
        }
        return $user;
    }

    /**
     * Require specific role(s)
     * Accepts single role string or array of roles
     */
    public static function requireRole($roles)
    {
        $user = self::requireAuth();
        $userRole = strtolower($user->getRole());

        $allowedRoles = is_array($roles) ? array_map('strtolower', $roles) : [strtolower($roles)];

        if (!in_array($userRole, $allowedRoles)) {
            ResponseHelper::error('Forbidden. Insufficient permissions.', 403);
            exit;
        }

        return $user;
    }

    /**
     * Check if user is authenticated without sending error
     */
    public static function check()
    {
        return self::authenticate() !== null;
    }

    /**
     * Check if authenticated user has specific role
     */
    public static function hasRole($roles)
    {
        $user = self::authenticate();
        if (!$user) {
            return false;
        }

        $userRole = strtolower($user->getRole());
        $allowedRoles = is_array($roles) ? array_map('strtolower', $roles) : [strtolower($roles)];

        return in_array($userRole, $allowedRoles);
    }

    /**
     * Get authenticated user ID
     */
    public static function getUserId()
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get authenticated user role
     */
    public static function getUserRole()
    {
        self::startSession();
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Get full authenticated user object
     */
    public static function getUser()
    {
        return self::authenticate();
    }

    /**
     * Log a user in - call this after verifying credentials
     * Usage: AuthMiddleware::login($user)
     */
    public static function login($user)
    {
        self::startSession();
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['user_email'] = $user->getEmail();

        self::$authenticatedUser = $user;
    }

    /**
     * Clear authentication (logout)
     */
    public static function logout()
    {
        self::startSession();
        session_unset();
        session_destroy();

        // Kill the session cookie
        setcookie(session_name(), '', time() - 3600, '/');

        self::$authenticatedUser = null;
    }
}
