<?php

require_once __DIR__ . '/../../utils/ResponseHelper.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../utils/Security.php';

class AuthMiddleware
{
    private static $authenticatedUser = null;

    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate request using Session OR Encrypted Cookie
     */
    public static function authenticate()
    {
        if (self::$authenticatedUser !== null) {
            return self::$authenticatedUser;
        }

        self::startSession();

        // 1. Try Session first
        $userId = $_SESSION['user_id'] ?? null;

        // 2. If no session, try the Remember Me Cookie
        if (!$userId && isset($_COOKIE['remember_me'])) {
            $decodedData = Security::decryptToken($_COOKIE['remember_me']);
            
            if ($decodedData && isset($decodedData['id'])) {
                // Restore session from cookie data
                $userId = $decodedData['id'];
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = $decodedData['role'] ?? null;
            }
        }

        if (!$userId) {
            return null;
        }

        // 3. Verify user still exists in DB
        $userRepo = new UserRepository();
        $user = $userRepo->getById($userId);

        if (!$user) {
            self::logout();
            return null;
        }

        self::$authenticatedUser = $user;
        return $user;
    }

    /**
     * Require authentication - used at the top of protected routes
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
     * Role-based access control
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
     * Update logout to clear both Session and the Security Cookie
     */
    public static function logout()
    {
        self::startSession();
        
        // Clear PHP Session
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');

        // Clear the Remember Me Cookie via Security class
        Security::clearRememberMeCookie();

        self::$authenticatedUser = null;
    }
}