<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
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
            self::error('Forbidden. Only admins and instructors can create courses.', 403);
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

        if ($role !== 'admin' && $course->instructor_id !== $user->getId()) {
            self::error('Forbidden. You can only update your own courses.', 403);
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
     * Protected: admin only.
     */
    public function delete(int $id)
    {
        AuthMiddleware::requireRole('admin');

        $result = $this->courseService->delete($id);

        if ($result['success']) {
            self::success(null, $result['message']);
            return;
        }

        self::error(implode(', ', $result['errors']), 404);
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
