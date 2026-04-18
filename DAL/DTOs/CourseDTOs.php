<?php

/**
 * DTO for creating/updating a course
 */
class CourseRequestDTO
{
    public $name;
    public $code;
    public $description;
    public $teacher_id;
    public $capacity;
    public $start_date;
    public $end_date;
    public $CourseImageId;

    public function __construct($name, $code, $teacher_id, $start_date, $end_date, $description = null, $capacity = 30, $CourseImageId = null)
    {
        $this->name = $name;
        $this->code = $code;
        $this->description = $description;
        $this->teacher_id = $teacher_id;
        $this->capacity = $capacity;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->CourseImageId = $CourseImageId;
    }

    public static function fromArray($data)
    {
        return new self(
            $data['name'] ?? null,
            $data['code'] ?? null,
            $data['teacher_id'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['description'] ?? null,
            $data['capacity'] ?? 30,
            $data['CourseImageId'] ?? null
        );
    }
}

/**
 * DTO for course response (includes computed/additional data)
 */
class CourseResponseDTO
{
    public $id;
    public $name;
    public $code;
    public $description;
    public $teacher_id;
    public $teacher_name;
    public $capacity;
    public $enrolled_count;
    public $start_date;
    public $end_date;
    public $CourseImageId;
    public $created_at;

    public function __construct($id, $name, $code, $teacher_id, $start_date, $end_date, $description = null, $capacity = 30, $CourseImageId = null, $teacher_name = null, $enrolled_count = 0, $created_at = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->description = $description;
        $this->teacher_id = $teacher_id;
        $this->teacher_name = $teacher_name;
        $this->capacity = $capacity;
        $this->enrolled_count = $enrolled_count;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->CourseImageId = $CourseImageId;
        $this->created_at = $created_at;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'teacher_id' => $this->teacher_id,
            'teacher_name' => $this->teacher_name,
            'capacity' => $this->capacity,
            'enrolled_count' => $this->enrolled_count,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'CourseImageId' => $this->CourseImageId,
            'created_at' => $this->created_at
        ];
    }
}

/**
 * DTO for course enrollment request
 */
class EnrollmentRequestDTO
{
    public $course_id;
    public $student_id;

    public function __construct($course_id, $student_id)
    {
        $this->course_id = $course_id;
        $this->student_id = $student_id;
    }

    public static function fromArray($data)
    {
        return new self(
            $data['course_id'] ?? null,
            $data['student_id'] ?? null
        );
    }
}

/**
 * DTO for course enrollment response
 */
class EnrollmentResponseDTO
{
    public $id;
    public $course_id;
    public $student_id;
    public $student_name;
    public $course_name;
    public $status;
    public $enrolled_at;

    public function __construct($id, $course_id, $student_id, $status, $enrolled_at, $student_name = null, $course_name = null)
    {
        $this->id = $id;
        $this->course_id = $course_id;
        $this->student_id = $student_id;
        $this->student_name = $student_name;
        $this->course_name = $course_name;
        $this->status = $status;
        $this->enrolled_at = $enrolled_at;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'student_id' => $this->student_id,
            'student_name' => $this->student_name,
            'course_name' => $this->course_name,
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at
        ];
    }
}
?>