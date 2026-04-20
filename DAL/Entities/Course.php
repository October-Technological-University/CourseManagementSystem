<?php

class Course
{
    private $id;
    private $name;
    private $code;
    private $description;
    private $instructor_id;
    private $capacity;
    private $start_date;
    private $end_date;
    private $CourseImageId;
    private $created_at;

    public function __construct($name, $code, $instructor_id, $start_date, $end_date, $description = null, $capacity = 30, $CourseImageId = null)
    {
        $this->name = $name;
        $this->code = $code;
        $this->description = $description;
        $this->instructor_id = $instructor_id;
        $this->capacity = $capacity;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->CourseImageId = $CourseImageId;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getCode()
    {
        return $this->code;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getInstructorId()
    {
        return $this->instructor_id;
    }
    public function getCapacity()
    {
        return $this->capacity;
    }
    public function getStartDate()
    {
        return $this->start_date;
    }
    public function getEndDate()
    {
        return $this->end_date;
    }
    public function getCourseImageId()
    {
        return $this->CourseImageId;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setCode($code)
    {
        $this->code = $code;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function setInstructorId($instructor_id)
    {
        $this->instructor_id = $instructor_id;
    }
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }
    public function setCourseImageId($CourseImageId)
    {
        $this->CourseImageId = $CourseImageId;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'instructor_id' => $this->instructor_id,
            'capacity' => $this->capacity,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'CourseImageId' => $this->CourseImageId,
            'created_at' => $this->created_at
        ];
    }
}
?>