<?php
require_once __DIR__ . '/../Mappers/FileAttachmentMapper.php';
require_once __DIR__ . '/../../DAL/DTOs/FileDTOs.php';
require_once __DIR__ . '/../../DAL/Repository/FileAttachmentRepository.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../DAL/Repository/CourseRepository.php';
require_once __DIR__ . '/../../utils/FileStorageHelper.php';
require_once __DIR__ . '/../../utils/Validator.php';

class FileAttachmentService
{
    private $fileAttachmentRepository;
    private $fileAttachmentMapper;
    private $userRepository;
    private $courseRepository;
    private $validator;

    /**
     * Constructor - Initialize all dependencies
     */
    public function __construct()
    {
        $this->fileAttachmentRepository = new FileAttachmentRepository();
        $this->userRepository = new UserRepository();
        $this->courseRepository = new CourseRepository();
        $this->fileAttachmentMapper = new FileAttachmentMapper($this->userRepository, $this->courseRepository);
        $this->validator = new Validator();
    }

    /**
     * Upload a file (profile picture or course attachment)
     *
     * @param array $fileData File data from FileUploadMiddleware
     * @param string $uploadedByUserId User ID uploading the file
     * @param string $type 'profile' for profile pictures or 'course' for course attachments
     * @param int|null $courseId Course ID (required for course files)
     * @param string|null $subtype 'assignment', 'resource', or null for profile pictures
     * @return array Response with success status and file data or errors
     */
    public function uploadFile(array $fileData, int $uploadedByUserId, string $type = 'profile', ?int $courseId = null, ?string $subtype = null): array
    {
        try {
            // Validate user exists
            $user = $this->userRepository->getById($uploadedByUserId);
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }

            // Validate file data
            if (!$this->validator->validateRequired($fileData, ['filename', 'tmp_path', 'mime_type', 'size', 'stored_name'])) {
                return ['success' => false, 'errors' => $this->validator->getErrors()];
            }

            // For course files, validate course exists
            if ($type === 'course' && $courseId) {
                $course = $this->courseRepository->getById($courseId);
                if (!$course) {
                    return ['success' => false, 'errors' => ['Course not found']];
                }
            }

            // Store file on filesystem
            $storedFile = FileStorageHelper::store($fileData, $type, $courseId);
            if (!$storedFile) {
                return ['success' => false, 'errors' => ['Failed to store file on disk']];
            }

            // Create FileAttachment entity
            $fileEntity = new FileAttachment(
                $storedFile['filename'],
                $storedFile['stored_name'],
                $storedFile['file_path'],
                $storedFile['mime_type'],
                $storedFile['file_size'],
                $uploadedByUserId,
                $courseId,
                $subtype
            );

            // Save to database
            $fileId = $this->fileAttachmentRepository->create($fileEntity);
            if (!$fileId) {
                // Clean up uploaded file if database save fails
                FileStorageHelper::delete($storedFile['file_path']);
                return ['success' => false, 'errors' => ['Failed to save file metadata to database']];
            }

            // Fetch saved file and return as DTO
            $savedFile = $this->fileAttachmentRepository->getById($fileId);
            if (!$savedFile) {
                return ['success' => false, 'errors' => ['Failed to retrieve saved file']];
            }

            return [
                'success' => true,
                'data' => $this->fileAttachmentMapper->toDTO($savedFile),
                'file_url' => $storedFile['url']
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get file attachment by ID
     *
     * @param int $fileId File attachment ID
     * @return array Response with file data or error
     */
    public function getFileById(int $fileId): array
    {
        try {
            $file = $this->fileAttachmentRepository->getById($fileId);
            if (!$file) {
                return ['success' => false, 'errors' => ['File not found']];
            }

            return [
                'success' => true,
                'data' => $this->fileAttachmentMapper->toDTO($file)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get all files for a course
     *
     * @param int $courseId Course ID
     * @return array Response with files list or error
     */
    public function getFilesByCourseId(int $courseId): array
    {
        try {
            // Validate course exists
            $course = $this->courseRepository->getById($courseId);
            if (!$course) {
                return ['success' => false, 'errors' => ['Course not found']];
            }

            $files = $this->fileAttachmentRepository->getByCourseId($courseId);
            $filesDTO = array_map(fn($file) => $this->fileAttachmentMapper->toDTO($file), $files);

            return [
                'success' => true,
                'data' => $filesDTO,
                'count' => count($filesDTO)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get assignments for a course
     *
     * @param int $courseId Course ID
     * @return array Response with assignments or error
     */
    public function getAssignmentsByCourseId(int $courseId): array
    {
        try {
            // Validate course exists
            $course = $this->courseRepository->getById($courseId);
            if (!$course) {
                return ['success' => false, 'errors' => ['Course not found']];
            }

            $assignments = $this->fileAttachmentRepository->getAssignmentsByCourseId($courseId);
            $assignmentsDTO = array_map(fn($file) => $this->fileAttachmentMapper->toDTO($file), $assignments);

            return [
                'success' => true,
                'data' => $assignmentsDTO,
                'count' => count($assignmentsDTO)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get resources for a course
     *
     * @param int $courseId Course ID
     * @return array Response with resources or error
     */
    public function getResourcesByCourseId(int $courseId): array
    {
        try {
            // Validate course exists
            $course = $this->courseRepository->getById($courseId);
            if (!$course) {
                return ['success' => false, 'errors' => ['Course not found']];
            }

            $resources = $this->fileAttachmentRepository->getResourcesByCourseId($courseId);
            $resourcesDTO = array_map(fn($file) => $this->fileAttachmentMapper->toDTO($file), $resources);

            return [
                'success' => true,
                'data' => $resourcesDTO,
                'count' => count($resourcesDTO)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get profile picture for a user
     *
     * @param int $userId User ID
     * @return array Response with profile picture or error
     */
    public function getProfilePictureByUserId(int $userId): array
    {
        try {
            // Validate user exists
            $user = $this->userRepository->getById($userId);
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }

            $profilePicture = $this->fileAttachmentRepository->getProfilePictureByUserId($userId);
            if (!$profilePicture) {
                return ['success' => false, 'errors' => ['No profile picture found for this user']];
            }

            return [
                'success' => true,
                'data' => $this->fileAttachmentMapper->toDTO($profilePicture)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get all files uploaded by a specific user
     *
     * @param int $userId User ID
     * @return array Response with files or error
     */
    public function getFilesByUploadedBy(int $userId): array
    {
        try {
            // Validate user exists
            $user = $this->userRepository->getById($userId);
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }

            $files = $this->fileAttachmentRepository->getByUploadedBy($userId);
            $filesDTO = array_map(fn($file) => $this->fileAttachmentMapper->toDTO($file), $files);

            return [
                'success' => true,
                'data' => $filesDTO,
                'count' => count($filesDTO)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get all file attachments (profile pictures and course files)
     *
     * @return array Response with all files or error
     */
    public function getAllFiles(): array
    {
        try {
            $files = $this->fileAttachmentRepository->getAll();
            $filesDTO = array_map(fn($file) => $this->fileAttachmentMapper->toDTO($file), $files);

            return [
                'success' => true,
                'data' => $filesDTO,
                'count' => count($filesDTO)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get all profile pictures
     *
     * @return array Response with profile pictures or error
     */
    public function getProfilePictures(): array
    {
        try {
            $pictures = $this->fileAttachmentRepository->getProfilePictures();
            $picturesDTO = array_map(fn($file) => $this->fileAttachmentMapper->toDTO($file), $pictures);

            return [
                'success' => true,
                'data' => $picturesDTO,
                'count' => count($picturesDTO)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Delete a file attachment
     *
     * Removes both database record and physical file from storage
     *
     * @param int $fileId File attachment ID
     * @return array Response indicating success or error
     */
    public function deleteFile(int $fileId): array
    {
        try {
            // Get file before deleting
            $file = $this->fileAttachmentRepository->getById($fileId);
            if (!$file) {
                return ['success' => false, 'errors' => ['File not found']];
            }

            // Delete from database
            $deleted = $this->fileAttachmentRepository->delete($fileId);
            if ($deleted <= 0) {
                return ['success' => false, 'errors' => ['Failed to delete file from database']];
            }

            // Delete physical file from storage
            $fileDeleted = FileStorageHelper::delete($file->getFilePath());
            if (!$fileDeleted) {
                // Log warning but don't fail - file is deleted from db, just not from disk
                // In production, consider logging this for cleanup
            }

            return ['success' => true, 'message' => 'File deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Update file attachment metadata
     *
     * Note: This updates metadata only, not the actual file content
     *
     * @param int $fileId File attachment ID
     * @param array $data Updated file data
     * @return array Response with updated file or error
     */
    public function updateFileMetadata(int $fileId, array $data): array
    {
        try {
            // Get existing file
            $file = $this->fileAttachmentRepository->getById($fileId);
            if (!$file) {
                return ['success' => false, 'errors' => ['File not found']];
            }

            // Update fields if provided
            if (isset($data['course_id'])) {
                $file->setCourseId($data['course_id']);
            }
            if (isset($data['subtype'])) {
                $file->setSubtype($data['subtype']);
            }

            // Update in database
            $updated = $this->fileAttachmentRepository->update($file);
            if ($updated <= 0) {
                return ['success' => false, 'errors' => ['Failed to update file metadata']];
            }

            // Fetch and return updated file
            $updatedFile = $this->fileAttachmentRepository->getById($fileId);
            return [
                'success' => true,
                'data' => $this->fileAttachmentMapper->toDTO($updatedFile)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }

    /**
     * Get file statistics
     *
     * @return array Response with file statistics
     */
    public function getStatistics(): array
    {
        try {
            $totalCount = $this->fileAttachmentRepository->getTotalCount();
            $profilePictures = $this->fileAttachmentRepository->getProfilePictures();
            $allFiles = $this->fileAttachmentRepository->getAll();

            $courseFiles = array_filter($allFiles, fn($file) => $file->getCourseId() !== null);

            return [
                'success' => true,
                'data' => [
                    'total_files' => $totalCount,
                    'profile_pictures' => count($profilePictures),
                    'course_files' => count($courseFiles)
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]];
        }
    }
}
 