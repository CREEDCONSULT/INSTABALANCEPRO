<?php

namespace App\Middleware;

use App\Middleware as BaseMiddleware;
use App\Controller;

/**
 * CsrfMiddleware — CSRF token validation
 * 
 * Validates CSRF tokens on POST, PUT, DELETE requests
 * Generates tokens for all requests and validates on form submissions
 */
class CsrfMiddleware extends BaseMiddleware
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_KEY = '_csrf_token';

    public function handle(Controller $controller): void
    {
        // Generate token if not exists
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = $this->generateToken();
        }

        // Validate token on unsafe methods
        $method = $_SERVER['REQUEST_METHOD'];
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_POST[self::TOKEN_KEY] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            $sessionToken = $_SESSION[self::TOKEN_KEY] ?? null;

            if (!$token || !$sessionToken || !hash_equals($token, $sessionToken)) {
                http_response_code(419);
                die(json_encode(['error' => 'CSRF token validation failed']));
            }
        }
    }

    /**
     * Generate a secure CSRF token
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    /**
     * Get current CSRF token
     * 
     * Call this in controllers to get token for forms
     */
    public static function token(): string
    {
        return $_SESSION[self::TOKEN_KEY] ?? '';
    }
}
