<?php

require_once __DIR__ . '/../../utils/ResponseHelper.php';

class BaseController
{
    protected static function getJsonInput()
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected static function getFormInput()
    {
        return $_POST ?? [];
    }

    protected static function getUploadedFile(string $field)
    {
        return $_FILES[$field] ?? null;
    }

    public static function success($data, $message = 'Success', $code = 200)
    {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
