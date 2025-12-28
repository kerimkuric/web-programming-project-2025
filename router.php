<?php
// Router script for PHP built-in server
// This routes all requests to backend/index.php or frontend index.html

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Normalize URI
$uri = $uri === null ? '/' : $uri;

// Serve static files directly if they exist (CSS, JS, images, html)
if ($uri === '/' || file_exists(__DIR__ . $uri)) {
    // Serve frontend index for root
    if ($uri === '/' || $uri === '/index.html') {
        header('Content-Type: text/html');
        readfile(__DIR__ . '/frontend/index.html');
        return true;
    }
    // If file exists on disk, let the server handle it
    if (file_exists(__DIR__ . $uri)) {
        return false;
    }
}

// If request starts with /api, route to backend API
if (strpos($uri, '/api') === 0 || strpos($uri, '/public') === 0) {
    chdir(__DIR__ . '/backend');
    // Forward Authorization header to PHP environment for built-in server
    // Try several ways to obtain the raw request headers and forward Authorization
    if (function_exists('getallheaders')) {
        $reqHeaders = getallheaders();
    } elseif (function_exists('apache_request_headers')) {
        $reqHeaders = apache_request_headers();
    } else {
        $reqHeaders = [];
    }

    if (!empty($reqHeaders)) {
        if (isset($reqHeaders['Authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $reqHeaders['Authorization'];
        } elseif (isset($reqHeaders['authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $reqHeaders['authorization'];
        }
    } else {
        // Fallback: some servers populate REDIRECT_HTTP_AUTHORIZATION
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
    }
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['REQUEST_URI'] = $uri;
    require __DIR__ . '/backend/index.php';
} else {
    // For frontend SPA routes (client-side routing) serve index.html
    header('Content-Type: text/html');
    readfile(__DIR__ . '/frontend/index.html');
    return true;
}
