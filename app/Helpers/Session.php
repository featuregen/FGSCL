<?php
/**
 * Session Helper
 * Manages user sessions with security features
 */

class Session
{
    private static bool $started = false;

    /**
     * Start session with secure settings
     */
    public static function start(): void
    {
        if (self::$started) return;
        
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.cookie_path', '/');
            ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);
            
            // Enable secure cookies on HTTPS
            if (APP_ENV === 'production' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
                ini_set('session.cookie_secure', '1');
            }
            
            session_name(SESSION_NAME);
            session_start();
            
            // Regenerate session ID periodically to prevent fixation
            if (!isset($_SESSION['_created'])) {
                $_SESSION['_created'] = time();
            } elseif (time() - $_SESSION['_created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_created'] = time();
            }
        }
        
        self::$started = true;
    }

    /**
     * Set session value
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy entire session
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
        self::$started = false;
    }

    /**
     * Set flash message (available only for next request)
     */
    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flash'][] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Get and clear flash messages
     */
    public static function getFlash(): array
    {
        self::start();
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user_id') && self::has('user_role');
    }

    /**
     * Get logged-in user ID
     */
    public static function userId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Get logged-in user role
     */
    public static function userRole(): ?string
    {
        return self::get('user_role');
    }

    /**
     * Get logged-in user's school ID
     */
    public static function schoolId(): ?int
    {
        $id = self::get('school_id');
        if ($id) {
            return (int)$id;
        }
        
        // For admin-level users without a school_id, auto-resolve to first active school
        $role = self::userRole();
        $adminRoles = ['super_admin', 'school_admin', 'principal'];
        if (in_array($role, $adminRoles) || $role === ROLE_SUPER_ADMIN) {
            if (self::has('super_admin_school_id')) {
                return (int)self::get('super_admin_school_id');
            }
            $school = Database::fetch("SELECT id FROM schools WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
            if ($school) {
                self::set('super_admin_school_id', (int)$school['id']);
                return (int)$school['id'];
            }
        }
        return null;
    }

    /**
     * Get logged-in user's full data
     */
    public static function user(): ?array
    {
        return self::get('user_data');
    }

    /**
     * Set user session after login
     */
    public static function setUser(array $user, array $permissions = []): void
    {
        // Use false to avoid deleting old session file (fixes shared hosting issues)
        session_regenerate_id(false);
        
        self::set('user_id', (int)$user['id']);
        self::set('user_role', $user['role_slug'] ?? $user['user_type'] ?? 'unknown');
        self::set('school_id', $user['school_id'] ? (int)$user['school_id'] : null);
        self::set('user_data', $user);
        self::set('permissions', $permissions);
        self::set('_created', time());
        self::set('last_activity', time());
    }

    /**
     * Get user permissions
     */
    public static function permissions(): array
    {
        return self::get('permissions', []);
    }

    /**
     * Check if user has a specific permission
     */
    public static function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if (self::userRole() === ROLE_SUPER_ADMIN) {
            return true;
        }
        return in_array($permission, self::permissions());
    }

    /**
     * Generate CSRF token
     */
    public static function csrfToken(): string
    {
        self::start();
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(string $token): bool
    {
        return hash_equals(self::csrfToken(), $token);
    }

    /**
     * Output hidden CSRF field
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::csrfToken() . '">';
    }

    /**
     * Update last activity timestamp
     */
    public static function touch(): void
    {
        self::set('last_activity', time());
    }

    /**
     * Check if session has expired
     */
    public static function isExpired(): bool
    {
        $lastActivity = self::get('last_activity', 0);
        return (time() - $lastActivity) > SESSION_LIFETIME;
    }
}
