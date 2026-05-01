<?php
require_once 'config/bootstrap.php';
require_once 'DAL/Repository/FileAttachmentRepository.php';

try {
    $repo = new FileAttachmentRepository();
    $files = $repo->getAll();
    
    echo "Current BASE_PATH: " . BASE_PATH . "\n";
    echo "Current sys_get_temp_dir: " . sys_get_temp_dir() . "\n";
    echo "Total files in DB: " . count($files) . "\n\n";
    
    foreach ($files as $file) {
        $path = $file->getFilePath();
        $exists = file_exists($path) ? "EXISTS" : "MISSING";
        echo "ID: " . $file->getId() . " | " . $exists . " | Path: " . $path . "\n";
        
        if (!$file_exists($path)) {
            // Try to see if it exists relative to current BASE_PATH
            // Assuming the path in DB might have an old BASE_PATH
            // Let's try to find 'PL/public/uploads' in the string
            $pos = strpos($path, 'PL/public/uploads');
            if ($pos !== false) {
                $relativePath = substr($path, $pos);
                $newPath = BASE_PATH . $relativePath;
                if (file_exists($newPath)) {
                    echo "  -> Found at alternative path: " . $newPath . "\n";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
