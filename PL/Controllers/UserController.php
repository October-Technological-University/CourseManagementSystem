<?php

require_once __DIR__ . '/../../BLL/Services/UserService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Middleware/FileUploadMiddleware.php';
require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    private function respond(array $result, int $successCode = 200, string $successMessage = 'Success')
    {
        if (!empty($result['success'])) {
            self::success($result['data'] ?? null, $successMessage, $successCode);
            exit;
        }

        $error = $result['error'] ?? (isset($result['errors']) ? implode(', ', $result['errors']) : 'An error occurred');
        self::error($error, 400);
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

    public function update(int $id)
    {
        $user = AuthMiddleware::requireAuth();
        
        // Only allow users to update their own profile, unless admin
        if ($user->getId() !== $id && strtolower($user->getRole()) !== 'admin') {
            self::error('Forbidden. You can only update your own profile.', 403);
            return;
        }

        $data = self::getJsonInput();
        $this->respond($this->userService->update($id, $data));
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

    // OVERLAPPED WITH show() - getById() will return either student or instructor based on ID

    // public function showStudent(int $id)
    // {
    //     AuthMiddleware::requireAuth();
    //     $this->respond($this->userService->getStudentById($id));
    // }

    // public function showInstructor(int $id)
    // {
    //     AuthMiddleware::requireAuth();
    //     $this->respond($this->userService->getInstructorById($id));
    // }

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
            self::success(
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
        self::error($error, 400);
        exit;
    }

    public function removeProfilePicture(int $userId)
    {
        AuthMiddleware::requireAuth();
        $this->respond($this->userService->removeProfilePicture($userId));
    }

    public function deleteAccount(){
        $user = AuthMiddleware::requireAuth();
        $id = $user->getId();

        if ($user->getId() != $id && !AuthMiddleware::requireRole('admin')) {
            self::error('Forbidden. You can only delete your own account.', 403);
            return;
        }

        $result = $this->userService->delete($id);
        if ($result['success']) {
            self::success(null, 'Account deleted successfully');
        } else {
            $error = $result['error'] ?? (isset($result['errors']) ? implode(', ', $result['errors']) : 'Failed to delete account');
            self::error($error, 400);
        }
    }
}

