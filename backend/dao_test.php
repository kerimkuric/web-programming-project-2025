<?php
<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/UserDao.php';
require_once __DIR__ . '/dao/AuthorDao.php';
require_once __DIR__ . '/dao/GenreDao.php';
require_once __DIR__ . '/dao/BookDao.php';
require_once __DIR__ . '/dao/BorrowingDao.php';

class DaoTest {
    private $userDao;
    private $authorDao;
    private $genreDao;
    private $bookDao;
    private $borrowingDao;
    
    public function __construct() {
        $this->userDao = new UserDao();
        $this->authorDao = new AuthorDao();
        $this->genreDao = new GenreDao();
        $this->bookDao = new BookDao();
        $this->borrowingDao = new BorrowingDao();
    }
    
    public function testUserCRUD() {
        echo "\nTesting User CRUD Operations:\n";
        
        // Test CREATE
        $userData = [
            'name' => 'Test User',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'is_admin' => 0
        ];
        
        $userId = $this->userDao->insert($userData);
        echo "Create User: " . ($userId ? "PASSED" : "FAILED") . "\n";
        
        // Test READ
        $user = $this->userDao->getById($userId);
        echo "Read User: " . ($user ? "PASSED" : "FAILED") . "\n";
        
        // Test UPDATE
        $updateData = ['name' => 'Updated User'];
        $updated = $this->userDao->update($userId, $updateData);
        echo "Update User: " . ($updated ? "PASSED" : "FAILED") . "\n";
        
        // Test DELETE
        $deleted = $this->userDao->delete($userId);
        echo "Delete User: " . ($deleted ? "PASSED" : "FAILED") . "\n";
    }
    
    public function testAuthorCRUD() {
        echo "\nTesting Author CRUD Operations:\n";
        
        $authorData = [
            'name' => 'Test Author',
            'country' => 'Test Country'
        ];
        
        $authorId = $this->authorDao->insert($authorData);
        echo "Create Author: " . ($authorId ? "PASSED" : "FAILED") . "\n";
        
        // Clean up
        $this->authorDao->delete($authorId);
    }
    
    public function testGenreCRUD() {
        echo "\nTesting Genre CRUD Operations:\n";
        
        $genreData = [
            'name' => 'Test Genre'
        ];
        
        $genreId = $this->genreDao->insert($genreData);
        echo "Create Genre: " . ($genreId ? "PASSED" : "FAILED") . "\n";
        
        // Clean up
        $this->genreDao->delete($genreId);
    }
    
    public function runAllTests() {
        try {
            $this->testUserCRUD();
            $this->testAuthorCRUD();
            $this->testGenreCRUD();
            echo "\nAll tests completed!\n";
        } catch (Exception $e) {
            echo "Test failed: " . $e->getMessage() . "\n";
        }
    }
}

// Run the tests
$tester = new DaoTest();
$tester->runAllTests();
?>