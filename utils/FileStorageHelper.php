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
     * @return array|false File data for database storage
     */
    public static function store($fileData, $type = 'general', $id = null)
    {
        $destination = self::getStoragePath($type, $id);
        $fullPath = $destination . $fileData['stored_name'];

        // Ensure directory exists
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($fileData['tmp_path'], $fullPath)) {
            return false;
        }

        return [
            'filename'    => $fileData['filename'],
            'stored_name' => $fileData['stored_name'],
            'file_path'   => $fullPath,
            'mime_type'   => $fileData['mime_type'],
            'file_size'   => $fileData['size'],
            'url'         => self::getPublicUrl($fileData['stored_name'], $type, $id),
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
        $base = BASE_PATH . 'PL/public/uploads/';

        return match($type) {
            'profile' => $base . 'profiles/' . ($id ?? 'temp') . '/',
            'course'  => $base . 'courses/' . ($id ?? 'temp') . '/',
            'cover'   => $base . 'covers/',
            'general' => $base . 'files/',
            default   => $base . 'misc/',
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
