<?php

/**
 * DTO for user registration/creation
 */
class UserRequestDTO
{
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $role;
    public $profile_picture_id;

    public function __construct($email, $password, $first_name, $last_name, $role = null, $profile_picture_id = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->role = $role;
        $this->profile_picture_id = $profile_picture_id;
    }

    public static function fromArray($data)
    {
        return new self(
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['role'] ?? 'student',
            $data['profile_picture_id'] ?? null
        );
    }
}

/**
 * DTO for user response (excludes sensitive data)
 */
class UserResponseDTO
{
    public $id;
    public $email;
    public $first_name;
    public $last_name;
    public $name; // Full name for compatibility
    public $role;
    public $profile_picture_id;
    public $created_at;
    public $profile_picture_url;

    public function __construct($id, $email, $first_name, $last_name, $role, $profile_picture_id = null, $created_at = null, $profile_picture_url = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->name = trim($first_name . ' ' . $last_name);
        $this->role = $role;
        $this->profile_picture_id = $profile_picture_id;
        $this->created_at = $created_at;
        $this->profile_picture_url = $profile_picture_url;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'role' => $this->role,
            'profile_picture_id' => $this->profile_picture_id,
            'created_at' => $this->created_at,
            'profile_picture_url' => $this->profile_picture_url
        ];
    }
}
?>