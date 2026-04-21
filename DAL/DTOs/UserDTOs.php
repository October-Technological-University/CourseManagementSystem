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

    public function __construct($email, $password, $first_name, $last_name, $role = 'student', $profile_picture_id = null)
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
            'role' => $this->role,
            'profile_picture_id' => $this->profile_picture_id,
            'created_at' => $this->created_at
        ];
    }
}

/**
 * DTO for remember me token response
 */
class RememberTokenDTO
{
    public $selector;
    public $validator;
    public $expires_at;

    public function __construct($selector, $validator, $expires_at)
    {
        $this->selector = $selector;
        $this->validator = $validator;
        $this->expires_at = $expires_at;
    }

    public function toArray()
    {
        return [
            'selector' => $this->selector,
            'validator' => $this->validator,
            'expires_at' => $this->expires_at
        ];
    }

    /**
     * Generate a remember token cookie value (selector:validator)
     */
    public function getCookieValue()
    {
        return $this->selector . ':' . $this->validator;
    }
}

/**
 * DTO for file attachment (profile picture or course file)
 */
class FileAttachmentRequestDTO
{
    public $filename;
    public $stored_name;
    public $file_path;
    public $mime_type;
    public $file_size;
    public $course_id;
    public $subtype;
    public $uploaded_by;

    public function __construct($filename, $stored_name, $file_path, $mime_type, $file_size, $uploaded_by, $course_id = null, $subtype = null)
    {
        $this->filename = $filename;
        $this->stored_name = $stored_name;
        $this->file_path = $file_path;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->course_id = $course_id;
        $this->subtype = $subtype;
        $this->uploaded_by = $uploaded_by;
    }

    public static function fromArray($data)
    {
        return new self(
            $data['filename'] ?? null,
            $data['stored_name'] ?? null,
            $data['file_path'] ?? null,
            $data['mime_type'] ?? null,
            $data['file_size'] ?? null,
            $data['uploaded_by'] ?? null,
            $data['course_id'] ?? null,
            $data['subtype'] ?? null
        );
    }
}

/**
 * DTO for file attachment response
 */
class FileAttachmentResponseDTO
{
    public $id;
    public $filename;
    public $stored_name;
    public $file_path;
    public $mime_type;
    public $file_size;
    public $course_id;
    public $subtype;
    public $uploaded_by;
    public $uploaded_at;
    public $uploader_name;
    public $course_name;

    public function __construct($id, $filename, $stored_name, $file_path, $mime_type, $file_size, $uploaded_by, $uploaded_at, $course_id = null, $subtype = null, $uploader_name = null, $course_name = null)
    {
        $this->id = $id;
        $this->filename = $filename;
        $this->stored_name = $stored_name;
        $this->file_path = $file_path;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->course_id = $course_id;
        $this->subtype = $subtype;
        $this->uploaded_by = $uploaded_by;
        $this->uploaded_at = $uploaded_at;
        $this->uploader_name = $uploader_name;
        $this->course_name = $course_name;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'stored_name' => $this->stored_name,
            'file_path' => $this->file_path,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'course_id' => $this->course_id,
            'subtype' => $this->subtype,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->uploaded_at,
            'uploader_name' => $this->uploader_name,
            'course_name' => $this->course_name
        ];
    }

    /**
     * Check if this is a profile picture
     */
    public function isProfilePicture()
    {
        return $this->course_id === null;
    }

    /**
     * Check if this is a course file
     */
    public function isCourseFile()
    {
        return $this->course_id !== null;
    }
}
?>