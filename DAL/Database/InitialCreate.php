<?php
require_once BASE_PATH . 'DAL/Database/DBContext.php';

class InitialCreate
{
    private $dbContext;
    private $conn;

    public function __construct()
    {
        $this->dbContext = DBContext::getInstance();
        $this->conn = $this->dbContext->getConnection();
    }

    /**
     * Create all tables for the Course Management System
     */
    public function createTables()
    {
        try {
            // Create database if not exists
            $env = parse_ini_file(BASE_PATH . '/config/.env');
            $dbName = $env['DATABASE_NAME'] ?: 'course_management_system';
            $this->conn->query("CREATE DATABASE IF NOT EXISTS `$dbName`");
            $this->conn->select_db($dbName);

            // Create users table first (no dependencies)
            $this->createUsersTable();

            // Create courses table (depends on users)
            $this->createCoursesTable();

            // Create course_students table (depends on courses and users)
            $this->createCourseStudentsTable();

            // Create file_attachments table (depends on courses and users)
            $this->createFileAttachmentsTable();

            // Add foreign key from users to file_attachments (profile picture)
            $this->addProfilePictureForeignKey();

            // Add foreign key from courses to file_attachments (course image)
            $this->addCourseImageForeignKey();

            // echo "✓ All tables created successfully!\n";
            return true;
        } catch (Exception $e) {
            // echo "✗ Error creating tables: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Create users table
     */
    private function createUsersTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `email` VARCHAR(255) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `first_name` VARCHAR(100) NOT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `role` ENUM('admin', 'teacher', 'student') NOT NULL,
            `profile_picture_id` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_email` (`email`),
            INDEX `idx_role` (`role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating users table: " . $this->conn->error);
        }
        // echo "✓ Created users table\n";
    }

    /**
     * Create courses table
     */
    private function createCoursesTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `courses` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(50) UNIQUE NOT NULL,
            `description` TEXT NULL,
            `teacher_id` INT NOT NULL,
            `capacity` INT NOT NULL DEFAULT 30,
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `CourseImageId` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_code` (`code`),
            INDEX `idx_teacher_id` (`teacher_id`),
            INDEX `idx_course_image_id` (`CourseImageId`),
            CONSTRAINT `fk_courses_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating courses table: " . $this->conn->error);
        }
        // echo "✓ Created courses table\n";
    }

    /**
     * Create course_students table
     */
    private function createCourseStudentsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `course_students` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `course_id` INT NOT NULL,
            `student_id` INT NOT NULL,
            `status` ENUM('active', 'completed', 'dropped') DEFAULT 'active',
            `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_course_student` (`course_id`, `student_id`),
            INDEX `idx_student_id` (`student_id`),
            CONSTRAINT `fk_course_students_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_course_students_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating course_students table: " . $this->conn->error);
        }
        // echo "✓ Created course_students table\n";
    }

    /**
     * Create file_attachments table
     * 
     * Logic:
     * - course_id IS NULL → This is a user's profile picture
     * - course_id IS NOT NULL → This is a file attached to the specified course
     * - subtype: 'assignment' or 'resource' for course files; NULL for profile pictures
     */
    private function createFileAttachmentsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `file_attachments` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `filename` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) UNIQUE NOT NULL,
            `file_path` VARCHAR(500) NOT NULL,
            `mime_type` VARCHAR(100) NOT NULL,
            `file_size` INT NOT NULL,
            `course_id` INT NULL,
            `subtype` ENUM('assignment', 'resource') NULL,
            `uploaded_by` INT NULL,
            `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_stored_name` (`stored_name`),
            INDEX `idx_course_id` (`course_id`),
            INDEX `idx_uploaded_by` (`uploaded_by`),
            INDEX `idx_subtype` (`subtype`),
            CONSTRAINT `fk_file_attachments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_file_attachments_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating file_attachments table: " . $this->conn->error);
        }
        // echo "✓ Created file_attachments table\n";
    }

    /**
     * Add foreign key from users.profile_picture_id to file_attachments.id
     * This must be done after both tables are created
     */
    private function addProfilePictureForeignKey()
    {
        // Check if constraint already exists
        $sql = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'users' 
                AND COLUMN_NAME = 'profile_picture_id' 
                AND CONSTRAINT_NAME LIKE 'fk_%'";

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows === 0) {
            $sql = "ALTER TABLE `users` ADD CONSTRAINT `fk_users_profile_picture` 
                    FOREIGN KEY (`profile_picture_id`) REFERENCES `file_attachments` (`id`) ON DELETE SET NULL";

            if (!$this->conn->query($sql)) {
                throw new Exception("Error adding profile picture foreign key: " . $this->conn->error);
            }
            // echo "✓ Added profile picture foreign key\n";
        } else {
            // echo "✓ Profile picture foreign key already exists\n";
        }
    }

    /**
     * Add foreign key from courses.CourseImageId to file_attachments.id
     * This must be done after both tables are created
     */
    private function addCourseImageForeignKey()
    {
        // Check if constraint already exists
        $sql = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'courses' 
                AND COLUMN_NAME = 'CourseImageId' 
                AND CONSTRAINT_NAME LIKE 'fk_%'";

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows === 0) {
            $sql = "ALTER TABLE `courses` ADD CONSTRAINT `fk_courses_course_image` 
                    FOREIGN KEY (`CourseImageId`) REFERENCES `file_attachments` (`id`) ON DELETE SET NULL";

            if (!$this->conn->query($sql)) {
                throw new Exception("Error adding course image foreign key: " . $this->conn->error);
            }
            // echo "✓ Added course image foreign key\n";
        } else {
            // echo "✓ Course image foreign key already exists\n";
        }
    }

    /**
     * Drop all tables (for testing/reset purposes)
     */
    public function dropAllTables()
    {
        try {
            // Drop in reverse order of dependencies
            $tables = ['course_students', 'file_attachments', 'courses', 'users'];

            foreach ($tables as $table) {
                $this->conn->query("DROP TABLE IF NOT EXISTS `$table`");
                // echo "✓ Dropped table: $table\n";
            }

            return true;
        } catch (Exception $e) {
            // echo "✗ Error dropping tables: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Reset all tables (drop and recreate)
     */
    public function resetTables()
    {
        $this->dropAllTables();
        return $this->createTables();
    }
}
?>