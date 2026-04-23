<?php

require_once __DIR__ . '/../../BLL/Services/UserService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Middleware/FileUploadMiddleware.php';

class UserController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    private function respond(array $result, int $successCode = 200, string $successMessage = 'Success')
    {
        if (!empty($result['success'])) {
            BaseController::success($result['data'] ?? null, $successMessage, $successCode);
            exit;
        }

        $error = $result['error'] ?? (isset($result['errors']) ? implode(', ', $result['errors']) : 'An error occurred');
        BaseController::error($error, 400);
        exit;
    }

    public function index()
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::requireRole('admin');
        $this->respond($this->userService->getAll());
    }

    public function show(int $id)
    {
        AuthMiddleware::requireAuth();
        $this->respond($this->userService->getById($id));
    }

    public function listStudents()
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::requireRole('admin');
        $this->respond($this->userService->getAllStudents());
    }

    public function listInstructors()
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::requireRole('admin');
        $this->respond($this->userService->getAllInstructors());
    }

    public function showStudent(int $id)
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::requireRole('admin');
        $this->respond($this->userService->getStudentById($id));
    }

    public function showInstructor(int $id)
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::requireRole('admin');
        $this->respond($this->userService->getInstructorById($id));
    }

    public function uploadProfilePicture(int $userId)
    {
        AuthMiddleware::requireAuth();

        $upload = FileUploadMiddleware::requireUpload('file');
        $fileData = FileUploadMiddleware::process($upload, 'image');
        if (!$fileData) {
            return;
        }

        $result = $this->userService->uploadProfilePicture($fileData, $userId);
        if ($result['success']) {
            BaseController::success(
                [
                    'file' => $result['data'],
                    'file_url' => $result['file_url'] ?? null
                ],
                'Profile picture uploaded successfully',
                201
            );
            exit;
        }

        $error = $result['error'] ?? (isset($result['errors']) ? implode(', ', $result['errors']) : 'Failed to upload profile picture');
        BaseController::error($error, 400);
        exit;
    }

    public function removeProfilePicture(int $userId)
    {
        AuthMiddleware::requireAuth();
        $this->respond($this->userService->removeProfilePicture($userId));
    }

    public function deleteAccount($id){
        $user = AuthMiddleware::requireAuth();

        if ($user->getId() != $id && !AuthMiddleware::requireRole('admin')) {
            BaseController::error('Forbidden. You can only delete your own account.', 403);
            return;
        }

        $result = $this->userService->delete($id);
        if ($result['success']) {
            BaseController::success(null, 'Account deleted successfully');
        } else {
            $error = $result['error'] ?? (isset($result['errors']) ? implode(', ', $result['errors']) : 'Failed to delete account');
            BaseController::error($error, 400);
        }
    }
}

