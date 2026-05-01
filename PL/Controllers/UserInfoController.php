<?php

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../BLL/Services/UserInfoService.php';
require_once __DIR__ . '/BaseController.php';

class UserInfoController extends BaseController
{
    private $userInfoService;

    public function __construct()
    {
        $this->userInfoService = new UserInfoService();
    }

    /**
     * Get the currently authenticated user's information
     * Protected by AuthMiddleware::authenticate
     */
    public function getInfo()
    {
        // Enforce authentication - this will also decrypt the token and verify the user
        $user = AuthMiddleware::requireAuth();

        // Get formatted user info from service
        $userInfo = $this->userInfoService->getUserInfo($user);

        // Return success response
        self::success($userInfo, 'User information retrieved successfully');
    }
}
