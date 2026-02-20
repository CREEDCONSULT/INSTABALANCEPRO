<?php

namespace App\Middleware;

use App\Middleware as BaseMiddleware;
use App\Controller;

/**
 * AdminMiddleware — Require admin user
 * 
 * Checks if user is admin; returns 403 if not
 */
class AdminMiddleware extends BaseMiddleware
{
    public function handle(Controller $controller): void
    {
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            die("<h1>401 Unauthorized</h1><p>You must be logged in to access this page.</p>");
        }

        if (!$this->isAdmin()) {
            http_response_code(403);
            die("<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>");
        }
    }
}
