<?php

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authentication, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/Database.php';

// Load middleware
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/data/roles.php';

// Load services
require_once __DIR__ . '/services/BaseService.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/AuthorService.php';
require_once __DIR__ . '/services/GenreService.php';
require_once __DIR__ . '/services/BookService.php';
require_once __DIR__ . '/services/BorrowingService.php';

// Register services with FlightPHP
Flight::register('auth_service', "AuthService");
Flight::register('userService', 'UserService');
Flight::register('authorService', 'AuthorService');
Flight::register('genreService', 'GenreService');
Flight::register('bookService', 'BookService');
Flight::register('borrowingService', 'BorrowingService');
Flight::register('auth_middleware', "AuthMiddleware");

// Global middleware for authentication (excluding public routes)
Flight::route('/*', function() {
    $requestUrl = Flight::request()->url;
    
    // Public routes that don't require authentication
    if(
        strpos($requestUrl, '/auth/login') === 0 ||
        strpos($requestUrl, '/auth/register') === 0 ||
        strpos($requestUrl, '/public/') === 0
    ) {
        return TRUE;
    } else {
        try {
            $token = Flight::request()->getHeader("Authentication");
            if(Flight::auth_middleware()->verifyToken($token))
                return TRUE;
        } catch (\Exception $e) {
            Flight::halt(401, $e->getMessage());
        }
    }
});

// Load route files (auth routes first, then protected routes)
require_once __DIR__ . '/routes/auth_routes.php';
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
