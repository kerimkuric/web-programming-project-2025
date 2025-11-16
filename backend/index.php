<?php

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/config/Database.php';

// Load services
require_once __DIR__ . '/services/BaseService.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/AuthorService.php';
require_once __DIR__ . '/services/GenreService.php';
require_once __DIR__ . '/services/BookService.php';
require_once __DIR__ . '/services/BorrowingService.php';

// Register services with FlightPHP
Flight::register('userService', 'UserService');
Flight::register('authorService', 'AuthorService');
Flight::register('genreService', 'GenreService');
Flight::register('bookService', 'BookService');
Flight::register('borrowingService', 'BorrowingService');

// Register DELETE routes first to avoid conflicts with other routes
Flight::route('DELETE /api/users/@id', function($id) {
    try {
        $id = (int)$id;
        $user = Flight::userService()->getById($id);
        if (!$user) {
            Flight::json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        $result = Flight::userService()->delete($id);
        if ($result) {
            Flight::json(['success' => true, 'message' => 'User deleted successfully'], 200);
        } else {
            Flight::json(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    } catch (Exception $e) {
        Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Flight::route('DELETE /api/authors/@id', function($id) {
    try {
        $id = (int)$id;
        $author = Flight::authorService()->getById($id);
        if (!$author) {
            Flight::json(['success' => false, 'message' => 'Author not found'], 404);
            return;
        }
        $result = Flight::authorService()->delete($id);
        Flight::json(['success' => $result, 'message' => $result ? 'Author deleted successfully' : 'Failed to delete author'], $result ? 200 : 500);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Flight::route('DELETE /api/genres/@id', function($id) {
    try {
        $id = (int)$id;
        $genre = Flight::genreService()->getById($id);
        if (!$genre) {
            Flight::json(['success' => false, 'message' => 'Genre not found'], 404);
            return;
        }
        $result = Flight::genreService()->delete($id);
        Flight::json(['success' => $result, 'message' => $result ? 'Genre deleted successfully' : 'Failed to delete genre'], $result ? 200 : 500);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Flight::route('DELETE /api/books/@id', function($id) {
    try {
        $id = (int)$id;
        $book = Flight::bookService()->getById($id);
        if (!$book) {
            Flight::json(['success' => false, 'message' => 'Book not found'], 404);
            return;
        }
        $result = Flight::bookService()->delete($id);
        Flight::json(['success' => $result, 'message' => $result ? 'Book deleted successfully' : 'Failed to delete book'], $result ? 200 : 500);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Flight::route('DELETE /api/borrowings/@id', function($id) {
    try {
        $id = (int)$id;
        $borrowing = Flight::borrowingService()->getById($id);
        if (!$borrowing) {
            Flight::json(['success' => false, 'message' => 'Borrowing not found'], 404);
            return;
        }
        $result = Flight::borrowingService()->delete($id);
        Flight::json(['success' => $result, 'message' => $result ? 'Borrowing deleted successfully' : 'Failed to delete borrowing'], $result ? 200 : 500);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Load route files
require_once __DIR__ . '/routes/user_routes.php';
require_once __DIR__ . '/routes/author_routes.php';
require_once __DIR__ . '/routes/genre_routes.php';
require_once __DIR__ . '/routes/book_routes.php';
require_once __DIR__ . '/routes/borrowing_routes.php';

// Error handling
Flight::map('error', function(Exception $ex) {
    Flight::json([
        'success' => false,
        'message' => $ex->getMessage(),
        'error' => $ex->getMessage()
    ], 500);
});

Flight::map('notFound', function() {
    Flight::json([
        'success' => false,
        'message' => 'Endpoint not found',
        'error' => 'The requested endpoint does not exist'
    ], 404);
});

// Start FlightPHP
Flight::start();

?>

