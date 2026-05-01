<?php

require_once __DIR__ . '/../../DAL/Entities/User.php';

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
        return [
            'id' => $user->getId(),
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ];
    }
}
