<?php

class ApiResponse {

    public function __construct($statusCode = 200, $message = null, $fieldsErrors = null, $data = null) {
        http_response_code($statusCode);
        echo json_encode([
            'IsSuccess'    => ($statusCode >= 200 && $statusCode < 300),
            'Message'      => $message,
            'FieldsErrors' => $fieldsErrors,
            'Data'         => $data
        ]);
        die;
    }
}
