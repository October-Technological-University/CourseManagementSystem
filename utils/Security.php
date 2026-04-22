<?php

/**
 * Security utility class
 * Provides password hashing and CSRF token generation
 */
class Security
{
    /**
     * Hash a password using bcrypt
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate a CSRF token
     */
    public static function generateCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = bin2hex(random_bytes(16));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Verify a CSRF token
     */
    public static function verifyCsrfToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function unsetTokenFromCookies()
    {

        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            unset($_COOKIE['remember_me']);
        }
    }
}
