<?php
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../DAL/Repository/FileAttachmentRepository.php';
require_once __DIR__ . '/../../DAL/DTOs/UserDTOs.php';
require_once __DIR__ . '/../../BLL/Mappers/UserMapper.php';
require_once __DIR__ . '/../../BLL/Services/FileAttachmentService.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/FileStorageHelper.php';

class UserService
{
    private $validator;
    private $userRepo;
    private $fileAttachmentRepo;
    private $fileAttachmentService;
    private $mapper;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->userRepo = new UserRepository();
        $this->fileAttachmentRepo = new FileAttachmentRepository();
        $this->fileAttachmentService = new FileAttachmentService();
        $this->mapper = new UserMapper();
    }

    public function getAll(): array
    {
        $users = $this->userRepo->getAll();
        return [
            'success' => true,
            'data' => $this->mapper->toDTOList($users),
            'count' => count($users)
        ];
    }

    public function getById(int $id): array
    {
        $user = $this->userRepo->getById($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }
        $userDto = $this->mapper->toDTO($user);
        $profilePicture = $this->fileAttachmentRepo->getProfilePictureByUserId($id);
        $userDto->profile_picture_url = $profilePicture;
        return ['success' => true, 'data' => $userDto];
    }

    public function getAllStudents(): array
    {
        $students = $this->userRepo->getAllStudents();
        return [
            'success' => true,
            'data' => $this->mapper->toDTOList($students),
            'count' => count($students)
        ];
    }

    public function getAllInstructors(): array
    {
        $instructors = $this->userRepo->getAllInstructors();
        return [
            'success' => true,
            'data' => $this->mapper->toDTOList($instructors),
            'count' => count($instructors)
        ];
    }

    public function getInstructorById(int $id): array
    {
        $user = $this->userRepo->getById($id);
        if (!$user || strtolower($user->getRole()) !== 'instructor') {
            return ['success' => false, 'errors' => ['Instructor not found']];
        }

        return ['success' => true, 'data' => $this->mapper->toDTO($user)];
    }

    public function getStudentById(int $id): array
    {
        $user = $this->userRepo->getById($id);
        if (!$user || strtolower($user->getRole()) !== 'student') {
            return ['success' => false, 'errors' => ['Student not found']];
        }

        return ['success' => true, 'data' => $this->mapper->toDTO($user)];
    }

    public function update(array $data): array
    {
        if (!$this->validator->validateRequired($data, ['email', 'first_name', 'last_name'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        if (!$this->validator->validateEmail($data['email'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        $user = $this->userRepo->getByEmail($data['email']);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        $this->mapper->updateEntity($user, new UserRequestDTO(
            $data['email'],
            null,
            $data['first_name'],
            $data['last_name']
        ));
        $this->userRepo->update($user);
        $updatedUser = $this->mapper->toDTO($this->userRepo->getById($user->getId()));

        return [
            'success' => true,
            'user' => $updatedUser
        ];
    }

    public function delete(array $data): array
    {
        // Validate required fields
        if (!$this->validator->validateRequired($data, ['email'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // Validate email format
        if (!$this->validator->validateEmail($data['email'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // Check if user exists
        $user = $this->userRepo->getByEmail($data['email']);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        // Delete user from database
        $this->userRepo->delete($user->getId());

        // Return success response
        return ['success' => true];
    }

    public function uploadProfilePicture(array $fileData, int $userId): array
    {
        $user = $this->userRepo->getById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        if (!$this->validator->validateRequired($fileData, ['filename', 'tmp_path', 'mime_type', 'size', 'stored_name'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        $oldPicture = $this->fileAttachmentRepo->getProfilePictureByUserId($userId);

        $uploadResult = $this->fileAttachmentService->uploadFile($fileData, $userId, 'profile', null, null);
        if (!$uploadResult['success']) {
            return $uploadResult;
        }

        $fileDto = $uploadResult['data'];
        $updateResult = $this->userRepo->updateProfilePicture($userId, $fileDto->id);
        if ($updateResult <= 0) {
            $this->fileAttachmentRepo->delete($fileDto->id);
            FileStorageHelper::delete($fileDto->file_path);
            return ['success' => false, 'errors' => ['Failed to update user profile picture']];
        }

        if ($oldPicture && $oldPicture->getId() !== $fileDto->id) {
            $this->fileAttachmentRepo->delete($oldPicture->getId());
            FileStorageHelper::delete($oldPicture->getFilePath());
        }

        return [
            'success' => true,
            'data' => $fileDto,
            'file_url' => $uploadResult['file_url']
        ];
    }

    public function removeProfilePicture(int $userId): array
    {
        $user = $this->userRepo->getById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        $profilePicture = $this->fileAttachmentRepo->getProfilePictureByUserId($userId);
        if (!$profilePicture) {
            return ['success' => false, 'errors' => ['No profile picture found']];
        }

        $deleted = $this->fileAttachmentRepo->delete($profilePicture->getId());
        if ($deleted <= 0) {
            return ['success' => false, 'errors' => ['Failed to remove profile picture']];
        }

        FileStorageHelper::delete($profilePicture->getFilePath());
        $this->userRepo->updateProfilePicture($userId, null);

        return ['success' => true, 'message' => 'Profile picture removed successfully'];
    }
}
