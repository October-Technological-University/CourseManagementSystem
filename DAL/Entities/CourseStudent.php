<?php

class CourseStudent
{
    private $id;
    private $course_id;
    private $student_id;
    private $status;
    private $enrolled_at;

    public function __construct($course_id, $student_id, $status = 'active')
    {
        $this->course_id = $course_id;
        $this->student_id = $student_id;
        $this->status = $status;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getCourseId()
    {
        return $this->course_id;
    }
    public function getStudentId()
    {
        return $this->student_id;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getEnrolledAt()
    {
        return $this->enrolled_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setCourseId($course_id)
    {
        $this->course_id = $course_id;
    }
    public function setStudentId($student_id)
    {
        $this->student_id = $student_id;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function setEnrolledAt($enrolled_at)
    {
        $this->enrolled_at = $enrolled_at;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at
        ];
    }
}
?>