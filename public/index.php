<?php
/**
 * UnfollowIQ — Front Controller
 * 
 * This is the main entry point for all HTTP requests. Apache routes all requests
 * to this file via .htaccess rewrite rules. The router then dispatches to the
 * appropriate controller based on the request URI.
 */

// Define the root path
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment variables and configuration
$config = require ROOT_PATH . '/config/app.php';

// Set error reporting based on environment
if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}

// Set default timezone
date_default_timezone_set('UTC');

// Start session
session_start([
    'cookie_lifetime' => $config['session']['lifetime'],
    'cookie_httponly' => true,
    'cookie_secure' => !$config['app']['debug'],
    'cookie_samesite' => 'Lax',
]);

try {
    // Initialize database connection
    $database = new \App\Database($config['database']);
    
    // Enable query logging in debug mode
    if ($config['app']['debug']) {
        $database->setLogging(true);
    }

    // Create router and register routes
    $router = new \App\Router($database, $config);
    \App\registerRoutes($router);

    // Dispatch the request
    $router->dispatch();

} catch (\Exception $e) {
    // Log error
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());

    // Display error (or error page in production)
    if ($config['app']['debug']) {
        http_response_code(500);
        echo '<h1>Application Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>An error occurred. Please try again later.</p>';
    }
    exit;
}
