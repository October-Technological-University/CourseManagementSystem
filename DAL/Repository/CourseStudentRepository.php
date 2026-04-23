<?php
require_once BASE_PATH . 'DAL/Repository/BaseRepository.php';
require_once BASE_PATH . 'DAL/Entities/CourseStudent.php';

class CourseStudentRepository extends BaseRepository
{
    protected $table = 'course_students';

    /**
     * Enroll a student in a course
     */
    public function enroll($course_id, $student_id, $status = 'active')
    {
        $sql = "INSERT INTO `{$this->table}` (course_id, student_id, status)
                VALUES (?, ?, ?)";

        $stmt = $this->executePreparedStatement($sql, 'iis', [
            $course_id,
            $student_id,
            $status
        ]);

        $lastInsertId = $this->getLastInsertId();
        $stmt->close();
        return $lastInsertId;
    }

    /**
     * Get enrollment by ID
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

        return $this->mapRowToEnrollment($row);
    }

    /**
     * Get all enrollments
     */
    public function getAll()
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY enrolled_at DESC";
        $result = $this->executeQuery($sql);
        $enrollments = [];

        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        return $enrollments;
    }

    /**
     * Get enrollments by course ID
     */
    public function getByCourseId($course_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE course_id = ? ORDER BY enrolled_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$course_id]);
        $result = $stmt->get_result();
        $enrollments = [];

        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        $stmt->close();
        return $enrollments;
    }

    /**
     * Get enrollments by student ID
     */
    public function getByStudentId($student_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE student_id = ? ORDER BY enrolled_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$student_id]);
        $result = $stmt->get_result();
        $enrollments = [];

        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        $stmt->close();
        return $enrollments;
    }

    /**
     * Get active enrollments by course ID
     */
    public function getActiveByCourseId($course_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE course_id = ? AND status = 'active' ORDER BY enrolled_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$course_id]);
        $result = $stmt->get_result();
        $enrollments = [];

        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        $stmt->close();
        return $enrollments;
    }

    /**
     * Get active enrollments by student ID
     */
    public function getActiveByStudentId($student_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE student_id = ? AND status = 'active' ORDER BY enrolled_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$student_id]);
        $result = $stmt->get_result();
        $enrollments = [];

        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        $stmt->close();
        return $enrollments;
    }

    /**
     * Get enrollment by course ID and student ID
     */
    public function getByCourseAndStudent($course_id, $student_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE course_id = ? AND student_id = ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$course_id, $student_id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToEnrollment($row);
    }

    /**
     * Check if student is enrolled in course
     */
    public function isEnrolled($course_id, $student_id)
    {
        $enrollment = $this->getByCourseAndStudent($course_id, $student_id);
        return $enrollment !== null && $enrollment->getStatus() === 'active';
    }

    /**
     * Update enrollment status
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE `{$this->table}` SET status = ? WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'si', [$status, $id]);
        $affectedRows = $this->getAffectedRows();
        $stmt->close();
        return $affectedRows;
    }

    /**
     * Update enrollment status by course and student
     */
    public function updateStatusByCourseAndStudent($course_id, $student_id, $status)
    {
        $sql = "UPDATE `{$this->table}` SET status = ? WHERE course_id = ? AND student_id = ?";
        $stmt = $this->executePreparedStatement($sql, 'sii', [$status, $course_id, $student_id]);
        $affectedRows = $this->getAffectedRows();
        $stmt->close();
        return $affectedRows;
    }

    /**
     * Delete enrollment
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
     * Delete enrollment by course and student
     */
    public function deleteByCourseAndStudent($course_id, $student_id)
    {
        $sql = "DELETE FROM `{$this->table}` WHERE course_id = ? AND student_id = ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$course_id, $student_id]);
        $affectedRows = $this->getAffectedRows();
        $stmt->close();
        return $affectedRows;
    }

    /**
     * Get enrollment count for course
     */
    public function getCountByCourseId($course_id)
    {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE course_id = ? AND status = 'active'";
        $stmt = $this->executePreparedStatement($sql, 'i', [$course_id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'];
    }

    /**
     * Map database row to CourseStudent entity
     */
    private function mapRowToEnrollment($row)
    {
        $enrollment = new CourseStudent(
            $row['course_id'],
            $row['student_id'],
            $row['status']
        );

        $enrollment->setId($row['id']);
        $enrollment->setEnrolledAt($row['enrolled_at']);

        return $enrollment;
    }
}
?>