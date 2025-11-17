<?php
// Router script for PHP built-in server
// This routes all requests to backend/index.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the request is for a file that exists (like CSS, JS, images), serve it
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve the file as-is
}

// Route API requests and everything else to backend/index.php
chdir(__DIR__ . '/backend');
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = $uri;
require __DIR__ . '/backend/index.php';

