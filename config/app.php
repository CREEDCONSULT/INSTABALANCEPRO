<?php
/**
 * Application Configuration Loader
 * 
 * Loads environment variables from .env file and returns a configuration array
 * used throughout the application.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define configuration array
return [
    'app' => [
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => (bool) ($_ENV['APP_DEBUG'] ?? false),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'key' => $_ENV['APP_KEY'] ?? '',
    ],
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'instagram_unfollower',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'charset' => 'utf8mb4',
    ],
    'instagram' => [
        'app_id' => $_ENV['INSTAGRAM_APP_ID'] ?? '',
        'app_secret' => $_ENV['INSTAGRAM_APP_SECRET'] ?? '',
        'redirect_uri' => $_ENV['INSTAGRAM_REDIRECT_URI'] ?? '',
    ],
    'encryption' => [
        'key' => $_ENV['ENCRYPTION_KEY'] ?? '',
    ],
    'stripe' => [
        'secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? '',
        'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '',
        'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
    ],
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'user' => $_ENV['MAIL_USERNAME'] ?? '',
        'pass' => $_ENV['MAIL_PASSWORD'] ?? '',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@unfollowiq.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'UnfollowIQ',
    ],
    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 43200), // 30 days in seconds
        'timeout' => (int) ($_ENV['SESSION_TIMEOUT'] ?? 86400), // 1 day in seconds
    ],
];
