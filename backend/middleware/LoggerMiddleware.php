<?php

/**
 * Basic Logging Middleware
 * Logs API requests for debugging and monitoring
 */
class LoggerMiddleware {
    
    private static $logFile = __DIR__ . '/../logs/api.log';
    
    /**
     * Log request information
     */
    public static function logRequest() {
        // Ensure logs directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $request = Flight::request();
        $method = $request->method;
        $url = $request->url;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "[$timestamp] $method $url - IP: $ip\n";
        
        // Log to file (append mode)
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Log error
     */
    public static function logError($message, $exception = null) {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $errorMsg = $exception ? $exception->getMessage() : $message;
        $logEntry = "[$timestamp] ERROR: $message - $errorMsg\n";
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
}

?>

