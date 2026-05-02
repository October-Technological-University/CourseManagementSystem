<?php

require_once __DIR__ . '/../../DAL/Entities/User.php';
require_once __DIR__ . '/../Mappers/UserMapper.php';

class UserInfoService
{
    /**
     * Get user info formatted for the frontend
     * 
     * @param User $user The authenticated user entity
     * @return array The user info containing id, name, email, and role
     */
    public function getUserInfo(User $user): array
    {
        $mapper = new UserMapper();
        $dto = $mapper->toDTO($user);
        return $dto->toArray();
    }
}
