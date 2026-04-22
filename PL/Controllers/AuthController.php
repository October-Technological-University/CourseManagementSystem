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
                    'token' => $result['token']
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