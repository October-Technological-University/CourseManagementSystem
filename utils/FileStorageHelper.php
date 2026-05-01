<?php

/**
 * File Storage Helper
 * Handles file system operations for uploaded files
 */
class FileStorageHelper
{
    private static $baseUrl = '/uploads/';

    /**
     * Store an uploaded file
     *
     * @param array $fileData Array from FileUploadMiddleware::process()
     * @param string $type 'profile', 'course', 'cover', or 'general'
     * @param int|null $id User ID or Course ID for subdirectory
     * @return array Response with success, data, and error
     */
    public static function store($fileData, $type = 'general', $id = null)
    {
        $destination = self::getStoragePath($type, $id);
        $fullPath = $destination . $fileData['stored_name'];

        // Ensure directory exists with broad permissions for cloud environments
        if (!is_dir($destination)) {
            if (!mkdir($destination, 0777, true)) {
                $err = "Failed to create directory: " . $destination;
                error_log("FileStorageHelper Error: " . $err);
                return ['success' => false, 'error' => $err];
            }
            chmod($destination, 0777); 
        }

        if (!is_writable($destination)) {
            $err = "Destination directory is not writable: " . $destination;
            error_log("FileStorageHelper Error: " . $err);
            return ['success' => false, 'error' => $err];
        }

        // Move uploaded file
        if (!move_uploaded_file($fileData['tmp_path'], $fullPath)) {
            $err = "Failed to move file from " . $fileData['tmp_path'] . " to " . $fullPath;
            error_log("FileStorageHelper Error: " . $err);
            return ['success' => false, 'error' => $err];
        }

        chmod($fullPath, 0666); 

        return [
            'success' => true,
            'data' => [
                'filename'    => $fileData['filename'],
                'stored_name' => $fileData['stored_name'],
                'file_path'   => $fullPath,
                'mime_type'   => $fileData['mime_type'],
                'file_size'   => $fileData['size'],
                'url'         => self::getPublicUrl($fileData['stored_name'], $type, $id),
            ]
        ];
    }

    /**
     * Delete a file from storage
     *
     * @param string $filepath Full file path
     * @return bool Success
     */
    public static function delete($filepath)
    {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Delete file by stored name
     *
     * @param string $storedName Unique stored filename
     * @param string $type File type category
     * @param int|null $id Associated user/course ID
     * @return bool Success
     */
    public static function deleteByName($storedName, $type = 'general', $id = null)
    {
        $filepath = self::getStoragePath($type, $id) . $storedName;
        return self::delete($filepath);
    }

    /**
     * Get the full storage path for a file type
     */
    public static function getStoragePath($type, $id = null)
    {
        // On Azure/Linux, we might not have write access to the project folder.
        // We will try to use the public uploads folder first, then fallback to a writable temp dir.
        $base = BASE_PATH . 'PL/public/uploads/';
        
        if (!is_dir($base)) {
            @mkdir($base, 0777, true);
        }

        // If not writable, use a more reliable location like /tmp or similar in Azure
        if (!is_writable($base)) {
            $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cms_uploads' . DIRECTORY_SEPARATOR;
            if (!is_dir($base)) {
                @mkdir($base, 0777, true);
            }
        }
        
        $resolvedBase = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return match($type) {
            'profile' => $resolvedBase . 'profiles' . DIRECTORY_SEPARATOR . ($id ?? 'temp') . DIRECTORY_SEPARATOR,
            'course'  => $resolvedBase . 'courses' . DIRECTORY_SEPARATOR . ($id ?? 'temp') . DIRECTORY_SEPARATOR,
            'cover'   => $resolvedBase . 'covers' . DIRECTORY_SEPARATOR,
            'general' => $resolvedBase . 'files' . DIRECTORY_SEPARATOR,
            default   => $resolvedBase . 'misc' . DIRECTORY_SEPARATOR,
        };
    }

    /**
     * Get public URL for a file
     */
    public static function getPublicUrl($storedName, $type, $id = null)
    {
        $path = match($type) {
            'profile' => "uploads/profiles/{$id}/",
            'course'  => "uploads/courses/{$id}/",
            'cover'   => "uploads/covers/",
            'general' => "uploads/files/",
            default   => "uploads/misc/",
        };

        return $path . $storedName;
    }

    /**
     * Generate file access URL for API responses
     * 
     * @param string $storedName The stored filename
     * @param int|null $courseId Course ID (if file is course-related)
     * @param string|null $subtype File subtype (if course file)
     * @return string The file access URL
     */
    public static function getFileUrl($storedName, $courseId = null, $subtype = null)
    {
        // If courseId is set, it's a course file; otherwise it's a profile picture
        if ($courseId !== null) {
            return "/api/files/serve/{$storedName}?type=course&course_id={$courseId}&subtype={$subtype}";
        } else {
            return "/api/files/serve/{$storedName}?type=profile";
        }
    }

    /**
     * Check if file exists
     */
    public static function exists($filepath)
    {
        return file_exists($filepath);
    }

    /**
     * Get file size in human-readable format
     */
    public static function humanReadableSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Create directory if it doesn't exist
     */
    public static function ensureDirectory($path)
    {
        if (!is_dir($path)) {
            return mkdir($path, 0755, true);
        }
        return true;
    }

    /**
     * Clean up empty directories
     */
    public static function cleanupDirectory($path)
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            if (empty($files)) {
                rmdir($path);
            }
        }
    }
}
