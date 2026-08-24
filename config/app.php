<?php
/**
 * Application Configuration
 * Loads environment variables and defines app-wide constants
 */

// Load environment variables from .env file
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        die('.env file not found. Please copy .env.example to .env and configure it.');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load .env
loadEnv(dirname(__DIR__) . '/.env');

/**
 * Get environment variable with default fallback
 */
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ─── App Constants ──────────────────────────────────────
define('APP_NAME', env('APP_NAME', 'ClassoraGen'));
define('APP_URL', env('APP_URL', 'http://localhost:8888/FGSL'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', 'true') === 'true');
define('APP_TIMEZONE', env('APP_TIMEZONE', 'Asia/Kolkata'));
define('APP_SECRET', env('APP_SECRET_KEY', ''));
define('APP_VERSION', '1.0.0');

// ─── Paths ──────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// ─── Session ────────────────────────────────────────────
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', 7200));
define('SESSION_NAME', env('SESSION_NAME', 'fgsl_session'));

// ─── Upload Limits ──────────────────────────────────────
define('UPLOAD_MAX_SIZE', (int)env('UPLOAD_MAX_SIZE', 5242880));
define('ALLOWED_IMAGE_TYPES', explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp')));
define('ALLOWED_DOC_TYPES', explode(',', env('ALLOWED_DOC_TYPES', 'pdf,doc,docx,xls,xlsx')));

// ─── Timezone ───────────────────────────────────────────
date_default_timezone_set(APP_TIMEZONE);

// ─── Error Reporting ────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ─── User Roles ─────────────────────────────────────────
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_SCHOOL_ADMIN', 'school_admin');
define('ROLE_PRINCIPAL', 'principal');
define('ROLE_STAFF', 'staff');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');
define('ROLE_PARENT', 'parent_user');
define('ROLE_ACCOUNTANT', 'accountant');
define('ROLE_LIBRARIAN', 'librarian');
define('ROLE_TRANSPORT_MANAGER', 'transport_manager');

// ─── Subscription Plans ─────────────────────────────────
define('PLAN_FREE', 'free');
define('PLAN_BASIC', 'basic');
define('PLAN_PREMIUM', 'premium');
define('PLAN_ENTERPRISE', 'enterprise');
