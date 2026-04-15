<?php

class ResponseHelper
{
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function success($message, $data = null, $code = 200)
    {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response, $code);
    }

    public static function error($message, $statusCode = 400)
    {
        self::json(['success' => false, 'error' => $message], $statusCode);
    }
}
