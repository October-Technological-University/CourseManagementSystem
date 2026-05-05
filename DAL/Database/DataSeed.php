<?php

/**
 * DataSeed - Database Seeding Script
 *
 * Can be run standalone from CLI or included for programmatic use.
 * Supports dependency injection for unit testing.
 *
 * Usage:
 *   CLI:     php Database/DataSeed.php [options]
 *   Include: $seeder = new DatabaseSeeding($repos...); $seeder->seed();
 */

// Autoloader for when running standalone
// Delay loading until BASE_PATH is set (for CLI execution)
function dataSeedAutoload(): void {
    if (!class_exists('User')) {
        require_once __DIR__ . '/../Entities/User.php';
    }
    if (!class_exists('Course')) {
        require_once __DIR__ . '/../Entities/Course.php';
    }
    if (!class_exists('UserRepository')) {
        require_once __DIR__ . '/../Repository/UserRepository.php';
    }
    if (!class_exists('CourseRepository')) {
        require_once __DIR__ . '/../Repository/CourseRepository.php';
    }
    if (!class_exists('CourseStudentRepository')) {
        require_once __DIR__ . '/../Repository/CourseStudentRepository.php';
    }
    if (!class_exists('FileAttachmentRepository')) {
        require_once __DIR__ . '/../Repository/FileAttachmentRepository.php';
    }
    if (!class_exists('DBContext')) {
        require_once __DIR__ . '/DBContext.php';
    }
    if (!class_exists('FileStorageHelper')) {
        require_once __DIR__ . '/../../utils/FileStorageHelper.php';
    }
}

/**
 * Interface for database seeding operations
 * Enables mocking for unit tests
 */
interface ISeeder
{
    public function seed(): bool;
    public function seedUsers(): array;
    public function seedCourses(): array;
    public function seedEnrollments(): array;
    public function seedFiles(): array;
    public function clearAll(): bool;
}

/**
 * Seeding result container for test assertions
 */
class SeedingResult
{
    public bool $success = false;
    public array $createdUsers = [];
    public array $createdCourses = [];
    public array $createdEnrollments = [];
    public array $createdFiles = [];
    public array $errors = [];
    public string $message = '';

    public function addError(string $context, string $message): void
    {
        $this->errors[] = [
            'context' => $context,
            'message' => $message,
            'time' => date('Y-m-d H:i:s')
        ];
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'users_count' => count($this->createdUsers),
            'courses_count' => count($this->createdCourses),
            'enrollments_count' => count($this->createdEnrollments),
            'files_count' => count($this->createdFiles),
            'errors' => $this->errors
        ];
    }
}

/**
 * Configuration for seeding operations
 * Allows test customization without code changes
 */
class SeedingConfig
{
    public bool $dryRun = false;
    public bool $verbose = false;
    public bool $clearFirst = false;
    public int $instructorCount = 3;
    public int $studentCount = 15;
    public int $courseCount = 3;
    public int $studentsPerCourse = 5;
    public int $materialsPerCourse = 5;
    public string $defaultPassword = 'password123';
    public array $customUsers = [];
    public array $customCourses = [];

    public static function forTesting(): self
    {
        $config = new self();
        $config->dryRun = true;
        $config->instructorCount = 2;
        $config->studentCount = 5;
        $config->courseCount = 2;
        return $config;
    }

    public static function fromCliArgs(array $args): self
    {
        $config = new self();

        foreach ($args as $arg) {
            switch ($arg) {
                case '--dry-run':
                case '-d':
                    $config->dryRun = true;
                    break;
                case '--verbose':
                case '-v':
                    $config->verbose = true;
                    break;
                case '--clear':
                case '-c':
                    $config->clearFirst = true;
                    break;
            }
        }

        return $config;
    }
}

/**
 * Main database seeding class
 * Implements ISeeder for testability
 */
class DatabaseSeeding implements ISeeder
{
    /** @var UserRepository|object|null */
    private $userRepository;

    /** @var CourseRepository|object|null */
    private $courseRepository;

    /** @var CourseStudentRepository|object|null */
    private $courseStudentRepository;

    /** @var FileAttachmentRepository|object|null */
    private $fileAttachmentRepository;

