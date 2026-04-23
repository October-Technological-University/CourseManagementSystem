<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Middleware/FileUploadMiddleware.php';
require_once __DIR__ . '/../../BLL/Services/FileAttachmentService.php';
require_once __DIR__ . '/../../utils/FileStorageHelper.php';

class FileAttachmentController extends BaseController
{
    private $fileAttachmentService;

    public function __construct()
    {
        $this->fileAttachmentService = new FileAttachmentService();
    }

    /**
     * POST /api/files/upload/course
     * Upload a file attachment to a course
     */
    public function uploadCourseFile()
    {
        try {
            // Authenticate user
            $user = AuthMiddleware::requireAuth();
            $userId = $user->getId();

            // Get POST data
            $courseId = $_POST['course_id'] ?? null;
            $subtype = $_POST['subtype'] ?? null;

            if (!$courseId || !$subtype) {
                $this->error('course_id and subtype are required', 400);
                return;
            }

            if (!in_array($subtype, ['assignment', 'resource'])) {
                $this->error('subtype must be assignment or resource', 400);
                return;
            }

            // Require and process file upload
            $rawFile = FileUploadMiddleware::requireUpload('file');
            $fileData = FileUploadMiddleware::process($rawFile, 'any');
            if (!$fileData) {
                return; // Error already sent by process()
            }

            // Upload file via service
            $result = $this->fileAttachmentService->uploadFile($fileData, $userId, 'course', $courseId, $subtype);

            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 400);
                return;
            }

            $response = $result['data'];
            $response->file_url = $result['file_url'];
            $this->success($response, 'File uploaded successfully', 201);
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/files/upload/profile
     * Upload or replace profile picture
     */
    public function uploadProfilePicture()
    {
        try {
            // Authenticate user
            $user = AuthMiddleware::requireAuth();
            $userId = $user->getId();

            // Require and process image upload
            $rawFile = FileUploadMiddleware::requireUpload('file');
            $fileData = FileUploadMiddleware::process($rawFile, 'image');
            if (!$fileData) {
                return; // Error already sent by process()
            }

            // Upload file via service
            $result = $this->fileAttachmentService->uploadFile($fileData, $userId, 'profile', null, null);

            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 400);
                return;
            }

            $response = $result['data'];
            $response->file_url = $result['file_url'];
            $this->success($response, 'Profile picture uploaded successfully', 201);
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/download/{id}
     * Download a file by ID
     */
    public function download($id)
    {
        try {
            // Authenticate user
            $user = AuthMiddleware::requireAuth();
            $userId = $user->getId();

            // Validate ID
            if (!is_numeric($id)) {
                $this->error('Invalid file ID', 400);
                return;
            }

            // Get file info
            $result = $this->fileAttachmentService->getFileById($id);
            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 404);
                return;
            }

            $fileDTO = $result['data'];

            // Permission check
            $role = strtolower($user->getRole());

            if ($role === 'admin') {
                // Admins can download anything
            } elseif ($fileDTO->uploaded_by === $userId) {
                // Uploaders can always download their own files
            } elseif ($fileDTO->course_id !== null) {
                // Course file: allow the course instructor and enrolled students
                require_once __DIR__ . '/../../DAL/Repository/CourseRepository.php';
                require_once __DIR__ . '/../../DAL/Repository/CourseStudentRepository.php';

                $courseRepo     = new CourseRepository();
                $enrollmentRepo = new CourseStudentRepository();
                $course         = $courseRepo->getById($fileDTO->course_id);

                $isInstructor = $course && $course->getInstructorId() === $userId;
                $isEnrolled   = $enrollmentRepo->isEnrolled($fileDTO->course_id, $userId);

                if (!$isInstructor && !$isEnrolled) {
                    $this->error('Forbidden. You are not enrolled in this course.', 403);
                    return;
                }
            } else {
                $this->error('Forbidden', 403);
                return;
            }

            // Resolve file path
            $filePath = FileStorageHelper::getStoragePath($fileDTO->course_id ? 'course' : 'profile', $fileDTO->course_id) . $fileDTO->stored_name;

            if (!file_exists($filePath)) {
                $this->error('File not found on disk', 500);
                return;
            }

            // Set headers and stream file
            header('Content-Type: ' . $fileDTO->mime_type);
            header('Content-Disposition: attachment; filename="' . $fileDTO->filename . '"');
            header('Content-Length: ' . $fileDTO->file_size);
            header('Cache-Control: private');
            readfile($filePath);
            exit;
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/files/{id}
     * Delete a file
     */
    public function delete($id)
    {
        try {
            // Authenticate user
            $user = AuthMiddleware::requireAuth();
            $userId = $user->getId();

            // Validate ID
            if (!is_numeric($id)) {
                $this->error('Invalid file ID', 400);
                return;
            }

            // Get file to check ownership
            $result = $this->fileAttachmentService->getFileById($id);
            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 404);
                return;
            }

            $fileDTO = $result['data'];

            // Permission check: uploader or admin
            if ($fileDTO->uploaded_by !== $userId && $user->getRole() !== 'admin') {
                $this->error('Forbidden', 403);
                return;
            }

            // Delete file
            $deleteResult = $this->fileAttachmentService->deleteFile($id);
            if (!$deleteResult['success']) {
                $this->error(implode(', ', $deleteResult['errors']), 500);
                return;
            }

            $this->success(null, 'File deleted successfully');
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/course/{courseId}
     * Get all files for a course
     */
    public function getCourseFiles($courseId)
    {
        try {
            // Authenticate user
            AuthMiddleware::requireAuth();

            // Validate course ID
            if (!is_numeric($courseId)) {
                $this->error('Invalid course ID', 400);
                return;
            }

            // Get files
            $result = $this->fileAttachmentService->getFilesByCourseId($courseId);
            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 404);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Success',
                'data' => $result['data'],
                'count' => $result['count']
            ]);
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/course/{courseId}/assignments
     * Get assignments for a course
     */
    public function getCourseAssignments($courseId)
    {
        try {
            // Authenticate user
            AuthMiddleware::requireAuth();

            // Validate course ID
            if (!is_numeric($courseId)) {
                $this->error('Invalid course ID', 400);
                return;
            }

            // Get assignments
            $result = $this->fileAttachmentService->getAssignmentsByCourseId($courseId);
            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 404);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Success',
                'data' => $result['data'],
                'count' => $result['count']
            ]);
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/course/{courseId}/resources
     * Get resources for a course
     */
    public function getCourseResources($courseId)
    {
        try {
            // Authenticate user
            AuthMiddleware::requireAuth();

            // Validate course ID
            if (!is_numeric($courseId)) {
                $this->error('Invalid course ID', 400);
                return;
            }

            // Get resources
            $result = $this->fileAttachmentService->getResourcesByCourseId($courseId);
            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 404);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Success',
                'data' => $result['data'],
                'count' => $result['count']
            ]);
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/{id}
     * Get file metadata by ID
     */
    public function getById($id)
    {
        try {
            // Authenticate user
            AuthMiddleware::requireAuth();

            // Validate ID
            if (!is_numeric($id)) {
                $this->error('Invalid file ID', 400);
                return;
            }

            // Get file
            $result = $this->fileAttachmentService->getFileById($id);
            if (!$result['success']) {
                $this->error(implode(', ', $result['errors']), 404);
                return;
            }

            $this->success($result['data']);
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}