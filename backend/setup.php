<?php
require_once __DIR__ . '/config/Config.php';

function setupDatabase() {
    try {
        $pdo = new PDO("mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT(), Config::DB_USER(), Config::DB_PASSWORD());
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'library_schema'");
        $exists = $stmt->fetch();
        
        if (!$exists) {
            echo "Database does not exist. Creating...\n";
            
            // Create database with proper encoding
            $pdo->exec("CREATE DATABASE library_schema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Import schema
            $pdo->exec("USE library_schema");
            $schema = file_get_contents(__DIR__ . '/../database/library_schema.sql');
            
            // Remove comments and split into statements
            $schema = preg_replace('/--.*$/m', '', $schema); // Remove single-line comments
            $schema = preg_replace('/\/\*.*?\*\//s', '', $schema); // Remove multi-line comments
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            echo "Database and tables created successfully!\n";
        } else {
            echo "Database already exists.\n";
            
            // Verify tables
            $pdo->exec("USE library_schema");
            $tables = ['users', 'authors', 'genres', 'books', 'borrowings'];
            $missing = [];
            
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if (!$stmt->fetch()) {
                    $missing[] = $table;
                }
            }
            
            if (!empty($missing)) {
                echo "Missing tables found. Recreating: " . implode(", ", $missing) . "\n";
                $schema = file_get_contents(__DIR__ . '/../database/library_schema.sql');
                
                // Remove comments and split into statements
                $schema = preg_replace('/--.*$/m', '', $schema); // Remove single-line comments
                $schema = preg_replace('/\/\*.*?\*\//s', '', $schema); // Remove multi-line comments
                $statements = array_filter(array_map('trim', explode(';', $schema)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                echo "Tables recreated successfully!\n";
            } else {
                echo "All required tables exist.\n";
            }
        }
        
        return true;
    } catch (PDOException $e) {
        echo "Setup failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Auto-setup when included in tests
if (setupDatabase()) {
    echo "Database setup complete.\n";
} else {
    echo "Database setup failed.\n";
    exit(1);
}