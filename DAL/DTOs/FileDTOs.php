<?php

/**
 * DTO for file attachment response
 */
class FileAttachmentResponseDTO
{
    public $id;
    public $filename;
    public $stored_name;
    public $mime_type;
    public $file_size;
    public $uploaded_by;
    public $course_id;
    public $subtype;
    public $file_url; // URL to access the file via server

    public function __construct($id, $filename, $stored_name, $mime_type, $file_size, $uploaded_by, $course_id = null, $subtype = null, $file_url = null)
    {
        $this->id = $id;
        $this->filename = $filename;
        $this->stored_name = $stored_name;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->uploaded_by = $uploaded_by;
        $this->course_id = $course_id;
        $this->subtype = $subtype;
        $this->file_url = $file_url;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'stored_name' => $this->stored_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'uploaded_by' => $this->uploaded_by,
            'course_id' => $this->course_id,
            'subtype' => $this->subtype,
            'file_url' => $this->file_url
        ];
    }
}

/**
 * DTO for creating a file attachment
 */
class FileAttachmentRequestDTO
{
    public $filename;
    public $stored_name;
    public $file_path;
    public $mime_type;
    public $file_size;
    public $uploaded_by;
    public $course_id;
    public $subtype;

    public function __construct($filename, $stored_name, $file_path, $mime_type, $file_size, $uploaded_by, $course_id = null, $subtype = null)
    {
        $this->filename = $filename;
        $this->stored_name = $stored_name;
        $this->file_path = $file_path;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->uploaded_by = $uploaded_by;
        $this->course_id = $course_id;
        $this->subtype = $subtype;
    }
}


