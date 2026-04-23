<?php
require_once __DIR__ . '/../../utils/FileStorageHelper.php';

/**
 * FileAttachmentMapper
 * 
 * Transforms FileAttachment entities to/from DTOs
 * Handles URL generation and file type detection
 */
class FileAttachmentMapper
{
    private $userRepository;
    private $courseRepository;

    /**
     * Constructor with required dependencies
     * 
     * @param UserRepository $userRepository Repository for fetching user/uploader data
     * @param CourseRepository $courseRepository Repository for fetching course data
     */
    public function __construct(UserRepository $userRepository, CourseRepository $courseRepository)
    {
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
    }

    /**
     * Convert FileAttachment entity to FileAttachmentResponseDTO
     * 
     * @param FileAttachment $file The file attachment entity from database
     * @return FileAttachmentResponseDTO The response DTO
     */
    public function toDTO(FileAttachment $file): FileAttachmentResponseDTO
    {
        $fileUrl = FileStorageHelper::getFileUrl(
            $file->getStoredName(),
            $file->getCourseId(),
            $file->getSubtype()
        );

        return new FileAttachmentResponseDTO(
            $file->getId(),
            $file->getFilename(),
            $file->getStoredName(),
            $file->getMimeType(),
            $file->getFileSize(),
            $file->getUploadedBy(),
            $file->getCourseId(),
            $file->getSubtype(),
            $fileUrl
        );
    }

    /**
     * Create FileAttachment entity from FileAttachmentRequestDTO
     * 
     * @param FileAttachmentRequestDTO $dto The request DTO with file upload data
     * @return FileAttachment A new FileAttachment entity (not yet saved to database)
     */
    public function toEntity(FileAttachmentRequestDTO $dto): FileAttachment
    {
        return new FileAttachment(
            $dto->filename,
            $dto->stored_name,
            $dto->file_path,
            $dto->mime_type,
            $dto->file_size,
            $dto->uploaded_by,
            $dto->course_id,
            $dto->subtype
        );
    }

    /**
     * Determine if a file is a profile picture
     * 
     * Profile pictures have no course_id and no subtype
     * 
     * @param FileAttachment $file The file to check
     * @return bool True if file is a profile picture
     */
    public function isProfilePicture(FileAttachment $file): bool
    {
        return $file->getCourseId() === null && $file->getSubtype() === null;
    }

    /**
     * Determine if a file is attached to a course
     * 
     * Course files have a non-null course_id
     * 
     * @param FileAttachment $file The file to check
     * @return bool True if file is attached to a course
     */
    public function isCourseFile(FileAttachment $file): bool
    {
        return $file->getCourseId() !== null;
    }
}
?>