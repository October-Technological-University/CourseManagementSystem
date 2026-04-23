<?php
require_once __DIR__ . '/../../DAL/Repository/CourseStudentRepository.php';
require_once __DIR__ . '/../../DAL/Repository/CourseRepository.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../DAL/DTOs/CourseDTOs.php';
require_once __DIR__ . '/../../BLL/Mappers/EnrollmentMapper.php';

class EnrollmentService
{
    private $enrollmentRepo;
    private $courseRepo;
    private $userRepo;
    private $mapper;

    public function __construct()
    {
        $this->enrollmentRepo = new CourseStudentRepository();
        $this->courseRepo     = new CourseRepository();
        $this->userRepo       = new UserRepository();
        $this->mapper         = new EnrollmentMapper(
            new UserRepository(),
            new CourseRepository()
        );
    }

    public function enroll(int $courseId, int $studentId): array
    {
        $course = $this->courseRepo->getById($courseId);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $student = $this->userRepo->getById($studentId);
        if (!$student) {
            return ['success' => false, 'errors' => ['Student not found']];
        }

        if (strtolower($student->getRole()) !== 'student') {
            return ['success' => false, 'errors' => ['Only users with the student role can be enrolled']];
        }

        if ($this->enrollmentRepo->isEnrolled($courseId, $studentId)) {
            return ['success' => false, 'errors' => ['Student is already enrolled in this course']];
        }

        if ($this->courseRepo->isFull($courseId)) {
            return ['success' => false, 'errors' => ['Course is full']];
        }

        $today = date('Y-m-d');
        if ($today > $course->getEndDate()) {
            return ['success' => false, 'errors' => ['Cannot enroll: course has already ended']];
        }

        try {
            $this->enrollmentRepo->beginTransaction();
            $enrollmentId = $this->enrollmentRepo->enroll($courseId, $studentId, 'active');
            $this->enrollmentRepo->commit();
        } catch (Exception $e) {
            $this->enrollmentRepo->rollback();
            return ['success' => false, 'errors' => ['Enrollment failed: ' . $e->getMessage()]];
        }

        $enrollment = $this->enrollmentRepo->getById($enrollmentId);
        return [
            'success' => true,
            'data'    => $this->mapper->toDTO($enrollment),
        ];
    }

    public function drop(int $courseId, int $studentId): array
    {
        if (!$this->enrollmentRepo->isEnrolled($courseId, $studentId)) {
            return ['success' => false, 'errors' => ['Student is not enrolled in this course']];
        }

        $this->enrollmentRepo->updateStatusByCourseAndStudent($courseId, $studentId, 'dropped');

        return ['success' => true, 'message' => 'Enrollment dropped successfully'];
    }

    public function getStudentsByCourse(int $courseId): array
    {
        $course = $this->courseRepo->getById($courseId);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $enrollments = $this->enrollmentRepo->getActiveByCourseId($courseId);
        $students    = $this->mapper->toStudentList($enrollments);

        return ['success' => true, 'data' => $students];
    }

    public function getCoursesByStudent(int $studentId): array
    {
        $student = $this->userRepo->getById($studentId);
        if (!$student) {
            return ['success' => false, 'errors' => ['Student not found']];
        }

        $enrollments = $this->enrollmentRepo->getActiveByStudentId($studentId);
        $courses     = $this->mapper->toCourseList($enrollments);

        return ['success' => true, 'data' => $courses];
    }

    public function getById(int $enrollmentId): array
    {
        $enrollment = $this->enrollmentRepo->getById($enrollmentId);
        if (!$enrollment) {
            return ['success' => false, 'errors' => ['Enrollment not found']];
        }

        return ['success' => true, 'data' => $this->mapper->toDTO($enrollment)];
    }

    public function isEnrolled(int $courseId, int $studentId): bool
    {
        return $this->enrollmentRepo->isEnrolled($courseId, $studentId);
    }
}
