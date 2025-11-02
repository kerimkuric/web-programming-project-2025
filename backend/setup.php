<?php
<?php
function setupDatabase() {
    try {
        $pdo = new PDO("mysql:host=localhost", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'library_schema'");
        $exists = $stmt->fetch();
        
        if (!$exists) {
            echo "Database does not exist. Creating...\n";
            
            // Create database with proper encoding
            $pdo->exec("CREATE DATABASE library_schema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Import schema
            $schema = file_get_contents(__DIR__ . '/../database/library_schema.sql');
            $pdo->exec("USE library_schema");
            $pdo->exec($schema);
            
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
                $pdo->exec($schema);
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