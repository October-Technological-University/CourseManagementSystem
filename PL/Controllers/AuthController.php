<?php
require_once __DIR__ . '/../../BLL/Services/AuthService.php';
require_once __DIR__ . '/../../DAL/DTOs/UserDTOs.php';

class AuthController extends BaseController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Register a new user
     * POST /api/auth/register
     */
    public function register()
    {
        $data = self::getJsonInput();
        $result = $this->authService->register($data);

        if ($result['success']) {
            BaseController::success(
                $result['user'],
                'User registered successfully',
                201
            );
            exit;
        }

        BaseController::error(
            implode(', ', $result['errors']),
            400
        );
        exit;
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login()
    {
        $data = self::getJsonInput();
        $result = $this->authService->login($data);

        if ($result['success']) {
            BaseController::success(
                [
                    'user' => $result['user'],
                ],
                'Login successful'
            );
            exit;
        }

        BaseController::error(
            implode(', ', $result['errors']),
            401
        );
        exit;
    }
    public function changePassword()
    {
        $data = self::getJsonInput();

        // Fix 1: Validate input existence to stop the "Undefined array key" warnings
        if (!isset($data['current_password']) || !isset($data['new_password'])) {
            BaseController::error('Current and new password are required', 400);
            exit;
        }

        $result = $this->authService->changePassword($data['current_password'], $data['new_password']);

        // Fix 2: Sync keys. Your service returns 'status' or 'success'
        // Let's standardize the Service to return 'success' to match the rest of your app.
        if (isset($result['success']) && $result['success']) {
            BaseController::success(null, 'Password changed successfully');
            exit;
        }

        // Fix 3: Robust Error Handling
        $errorMessage = $result['error'] ?? (isset($result['errors']) ? implode(', ', $result['errors']) : 'An unexpected error occurred');

        BaseController::error($errorMessage, 400);
        exit;
    }
    public function logout()
    {
        $this->authService->logout();
        BaseController::success(
            null,
            'Logout successful'
        );
        exit;
    }
}