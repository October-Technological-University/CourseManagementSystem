<?php

class Validator
{
    private $errors = [];

    public function validateEmail($email)
    {
        if (empty($email)) {
            $this->errors[] = 'Email is required';
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
            return false;
        }
        return true;
    }

    // Simplified — no uppercase/special char requirements
    public function validatePassword($password)
    {
        if (empty($password)) {
            $this->errors[] = 'Password is required';
            return false;
        }
        if (strlen($password) < 8) {
            $this->errors[] = 'Password must be at least 8 characters';
            return false;
        }
        return true;
    }

    public function validateRequired($data, $fields)
    {
        $valid = true;
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $this->errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                $valid = false;
            }
        }
        return $valid;
    }

    public function validateStringLength($value, $min = null, $max = null)
    {
        $length = strlen($value);
        if ($min !== null && $length < $min) {
            $this->errors[] = "Must be at least $min characters";
            return false;
        }
        if ($max !== null && $length > $max) {
            $this->errors[] = "Must be no more than $max characters";
            return false;
        }
        return true;
    }

    public function validateName($name, $fieldName = 'Name')
    {
        if (empty($name)) {
            $this->errors[] = "$fieldName is required";
            return false;
        }
        if (!preg_match('/^[a-zA-Z\s\-\']+$/', $name)) {
            $this->errors[] = "$fieldName can only contain letters, spaces, hyphens, and apostrophes";
            return false;
        }
        return true;
    }

    public function validateRole($role)
    {
        if (!in_array(strtolower($role), ['admin', 'teacher', 'student'])) {
            $this->errors[] = 'Invalid role. Must be admin, teacher, or student';
            return false;
        }
        return true;
    }

    public function validateFile($file, $allowedTypes = [], $maxSizeMB = null)
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $this->errors[] = 'No file uploaded';
            return false;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'File upload error: ' . $file['error'];
            return false;
        }
        if (!empty($allowedTypes) && !in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            $this->errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes);
            return false;
        }
        if ($maxSizeMB !== null && $file['size'] > $maxSizeMB * 1024 * 1024) {
            $this->errors[] = "File too large. Maximum size: {$maxSizeMB}MB";
            return false;
        }
        return true;
    }

    public function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->errors[] = "Invalid date format. Expected $format";
            return false;
        }
        return true;
    }

    public function getErrors()  { return $this->errors; }
    public function hasErrors()  { return !empty($this->errors); }
    public function clearErrors(){ $this->errors = []; }
}

// Usage example:
// $v = new Validator();
// $v->validateEmail($data['email']);
// $v->validatePassword($data['password']);
// if ($v->hasErrors()) return ['errors' => $v->getErrors()];