<?php
require_once __DIR__ . '/../../DAL/Repository/CourseRepository.php';
require_once __DIR__ . '/../../DAL/Repository/UserRepository.php';
require_once __DIR__ . '/../../DAL/Repository/CourseStudentRepository.php';
require_once __DIR__ . '/../../DAL/Repository/FileAttachmentRepository.php';                                                                                                                 
require_once __DIR__ . '/../../DAL/DTOs/CourseDTOs.php';
require_once __DIR__ . '/../../BLL/Mappers/CourseMapper.php';
require_once __DIR__ . '/../../BLL/Services/FileAttachmentService.php';                                                                                                                      
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/FileStorageHelper.php';  

class CourseService
{
    private $courseRepo;
    private $userRepo;
    private $enrollmentRepo;
    private $mapper;
    private $validator;
    private $fileAttachmentRepo;
    private $fileAttachmentService;
    public function __construct()
    {
        $this->courseRepo     = new CourseRepository();
        $this->userRepo       = new UserRepository();
        $this->enrollmentRepo = new CourseStudentRepository();
        $this->mapper         = new CourseMapper();
        $this->validator      = new Validator();
        $this->fileAttachmentRepo = new FileAttachmentRepository();
        $this->fileAttachmentService = new FileAttachmentService();
    }

    public function create(array $data): array
    {
        if (!$this->validator->validateRequired($data, ['name', 'instructor_id', 'start_date', 'end_date'])) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        if (!$this->validator->validateDate($data['start_date']) || !$this->validator->validateDate($data['end_date'])) {
            return ['success' => false, 'errors' => ['Invalid date format. Use Y-m-d.']];
        }
        if (empty($data['code'])) {
            $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            while ($this->courseRepo->getByCode($code) !== null) {
                $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                }
            $data['code'] = $code;
        }
        if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            return ['success' => false, 'errors' => ['end_date must be after start_date']];
        }

        $instructor = $this->userRepo->getById($data['instructor_id']);
        if (!$instructor) {
            return ['success' => false, 'errors' => ['Instructor not found']];
        }

        if (strtolower($instructor->getRole()) !== 'instructor') {
            return ['success' => false, 'errors' => ['The specified user is not an instructor']];
        }

        $dto    = CourseRequestDTO::fromArray($data);
        $entity = $this->mapper->toEntity($dto);
        $newId  = $this->courseRepo->create($entity);
        $course = $this->courseRepo->getById($newId);

        $enrolled       = $this->enrollmentRepo->getCountByCourseId($newId);
        $instructorName = $instructor->getFirstName() . ' ' . $instructor->getLastName();