    private SeedingConfig $config;
    private SeedingResult $result;

    /**
     * Constructor accepts nullable repositories for testing
     * When null, repositories are auto-created for production use
     *
     * @param UserRepository|object|null $userRepository
     * @param CourseRepository|object|null $courseRepository
     * @param CourseStudentRepository|object|null $courseStudentRepository
     * @param FileAttachmentRepository|object|null $fileAttachmentRepository
     */
    public function __construct(
        $userRepository = null,
        $courseRepository = null,
        $courseStudentRepository = null,
        $fileAttachmentRepository = null,
        ?SeedingConfig $config = null
    ) {
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
        $this->courseStudentRepository = $courseStudentRepository;
        $this->fileAttachmentRepository = $fileAttachmentRepository;
        $this->config = $config ?? new SeedingConfig();
        $this->result = new SeedingResult();

        // Auto-create repositories if not provided (production mode)
        if (!$this->config->dryRun) {
            $this->initializeRepositories();
        }
    }

    /**
     * Initialize repositories from DBContext
     */
    private function initializeRepositories(): void
    {
        try {
            // Ensure autoloading is done
            dataSeedAutoload();

            if ($this->userRepository === null) {
                $this->userRepository = new UserRepository();
            }
            if ($this->courseRepository === null) {
                $this->courseRepository = new CourseRepository();
            }
            if ($this->courseStudentRepository === null) {
                $this->courseStudentRepository = new CourseStudentRepository();
            }
            if ($this->fileAttachmentRepository === null) {
                $this->fileAttachmentRepository = new FileAttachmentRepository();
            }
        } catch (Exception $e) {
            $this->log("Failed to initialize repositories: " . $e->getMessage(), true);
            throw $e;
        }
    }

    /**
     * Run complete seeding operation
     */
    public function seed(): bool
    {
        $this->log("Starting database seeding...");

        try {
            if ($this->config->clearFirst) {
                $this->clearAll();
            }

            $this->beginTransaction();

            $this->seedUsers();
            $this->seedCourses();
            $this->seedEnrollments();
            $this->seedFiles();

            if (!$this->config->dryRun) {
                $this->commit();
            } else {
                $this->rollback();
                $this->log("Dry run completed - changes rolled back");
            }

            $this->result->success = !$this->result->hasErrors();
            $this->result->message = $this->result->success
                ? "Seeding completed successfully"
                : "Seeding completed with errors";

            $this->log($this->result->message);

            return $this->result->success;

        } catch (Exception $e) {
            $this->rollback();
            $this->result->addError('seed', $e->getMessage());
            $this->result->message = "Seeding failed: " . $e->getMessage();
            $this->log($this->result->message, true);
            return false;
        }
    }

    /**
     * Seed users table
     */
    public function seedUsers(): array
    {
        $this->log("Seeding users...");
        $users = [];

        // Create sample instructors
        for ($i = 1; $i <= $this->config->instructorCount; $i++) {
            $instructorData = [
                'email' => "instructor{$i}@example.com",
                'password' => $this->config->defaultPassword,
                'first_name' => "Instructor{$i}",
                'last_name' => 'Expert',
                'role' => 'Instructor'
            ];
            $id = $this->createUser($instructorData);
            if ($id > 0) {
                $users[] = $id;
                $this->generateProfilePicture($id, "I{$i}");
            }
        }

        // Create sample students
        for ($i = 1; $i <= $this->config->studentCount; $i++) {
            $studentData = [
                'email' => "student{$i}@example.com",
                'password' => $this->config->defaultPassword,
                'first_name' => "Student{$i}",
                'last_name' => 'Learner',
                'role' => 'student'
            ];
            $id = $this->createUser($studentData);
            if ($id > 0) {
                $users[] = $id;
                $this->generateProfilePicture($id, "S{$i}");
            }
        }

        $this->result->createdUsers = array_filter($users);
        $this->log("Created " . count($this->result->createdUsers) . " users");

        return $this->result->createdUsers;
    }

