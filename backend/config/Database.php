<?php

class Database {
    private static $connection = null;
    private static $host = 'localhost';
    private static $dbname = 'librarydb';
    private static $username = 'root';
    private static $password = '';
    
    public static function connect() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbname,
                    self::$username,
                    self::$password
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (PDOException $e) {
                die("Database connection error: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}

?>

