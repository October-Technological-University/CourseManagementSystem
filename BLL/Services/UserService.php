<?php
require_once __DIR__ . '/../../DAL/Repositories/UserRepository.php';
require_once __DIR__ . '/../../DAL/DTOs/UserDTOs.php';
require_once __DIR__ . '/../../BLL/Mappers/UserMapper.php';
require_once __DIR__ . '/../../utils/Validator.php';
class UserService
{
    private $validator;
    private $userRepo;
    private $mapper;
    public function __construct()
    {
        $this->validator = new Validator();
        $this->userRepo = new UserRepository();
        $this->mapper = new UserMapper();
    }

    public function getInstructorById(UserResponseDTO $response)
    {
        $userEntity = $this->userRepo->getById($response->id);
        return $this->mapper->toDTO($userEntity);
    }

    public function getStudentById(UserResponseDTO $response)
    {
        $userEntity = $this->userRepo->getById($response->id);
        return $this->mapper->toDTO($userEntity);
    }

    public function update(array $data): array
    {
        // Validate required fields
        if (!$this->validator->validateRequired($data, ['email', 'first_name', 'last_name'])) {
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

        if (strtoupper($user->getEmail()) == strtoupper($data['email'])) {
            return ['success' => false, 'errors' => ['This email is already associated with your account.']];
        }

        // Update user entity and save to database
        $userEntity = $this->mapper->toEntity(new UserRequestDTO(
            $data['email'],
            null, // Password is not updated here
            $data['first_name'],
            $data['last_name']
        ));
        $isUpdated = $this->userRepo->update($userEntity);
        $updatedUser = $this->mapper->toDTO($this->userRepo->getById($isUpdated));
        // Return success response with updated user data (excluding sensitive info)
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
        $this->userRepo->delete($user);

        // Return success response
        return ['success' => true];
    }

}