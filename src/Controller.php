<?php

namespace App;

/**
 * Controller — Abstract base class for all request handlers
 * 
 * Provides common request/response handling, view rendering, JSON responses, redirects
 */
abstract class Controller
{
    protected Database $db;
    protected array $config;
    protected $user = null;
    protected array $viewData = [];

    /**
     * Initialize controller
     */
    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
        
        // Load current user from session
        if (isset($_SESSION['user_id'])) {
            // User will be loaded as needed
        }
    }

    /**
     * Render a view template
     * 
     * @param string $view View path (e.g., 'pages/dashboard', 'partials/card')
     * @param array $data Data to pass to view
     */
    protected function view(string $view, array $data = []): void
    {
        $viewPath = ROOT_PATH . '/src/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            die("View not found: $view");
        }

        // Extract data into local scope
        extract($this->mergeViewData($data));

        // Include the view
        include $viewPath;
    }

    /**
     * Render a partial (e.g., for htmx)
     * 
     * @param string $partial Partial path (e.g., 'partials/account-row')
     * @param array $data Data for partial
     */
    protected function partial(string $partial, array $data = []): void
    {
        $this->view($partial, $data);
    }

    /**
     * Render JSON response
     * 
     * @param array $data JSON data
     * @param int $statusCode HTTP status code
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Render error JSON response
     */
    protected function jsonError(string $message, int $statusCode = 400, array $extra = []): void
    {
        $this->json(array_merge(['error' => $message], $extra), $statusCode);
    }

    /**
     * Render success JSON response
     */
    protected function jsonSuccess(string $message, array $data = [], int $statusCode = 200): void
    {
        $this->json(array_merge(['success' => $message], $data), $statusCode);
    }

    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (301, 302, 303, 307)
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }

    /**
     * Redirect with flash message
     */
    protected function redirectWith(string $url, string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        $this->redirect($url);
    }

    /**
     * Abort with HTTP error
     * 
     * @param int $statusCode HTTP status code
     * @param string $message Error message
     */
    protected function abort(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);

        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
        ];

        $title = $messages[$statusCode] ?? 'Error';
        $details = $message ?: $title;

        // Try to render error view if available
        $errorView = ROOT_PATH . '/src/Views/errors/' . $statusCode . '.php';
        if (file_exists($errorView)) {
            include $errorView;
            exit;
        }

        // Fallback to plain response
        echo "<h1>$title</h1>";
        if ($message) {
            echo "<p>$message</p>";
        }
        exit;
    }

    /**
     * Validate request input
     * 
     * @param array $rules Validation rules (field => rule)
     * @return array Errors array (empty if valid)
     */
    protected function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $_REQUEST[$field] ?? null;
            $ruleList = is_array($rule) ? $rule : explode('|', $rule);

            foreach ($ruleList as $r) {
                if (strpos($r, ':') !== false) {
                    [$ruleName, $param] = explode(':', $r, 2);
                } else {
                    $ruleName = $r;
                    $param = null;
                }

                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "This field is required";
                        }
                        break;
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "Invalid email address";
                        }
                        break;
                    case 'min':
                        if (!empty($value) && strlen($value) < (int)$param) {
                            $errors[$field][] = "Must be at least {$param} characters";
                        }
                        break;
                    case 'max':
                        if (!empty($value) && strlen($value) > (int)$param) {
                            $errors[$field][] = "Must be no more than {$param} characters";
                        }
                        break;
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = "Must be a number";
                        }
                        break;
                    case 'unique':
                        if (!empty($value)) {
                            [$table, $column] = explode('.', $param);
                            $count = \App\Models\User::class; // Placeholder: would check database
                            // TODO: Implement unique check
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * Get input value
     */
    protected function input(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**
     * Get POST input only
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET input only
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is AJAX (htmx)
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_HX_REQUEST']);
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Get request method
     */
    protected function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get current user from session
     */
    protected function getUser()
    {
        if ($this->user === null && isset($_SESSION['user_id'])) {
            // TODO: Load user from database
            // $this->user = User::find($this->db, $_SESSION['user_id']);
        }
        return $this->user;
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
     * Share data with all views
     */
    protected function share(array $data): void
    {
        $this->viewData = array_merge($this->viewData, $data);
    }

    /**
     * Merge view data
     */
    private function mergeViewData(array $data): array
    {
        return array_merge($this->viewData, $data);
    }
}
