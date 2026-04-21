<?php

/**
 * EnrollmentMapper
 * 
 * Transforms CourseStudent (enrollment) entities to/from DTOs
 * Handles joined data (student names, course names) for rich response objects
 */
class EnrollmentMapper
{
    private $userRepository;
    private $courseRepository;

    /**
     * Constructor with required dependencies
     * 
     * @param UserRepository $userRepository Repository for fetching user data
     * @param CourseRepository $courseRepository Repository for fetching course data
     */
    public function __construct(UserRepository $userRepository, CourseRepository $courseRepository)
    {
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
    }

    /**
     * Convert CourseStudent (enrollment) entity to EnrollmentResponseDTO
     * 
     * Includes joined data: student_name and course_name
     * 
     * @param CourseStudent $enrollment The enrollment entity from database
     * @return EnrollmentResponseDTO The response DTO with joined data
     */
    public function toDTO(CourseStudent $enrollment): EnrollmentResponseDTO
    {
        // Fetch joined data
        $student = $this->userRepository->getById($enrollment->getStudentId());
        $course = $this->courseRepository->getById($enrollment->getCourseId());

        $studentName = $student ?
            $student->getFirstName() . ' ' . $student->getLastName() :
            'Unknown Student';

        $courseName = $course ? $course->getName() : 'Unknown Course';

        return new EnrollmentResponseDTO(
            $enrollment->getId(),
            $enrollment->getCourseId(),
            $enrollment->getStudentId(),
            $enrollment->getStatus(),
            $enrollment->getEnrolledAt(),
            $studentName,
            $courseName
        );
    }

    /**
     * Create CourseStudent entity from EnrollmentRequestDTO (for new enrollments)
     * 
     * @param EnrollmentRequestDTO $dto The request DTO with enrollment data
     * @return CourseStudent A new CourseStudent entity (not yet saved to database)
     */
    public function toEntity(EnrollmentRequestDTO $dto): CourseStudent
    {
        return new CourseStudent(
            $dto->course_id,
            $dto->student_id,
            'active' // Default status
        );
    }

    /**
     * Convert array of enrollments to array of Course DTOs
     * 
     * Used for "my courses" view - shows all courses a student is enrolled in
     * 
     * @param array $enrollments Array of CourseStudent entities
     * @return array Array of CourseResponseDTO objects
     */
    public function toCourseList(array $enrollments): array
    {
        $courseMapper = new CourseMapper();
        $courses = [];

        foreach ($enrollments as $enrollment) {
            $course = $this->courseRepository->getById($enrollment->getCourseId());
            if ($course) {
                $courses[] = $courseMapper->toDTO($course);
            }
        }

        return $courses;
    }

    /**
     * Convert array of enrollments to array of User DTOs
     * 
     * Used for "course students" view - shows all students enrolled in a course
     * 
     * @param array $enrollments Array of CourseStudent entities
     * @return array Array of UserResponseDTO objects
     */
    public function toStudentList(array $enrollments): array
    {
        $userMapper = new UserMapper();
        $students = [];

        foreach ($enrollments as $enrollment) {
            $student = $this->userRepository->getById($enrollment->getStudentId());
            if ($student) {
                $students[] = $userMapper->toDTO($student);
            }
        }

        return $students;
    }
}
?>