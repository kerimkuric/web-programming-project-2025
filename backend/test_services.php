<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/services/BaseService.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/AuthorService.php';
require_once __DIR__ . '/services/GenreService.php';
require_once __DIR__ . '/services/BookService.php';
require_once __DIR__ . '/services/BorrowingService.php';

echo "=== Testing Service Layer ===\n\n";

try {
    // Test database connection
    $connection = Database::connect();
    echo "✓ Database connection successful\n\n";

    // Initialize services
    $userService = new UserService();
    $authorService = new AuthorService();
    $genreService = new GenreService();
    $bookService = new BookService();
    $borrowingService = new BorrowingService();

    echo "=== Testing UserService ===\n";
    
    // Test creating user with validation
    try {
        $userId = $userService->createUser([
            'name' => 'Test User ' . uniqid(),
            'email' => 'test.' . uniqid() . '@example.com',
            'phone' => '1234567890',
            'password' => 'test123',
            'is_admin' => 0
        ]);
        echo "✓ Created user with ID: $userId\n";
    } catch (Exception $e) {
        echo "✗ Error creating user: " . $e->getMessage() . "\n";
    }

    // Test validation - invalid email
    try {
        $userService->createUser([
            'name' => 'Test User',
            'email' => 'invalid-email',
            'phone' => '1234567890',
            'password' => 'test123'
        ]);
        echo "✗ Should have thrown exception for invalid email\n";
    } catch (Exception $e) {
        echo "✓ Validation caught invalid email: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing AuthorService ===\n";
    
    // Test creating author
    try {
        $authorId = $authorService->createAuthor([
            'name' => 'Test Author ' . uniqid(),
            'country' => 'Test Country'
        ]);
        echo "✓ Created author with ID: $authorId\n";
    } catch (Exception $e) {
        echo "✗ Error creating author: " . $e->getMessage() . "\n";
    }

    // Test validation - empty name
    try {
        $authorService->createAuthor([
            'name' => '',
            'country' => 'Test Country'
        ]);
        echo "✗ Should have thrown exception for empty name\n";
    } catch (Exception $e) {
        echo "✓ Validation caught empty name: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing GenreService ===\n";
    
    // Test creating genre
    try {
        $genreId = $genreService->createGenre([
            'name' => 'Test Genre ' . uniqid()
        ]);
        echo "✓ Created genre with ID: $genreId\n";
    } catch (Exception $e) {
        echo "✗ Error creating genre: " . $e->getMessage() . "\n";
    }

    // Test validation - duplicate genre
    try {
        $genreName = 'Unique Genre ' . uniqid();
        $genreService->createGenre(['name' => $genreName]);
        $genreService->createGenre(['name' => $genreName]); // Try duplicate
        echo "✗ Should have thrown exception for duplicate genre\n";
    } catch (Exception $e) {
        echo "✓ Validation caught duplicate genre: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing BookService ===\n";
    
    // First create author and genre for book
    $testAuthorId = $authorService->createAuthor([
        'name' => 'Book Author ' . uniqid(),
        'country' => 'USA'
    ]);
    $testGenreId = $genreService->createGenre([
        'name' => 'Book Genre ' . uniqid()
    ]);

    // Test creating book
    try {
        $bookId = $bookService->createBook([
            'title' => 'Test Book ' . uniqid(),
            'author_id' => $testAuthorId,
            'genre_id' => $testGenreId,
            'year' => 2023,
            'isbn' => 'TEST-' . uniqid()
        ]);
        echo "✓ Created book with ID: $bookId\n";
    } catch (Exception $e) {
        echo "✗ Error creating book: " . $e->getMessage() . "\n";
    }

    // Test validation - invalid year
    try {
        $bookService->createBook([
            'title' => 'Test Book',
            'author_id' => $testAuthorId,
            'genre_id' => $testGenreId,
            'year' => 3000, // Invalid future year
            'isbn' => 'TEST-' . uniqid()
        ]);
        echo "✗ Should have thrown exception for invalid year\n";
    } catch (Exception $e) {
        echo "✓ Validation caught invalid year: " . $e->getMessage() . "\n";
    }

    // Test validation - invalid author
    try {
        $bookService->createBook([
            'title' => 'Test Book',
            'author_id' => 99999, // Non-existent author
            'genre_id' => $testGenreId,
            'year' => 2023,
            'isbn' => 'TEST-' . uniqid()
        ]);
        echo "✗ Should have thrown exception for invalid author\n";
    } catch (Exception $e) {
        echo "✓ Validation caught invalid author: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing BorrowingService ===\n";
    
    // Create test user and book for borrowing
    $testUserId = $userService->createUser([
        'name' => 'Borrower ' . uniqid(),
        'email' => 'borrower.' . uniqid() . '@example.com',
        'phone' => '1234567890',
        'password' => 'test123'
    ]);
    $testBookId = $bookService->createBook([
        'title' => 'Borrowable Book ' . uniqid(),
        'author_id' => $testAuthorId,
        'genre_id' => $testGenreId,
        'year' => 2023,
        'isbn' => 'BORROW-' . uniqid()
    ]);

    // Test creating borrowing
    try {
        $borrowingId = $borrowingService->createBorrowing([
            'user_id' => $testUserId,
            'book_id' => $testBookId,
            'borrow_date' => date('Y-m-d')
        ]);
        echo "✓ Created borrowing with ID: $borrowingId\n";
    } catch (Exception $e) {
        echo "✗ Error creating borrowing: " . $e->getMessage() . "\n";
    }

    // Test validation - duplicate borrowing (same book not returned)
    try {
        $borrowingService->createBorrowing([
            'user_id' => $testUserId,
            'book_id' => $testBookId, // Same book
            'borrow_date' => date('Y-m-d')
        ]);
        echo "✗ Should have thrown exception for duplicate borrowing\n";
    } catch (Exception $e) {
        echo "✓ Validation caught duplicate borrowing: " . $e->getMessage() . "\n";
    }

    // Test returning a book
    try {
        $borrowingService->returnBook($borrowingId);
        echo "✓ Successfully returned book\n";
    } catch (Exception $e) {
        echo "✗ Error returning book: " . $e->getMessage() . "\n";
    }

    // Test getAll methods
    echo "\n=== Testing getAll Methods ===\n";
    $users = $userService->getAll();
    echo "✓ UserService->getAll(): " . count($users) . " users\n";
    
    $authors = $authorService->getAll();
    echo "✓ AuthorService->getAll(): " . count($authors) . " authors\n";
    
    $genres = $genreService->getAll();
    echo "✓ GenreService->getAll(): " . count($genres) . " genres\n";
    
    $books = $bookService->getAll();
    echo "✓ BookService->getAll(): " . count($books) . " books\n";
    
    $borrowings = $borrowingService->getAll();
    echo "✓ BorrowingService->getAll(): " . count($borrowings) . " borrowings\n";

    echo "\n=== All Service Tests Completed ===\n";

} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>