    /**
     * Seed courses table
     */
    public function seedCourses(): array
    {
        $this->log("Seeding courses...");
        $courses = [];

        // Get instructor IDs for course assignment
        $instructorIds = $this->getInstructorIds();
        if (empty($instructorIds)) {
            $this->result->addError('courses', 'No instructors available to assign courses');
            return $courses;
        }

        $sampleCourses = [
            ['name' => 'Introduction to Programming', 'code' => 'CS101', 'capacity' => 30],
            ['name' => 'Web Development', 'code' => 'CS201', 'capacity' => 25],
            ['name' => 'Database Systems', 'code' => 'CS301', 'capacity' => 20],
            ['name' => 'Software Engineering', 'code' => 'CS401', 'capacity' => 15],
            ['name' => 'Data Structures', 'code' => 'CS102', 'capacity' => 35],
        ];

        $count = max($this->config->courseCount, 3);
        for ($i = 0; $i < $count; $i++) {
            $courseData = $sampleCourses[$i % count($sampleCourses)];
            if ($i >= count($sampleCourses)) {
                $courseData['code'] .= "_" . ($i + 1);
            }
            
            $courseData['instructor_id'] = $instructorIds[$i % count($instructorIds)];
            $courseData['description'] = "This is a comprehensive course covering {$courseData['name']}.";
            $courseData['start_date'] = date('Y-m-d', strtotime('+1 week'));
            $courseData['end_date'] = date('Y-m-d', strtotime('+4 months'));

            $id = $this->createCourse($courseData);
            if ($id > 0) {
                $courses[] = $id;
                $this->generateCourseCover($id, $courseData['code']);
            }
        }

        $this->result->createdCourses = array_filter($courses);
        $this->log("Created " . count($this->result->createdCourses) . " courses");

        return $this->result->createdCourses;
    }

    /**
     * Seed course enrollments
     */
    public function seedEnrollments(): array
    {
        $this->log("Seeding enrollments...");
        $enrollments = [];

        $studentIds = $this->getStudentIds();
        $courseIds = $this->getCourseIds();

        if (empty($studentIds) || empty($courseIds)) {
            $this->result->addError('enrollments', 'Missing students or courses for enrollments');
            return $enrollments;
        }

        foreach ($courseIds as $courseId) {
            // Shuffle students to get unique random selection for each course
            shuffle($studentIds);
            $studentsToEnroll = array_slice($studentIds, 0, $this->config->studentsPerCourse);
            
            foreach ($studentsToEnroll as $studentId) {
                $enrollments[] = $this->createEnrollment([
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'status' => 'active'
                ]);
            }
        }

        $this->result->createdEnrollments = array_filter($enrollments);
        $this->log("Created " . count($this->result->createdEnrollments) . " enrollments");

        return $this->result->createdEnrollments;
    }

    /**
     * Seed file attachments (Resources)
     */
    public function seedFiles(): array
    {
        $this->log("Seeding resource materials...");
        $files = [];

        $courseIds = $this->getCourseIds();
        $instructorIds = $this->getInstructorIds();
        
        foreach ($courseIds as $courseId) {
            $instructorId = $instructorIds[rand(0, count($instructorIds) - 1)];
            
            for ($i = 1; $i <= $this->config->materialsPerCourse; $i++) {
                $fileName = "Material_{$i}_Lecture_Notes.pdf";
                $files[] = $this->createResourceFile($courseId, $instructorId, $fileName);
            }
        }

        $this->result->createdFiles = array_filter($files);
        $this->log("Created " . count($this->result->createdFiles) . " resource files");
        return $files;
    }

    /**
     * Clear all seeded data and physical files
     */
    public function clearAll(): bool
    {
        $this->log("Clearing existing seed data and files...");

        if ($this->config->dryRun) {
            $this->log("Would clear: database records and uploads directory");
            return true;
        }

        try {
            $conn = DBContext::getInstance()->getConnection();
            
            // Disable FK checks for easier truncation
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            $tables = ['course_students', 'file_attachments', 'courses', 'users'];
            foreach ($tables as $table) {
                if ($table === 'users') {
                    // Only delete seeded users
                    $conn->query("DELETE FROM `users` WHERE email LIKE '%@example.com' OR email = 'admin@admin.com'");
                } else {
                    $conn->query("TRUNCATE TABLE `$table`");
                }
            }
            
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");

            // Clean physical files
            $uploadsBase = BASE_PATH . 'PL/public/uploads/';
            $this->deleteDirectoryContents($uploadsBase);

            $this->log("Cleared existing seed data and physical files");
            return true;

        } catch (Exception $e) {
            $this->result->addError('clear', $e->getMessage());
            return false;
        }
    }

