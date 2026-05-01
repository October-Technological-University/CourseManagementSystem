<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Middleware/FileUploadMiddleware.php';
require_once __DIR__ . '/../../BLL/Services/FileAttachmentService.php';
require_once __DIR__ . '/../../BLL/Services/CourseService.php';
require_once __DIR__ . '/../../utils/FileStorageHelper.php';

class FileAttachmentController extends BaseController
{
    private $fileAttachmentService;
    private $courseService;
    public function __construct()
    {
        $this->fileAttachmentService = new FileAttachmentService();
        $this->courseService = new CourseService();
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
            AuthMiddleware::requireRole('instructor');
            $userId = $user->getId();

            // Get POST data
            $courseId = $_POST['course_id'] ?? null;
            $subtype = $_POST['subtype'] ?? null;
            
            if (!$courseId) {
                self::error('course_id is required', 400);
                return;
            }

            $courseService = new CourseService();
            $courseResult = $courseService->getById((int)$courseId); 
            if (!$courseResult['success']) {
                self::error('Course not found', 404);
                return;
            }

            $courseDTO = $courseResult['data'];
            $role = strtolower($user->getRole());

            if ($role !== 'admin' && $courseDTO->instructor_id != $userId) {
                self::error('Forbidden (FAC-U). You are not the instructor of this course.', 403);
                return;
            }

            if (!$subtype) {
                self::error('subtype is required', 400);
                return;
            }

            if (!in_array($subtype, ['assignment', 'resource'])) {
                self::error('subtype must be assignment or resource', 400);
                return;
            }

            // Require and process file upload
            $rawFile = FileUploadMiddleware::requireUpload('file');
            $fileData = FileUploadMiddleware::process($rawFile, 'any');
            if (!$fileData) {
                return; // Error already sent by process()
            }

            // Upload file via service
            $result = $this->fileAttachmentService->uploadFile($fileData, $userId, 'course', (int)$courseId, $subtype);

            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 400);
                return;
            }

            $response = $result['data'];
            if (method_exists($response, 'toArray')) {
                $data = $response->toArray();
                $data['file_url'] = $result['file_url'];
                self::success($data, 'File uploaded successfully', 201);
            } else {
                self::success($response, 'File uploaded successfully', 201);
            }
        } catch (Exception $e) {
            self::error('An error occurred: ' . $e->getMessage(), 500);
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
                self::error('Invalid file ID', 400);
                return;
            }

            // Get file info
            $result = $this->fileAttachmentService->getFileById($id);
            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 404);
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
                    self::error('Forbidden. You are not enrolled in this course.', 403);
                    return;
                }
            } else {
                self::error('Forbidden', 403);
                return;
            }

            // Resolve file path
            $filePath = FileStorageHelper::getStoragePath($fileDTO->course_id ? 'course' : 'profile', $fileDTO->course_id) . $fileDTO->stored_name;

            if (!file_exists($filePath)) {
                self::error('File not found on disk', 500);
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
            self::error('An error occurred: ' . $e->getMessage(), 500);
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
                self::error('Invalid file ID', 400);
                return;
            }

            // Get file to check ownership
            $result = $this->fileAttachmentService->getFileById($id);
            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 404);
                return;
            }

            $fileDTO = $result['data'];

            // Permission check: uploader or admin
            if ($fileDTO->uploaded_by !== $userId && $user->getRole() !== 'admin') {
                self::error('Forbidden', 403);
                return;
            }

            // Delete file
            $deleteResult = $this->fileAttachmentService->deleteFile($id);
            if (!$deleteResult['success']) {
                self::error(implode(', ', $deleteResult['errors']), 500);
                return;
            }

            BaseController::success(null, 'File deleted successfully');
        } catch (Exception $e) {
            self::error('An error occurred: ' . $e->getMessage(), 500);
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
                self::error('Invalid course ID', 400);
                return;
            }

            // Get files
            $result = $this->fileAttachmentService->getFilesByCourseId($courseId);
            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 404);
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
            self::error('An error occurred: ' . $e->getMessage(), 500);
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
                self::error('Invalid course ID', 400);
                return;
            }

            // Get assignments
            $result = $this->fileAttachmentService->getAssignmentsByCourseId($courseId);
            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 404);
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
            self::error('An error occurred: ' . $e->getMessage(), 500);
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
                self::error('Invalid course ID', 400);
                return;
            }

            // Get resources
            $result = $this->fileAttachmentService->getResourcesByCourseId($courseId);
            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 404);
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
            self::error('An error occurred: ' . $e->getMessage(), 500);
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
                self::error('Invalid file ID', 400);
                return;
            }

            // Get file
            $result = $this->fileAttachmentService->getFileById($id);
            if (!$result['success']) {
                self::error(implode(', ', $result['errors']), 404);
                return;
            }

            BaseController::success($result['data']);
        } catch (Exception $e) {
            self::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/serve/{storedName}
     * Serve a file by stored name (public endpoint)
     */
    public function serve($storedName)
    {
        try {
            // Get query parameters
            $type = $_GET['type'] ?? 'profile';
            $courseId = $_GET['course_id'] ?? null;
            $subtype = $_GET['subtype'] ?? null;

            // Sanitize stored name to prevent path traversal
            if (preg_match('/[^a-zA-Z0-9._-]/', $storedName)) {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode(['error' => 'Invalid file name']);
                return;
            }

            // Determine file path based on type
            if ($type === 'course' && $courseId) {
                $filePath = FileStorageHelper::getStoragePath('course', $courseId) . $storedName;
            } else {
                // For profile pictures, we need to determine the user ID from the stored name or query
                // Since profile pictures are stored in /uploads/profiles/{userId}/
                // We can try multiple user IDs or extract from path
                // For now, we'll scan common profile directory
                $filePath = BASE_PATH . 'PL/public/uploads/profiles/*/' . $storedName;
                $matches = glob($filePath);
                if (empty($matches)) {
                    header("HTTP/1.0 404 Not Found");
                    echo json_encode(['error' => 'File not found']);
                    return;
                }
                $filePath = $matches[0];
            }

            // Check if file exists
            if (!file_exists($filePath)) {
                header("HTTP/1.0 404 Not Found");
                echo json_encode(['error' => 'File not found']);
                return;
            }

            // Get MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if (!$mimeType) {
                $mimeType = 'application/octet-stream';
            }

            // Set headers for inline display (images will show in browser, others may trigger download)
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: public, max-age=3600');

            // For images, use inline display; for other files, use attachment
            if (strpos($mimeType, 'image') === 0) {
                header('Content-Disposition: inline; filename="' . basename($storedName) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . basename($storedName) . '"');
            }

            readfile($filePath);
            exit;
        } catch (Exception $e) {
            header("HTTP/1.0 500 Internal Server Error");
            echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}