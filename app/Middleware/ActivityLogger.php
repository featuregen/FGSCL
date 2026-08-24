<?php
/**
 * Activity Logger Middleware
 * Logs user actions for audit trail
 */

class ActivityLogger
{
    /**
     * Log an activity
     */
    public static function log(string $module, string $action, string $description = '', ?int $userId = null): void
    {
        try {
            $userId = $userId ?? Session::userId();
            if (!$userId) return;

            $description = $description ?: self::generateDescription($module, $action);
            
            Database::insert('activity_logs', [
                'user_id'     => $userId,
                'module'      => $module,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => self::getClientIp(),
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'url'         => $_SERVER['REQUEST_URI'] ?? '',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // Silently fail — logging should never break the app
            if (APP_DEBUG) {
                error_log('Activity log error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Generate human-readable description
     */
    private static function generateDescription(string $module, string $action): string
    {
        $user = Session::user();
        $name = $user['full_name'] ?? 'User';
        
        $actionLabels = [
            'store'   => 'created',
            'create'  => 'created',
            'update'  => 'updated',
            'delete'  => 'deleted',
            'login'   => 'logged in',
            'logout'  => 'logged out',
            'approve' => 'approved',
            'reject'  => 'rejected',
            'export'  => 'exported',
        ];
        
        $actionLabel = $actionLabels[$action] ?? $action;
        $moduleLabel = str_replace('-', ' ', $module);
        
        return "{$name} {$actionLabel} {$moduleLabel}";
    }

    /**
     * Get client IP address
     */
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get recent activity logs for a user
     */
    public static function getUserLogs(int $userId, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Get recent activity logs for a school
     */
    public static function getSchoolLogs(int $schoolId, int $limit = 100): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.full_name, u.email 
             FROM activity_logs al 
             JOIN users u ON al.user_id = u.id 
             WHERE u.school_id = ? 
             ORDER BY al.created_at DESC 
             LIMIT ?",
            [$schoolId, $limit]
        );
    }
}
