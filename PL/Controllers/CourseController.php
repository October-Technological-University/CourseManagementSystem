<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Middleware/FileUploadMiddleware.php';
require_once __DIR__ . '/../../BLL/Services/CourseService.php';

class CourseController extends BaseController
{
    private $courseService;

    public function __construct()
    {
        $this->courseService = new CourseService();
    }

    /**
     * GET /api/courses
     * Supports optional query params: ?keyword=php&instructor_id=3&available=1
     */
    public function index()
    {
        $filters = [
            'keyword'       => $_GET['keyword']       ?? null,
            'instructor_id' => $_GET['instructor_id'] ?? null,
            'available'     => $_GET['available']      ?? null,
        ];

        $hasFilters = array_filter($filters);
        $result     = $hasFilters
            ? $this->courseService->search($filters)
            : $this->courseService->getAll();

        if ($result['success']) {
            $data = array_map(fn($dto) => $dto->toArray(), $result['data']);
            self::success($data, 'Courses retrieved successfully');
            return;
        }

        self::error(implode(', ', $result['errors']), 400);
    }

    /**
     * GET /api/courses/{id}
     */
    public function show(int $id)
    {
        $result = $this->courseService->getById($id);

        if ($result['success']) {
            self::success($result['data']->toArray(), 'Course retrieved successfully');
            return;
        }

        self::error(implode(', ', $result['errors']), 404);
    }

    /**
     * POST /api/courses
     * Protected: admin or instructor only.
     */
    public function create()
    {
        $user = AuthMiddleware::requireAuth();
        $role = strtolower($user->getRole());

        if (!in_array($role, ['admin', 'instructor'])) {
            self::error('Forbidden (CC-C). Only admins and instructors can create courses.', 403);
            return;
        }

        $data = self::getJsonInput();

        if ($role === 'instructor') {
            $data['instructor_id'] = $user->getId();
        }

        $result = $this->courseService->create($data);

        if ($result['success']) {
            self::success($result['data']->toArray(), 'Course created successfully', 201);
            return;
        }

        self::error(implode(', ', $result['errors']), 400);
    }

    public function generateCode($id){
        $user = AuthMiddleware::requireRole('instructor');

        $courseResult = $this->courseService->getById($id);
        if (!$courseResult['success']) {
            self::error('Course not found', 404);
            return;
        }
        $course = $courseResult['data'];
        if ($course->getInstructorId() != $user->getId()) {
            self::error('Forbidden. You can only generate invite codes for your own courses.', 403);
            return;
        }
        $result = $this->courseService->generateCourseInviteCode($id);
        if ($result['success']) {
            self::success($result['data'], 'Course invite code generated successfully');
            return;
        }
        self::error(implode(', ', $result['errors']), 400);
    }

    /**
     * PUT /api/courses/{id}
     * Protected: admin or the course's own instructor.
     */
    public function update(int $id)
    {
        $user   = AuthMiddleware::requireAuth();
        $role   = strtolower($user->getRole());
        $result = $this->courseService->getById($id);

        if (!$result['success']) {
            self::error('Course not found', 404);
            return;
        }

        $course = $result['data'];

        if ($role !== 'admin' && $course->getInstructorId() != $user->getId()) {
            self::error('Forbidden. Only the course instructor or an admin can update your own courses.', 403);
            return;
        }

        $data         = self::getJsonInput();
        $updateResult = $this->courseService->update($id, $data);

        if ($updateResult['success']) {
            self::success($updateResult['data']->toArray(), 'Course updated successfully');
            return;
        }

        self::error(implode(', ', $updateResult['errors']), 400);
    }

    /**
     * DELETE /api/courses/{id}
     * Protected: admin or course instructor.
     */
    public function delete(int $id)
    {
        $user = AuthMiddleware::requireAuth();
        $role = strtolower($user->getRole());

        $result = $this->courseService->getById($id);
        if (!$result['success']) {
            self::error('Course not found', 404);
            return;
        }

        $course = $result['data'];
        if ($role !== 'admin' && $course->getInstructorId() != $user->getId()) {
            self::error('Forbidden. Only the course instructor or an admin can delete this course.', 403);
            return;
        }

        $deleteResult = $this->courseService->delete($id);

        if ($deleteResult['success']) {
            self::success(null, $deleteResult['message']);
            return;
        }

        self::error(implode(', ', $deleteResult['errors']), 400);
    }

    /**
     * POST /api/courses/{id}/course-image
     * Protected: admin or course's own instructor.
     */
    public function uploadCourseImage(int $id)
    {
        $user = AuthMiddleware::requireAuth();
        $role = strtolower($user->getRole());

        $result = $this->courseService->getById($id);
        if (!$result['success']) {
            self::error('Course not found', 404);
            return;
        }

        $course = $result['data'];
        if ($role !== 'admin' && $course->getInstructorId() != $user->getId()) {
            self::error('Forbidden. Only the course instructor or an admin can upload a course image.', 403);
            return;
        }

        $upload = FileUploadMiddleware::requireUpload('file');
        $fileData = FileUploadMiddleware::process($upload, 'image');
        if (!$fileData) {
            return;
        }

        $uploadResult = $this->courseService->uploadCourseImage($fileData, $id, $user->getId());
        if ($uploadResult['success']) {
            self::success(
                [
                    'file' => $uploadResult['data'],
                    'file_url' => $uploadResult['file_url'] ?? null
                ],
                'Course image uploaded successfully',
                201
            );
            return;
        }

        $error = $uploadResult['error'] ?? (isset($uploadResult['errors']) ? implode(', ', $uploadResult['errors']) : 'Failed to upload course image');
        self::error($error, 400);
    }

    /**
     * DELETE /api/courses/{id}/course-image
     * Protected: admin or course's own instructor.
     */
    public function removeCourseImage(int $id)
    {
        $user = AuthMiddleware::requireAuth();
        $role = strtolower($user->getRole());

        $result = $this->courseService->getById($id);
        if (!$result['success']) {
            self::error('Course not found', 404);
            return;
        }

        if ($role !== 'admin' && $result['data']->instructor_id !== $user->getId()) {
            self::error('Forbidden. Only the course instructor or an admin can remove the course image.', 403);
            return;
        }

        $removeResult = $this->courseService->removeCourseImage($id);
        if ($removeResult['success']) {
            self::success(null, $removeResult['message']);
            return;
        }

        self::error(implode(', ', $removeResult['errors']), 400);
    }

    /**
     * GET /api/courses/instructor/{instructorId}
     */
    public function getByInstructor(int $instructorId)
    {
        $result = $this->courseService->getByInstructor($instructorId);

        if ($result['success']) {
            $data = array_map(fn($dto) => $dto->toArray(), $result['data']);
            self::success($data, 'Instructor courses retrieved successfully');
            return;
        }

        self::error(implode(', ', $result['errors']), 404);
    }
}
