<?php

class User
{
    private $id;
    private $email;
    private $password;
    private $first_name;
    private $last_name;
    private $role;
    private $profile_picture_id;
    private $created_at;

    public function __construct($email, $password, $first_name, $last_name, $role = 'student', $profile_picture_id = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->role = $role;
        $this->profile_picture_id = $profile_picture_id;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getFirstName()
    {
        return $this->first_name;
    }
    public function getLastName()
    {
        return $this->last_name;
    }
    public function getRole()
    {
        return $this->role;
    }
    public function getProfilePictureId()
    {
        return $this->profile_picture_id;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }
    public function setRole($role)
    {
        $this->role = $role;
    }
    public function setProfilePictureId($profile_picture_id)
    {
        $this->profile_picture_id = $profile_picture_id;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
            'profile_picture_id' => $this->profile_picture_id,
            'created_at' => $this->created_at
        ];
    }

    public function toArrayWithoutPassword()
    {
        $array = $this->toArray();
        unset($array['password']);
        return $array;
    }
}
?>