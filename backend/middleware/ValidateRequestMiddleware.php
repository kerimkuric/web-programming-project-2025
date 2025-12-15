<?php

/**
 * Request Validation Middleware
 * Validates request data before processing
 */
class ValidateRequestMiddleware {
    
    /**
     * Validate required fields in request body
     */
    public static function validateRequired($requiredFields) {
        $rawBody = Flight::request()->getBody();
        $data = json_decode($rawBody, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $data = Flight::request()->data->getData();
        }
        
        if (!is_array($data)) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid request data format'
            ], 400);
            return false;
        }
        
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            Flight::json([
                'success' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missing)
            ], 400);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid email format'
            ], 400);
            return false;
        }
        return true;
    }
    
    /**
     * Validate integer ID parameter
     */
    public static function validateId($id) {
        if (!is_numeric($id) || $id <= 0) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid ID parameter'
            ], 400);
            return false;
        }
        return true;
    }
}

?>

