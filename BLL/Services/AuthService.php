<?php
require_once __DIR__ . '/../Mappers/UserMapper.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../utils/Security.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../DAL/Entities/User.php';

class AuthService
{
    private $userRepository;
    private $userMapper;
    private $validator;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->userMapper = new UserMapper();
        $this->validator = new Validator();
    }

    public function register(array $data): array
    {
        // Validate required fields
        if (!$this->validator->validateRequired($data, ['email', 'password', 'first_name', 'last_name'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        if(strtolower($data['role']) == 'admin') {
            return ['success' => false, 'errors' => ['Cannot assign admin role during registration']];
        }

        // Validate email and password formats
        if (!$this->validator->validateEmail($data['email']) || !$this->validator->validatePassword($data['password'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // Check if email already exists
        if ($this->userRepository->getByEmail($data['email'])) {
            return ['success' => false, 'errors' => ['Email already in use']];
        }

        $role = strtolower($data['role'] ?? 'student');

        if ($role ?? null) {
            $validRoles = ['student', 'instructor'];
            if (!in_array($role, $validRoles)) {
                return ['success' => false, 'errors' => ['Invalid role specified']];
            }
        }

        // Create user entity and save to database
        $userEntity = $this->userMapper->toEntity(new UserRequestDTO(
            $data['email'],
            Security::hashPassword($data['password']),
            $data['first_name'],
            $data['last_name'],
            $role = $data['role'] ?? 'student'
        ));
        $isCreated = $this->userRepository->create($userEntity);
        $createdUser = $this->userMapper->toDTO($this->userRepository->getById($isCreated)); // To get the created user with its ID and othe sql side generated fields

        // Return success response with user data (excluding sensitive info)
        return [
            'success' => true,
            'user' => $createdUser
        ];
    }

    public function login(array $data): array
    {
        // Validate required fields
        if (!$this->validator->validateRequired($data, ['email', 'password'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // Validate email format
        if (!$this->validator->validateEmail($data['email'])) {
            return ['success' => false, 'errors' => ['Invalid email format']];
        }

        // Find user by email
        $user = $this->userRepository->getByEmail($data['email']);
        if (!$user) {
            return ['success' => false, 'errors' => ['Invalid email or password']];
        }

        // Verify password
        if (!Security::verifyPassword($data['password'], $user->getPassword())) {
            return ['success' => false, 'errors' => ['Invalid email or password']];
        }
        // 1. SET THE REMEMBER ME COOKIE HERE
        // Usually triggered if a "Remember Me" checkbox was checked in the UI
        // if (isset($data['remember']) && $data['remember'] === true) {
        Security::setRememberMeCookie([
            'id' => $user->getId(),
            'role' => $user->getRole(),
            'email' => $user->getEmail()
        ]);
        // }


        // Return success response with user data and token
        return [
            'success' => true,
            'user' => $this->userMapper->toDTO($user),
        ];
    }
    public function changePassword($currentPassword, $newPassword)
    {
        // 1. Checks the remember_me token
        // We look for the cookie and attempt to decrypt it
        $token = $_COOKIE['remember_me'] ?? null;
        if (!$token) {
            return ['success' => false, 'error' => 'Authentication token missing.'];
        }

        $userData = Security::decryptToken($token);
        if (!$userData) {
            return ['success' => false, 'error' => 'Invalid or expired session.'];
        }

        $userId = $userData['id'];

        // 2. Checks if the new password is the same as the current password
        // (Pre-validation check to save DB resources)
        if ($currentPassword === $newPassword) {
            return ['success' => false, 'error' => 'New password cannot be the same as the current password.'];
        }

        // Fetch the user from the database to get the current hash
        $user = $this->userRepository->getById($userId);

        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }

        // 3. Checks if the current password is correct
        if (!Security::verifyPassword($currentPassword, $user->getPassword())) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }

        // 4. Validate the new password using Validator's functions
        if (!$this->validator->validatePassword($newPassword)) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // If all validations pass, proceed to update
        $newHash = Security::hashPassword($newPassword);
        $user->setPassword($newHash);
        $updateStmt = $this->userRepository->update($user);

        if ($updateStmt > 0) {
            return ['success' => true, 'message' => 'Password updated successfully.'];
        }

        return ['success' => false, 'error' => 'Failed to update password in database.'];
    }

    /**
     * Logout helper using the refactored Security class
     */
    public function logout()
    {
        $security = new Security();
        $security->clearRememberMeCookie();
    }

}