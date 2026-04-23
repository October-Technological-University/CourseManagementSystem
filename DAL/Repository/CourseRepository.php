<?php
require_once BASE_PATH . 'DAL/Repository/BaseRepository.php';
require_once BASE_PATH . 'DAL/Entities/Course.php';

class CourseRepository extends BaseRepository
{
    protected $table = 'courses';

    /**
     * Create a new course
     */
    public function create(Course $course)
    {
        $sql = "INSERT INTO `{$this->table}` (name, code, description, instructor_id, capacity, start_date, end_date, CourseImageId)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->executePreparedStatement($sql, 'sssiissi', [
            $course->getName(),
            $course->getCode(),
            $course->getDescription(),
            $course->getInstructorId(),
            $course->getCapacity(),
            $course->getStartDate(),
            $course->getEndDate(),
            $course->getCourseImageId()
        ]);

        $stmt->close();
        return $this->getLastInsertId();
    }

    /**
     * Get course by ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToCourse($row);
    }

    /**
     * Get all courses
     */
    public function getAll()
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC";
        $result = $this->executeQuery($sql);
        $courses = [];

        while ($row = $result->fetch_assoc()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    /**
     * Get courses by instructor ID
     */
    public function getByInstructorId($instructor_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE instructor_id = ? ORDER BY created_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$instructor_id]);
        $result = $stmt->get_result();
        $courses = [];

        while ($row = $result->fetch_assoc()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        $stmt->close();
        return $courses;
    }

    /**
     * Get course by code
     */
    public function getByCode($code)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE code = ?";
        $stmt = $this->executePreparedStatement($sql, 's', [$code]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToCourse($row);
    }

    /**
     * Update course
     */
    public function update(Course $course)
    {
        $sql = "UPDATE `{$this->table}` 
                SET name = ?, code = ?, description = ?, instructor_id = ?, capacity = ?, 
                    start_date = ?, end_date = ?, CourseImageId = ?
                WHERE id = ?";

        $stmt = $this->executePreparedStatement($sql, 'sssiissii', [
            $course->getName(),
            $course->getCode(),
            $course->getDescription(),
            $course->getInstructorId(),
            $course->getCapacity(),
            $course->getStartDate(),
            $course->getEndDate(),
            $course->getCourseImageId(),
            $course->getId()
        ]);

        $affectedRows = $this->getAffectedRows();
        $stmt->close();
        return $affectedRows;
    }

    /**
     * Update course image ID
     */
    public function updateCourseImage($course_id, $image_id)
    {
        $sql = "UPDATE `{$this->table}` SET CourseImageId = ? WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$image_id, $course_id]);
        $affectedRows = $this->getAffectedRows();
        $stmt->close();
        return $affectedRows;
    }

    /**
     * Delete course
     */
    public function delete($id)
    {
        $sql = "DELETE FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$id]);
        $affectedRows = $this->getAffectedRows();
        $stmt->close();
        return $affectedRows;
    }

    /**
     * Get total course count
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}`";
        $result = $this->executeQuery($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    /**
     * Get courses with pagination
     */
    public function getPaginated($page = 1, $pageSize = 10)
    {
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT ?, ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$offset, $pageSize]);
        $result = $stmt->get_result();
        $courses = [];

        while ($row = $result->fetch_assoc()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        $stmt->close();
        return $courses;
    }

    /**
     * Map database row to Course entity
     */
    private function mapRowToCourse($row)
    {
        $course = new Course(
            $row['name'],
            $row['code'],
            $row['instructor_id'],
            $row['start_date'],
            $row['end_date'],
            $row['description'],
            $row['capacity'],
            $row['CourseImageId']
        );

        $course->setId($row['id']);
        $course->setCreatedAt($row['created_at']);

        return $course;
    }
}
?>