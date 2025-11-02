<?php

class Database {
    private static $connection = null;
    private static $host = 'localhost';
    private static $dbname = 'library_schema';
    private static $username = 'root';
    private static $password = '';
    
    public static function connect() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
                self::$connection = new PDO($dsn, self::$username, self::$password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                return self::$connection;
            } catch (PDOException $e) {
                throw new Exception("Database connection error: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
