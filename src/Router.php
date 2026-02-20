<?php

namespace App;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\MarkBased as DataGenerator;

/**
 * Router — FastRoute-based URL router and dispatcher
 * 
 * Maps HTTP requests to controller actions
 * Supports HTTP methods, route parameters, and middleware chains
 */
class Router
{
    private Database $db;
    private array $config;
    private RouteCollector $routeCollector;
    private array $routes = [];
    private array $middleware = [];
    private ?string $currentPrefix = null;

    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->routeCollector = new RouteCollector(
            new RouteParser(),
            new DataGenerator()
        );
    }

    /**
     * Register GET route
     */
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->registerRoute(['GET', 'HEAD'], $path, $handler, $middleware);
    }

    /**
     * Register POST route
     */
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->registerRoute(['POST'], $path, $handler, $middleware);
    }

    /**
     * Register PUT route
     */
    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->registerRoute(['PUT'], $path, $handler, $middleware);
    }

    /**
     * Register DELETE route
     */
    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->registerRoute(['DELETE'], $path, $handler, $middleware);
    }

    /**
     * Register route for multiple methods
     */
    public function match(array $methods, string $path, $handler, array $middleware = []): void
    {
        $this->registerRoute($methods, $path, $handler, $middleware);
    }

    /**
     * Register a route group with shared prefix and middleware
     */
    public function group(array $options, callable $callback): void
    {
        $previousPrefix = $this->currentPrefix;
        $previousMiddleware = $this->middleware;

        $prefix = $options['prefix'] ?? '';
        $middleware = $options['middleware'] ?? [];

        $this->currentPrefix = ($this->currentPrefix ?? '') . $prefix;
        $this->middleware = array_merge($previousMiddleware, $middleware);

        call_user_func($callback, $this);

        $this->currentPrefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
    }

    /**
     * Internal method to register a route
     */
    private function registerRoute(array $methods, string $path, $handler, array $middleware = []): void
    {
        // Apply current prefix
        if ($this->currentPrefix) {
            $path = $this->currentPrefix . $path;
        }

        // Merge with group middleware
        $allMiddleware = array_merge($this->middleware, $middleware);

        // Register with FastRoute
        foreach ($methods as $method) {
            $this->routeCollector->addRoute($method, $path, $handler);
        }

        // Store route metadata
        $this->routes[] = [
            'methods' => $methods,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $allMiddleware,
        ];
    }

    /**
     * Dispatch request and call appropriate controller action
     * 
     * @throws \Exception If route not found or invalid
     */
    public function dispatch(): void
    {
        // Get request method and path
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if deployed in subdirectory
        $basePath = ''; // Assume root deployment
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        // Create dispatcher from collected routes
        $dispatcher = new Dispatcher\GroupCountBased($this->routeCollector->getData());

        // Match route
        $routeInfo = $dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                die("<h1>404 Not Found</h1><p>The requested page does not exist.</p>");

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                die("<h1>405 Method Not Allowed</h1><p>HTTP method {$method} is not allowed for this route.</p>");

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $params = $routeInfo[2];

                // Find route metadata for middleware
                $foundRoute = $this->findRoute($path, $method);
                $middleware = $foundRoute['middleware'] ?? [];

                // Call handler with middleware chain
                $this->executeHandler($handler, $params, $middleware);
                break;
        }
    }

    /**
     * Execute handler with middleware chain
     */
    private function executeHandler($handler, array $params, array $middleware = []): void
    {
        // Handle closure/callable
        if (is_callable($handler)) {
            call_user_func($handler, ...array_values($params));
            return;
        }

        // Handle "Controller@action" format
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerClass, $methodName] = explode('@', $handler);

            // Build full class path
            if (strpos($controllerClass, '\\') === false) {
                $controllerClass = 'App\\Controllers\\' . $controllerClass;
            }

            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller not found: $controllerClass");
            }

            // Instantiate controller
            $controller = new $controllerClass($this->db, $this->config);

            // Apply middleware (validate auth, csrf, etc.)
            foreach ($middleware as $mw) {
                $this->applyMiddleware($mw, $controller);
            }

            // Call action method
            if (!method_exists($controller, $methodName)) {
                throw new \Exception("Method not found: {$controllerClass}@{$methodName}");
            }

            call_user_func([$controller, $methodName], ...array_values($params));
            return;
        }

        throw new \Exception("Invalid handler format");
    }

    /**
     * Apply middleware to controller
     */
    private function applyMiddleware(string $middlewareClass, Controller $controller): void
    {
        if (strpos($middlewareClass, '\\') === false) {
            $middlewareClass = 'App\\Middleware\\' . $middlewareClass;
        }

        if (!class_exists($middlewareClass)) {
            throw new \Exception("Middleware not found: $middlewareClass");
        }

        $middleware = new $middlewareClass($this->db, $this->config);

        if (!method_exists($middleware, 'handle')) {
            throw new \Exception("Middleware must implement handle() method");
        }

        $middleware->handle($controller);
    }

    /**
     * Find route metadata
     */
    private function findRoute(string $path, string $method): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['path'] === $path && in_array($method, $route['methods'])) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Generate URL for route
     */
    public function url(string $name, array $params = []): string
    {
        // TODO: Implement named routes
        return '#';
    }

    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
