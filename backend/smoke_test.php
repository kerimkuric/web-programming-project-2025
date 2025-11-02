<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/UserDao.php';
require_once __DIR__ . '/dao/AuthorDao.php';
require_once __DIR__ . '/dao/GenreDao.php';
require_once __DIR__ . '/dao/BookDao.php';
require_once __DIR__ . '/dao/BorrowingDao.php';

class SmokeTest {
    private $connection;
    private $daos = [];

    public function __construct() {
        echo "Starting smoke tests...\n\n";
    }

    public function testDatabaseConnection() {
        echo "Testing database connection... ";
        try {
            $this->connection = Database::connect();
            $stmt = $this->connection->query("SELECT DATABASE()");
            $dbname = $stmt->fetchColumn();
            echo "SUCCESS ✓\n";
            echo "Connected to database: $dbname\n\n";
            return true;
        } catch (Exception $e) {
            echo "FAILED ✗\n";
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function testDaos() {
        echo "Testing DAO instantiation:\n";
        
        $daoClasses = [
            'User' => new UserDao(),
            'Author' => new AuthorDao(),
            'Genre' => new GenreDao(),
            'Book' => new BookDao(),
            'Borrowing' => new BorrowingDao()
        ];

        foreach ($daoClasses as $name => $dao) {
            echo "- Testing $name DAO... ";
            try {
                $results = $dao->getAll();
                echo "SUCCESS ✓ (Found " . count($results) . " records)\n";
                $this->daos[$name] = $dao;
            } catch (Exception $e) {
                echo "FAILED ✗\n";
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }
    }

    public function testCrudOperations() {
        echo "\nTesting CRUD operations:\n";

        try {
            // Start transaction to rollback test data
            $this->connection->beginTransaction();

            // Test User CRUD
            echo "- Testing User CRUD... ";
            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
                'is_admin' => 0
            ];
            
            $userId = $this->daos['User']->insert($userData);
            if (!$userId) throw new Exception("Failed to insert user");
            
            $user = $this->daos['User']->getById($userId);
            if (!$user) throw new Exception("Failed to get user by ID");
            
            if (!$this->daos['User']->update($userId, ['name' => 'Updated User'])) {
                throw new Exception("Failed to update user");
            }
            
            echo "SUCCESS ✓\n";

            // Test Author CRUD
            echo "- Testing Author CRUD... ";
            $authorId = $this->daos['Author']->insert([
                'name' => 'Test Author',
                'country' => 'Test Country'
            ]);
            if (!$authorId) throw new Exception("Failed to insert author");
            
            echo "SUCCESS ✓\n";

            // Rollback all test data
            $this->connection->rollBack();
            echo "\nTest data rolled back successfully.\n";

        } catch (Exception $e) {
            echo "FAILED ✗\n";
            echo "  Error: " . $e->getMessage() . "\n";
            if ($this->connection && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
        }
    }

    public function run() {
        if (!$this->testDatabaseConnection()) {
            echo "\nAborting tests due to database connection failure.\n";
            return;
        }

        $this->testDaos();
        $this->testCrudOperations();
        echo "\nSmoke tests completed.\n";
    }
}

// Run the smoke tests
$smokeTest = new SmokeTest();
$smokeTest->run();
?>