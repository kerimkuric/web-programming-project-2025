<?php

// Set the reporting - disable in production
$isProduction = getenv('APP_ENV') === 'production' || (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production');

if ($isProduction) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));
}

class Config
{
    public static function DB_NAME()
    {
        // Try environment variable first (for production), then default
        return getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'library_schema');
    }

    public static function DB_PORT()
    {
        return getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306);
    }

    public static function DB_USER()
    {
        return getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
    }

    public static function DB_PASSWORD()
    {
        return getenv('DB_PASSWORD') ?: (isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '');
    }

    public static function DB_HOST()
    {
        // Parse database URL if provided (for Heroku, etc.)
        $dbUrl = getenv('CLEARDB_DATABASE_URL') ?: getenv('JAWSDB_URL') ?: (isset($_ENV['CLEARDB_DATABASE_URL']) ? $_ENV['CLEARDB_DATABASE_URL'] : (isset($_ENV['JAWSDB_URL']) ? $_ENV['JAWSDB_URL'] : null));
        
        if ($dbUrl) {
            // Parse URL format: mysql://user:password@host:port/database
            $url = parse_url($dbUrl);
            return $url['host'] ?? '127.0.0.1';
        }
        
        return getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : '127.0.0.1');
    }

    public static function JWT_SECRET() {
        return getenv('JWT_SECRET') ?: (isset($_ENV['JWT_SECRET']) ? $_ENV['JWT_SECRET'] : 'library_management_secret_key_2025_change_in_production');
    }
    
    // Helper method to parse database URL for all connection details
    public static function parseDatabaseUrl() {
        $dbUrl = getenv('CLEARDB_DATABASE_URL') ?: getenv('JAWSDB_URL') ?: (isset($_ENV['CLEARDB_DATABASE_URL']) ? $_ENV['CLEARDB_DATABASE_URL'] : (isset($_ENV['JAWSDB_URL']) ? $_ENV['JAWSDB_URL'] : null));
        
        if ($dbUrl) {
            $url = parse_url($dbUrl);
            return [
                'host' => $url['host'] ?? '127.0.0.1',
                'port' => isset($url['port']) ? $url['port'] : 3306,
                'user' => $url['user'] ?? 'root',
                'pass' => $url['pass'] ?? '',
                'name' => isset($url['path']) ? ltrim($url['path'], '/') : 'library_schema'
            ];
        }
        
        return null;
    }
}

?>
