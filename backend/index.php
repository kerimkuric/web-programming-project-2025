<?php

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authentication, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load Composer autoloaders: prefer both root and backend vendor autoloads when present
$rootAutoload = __DIR__ . '/../vendor/autoload.php';
$backendAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($rootAutoload)) {
    require_once $rootAutoload;
}
if (file_exists($backendAutoload)) {
    require_once $backendAutoload;
}

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
    // Use raw request URI for matching (works with Apache subfolders and PATH_INFO variations)
    $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : Flight::request()->url;
    
    // Skip auth for static files and favicons (anywhere in path)
    if (
        strpos($requestUrl, '/favicon.ico') !== false ||
        strpos($requestUrl, '/assets/') !== false ||
        strpos($requestUrl, '/views/') !== false ||
        strpos($requestUrl, '/utils/') !== false ||
        strpos($requestUrl, '/services/') !== false ||
        preg_match('/\.(css|js|png|jpg|gif|svg|ico|woff|woff2|ttf)$/i', $requestUrl)
    ) {
        return TRUE;
    }

    // Public API routes that don't require authentication (match anywhere)
    if(
        strpos($requestUrl, '/api/auth/login') !== false ||
        strpos($requestUrl, '/api/auth/register') !== false ||
        strpos($requestUrl, '/public/') !== false ||
        // Allow public GET access to listing endpoints only
        (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && (
            strpos($requestUrl, '/api/books') !== false ||
            strpos($requestUrl, '/api/authors') !== false ||
            strpos($requestUrl, '/api/genres') !== false
        ))
    ) {
        return TRUE;
    } else {
        try {
            // Get Authorization header from $_SERVER
            $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
            
            // Remove "Bearer " prefix if present
            if ($token && strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            if(!$token) {
                Flight::halt(401, "Missing authentication header");
            }
            
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
Flight::map('error', function(Throwable $ex) {
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
