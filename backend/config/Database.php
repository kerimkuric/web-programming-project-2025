<?php

require_once __DIR__ . '/Config.php';

class Database {
    private static $connection = null;
    
    public static function connect() {
        if (self::$connection === null) {
            try {
                // Check if we have a database URL (Heroku, etc.)
                $dbConfig = Config::parseDatabaseUrl();
                
                if ($dbConfig) {
                    // Use parsed database URL
                    $dsn = "mysql:host=" . $dbConfig['host'] . ";port=" . $dbConfig['port'] . ";dbname=" . $dbConfig['name'] . ";charset=utf8mb4";
                    $user = $dbConfig['user'];
                    $pass = $dbConfig['pass'];
                } else {
                    // Use individual config values
                    $dsn = "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() . ";dbname=" . Config::DB_NAME() . ";charset=utf8mb4";
                    $user = Config::DB_USER();
                    $pass = Config::DB_PASSWORD();
                }
                
                self::$connection = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                return self::$connection;
            } catch (PDOException $e) {
                throw new Exception("Database connection error: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