    private function deleteDirectoryContents($dir): void
    {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..', '.gitkeep', '.htaccess']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDirectoryContents($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    }

    /**
     * Generate a unique profile picture for a user
     */
    private function generateProfilePicture(int $userId, string $initials): void
    {
        if ($this->config->dryRun) return;

        $filename = "profile_{$userId}.png";
        $storedName = md5($filename . time() . rand()) . ".png";
        
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $storedName;
        $this->createPlaceholderImage($initials, $tempPath, 200, 200, true);

        $fileData = [
            'filename' => $filename,
            'stored_name' => $storedName,
            'tmp_path' => $tempPath,
            'mime_type' => 'image/png',
            'size' => filesize($tempPath)
        ];

        $result = FileStorageHelper::store($fileData, 'profile', $userId);
        
        if ($result['success']) {
            $file = new FileAttachment(
                $filename,
                $storedName,
                $result['data']['file_path'],
                'image/png',
                $fileData['size'],
                $userId,
                null,
                null
            );
            
            $fileId = $this->fileAttachmentRepository->create($file);
            $this->userRepository->updateProfilePicture($userId, $fileId);
        }
        
        if (file_exists($tempPath)) @unlink($tempPath);
    }

    /**
     * Generate a unique course cover
     */
    private function generateCourseCover(int $courseId, string $code): void
    {
        if ($this->config->dryRun) return;

        $filename = "cover_{$courseId}.png";
        $storedName = md5($filename . time() . rand()) . ".png";
        
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $storedName;
        $this->createPlaceholderImage($code, $tempPath, 800, 400, false);

        $fileData = [
            'filename' => $filename,
            'stored_name' => $storedName,
            'tmp_path' => $tempPath,
            'mime_type' => 'image/png',
            'size' => filesize($tempPath)
        ];

        $result = FileStorageHelper::store($fileData, 'cover', $courseId);
        
        if ($result['success']) {
            $file = new FileAttachment(
                $filename,
                $storedName,
                $result['data']['file_path'],
                'image/png',
                $fileData['size'],
                null, // uploaded_by placeholder
                $courseId,
                'cover'
            );
            
            $fileId = $this->fileAttachmentRepository->create($file);
            $this->courseRepository->updateCourseImage($courseId, $fileId);
        }
        
        if (file_exists($tempPath)) @unlink($tempPath);
    }

    /**
     * Create a resource file entry and physical file
     */
    private function createResourceFile(int $courseId, int $userId, string $filename): ?int
    {
        if ($this->config->dryRun) return -rand(5000, 6000);

        $seedAssetsPath = BASE_PATH . 'seed/assets/';
        $availableFiles = [];
        
        if (is_dir($seedAssetsPath)) {
            $availableFiles = array_diff(scandir($seedAssetsPath), ['.', '..']);
        }

        if (empty($availableFiles)) {
            // Fallback to dummy PDF if no assets found
            $storedName = md5($filename . time() . rand()) . ".pdf";
            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $storedName;
            file_put_contents($tempPath, "%PDF-1.4\n% Dummy PDF for Course {$courseId}\n%%EOF");
            $mimeType = 'application/pdf';
        } else {
            // Pick a random real file from assets
            $randomFile = $availableFiles[array_rand($availableFiles)];
            $sourcePath = $seedAssetsPath . $randomFile;
            $extension = pathinfo($randomFile, PATHINFO_EXTENSION);
            
            $storedName = md5($filename . time() . rand()) . "." . $extension;
            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $storedName;
            
            if (!copy($sourcePath, $tempPath)) {
                $this->result->addError('file', "Failed to copy seed asset: {$randomFile}");
                return null;
            }
            
            $filename = $randomFile; // Use the actual filename from the asset
            $mimeType = $this->getMimeTypeByExtension($extension);
        }

        $fileData = [
            'filename' => $filename,
            'stored_name' => $storedName,
            'tmp_path' => $tempPath,
            'mime_type' => $mimeType,
            'size' => filesize($tempPath)
        ];

        $result = FileStorageHelper::store($fileData, 'course', $courseId);
        
        if ($result['success']) {
            $file = new FileAttachment(
                $filename,
                $storedName,
                $result['data']['file_path'],
                $mimeType,
                $fileData['size'],
                $userId,
                $courseId,
                'resource'
            );
            
            $id = $this->fileAttachmentRepository->create($file);
            if (file_exists($tempPath)) @unlink($tempPath);
            return $id;
        }
        
        if (file_exists($tempPath)) @unlink($tempPath);
        return null;
    }

    private function getMimeTypeByExtension(string $ext): string
    {
        $mimes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg'
        ];
        return $mimes[strtolower($ext)] ?? 'application/octet-stream';
    }

