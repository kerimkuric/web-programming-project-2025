<?php

/**
 * Error Handling Middleware
 * Centralized error handling and response formatting
 */
class ErrorHandlerMiddleware {
    
    /**
     * Handle exceptions and format error responses
     */
    public static function handleError($exception) {
        LoggerMiddleware::logError('Exception occurred', $exception);
        
        $statusCode = 500;
        $message = 'Internal server error';
        
        // Customize error messages based on exception type
        if ($exception instanceof PDOException) {
            $message = 'Database error occurred';
            $statusCode = 500;
        } elseif (method_exists($exception, 'getCode') && $exception->getCode() >= 400 && $exception->getCode() < 600) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        } else {
            // In development, show full error; in production, show generic message
            $message = $exception->getMessage();
        }
        
        Flight::json([
            'success' => false,
            'message' => $message,
            'error' => $exception->getMessage()
        ], $statusCode);
    }
    
    /**
     * Handle 404 Not Found
     */
    public static function handleNotFound() {
        Flight::json([
            'success' => false,
            'message' => 'Endpoint not found',
            'error' => 'The requested endpoint does not exist'
        ], 404);
    }
}

?>

