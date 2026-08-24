<?php
/**
 * Response Helper
 * Handles redirects, JSON responses, and view rendering
 */

class Response
{
    /**
     * Redirect to URL
     */
    public static function redirect(string $url): void
    {
        // Handle relative URLs
        if (!str_starts_with($url, 'http')) {
            $url = APP_URL . '/' . ltrim($url, '/');
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back to previous page
     */
    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        header("Location: {$referer}");
        exit;
    }

    /**
     * Redirect with flash message
     */
    public static function redirectWith(string $url, string $type, string $message): void
    {
        Session::flash($type, $message);
        self::redirect($url);
    }

    /**
     * Send JSON response
     */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send JSON success response
     */
    public static function success(string $message = 'Success', mixed $data = null, int $status = 200): void
    {
        $response = ['status' => 'success', 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response, $status);
    }

    /**
     * Send JSON error response
     */
    public static function error(string $message = 'Error', int $status = 400, mixed $errors = null): void
    {
        $response = ['status' => 'error', 'message' => $message];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        self::json($response, $status);
    }

    /**
     * Render a view with data
     */
    public static function view(string $view, array $data = [], string $layout = 'app'): void
    {
        // Make data available as variables in the view
        extract($data);
        
        // Build view path
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            if (APP_DEBUG) {
                die("View not found: {$viewPath}");
            }
            self::abort(404);
        }

        // Capture view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // If layout is specified, wrap content in layout
        if ($layout) {
            $layoutPath = VIEW_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
        exit;
    }

    /**
     * Render view without layout
     */
    public static function partial(string $view, array $data = []): void
    {
        self::view($view, $data, '');
    }

    /**
     * Abort with error page
     */
    public static function abort(int $code = 404, string $message = ''): void
    {
        http_response_code($code);
        
        $messages = [
            403 => 'Access Denied',
            404 => 'Page Not Found',
            500 => 'Internal Server Error',
        ];
        
        $message = $message ?: ($messages[$code] ?? 'Error');
        $errorViewPath = VIEW_PATH . "/errors/{$code}.php";
        
        if (file_exists($errorViewPath)) {
            $data = ['message' => $message, 'code' => $code];
            extract($data);
            require $errorViewPath;
        } else {
            echo "<h1>{$code} - {$message}</h1>";
        }
        exit;
    }

    /**
     * Set HTTP header
     */
    public static function header(string $name, string $value): void
    {
        header("{$name}: {$value}");
    }

    /**
     * Set no-cache headers
     */
    public static function noCache(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Force download
     */
    public static function download(string $filePath, string $fileName = ''): void
    {
        if (!file_exists($filePath)) {
            self::abort(404, 'File not found');
        }

        $fileName = $fileName ?: basename($filePath);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($filePath);
        exit;
    }
}
