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
// class FileAttachmentResponseDTO
// {
//     public $id;
//     public $stored_name;
//     public $mime_type;
//     public $file_size;
//     public $course_id;
//     public $subtype;
//     public $file_url; // URL to access the file via server

//     public function __construct($id, $stored_name, $mime_type, $file_size, $course_id = null, $subtype = null, $file_url = null)
//     {
//         $this->id = $id;
//         $this->stored_name = $stored_name;
//         $this->mime_type = $mime_type;
//         $this->file_size = $file_size;
//         $this->course_id = $course_id;
//         $this->subtype = $subtype;
//         $this->file_url = $file_url;
//     }

//     public function toArray()
//     {
//         return [
//             'id' => $this->id,
//             'stored_name' => $this->stored_name,
//             'mime_type' => $this->mime_type,
//             'file_size' => $this->file_size,
//             'course_id' => $this->course_id,
//             'subtype' => $this->subtype,
//             'file_url' => $this->file_url
//         ];
//     }
//     public function isCourseFile()
//     {
//         return $this->course_id !== null;
//     }
// }    
?>