<?php
require_once __DIR__ . '/../../DAL/Repository/CourseRepository.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../DAL/Repository/CourseStudentRepository.php';
require_once __DIR__ . '/../../DAL/DTOs/CourseDTOs.php';
require_once __DIR__ . '/../../BLL/Mappers/CourseMapper.php';
require_once __DIR__ . '/../../utils/Validator.php';

class CourseService
{
    private $courseRepo;
    private $userRepo;
    private $enrollmentRepo;
    private $mapper;
    private $validator;

    public function __construct()
    {
        $this->courseRepo     = new CourseRepository();
        $this->userRepo       = new UserRepository();
        $this->enrollmentRepo = new CourseStudentRepository();
        $this->mapper         = new CourseMapper();
        $this->validator      = new Validator();
    }

    public function create(array $data): array
    {
        if (!$this->validator->validateRequired($data, ['name', 'instructor_id', 'start_date', 'end_date'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        if (!$this->validator->validateDate($data['start_date']) || !$this->validator->validateDate($data['end_date'])) {
            return ['success' => false, 'errors' => ['Invalid date format. Use Y-m-d.']];
        }
        if (empty($data['code'])) {
            $data['code'] = null; 
        }
        if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            return ['success' => false, 'errors' => ['end_date must be after start_date']];
        }

        $instructor = $this->userRepo->getById($data['instructor_id']);
        if (!$instructor) {
            return ['success' => false, 'errors' => ['Instructor not found']];
        }

        if (strtolower($instructor->getRole()) !== 'instructor') {
            return ['success' => false, 'errors' => ['The specified user is not an instructor']];
        }

        $dto    = CourseRequestDTO::fromArray($data);
        $entity = $this->mapper->toEntity($dto);
        $newId  = $this->courseRepo->create($entity);
        $course = $this->courseRepo->getById($newId);

        $enrolled       = $this->enrollmentRepo->getCountByCourseId($newId);
        $instructorName = $instructor->getFirstName() . ' ' . $instructor->getLastName();

        return [
            'success' => true,
            'data'    => $this->mapper->toDTO($course, $enrolled, $instructorName),
        ];
    }
    public function generateCourseInviteCode($id){
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }
        // Generate a unique 8-character alphanumeric code
        $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        while ($this->courseRepo->getByCode($code) !== null) {
            // If the generated code already exists, generate a new one
            $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        }
        $course->setCode($code);
        $isUpdate = ($this->courseRepo->update($course)) > 0 ? true : false;
        if (!$isUpdate) {
            return ['success' => false, 'errors' => ['Failed to generate invite code']];
        }
        return ['success' => true, 'data' => $course->getCode()];
    }
    public function getById(int $id): array
    {
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $enrolled       = $this->enrollmentRepo->getCountByCourseId($id);
        $instructor     = $this->userRepo->getById($course->getInstructorId());
        $instructorName = $instructor
            ? $instructor->getFirstName() . ' ' . $instructor->getLastName()
            : null;

        return [
            'success' => true,
            'data'    => $this->mapper->toDTO($course, $enrolled, $instructorName),
        ];
    }

    public function getAll(): array
    {
        $courses = $this->courseRepo->getAll();
        return ['success' => true, 'data' => $this->buildDTOList($courses)];
    }

    public function getByInstructor(int $instructorId): array
    {
        $instructor = $this->userRepo->getById($instructorId);
        if (!$instructor) {
            return ['success' => false, 'errors' => ['Instructor not found']];
        }

        $courses = $this->courseRepo->getByInstructorId($instructorId);
        return ['success' => true, 'data' => $this->buildDTOList($courses)];
    }

    public function update(int $id, array $data): array
    {
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $startDate = $data['start_date'] ?? $course->getStartDate();
        $endDate   = $data['end_date']   ?? $course->getEndDate();

        if (isset($data['start_date']) && !$this->validator->validateDate($data['start_date'])) {
            return ['success' => false, 'errors' => ['Invalid start_date format. Use Y-m-d.']];
        }

        if (isset($data['end_date']) && !$this->validator->validateDate($data['end_date'])) {
            return ['success' => false, 'errors' => ['Invalid end_date format. Use Y-m-d.']];
        }

        if (strtotime($endDate) <= strtotime($startDate)) {
            return ['success' => false, 'errors' => ['end_date must be after start_date']];
        }

        $dto = CourseRequestDTO::fromArray(array_merge([
            'name'          => $course->getName(),
            'code'          => $course->getCode(),
            'instructor_id' => $course->getInstructorId(),
            'start_date'    => $course->getStartDate(),
            'end_date'      => $course->getEndDate(),
            'description'   => $course->getDescription(),
            'capacity'      => $course->getCapacity(),
            'CourseImageId'  => $course->getCourseImageId(),
        ], $data));

        $this->mapper->updateEntity($course, $dto);
        $this->courseRepo->update($course);

        return $this->getById($id);
    }

    public function delete(int $id): array
    {
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $this->courseRepo->delete($id);
        return ['success' => true, 'message' => 'Course deleted successfully'];
    }

    public function search(array $filters): array
    {
        $courses = $this->courseRepo->getAll();

        if (!empty($filters['instructor_id'])) {
            $courses = array_filter($courses, fn($c) => $c->getInstructorId() == $filters['instructor_id']);
        }

        if (!empty($filters['keyword'])) {
            $kw = strtolower($filters['keyword']);
            $courses = array_filter($courses, function ($c) use ($kw) {
                return str_contains(strtolower($c->getName()), $kw)
                    || str_contains(strtolower($c->getCode() ?? ''), $kw);
            });
        }

        if (!empty($filters['available'])) {
            $courses = array_filter($courses, fn($c) => !$this->courseRepo->isFull($c->getId()));
        }

        return ['success' => true, 'data' => $this->buildDTOList(array_values($courses))];
    }

    private function buildDTOList(array $courses): array
    {
        return array_map(function ($course) {
            $enrolled   = $this->enrollmentRepo->getCountByCourseId($course->getId());
            $instructor = $this->userRepo->getById($course->getInstructorId());
            $name       = $instructor
                ? $instructor->getFirstName() . ' ' . $instructor->getLastName()
                : null;
            return $this->mapper->toDTO($course, $enrolled, $name);
        }, $courses);
    }
}