        return [
            'success' => true,
            'data'    => $this->mapper->toDTO($course, $enrolled, $instructorName),
        ];
    }
    public function generateCourseInviteCode($id){
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }
        // Generate a unique 8-character alphanumeric code
        $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        while ($this->courseRepo->getByCode($code) !== null) {
            // If the generated code already exists, generate a new one
            $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        }
        $course->setCode($code);
        $isUpdate = ($this->courseRepo->update($course)) > 0 ? true : false;
        if (!$isUpdate) {
            return ['success' => false, 'errors' => ['Failed to generate invite code']];
        }
        return ['success' => true, 'data' => $course->getCode()];
    }
    public function getById(int $id): array
    {
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $enrolled       = $this->enrollmentRepo->getCountByCourseId($id);
        $instructor     = $this->userRepo->getById($course->getInstructorId());
        $instructorName = $instructor
            ? $instructor->getFirstName() . ' ' . $instructor->getLastName()
            : null;

        $courseImageUrl = null;
        if ($course->getCourseImageId()) {
            $image = $this->fileAttachmentRepo->getById($course->getCourseImageId());
            if ($image) {
                $courseImageUrl = FileStorageHelper::getFileUrl($image->getStoredName(), $course->getId(), $image->getSubtype());
            }
        }

        return [
            'success' => true,
            'data'    => $this->mapper->toDTO($course, $enrolled, $instructorName, $courseImageUrl),
        ];
    }

    public function getAll(): array
    {
        $courses = $this->courseRepo->getAll();
        return ['success' => true, 'data' => $this->buildDTOList($courses)];
    }

    public function getByInstructor(int $instructorId): array
    {
        $instructor = $this->userRepo->getById($instructorId);
        if (!$instructor) {
            return ['success' => false, 'errors' => ['Instructor not found']];
        }

        $courses = $this->courseRepo->getByInstructorId($instructorId);
        return ['success' => true, 'data' => $this->buildDTOList($courses)];
    }

    public function update(int $id, array $data): array
    {
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $startDate = $data['start_date'] ?? $course->getStartDate();
        $endDate   = $data['end_date']   ?? $course->getEndDate();

        if (isset($data['start_date']) && !$this->validator->validateDate($data['start_date'])) {
            return ['success' => false, 'errors' => ['Invalid start_date format. Use Y-m-d.']];
        }

        if (isset($data['end_date']) && !$this->validator->validateDate($data['end_date'])) {
            return ['success' => false, 'errors' => ['Invalid end_date format. Use Y-m-d.']];
        }

        if (strtotime($endDate) <= strtotime($startDate)) {
            return ['success' => false, 'errors' => ['end_date must be after start_date']];
        }

        $dto = CourseRequestDTO::fromArray(array_merge([
            'name'          => $course->getName(),
            'code'          => $course->getCode(),
            'instructor_id' => $course->getInstructorId(),
            'start_date'    => $course->getStartDate(),
            'end_date'      => $course->getEndDate(),
            'description'   => $course->getDescription(),
            'capacity'      => $course->getCapacity(),
            'CourseImageId'  => $course->getCourseImageId(),
        ], $data));

        $this->mapper->updateEntity($course, $dto);
        $this->courseRepo->update($course);

        return $this->getById($id);
    }

    public function delete(int $id): array
    {
        $course = $this->courseRepo->getById($id);
        if (!$course) {
            return ['success' => false, 'errors' => ['Course not found']];
        }

        $this->courseRepo->delete($id);
        return ['success' => true, 'message' => 'Course deleted successfully'];
    }

    public function search(array $filters): array
    {
        $courses = $this->courseRepo->getAll();

        if (!empty($filters['instructor_id'])) {
            $courses = array_filter($courses, fn($c) => $c->getInstructorId() == $filters['instructor_id']);
        }

        if (!empty($filters['keyword'])) {
            $kw = strtolower($filters['keyword']);
            $courses = array_filter($courses, function ($c) use ($kw) {
                return str_contains(strtolower($c->getName()), $kw)
                    || str_contains(strtolower($c->getCode() ?? ''), $kw);
            });
        }

        if (!empty($filters['available'])) {
            $courses = array_filter($courses, fn($c) => !$this->courseRepo->isFull($c->getId()));
        }

        return ['success' => true, 'data' => $this->buildDTOList(array_values($courses))];
    }

    private function buildDTOList(array $courses): array
    {
        return array_map(function ($course) {
            $enrolled   = $this->enrollmentRepo->getCountByCourseId($course->getId());
            $instructor = $this->userRepo->getById($course->getInstructorId());
            $name       = $instructor
                ? $instructor->getFirstName() . ' ' . $instructor->getLastName()
                : null;
            
            $courseImageUrl = null;
            if ($course->getCourseImageId()) {
                $image = $this->fileAttachmentRepo->getById($course->getCourseImageId());
                if ($image) {
                    $courseImageUrl = FileStorageHelper::getFileUrl($image->getStoredName(), $course->getId(), $image->getSubtype());
                }
            }
            
            return $this->mapper->toDTO($course, $enrolled, $name, $courseImageUrl);
        }, $courses);
    }
    public function uploadCourseImage(array $fileData, int $courseId, int $userId): array                                                                                                   
    {                                                                                                                                                                                       
        $course = $this->courseRepo->getById($courseId);                                                                                                                                    
        if (!$course) {                                                                                                                                                                     
            return ['success' => false, 'errors' => ['Course not found']];                                                                                                                  
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        if (!$this->validator->validateRequired($fileData, ['filename', 'tmp_path', 'mime_type', 'size', 'stored_name'])) {                                                                 
            return ['success' => false, 'errors' => $this->validator->getErrors()];                                                                                                         
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        $oldImageId = $course->getCourseImageId();                                                                                                                                          
        $oldImage = $oldImageId ? $this->fileAttachmentRepo->getById($oldImageId) : null;                                                                                                   

        $uploadResult = $this->fileAttachmentService->uploadFile($fileData, $userId, 'cover', (int)$courseId, 'cover');                                                                                  
        if (!$uploadResult['success']) {
            return $uploadResult;                                                                                                                                                           
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        $fileDto = $uploadResult['data'];                                                                                                                                                   
        $updateResult = $this->courseRepo->updateCourseImage($courseId, $fileDto->id);                                                                                                      
        if ($updateResult <= 0) {                                                                                                                                                           
            $this->fileAttachmentRepo->delete($fileDto->id);                                                                                                                                
            FileStorageHelper::delete($fileDto->file_path);                                                                                                                                 
            return ['success' => false, 'errors' => ['Failed to update course image']];                                                                                                     
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        if ($oldImage && $oldImage->getId() !== $fileDto->id) {                                                                                                                             
            $this->fileAttachmentRepo->delete($oldImage->getId());                                                                                                                          
            FileStorageHelper::delete($oldImage->getFilePath());                                                                                                                            
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        return [                                                                                                                                                                            
            'success' => true,                                                                                                                                                              
            'data' => $fileDto,                                                                                                                                                             
            'file_url' => $uploadResult['file_url']                                                                                                                                         
        ];                                                                                                                                                                                  
    }                                                                                                                                                                                       
                                                                                                                                                                                            
    public function removeCourseImage(int $courseId): array                                                                                                                                 
    {                                                                                                                                                                                       
        $course = $this->courseRepo->getById($courseId);                                                                                                                                    
        if (!$course) {                                                                                                                                                                     
            return ['success' => false, 'errors' => ['Course not found']];                                                                                                                  
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        $imageId = $course->getCourseImageId();                                                                                                                                             
        if (!$imageId) {                                                                                                                                                                    
            return ['success' => false, 'errors' => ['No course image found']];                                                                                                             
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        $image = $this->fileAttachmentRepo->getById($imageId);                                                                                                                              
        if (!$image) {                                                                                                                                                                      
            $this->courseRepo->updateCourseImage($courseId, null);                                                                                                                          
            return ['success' => false, 'errors' => ['Course image record not found']];                                                                                                     
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        $deleted = $this->fileAttachmentRepo->delete($image->getId());                                                                                                                      
        if ($deleted <= 0) {                                                                                                                                                                
            return ['success' => false, 'errors' => ['Failed to remove course image']];                                                                                                     
        }                                                                                                                                                                                   
                                                                                                                                                                                            
        FileStorageHelper::delete($image->getFilePath());                                                                                                                                   
        $this->courseRepo->updateCourseImage($courseId, null);                                                                                                                              
                                                                                                                                                                                            
        return ['success' => true, 'message' => 'Course image removed successfully'];                                                                                                       
    }   
}
