<?php

namespace App\Middleware;

use App\Middleware as BaseMiddleware;
use App\Controller;

/**
 * AuthMiddleware — Require authenticated user
 * 
 * Checks if user is logged in; redirects to login if not
 */
class AuthMiddleware extends BaseMiddleware
{
    public function handle(Controller $controller): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /auth/login');
            exit;
        }
    }
}
