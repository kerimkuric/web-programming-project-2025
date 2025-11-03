<?php
<?php
require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/config/Database.php';

try {
    $connection = Database::connect();
    echo "Database connection successful!\n";
    
    // Test if we can query the database
    $stmt = $connection->query("SELECT DATABASE()");
    $dbname = $stmt->fetchColumn();
    echo "Connected to database: " . $dbname . "\n";
    
    // Show server info
    echo "MySQL Server Info: " . $connection->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test query execution
    $stmt = $connection->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\nAvailable tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}