    /**
     * GD Helper to create a placeholder image
     */
    private function createPlaceholderImage(string $text, string $path, int $width, int $height, bool $circle = false): void
    {
        $im = imagecreatetruecolor($width, $height);
        
        // Random background color
        $bg = imagecolorallocate($im, rand(50, 200), rand(50, 200), rand(50, 200));
        imagefill($im, 0, 0, $bg);
        
        if ($circle) {
            // Circle variant for profile pictures (actually just colors the whole thing, UI will handle border-radius)
        }

        $white = imagecolorallocate($im, 255, 255, 255);
        
        // Using built-in font for portability
        $fontSize = $width > 400 ? 5 : 5;
        $charWidth = imagefontwidth($fontSize);
        $charHeight = imagefontheight($fontSize);
        
        $textWidth = strlen($text) * $charWidth;
        $x = ($width - $textWidth) / 2;
        $y = ($height - $charHeight) / 2;
        
        // Scale text if possible or just use a larger built-in font
        imagestring($im, 5, (int)$x, (int)$y, $text, $white);
        
        imagepng($im, $path);
        imagedestroy($im);
    }

    /**
     * Get seeding result for assertions
     */
    public function getResult(): SeedingResult
    {
        return $this->result;
    }

    // Helper methods for creating entities

    private function createUser(array $data): ?int
    {
        try {
            if ($this->config->dryRun) {
                $this->log("Would create user: {$data['email']}");
                return -rand(1, 1000); // Fake ID for dry run
            }

            // Check if user already exists
            $existingUser = $this->userRepository->getByEmail($data['email']);
            if ($existingUser) {
                $this->log("Skipping user {$data['email']} - already exists (ID: {$existingUser->getId()})");
                return $existingUser->getId();
            }

            // Hash password if needed
            if (strlen($data['password']) < 60) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $user = new User(
                $data['email'],
                $data['password'],
                $data['first_name'],
                $data['last_name'],
                $data['role']
            );

            $id = $this->userRepository->create($user);
            $this->log("Created user: {$data['email']} (ID: {$id})");
            return $id;

        } catch (Exception $e) {
            $this->result->addError('user', "Failed to create {$data['email']}: {$e->getMessage()}");
            return null;
        }
    }

    private function createCourse(array $data): ?int
    {
        try {
            if ($this->config->dryRun) {
                $this->log("Would create course: {$data['name']}");
                return -rand(1001, 2000);
            }

            // Check if course already exists by code
            $existingCourse = $this->courseRepository->getByCode($data['code']);
            if ($existingCourse) {
                $this->log("Skipping course {$data['code']} - already exists (ID: {$existingCourse->getId()})");
                return $existingCourse->getId();
            }

            $course = new Course(
                $data['name'],
                $data['code'],
                $data['instructor_id'],
                $data['start_date'],
                $data['end_date'],
                $data['description'] ?? null,
                $data['capacity'] ?? 30,
                null
            );

            $id = $this->courseRepository->create($course);
            $this->log("Created course: {$data['name']} (ID: {$id})");
            return $id;

        } catch (Exception $e) {
            $this->result->addError('course', "Failed to create {$data['name']}: {$e->getMessage()}");
            return null;
        }
    }

