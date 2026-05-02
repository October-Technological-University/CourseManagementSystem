<?php

/**
 * CourseMapper
 * 
 * Transforms Course entities to/from DTOs
 * Handles computed fields like enrolled_count and instructor_name
 */
class CourseMapper
{
    /**
     * Convert Course entity to CourseResponseDTO
     * 
     * @param Course $course The course entity from database
     * @param int $enrolledCount Number of students currently enrolled
     * @param string|null $instructorName Name of the course instructor (first_name + last_name)
     * @param string|null $courseImageUrl URL to the course cover image
     * @return CourseResponseDTO The response DTO with computed fields
     */
    public function toDTO(Course $course, int $enrolledCount = 0, ?string $instructorName = null, ?string $courseImageUrl = null): CourseResponseDTO
    {
        return new CourseResponseDTO(
            $course->getId(),
            $course->getName(),
            $course->getCode(),
            $course->getInstructorId(),
            $course->getStartDate(),
            $course->getEndDate(),
            $course->getDescription(),
            $course->getCapacity(),
            $course->getCourseImageId(),
            $instructorName,
            $enrolledCount,
            $course->getCreatedAt(),
            $courseImageUrl
        );
    }

    /**
     * Convert array of Course entities to array of CourseResponseDTOs
     * 
     * @param array $courses Array of Course entities
     * @param array $context Optional context with 'enrolledCounts', 'instructorNames', and 'courseImageUrls' keyed by course id
     * @return array Array of CourseResponseDTO objects
     */
    public function toDTOList(array $courses, array $context = []): array
    {
        $enrolledCounts = $context['enrolledCounts'] ?? [];
        $instructorNames = $context['instructorNames'] ?? [];
        $courseImageUrls = $context['courseImageUrls'] ?? [];

        return array_map(function ($course) use ($enrolledCounts, $instructorNames, $courseImageUrls) {
            $courseId = $course->getId();
            return $this->toDTO(
                $course,
                $enrolledCounts[$courseId] ?? 0,
                $instructorNames[$courseId] ?? null,
                $courseImageUrls[$courseId] ?? null
            );
        }, $courses);
    }

    /**
     * Create Course entity from CourseRequestDTO (for new courses)
     * 
     * @param CourseRequestDTO $dto The request DTO with course creation data
     * @return Course A new Course entity (not yet saved to database)
     */
    public function toEntity(CourseRequestDTO $dto): Course
    {
        return new Course(
            $dto->name,
            $dto->code,
            $dto->instructor_id,
            $dto->start_date,
            $dto->end_date,
            $dto->description,
            $dto->capacity ?? 30,
            $dto->CourseImageId
        );
    }

    /**
     * Update existing Course entity with data from CourseRequestDTO
     * 
     * Does not modify: id, code, instructor_id, created_at (immutable fields)
     * 
     * @param Course $course The existing course entity to update
     * @param CourseRequestDTO $dto The request DTO with new data
     * @return void
     */
    public function updateEntity(Course $course, CourseRequestDTO $dto): void
    {
        // Update mutable fields only
        if ($dto->name !== null) {
            $course->setName($dto->name);
        }

        if ($dto->description !== null) {
            $course->setDescription($dto->description);
        }

        if ($dto->capacity !== null) {
            $course->setCapacity($dto->capacity);
        }

        if ($dto->start_date !== null) {
            $course->setStartDate($dto->start_date);
        }

        if ($dto->end_date !== null) {
            $course->setEndDate($dto->end_date);
        }

        if ($dto->CourseImageId !== null) {
            $course->setCourseImageId($dto->CourseImageId);
        }
    }
}
?>