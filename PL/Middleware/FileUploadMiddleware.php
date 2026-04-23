<?php

require_once __DIR__ . '/../../utils/ResponseHelper.php';
require_once __DIR__ .'/../../config/constants.php';
/**
 * File Upload Middleware
 * Handles validation and processing of uploaded files
 */
class FileUploadMiddleware
{
    // Default limits
    
    private static $maxSizeMB = Constants::MAX_FILE_SIZE_MB;
    private static $allowedMimeTypes = Constants::ALLOWED_MIME_TYPES;

    /**
     * Process an uploaded file
     * Returns file data array on success, false on failure
     *
     * @param array $file $_FILES['fieldname'] array
     * @param string $type 'image', 'document', or 'any'
     * @param int|null $maxSizeMB Override max size limit
     * @return array|false Array with: path, filename, stored_name, mime_type, size
     */
    public static function process($file, $type = 'any', $maxSizeMB = null)
    {
        // Basic validation
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            ResponseHelper::error('No file uploaded', 400);
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            ResponseHelper::error(self::getUploadErrorMessage($file['error']), 400);
            return false;
        }

        // Validate size
        $maxSize = ($maxSizeMB ?? self::$maxSizeMB) * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            ResponseHelper::error('File too large. Maximum: ' . ($maxSizeMB ?? self::$maxSizeMB) . 'MB', 400);
            return false;
        }

        // Detect MIME type
        $mimeType = mime_content_type($file['tmp_name']);

        // Validate type
        if ($type === 'image' && !str_starts_with($mimeType, 'image/')) {
            ResponseHelper::error('Only image files allowed', 400);
            return false;
        }

        if ($type === 'document' && str_starts_with($mimeType, 'image/')) {
            ResponseHelper::error('Only document files allowed', 400);
            return false;
        }

        if (!isset(self::$allowedMimeTypes[$mimeType])) {
            ResponseHelper::error('Invalid file type', 400);
            return false;
        }

        // Generate unique filename
        $extension = self::$allowedMimeTypes[$mimeType];
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;

        return [
            'tmp_path'   => $file['tmp_name'],
            'filename'   => self::sanitizeFilename($file['name']),
            'stored_name'=> $storedName,
            'mime_type'  => $mimeType,
            'size'       => $file['size'],
            'extension'  => $extension,
        ];
    }

    /**
     * Require a file upload - sends error if missing
     */
    public static function requireUpload($fieldName = 'file')
    {
        if (!isset($_FILES[$fieldName])) {
            ResponseHelper::error("Missing upload field: $fieldName", 400);
            exit;
        }
        return $_FILES[$fieldName];
    }

    /**
     * Move uploaded file to destination
     */
    public static function moveToStorage($tmpPath, $destination)
    {
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($tmpPath, $destination)) {
            ResponseHelper::error('Failed to save file', 500);
            return false;
        }

        return true;
    }

    /**
     * Get storage path for a file type
     */
    public static function getStoragePath($type, $id = null)
    {
        $basePath = BASE_PATH . 'PL/public/uploads/';

        switch ($type) {
            case 'profile':
                return $basePath . 'profiles/' . ($id ?? 'temp') . '/';
            case 'course':
                return $basePath . 'courses/' . ($id ?? 'temp') . '/';
            case 'cover':
                return $basePath . 'covers/';
            default:
                return $basePath . 'files/';
        }
    }

    /**
     * Get public URL for a stored file
     */
    public static function getPublicUrl($storedName, $type, $id = null)
    {
        $path = match($type) {
            'profile' => "uploads/profiles/{$id}/",
            'course'  => "uploads/courses/{$id}/",
            'cover'   => "uploads/covers/",
            default   => "uploads/files/",
        };

        return $path . $storedName;
    }

    /**
     * Delete a file from storage
     */
    public static function delete($filepath)
    {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Sanitize original filename
     */
    private static function sanitizeFilename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }

    /**
     * Get human-readable upload error message
     */
    private static function getUploadErrorMessage($code)
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL    => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension',
            default               => 'Unknown upload error',
        };
    }
}
