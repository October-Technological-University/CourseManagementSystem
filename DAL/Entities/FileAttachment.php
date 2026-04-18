<?php

class FileAttachment
{
    private $id;
    private $filename;
    private $stored_name;
    private $file_path;
    private $mime_type;
    private $file_size;
    private $course_id;
    private $subtype;
    private $uploaded_by;
    private $uploaded_at;

    public function __construct($filename, $stored_name, $file_path, $mime_type, $file_size, $uploaded_by, $course_id = null, $subtype = null)
    {
        $this->filename = $filename;
        $this->stored_name = $stored_name;
        $this->file_path = $file_path;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->course_id = $course_id;
        $this->subtype = $subtype;
        $this->uploaded_by = $uploaded_by;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getFilename()
    {
        return $this->filename;
    }
    public function getStoredName()
    {
        return $this->stored_name;
    }
    public function getFilePath()
    {
        return $this->file_path;
    }
    public function getMimeType()
    {
        return $this->mime_type;
    }
    public function getFileSize()
    {
        return $this->file_size;
    }
    public function getCourseId()
    {
        return $this->course_id;
    }
    public function getSubtype()
    {
        return $this->subtype;
    }
    public function getUploadedBy()
    {
        return $this->uploaded_by;
    }
    public function getUploadedAt()
    {
        return $this->uploaded_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
    public function setStoredName($stored_name)
    {
        $this->stored_name = $stored_name;
    }
    public function setFilePath($file_path)
    {
        $this->file_path = $file_path;
    }
    public function setMimeType($mime_type)
    {
        $this->mime_type = $mime_type;
    }
    public function setFileSize($file_size)
    {
        $this->file_size = $file_size;
    }
    public function setCourseId($course_id)
    {
        $this->course_id = $course_id;
    }
    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;
    }
    public function setUploadedBy($uploaded_by)
    {
        $this->uploaded_by = $uploaded_by;
    }
    public function setUploadedAt($uploaded_at)
    {
        $this->uploaded_at = $uploaded_at;
    }

    /**
     * Check if this is a profile picture
     * Profile pictures have course_id = NULL
     */
    public function isProfilePicture()
    {
        return $this->course_id === null;
    }

    /**
     * Check if this is a course file
     * Course files have course_id != NULL
     */
    public function isCourseFile()
    {
        return $this->course_id !== null;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'stored_name' => $this->stored_name,
            'file_path' => $this->file_path,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'course_id' => $this->course_id,
            'subtype' => $this->subtype,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->uploaded_at
        ];
    }
}
?>