    private function createEnrollment(array $data): ?int
    {
        try {
            if ($this->config->dryRun) {
                $this->log("Would create enrollment: student {$data['student_id']} -> course {$data['course_id']}");
                return -rand(2001, 3000);
            }

            // Check if enrollment already exists
            $existingEnrollment = $this->courseStudentRepository->getByCourseAndStudent(
                $data['course_id'],
                $data['student_id']
            );
            if ($existingEnrollment) {
                $this->log("Skipping enrollment: student {$data['student_id']} already enrolled in course {$data['course_id']}");
                return $existingEnrollment->getId();
            }

            $id = $this->courseStudentRepository->enroll(
                $data['course_id'],
                $data['student_id'],
                $data['status'] ?? 'active'
            );

            $this->log("Created enrollment: student {$data['student_id']} -> course {$data['course_id']} (ID: {$id})");
            return $id;

        } catch (Exception $e) {
            $this->result->addError('enrollment', $e->getMessage());
            return null;
        }
    }

    // ID retrieval helpers (mockable for tests)

    private function getInstructorIds(): array
    {
        if ($this->config->dryRun) {
            return [1, 2, 3];
        }
        if (!$this->userRepository) {
            return [];
        }
        $instructors = $this->userRepository->getByRole('Instructor');
        return array_map(fn($user) => $user->getId(), $instructors);
    }

    private function getStudentIds(): array
    {
        if ($this->config->dryRun) {
            return [10, 11, 12, 13, 14];
        }
        if (!$this->userRepository) {
            return [];
        }
        $students = $this->userRepository->getByRole('student');
        return array_map(fn($user) => $user->getId(), $students);
    }

    private function getCourseIds(): array
    {
        if ($this->config->dryRun) {
            return [100, 101, 102];
        }
        if (!$this->courseRepository) {
            return [];
        }
        $courses = $this->courseRepository->getAll();
        return array_map(fn($course) => $course->getId(), $courses);
    }

    // Transaction management

    private function beginTransaction(): void
    {
        if (!$this->config->dryRun && $this->userRepository) {
            $this->userRepository->beginTransaction();
        }
    }

    private function commit(): void
    {
        if (!$this->config->dryRun && $this->userRepository) {
            $this->userRepository->commit();
        }
    }

    private function rollback(): void
    {
        if (!$this->config->dryRun && $this->userRepository) {
            $this->userRepository->rollback();
        }
    }

    // Logging helper

    private function log(string $message, bool $isError = false): void
    {
        if ($this->config->verbose || $isError) {
            $prefix = $isError ? "[ERROR]" : "[INFO]";
            echo "{$prefix} {$message}" . PHP_EOL;
        }
    }
}

// CLI Execution
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    // Define BASE_PATH for CLI execution (set to project root: 2 levels up from Database folder)
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
    }

    echo "Database Seeding Tool" . PHP_EOL;
    echo "====================" . PHP_EOL . PHP_EOL;

    // Parse CLI arguments
    $args = array_slice($argv, 1);

    if (in_array('--help', $args) || in_array('-h', $args)) {
        echo "Usage: php DAL/Database/DataSeed.php [options]" . PHP_EOL . PHP_EOL;
        echo "Options:" . PHP_EOL;
        echo "  -d, --dry-run      Preview changes without committing" . PHP_EOL;
        echo "  -v, --verbose      Show detailed output" . PHP_EOL;
        echo "  -c, --clear        Clear existing seed data first" . PHP_EOL;
        echo "  -m, --minimal      Use minimal dataset (2 users, 1 course)" . PHP_EOL;
        echo "  -h, --help         Show this help" . PHP_EOL;
        exit(0);
    }

    try {
        // Load dependencies
        dataSeedAutoload();

        $config = SeedingConfig::fromCliArgs($args);
        $config->verbose = true; // Always verbose in CLI

        $seeder = new DatabaseSeeding(null, null, null, null, $config);
        $success = $seeder->seed();

        $result = $seeder->getResult();

        echo PHP_EOL . "Results:" . PHP_EOL;
        echo "--------" . PHP_EOL;
        foreach ($result->toArray() as $key => $value) {
            if (!is_array($value)) {
                echo "  {$key}: {$value}" . PHP_EOL;
            }
        }

        exit($success ? 0 : 1);

    } catch (Exception $e) {
        echo "[FATAL] " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}
