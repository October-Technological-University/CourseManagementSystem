<?php
class Constants
{
    public const MAX_FILE_SIZE_MB = 10; // Max file size in megabytes
    public const ALLOWED_MIME_TYPES = [
        // Images
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        // Documents
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'text/plain' => 'txt',
    ];

    public const FILE_STORAGE_PATH = __DIR__ . '../PL/public/uploads/documentations/';
    public const IMAGES_STORAGE_PATH = __DIR__ . '../PL/public/uploads/images/';
    public const SERVER_BASE_URL = 'http://localhost:8000/';

}


