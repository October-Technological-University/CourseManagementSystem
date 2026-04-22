<?php
require_once __DIR__ . '/../Mappers/UserMapper.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../utils/Security.php';
require_once __DIR__ . '/../../utils/Validator.php';

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

        // Validate email and password formats
        if (!$this->validator->validateEmail($data['email']) || !$this->validator->validatePassword($data['password'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // Check if email already exists
        if ($this->userRepository->getByEmail($data['email'])) {
            return ['success' => false, 'errors' => ['Email already in use']];
        }

        // Create user entity and save to database
        $userEntity = $this->userMapper->toEntity(new UserRequestDTO(
            $data['email'],
            Security::hashPassword($data['password']),
            $data['first_name'],
            $data['last_name']
        ));
        $isCreated = $this->userRepository->create($userEntity);
        $createdUser = $this->userMapper->toDTO($this->userRepository->getById($isCreated)); // To get the created user with its ID and othe sql side generated fields

        // Return success response with user data (excluding sensitive info)
        return [
            'success' => true,
            'user' => $createdUser
        ];
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
        $user = $this->userRepository->getByEmail($data['email']);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        // Update user entity and save to database
        $userEntity = $this->userMapper->toEntity(new UserRequestDTO(
            $data['email'],
            null, // Password is not updated here
            $data['first_name'],
            $data['last_name']
        ));
        $isUpdated = $this->userRepository->update($userEntity);
        $updatedUser = $this->userMapper->toDTO($this->userRepository->getById($isUpdated));
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
        $user = $this->userRepository->getByEmail($data['email']);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        // Delete user from database
        $this->userRepository->delete($user);

        // Return success response
        return ['success' => true];
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

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generate session token
        $token = bin2hex(random_bytes(32));
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['auth_token'] = $token;

        // Return success response with user data and token
        return [
            'success' => true,
            'user' => $this->userMapper->toDTO($user),
            'token' => $token
        ];
    }
    public function logout(): array
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        Security::unsetTokenFromCookies();

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();

        return ['success' => true];
    }
}
