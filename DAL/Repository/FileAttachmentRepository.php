<?php
require_once BASE_PATH . 'DAL/Repository/BaseRepository.php';
require_once BASE_PATH . 'DAL/Entities/FileAttachment.php';

class FileAttachmentRepository extends BaseRepository
{
    protected $table = 'file_attachments';

    /**
     * Create a new file attachment
     */
    public function create(FileAttachment $file)
    {
        $sql = "INSERT INTO `{$this->table}` (filename, stored_name, file_path, mime_type, file_size, course_id, subtype, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->executePreparedStatement($sql, 'ssssiisi', [
            $file->getFilename(),
            $file->getStoredName(),
            $file->getFilePath(),
            $file->getMimeType(),
            $file->getFileSize(),
            $file->getCourseId(),
            $file->getSubtype(),
            $file->getUploadedBy()
        ]);

        $lastInsertId = $this->getLastInsertId();
        $stmt->close();
        return $lastInsertId;
    }

    /**
     * Get file attachment by ID
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

        return $this->mapRowToFileAttachment($row);
    }

    /**
     * Get file attachment by stored name
     */
    public function getByStoredName($stored_name)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE stored_name = ?";
        $stmt = $this->executePreparedStatement($sql, 's', [$stored_name]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToFileAttachment($row);
    }

    /**
     * Get all file attachments
     */
    public function getAll()
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY uploaded_at DESC";
        $result = $this->executeQuery($sql);
        $files = [];

        while ($row = $result->fetch_assoc()) {
            $files[] = $this->mapRowToFileAttachment($row);
        }

        return $files;
    }

    /**
     * Get profile pictures (course_id IS NULL)
     */
    public function getProfilePictures()
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE course_id IS NULL ORDER BY uploaded_at DESC";
        $result = $this->executeQuery($sql);
        $files = [];

        while ($row = $result->fetch_assoc()) {
            $files[] = $this->mapRowToFileAttachment($row);
        }

        return $files;
    }

    /**
     * Get profile picture by user ID
     */
    public function getProfilePictureByUserId($user_id)
    {
        $sql = "SELECT fa.* FROM `{$this->table}` fa 
                INNER JOIN users u ON fa.id = u.profile_picture_id 
                WHERE u.id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$user_id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToFileAttachment($row);
    }

    /**
     * Get files by course ID
     */
    public function getByCourseId($course_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE course_id = ? ORDER BY uploaded_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$course_id]);
        $result = $stmt->get_result();
        $files = [];

        while ($row = $result->fetch_assoc()) {
            $files[] = $this->mapRowToFileAttachment($row);
        }

        $stmt->close();
        return $files;
    }

    /**
     * Get files by course ID and subtype
     */
    public function getByCourseIdAndSubtype($course_id, $subtype)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE course_id = ? AND subtype = ? ORDER BY uploaded_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'is', [$course_id, $subtype]);
        $result = $stmt->get_result();
        $files = [];

        while ($row = $result->fetch_assoc()) {
            $files[] = $this->mapRowToFileAttachment($row);
        }

        $stmt->close();
        return $files;
    }

    /**
     * Get assignments for a course
     */
    public function getAssignmentsByCourseId($course_id)
    {
        return $this->getByCourseIdAndSubtype($course_id, 'assignment');
    }

    /**
     * Get resources for a course
     */
    public function getResourcesByCourseId($course_id)
    {
        return $this->getByCourseIdAndSubtype($course_id, 'resource');
    }

    /**
     * Get files uploaded by user
     */
    public function getByUploadedBy($user_id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE uploaded_by = ? ORDER BY uploaded_at DESC";
        $stmt = $this->executePreparedStatement($sql, 'i', [$user_id]);
        $result = $stmt->get_result();
        $files = [];

        while ($row = $result->fetch_assoc()) {
            $files[] = $this->mapRowToFileAttachment($row);
        }

        $stmt->close();
        return $files;
    }

    /**
     * Update file attachment
     */
    public function update(FileAttachment $file)
    {
        $sql = "UPDATE `{$this->table}` 
                SET filename = ?, stored_name = ?, file_path = ?, mime_type = ?, file_size = ?, 
                    course_id = ?, subtype = ?, uploaded_by = ?
                WHERE id = ?";

        $stmt = $this->executePreparedStatement($sql, 'ssssiisii', [
            $file->getFilename(),
            $file->getStoredName(),
            $file->getFilePath(),
            $file->getMimeType(),
            $file->getFileSize(),
            $file->getCourseId(),
            $file->getSubtype(),
            $file->getUploadedBy(),
            $file->getId()
        ]);

        $effectedRows = $this->getAffectedRows();
        $stmt->close();
        return $effectedRows;
    }

    /**
     * Delete file attachment
     */
    public function delete($id)
    {
        $sql = "DELETE FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$id]);
        $effectedRows = $this->getAffectedRows();
        $stmt->close();
        return $effectedRows;
    }

    /**
     * Delete by stored name
     */
    public function deleteByStoredName($stored_name)
    {
        $sql = "DELETE FROM `{$this->table}` WHERE stored_name = ?";
        $stmt = $this->executePreparedStatement($sql, 's', [$stored_name]);
        $effectedRows = $this->getAffectedRows();
        $stmt->close();
        return $effectedRows;
    }

    /**
     * Get total file count
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}`";
        $result = $this->executeQuery($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    /**
     * Get file count by course
     */
    public function getCountByCourseId($course_id)
    {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE course_id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$course_id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'];
    }

    /**
     * Get files with pagination
     */
    public function getPaginated($page = 1, $pageSize = 10)
    {
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT * FROM `{$this->table}` ORDER BY uploaded_at DESC LIMIT ?, ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$offset, $pageSize]);
        $result = $stmt->get_result();
        $files = [];

        while ($row = $result->fetch_assoc()) {
            $files[] = $this->mapRowToFileAttachment($row);
        }

        $stmt->close();
        return $files;
    }

    /**
     * Map database row to FileAttachment entity
     */
    private function mapRowToFileAttachment($row)
    {
        $file = new FileAttachment(
            $row['filename'],
            $row['stored_name'],
            $row['file_path'],
            $row['mime_type'],
            $row['file_size'],
            $row['uploaded_by'],
            $row['course_id'],
            $row['subtype']
        );

        $file->setId($row['id']);
        $file->setUploadedAt($row['uploaded_at']);

        return $file;
    }
}
?>