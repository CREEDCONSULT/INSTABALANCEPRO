<?php

namespace App;

/**
 * Middleware — Abstract base class for request middleware
 * 
 * Middleware processes requests before they reach controllers
 * and can modify responses before they're sent
 */
abstract class Middleware
{
    protected Database $db;
    protected array $config;

    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Handle the middleware logic
     * Must be implemented by subclasses
     */
    abstract public function handle(Controller $controller): void;

    /**
     * Get current user from session
     */
    protected function getUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        // TODO: Load user from database
        // return User::find($this->db, $_SESSION['user_id']);
        return null;
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin(): bool
    {
        return isset($_SESSION['user_id']) && ($_SESSION['is_admin'] ?? false);
    }

    /**
     * Abort with HTTP error
     */
    protected function abort(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        throw new \Exception($message ?: "HTTP $statusCode");
    }
}
