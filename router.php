<?php
/**
 * PHP Built-in Dev Server Router
 * Usage: php -S localhost:8080 -t public/ router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files (CSS, JS, images, fonts) directly
if ($uri !== '/' && file_exists($_SERVER['DOCUMENT_ROOT'] . $uri)) {
    return false;
}

// Route everything else through the front controller
require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
