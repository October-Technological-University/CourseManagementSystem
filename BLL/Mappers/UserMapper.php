<?php

/**
 * UserMapper
 * 
 * Transforms User entities to/from DTOs
 * Handles conversion between database layer (entities) and business logic layer (DTOs)
 */
class UserMapper
{
    /**
     * Convert User entity to UserResponseDTO
     * 
     * @param User $user The user entity from database
     * @param string|null $profilePictureUrl Optional profile picture URL
     * @return UserResponseDTO The response DTO (excludes sensitive data)
     */
    public function toDTO(User $user, ?string $profilePictureUrl = null): UserResponseDTO
    {
        return new UserResponseDTO(
            $user->getId(),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRole(),
            $user->getProfilePictureId(),
            $user->getCreatedAt(),
            $profilePictureUrl
        );
    }

    /**
     * Convert array of User entities to array of UserResponseDTOs
     * 
     * @param array $users Array of User entities
     * @return array Array of UserResponseDTO objects
     */
    public function toDTOList(array $users): array
    {
        return array_map(function ($user) {
            return $this->toDTO($user);
        }, $users);
    }

    /**
     * Create User entity from UserRequestDTO (for new users)
     * 
     * @param UserRequestDTO $dto The request DTO with user creation data
     * @return User A new User entity (not yet saved to database)
     */
    public function toEntity(UserRequestDTO $dto): User
    {
        return new User(
            $dto->email,
            $dto->password,
            $dto->first_name,
            $dto->last_name,
            $dto->role ?? 'student',
            $dto->profile_picture_id
        );
    }

    /**
     * Update existing User entity with data from UserRequestDTO
     * 
     * @param User $user The existing user entity to update
     * @param UserRequestDTO $dto The request DTO with new data
     * @return void
     */
    public function updateEntity(User $user, UserRequestDTO $dto): void
    {
        // Only update fields that are present in the DTO
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }

        if ($dto->password !== null) {
            $user->setPassword($dto->password);
        }

        if ($dto->first_name !== null) {
            $user->setFirstName($dto->first_name);
        }

        if ($dto->last_name !== null) {
            $user->setLastName($dto->last_name);
        }

        if ($dto->role !== null) {
            $user->setRole($dto->role);
        }

        if ($dto->profile_picture_id !== null) {
            $user->setProfilePictureId($dto->profile_picture_id);
        }
    }
}
?>