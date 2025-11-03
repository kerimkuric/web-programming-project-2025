<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/UserDao.php';
require_once __DIR__ . '/dao/AuthorDao.php';
require_once __DIR__ . '/dao/GenreDao.php';
require_once __DIR__ . '/dao/BookDao.php';
require_once __DIR__ . '/dao/BorrowingDao.php';

try {
    // Test database connection first
    $connection = Database::connect();
    echo "Database connection successful!\n";
    echo "OK - connected to database: PDO\n\n";
    
    // Start transaction
    $connection->beginTransaction();

    // Initialize DAOs
    $userDao = new UserDao();
    $authorDao = new AuthorDao();
    $genreDao = new GenreDao();
    $bookDao = new BookDao();
    $borrowingDao = new BorrowingDao();

    echo "Starting DAO smoke test...\n";

    // Test Author creation with unique name
    $authorId = $authorDao->insert([
        'name' => 'Test Author ' . uniqid(),
        'country' => 'Test Country'
    ]);
    echo "Created author id={$authorId}\n";

    // Test Genre creation with unique name
    $genreId = $genreDao->insert([
        'name' => 'Test Genre ' . uniqid()
    ]);
    echo "Created genre id={$genreId}\n";

    // Test User creation with unique email
    $userId = $userDao->insert([
        'name' => 'Test User',
        'email' => 'test.' . uniqid() . '@example.com',
        'phone' => '1234567890',
        'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
        'is_admin' => 0
    ]);
    echo "Created user id={$userId}\n";

    // Test Book creation
    $bookId = $bookDao->insert([
        'title' => 'Test Book',
        'author_id' => $authorId,
        'genre_id' => $genreId,
        'year' => 2023,
        'isbn' => 'TEST-ISBN-' . uniqid()
    ]);
    echo "Created book id={$bookId}\n";

    // Test Borrowing creation
    $borrowingId = $borrowingDao->insert([
        'user_id' => $userId,
        'book_id' => $bookId,
        'borrow_date' => date('Y-m-d'),
        'return_date' => null
    ]);
    echo "Created borrowing id={$borrowingId}\n\n";

    // Print test results
    echo "Books by author {$authorId}:\n";
    print_r($bookDao->getAll(['author_id' => $authorId]));

    echo "\nBorrowings by user {$userId}:\n";
    print_r($borrowingDao->getAll(['user_id' => $userId]));

    // Rollback test data
    $connection->rollBack();
    echo "\nTransaction rolled back — no permanent changes were made.\n";
    echo "DAO smoke test completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
}