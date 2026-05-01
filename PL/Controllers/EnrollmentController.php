<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../BLL/Services/EnrollmentService.php';

class EnrollmentController extends BaseController
{
    private $enrollmentService;

    public function __construct()
    {
        $this->enrollmentService = new EnrollmentService();
    }

    /**
     * POST /api/enrollments
     * Body: { "course_id": 1, "student_id": 5 }
     * Students enroll themselves; admins can enroll any student.
     */
    public function enroll()
    {
        $user = AuthMiddleware::requireAuth();
        $data = self::getJsonInput();
        $role = strtolower($user->getRole());

        if (!isset($data['course_id'])) {
            self::error('course_id is required', 400);
            return;
        }

        $courseId = (int)$data['course_id'];

        if ($role === 'student') {
            $studentId = $user->getId();
        } elseif ($role === 'admin') {
            if (!isset($data['student_id'])) {
                self::error('student_id is required for admin enrollment', 400);
                return;
            }
            $studentId = (int)$data['student_id'];
        } else {
            self::error('Forbidden. Instructors cannot enroll students.', 403);
            return;
        }

        $result = $this->enrollmentService->enroll($courseId, $studentId);

        if ($result['success']) {
            self::success($result['data']->toArray(), 'Enrolled successfully', 201);
            return;
        }

        self::error(implode(', ', $result['errors']), 400);
    }

    /**
     * POST /api/enrollments/code
     * Body: { "course_code": "ABC123" }
     * Students enroll themselves using a course invite code.
     */
    public function enrollByCode()
    {
        $user = AuthMiddleware::requireRole('student');
        $data = self::getJsonInput();

        if (!isset($data['course_code']) || trim($data['course_code']) === '') {
            self::error('course_code is required', 400);
            return;
        }

        $result = $this->enrollmentService->enrollByCode(trim($data['course_code']), $user->getId());

        if ($result['success']) {
            self::success($result['data']->toArray(), 'Enrolled successfully', 201);
            return;
        }

        self::error(implode(', ', $result['errors']), 400);
    }

    /**
     * DELETE /api/enrollments/drop
     * Body: { "course_id": 1, "student_id": 5 }
     * Students drop themselves; admins can drop any student.
     */
    public function drop()
    {
        $user = AuthMiddleware::requireAuth();
        $data = self::getJsonInput();
        $role = strtolower($user->getRole());

        if (!isset($data['course_id'])) {
            self::error('course_id is required', 400);
            return;
        }

        $courseId = (int)$data['course_id'];

        if ($role === 'student') {
            $studentId = $user->getId();
        } elseif ($role === 'instructor') {
            // Check if this instructor actually teaches this course
            $courseRepo = new CourseRepository();
            $course = $courseRepo->getById($courseId);
            if (!$course || $course->getInstructorId() != $user->getId()) {
                self::error('Forbidden (EC-D-I). You can only drop students from your own courses.', 403);
                return;
            }
            if (!isset($data['student_id'])) {
                self::error('student_id is required', 400);
                return;
            }
            $studentId = (int)$data['student_id'];
        } elseif ($role === 'admin') {
            if (!isset($data['student_id'])) {
                self::error('student_id is required', 400);
                return;
            }
            $studentId = (int)$data['student_id'];
        } else {
            self::error('Forbidden (EC-D).', 403);
            return;
        }

        $result = $this->enrollmentService->drop($courseId, $studentId);

        if ($result['success']) {
            self::success(null, $result['message']);
            return;
        }

        self::error(implode(', ', $result['errors']), 400);
    }

    /**
     * GET /api/enrollments/course/{courseId}/students
     * Protected: admin or instructor.
     */
    public function getStudentsByCourse(int $courseId)
    {
        $user = AuthMiddleware::requireAuth();
        $role = strtolower($user->getRole());

        if ($role === 'student') {
            self::error('Forbidden (EC-GS-S). Students cannot view student lists.', 403);
            return;
        }

        $result = $this->enrollmentService->getStudentsByCourse($courseId);

        if ($result['success']) {
            $data = array_map(fn($dto) => method_exists($dto, 'toArray') ? $dto->toArray() : (array)$dto, $result['data']);
            self::success($data, 'Students retrieved successfully');
            return;
        }

        self::error(implode(', ', $result['errors']), 404);
    }

    /**
     * GET /api/enrollments/student/{studentId}/courses
     * Students can view only their own; admins can view any.
     */
    public function getCoursesByStudent(int $studentId)
    {
        $user = AuthMiddleware::requireAuth();
        $role = strtolower($user->getRole());

        if ($role === 'student' && $user->getId() !== $studentId) {
            self::error('Forbidden. You can only view your own courses.', 403);
            return;
        }

        $result = $this->enrollmentService->getCoursesByStudent($studentId);

        if ($result['success']) {
            $data = array_map(fn($dto) => method_exists($dto, 'toArray') ? $dto->toArray() : (array)$dto, $result['data']);
            self::success($data, 'Courses retrieved successfully');
            return;
        }

        self::error(implode(', ', $result['errors']), 404);
    }
}
