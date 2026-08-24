<?php
/**
 * User Model
 * Handles all user-related database operations
 */

class User
{
    /**
     * Find user by ID
     */
    public static function find(int $id): array|false
    {
        return Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE u.id = ?",
            [$id]
        );
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): array|false
    {
        return Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE u.email = ?",
            [$email]
        );
    }

    /**
     * Find user by email OR username (for login)
     */
    public static function findByLogin(string $login): array|false
    {
        return Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE u.email = ? OR u.username = ?",
            [$login, $login]
        );
    }

    /**
     * Find user by username
     */
    public static function findByUsername(string $username, ?int $schoolId = null): array|false
    {
        $sql = "SELECT u.*, r.slug as role_slug, r.name as role_name 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                LEFT JOIN roles r ON ur.role_id = r.id 
                WHERE u.username = ?";
        $params = [$username];
        
        if ($schoolId !== null) {
            $sql .= " AND u.school_id = ?";
            $params[] = $schoolId;
        }
        
        return Database::fetch($sql, $params);
    }

    /**
     * Get all users with filters
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['school_id'])) {
            $where[] = 'u.school_id = ?';
            $params[] = $filters['school_id'];
        }

        if (!empty($filters['user_type'])) {
            $where[] = 'u.user_type = ?';
            $params[] = $filters['user_type'];
        }

        if (!empty($filters['is_active'])) {
            $where[] = 'u.is_active = ?';
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR u.phone LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = Database::fetchColumn(
            "SELECT COUNT(*) FROM users u WHERE {$whereClause}",
            $params
        );

        // Get paginated results
        $users = Database::fetchAll(
            "SELECT u.*, r.name as role_name, r.slug as role_slug, s.name as school_name
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             LEFT JOIN schools s ON u.school_id = s.id
             WHERE {$whereClause}
             ORDER BY u.created_at DESC 
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data'     => $users,
            'total'    => (int)$total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => ceil($total / $perPage),
        ];
    }

    /**
     * Create a new user
     */
    public static function create(array $data): int|string
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        
        $userId = Database::insert('users', $data);
        
        return $userId;
    }

    /**
     * Update user
     */
    public static function update(int $id, array $data): int
    {
        // Hash password if being changed
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['password_changed_at'] = date('Y-m-d H:i:s');
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return Database::update('users', $data, 'id = ?', [$id]);
    }

    /**
     * Delete user (soft delete by deactivating)
     */
    public static function delete(int $id): int
    {
        return Database::update('users', ['is_active' => 0], 'id = ?', [$id]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Assign role to user
     */
    public static function assignRole(int $userId, string $roleSlug): void
    {
        $role = Database::fetch("SELECT id FROM roles WHERE slug = ?", [$roleSlug]);
        if ($role) {
            // Remove existing roles first
            Database::delete('user_roles', 'user_id = ?', [$userId]);
            // Assign new role
            Database::insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $role['id'],
            ]);
        }
    }

    /**
     * Get user permissions
     */
    public static function getPermissions(int $userId): array
    {
        $permissions = Database::fetchAll(
            "SELECT p.slug 
             FROM permissions p 
             JOIN role_permissions rp ON p.id = rp.permission_id 
             JOIN user_roles ur ON rp.role_id = ur.role_id 
             WHERE ur.user_id = ?",
            [$userId]
        );
        
        return array_column($permissions, 'slug');
    }

    /**
     * Generate and save OTP
     */
    public static function generateOtp(int $userId): string
    {
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        Database::update('users', [
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
        ], 'id = ?', [$userId]);
        
        return $otp;
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp(string $email, string $otp): array|false
    {
        $user = Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE u.email = ? AND u.otp = ? AND u.otp_expires_at > NOW()",
            [$email, $otp]
        );
        
        if ($user) {
            // Clear OTP
            Database::update('users', [
                'otp' => null,
                'otp_expires_at' => null,
            ], 'id = ?', [$user['id']]);
        }
        
        return $user;
    }

    /**
     * Record login attempt
     */
    public static function recordLogin(int $userId, bool $success): void
    {
        if ($success) {
            Database::update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'login_attempts' => 0,
                'locked_until' => null,
            ], 'id = ?', [$userId]);
        } else {
            $user = self::find($userId);
            $attempts = ($user['login_attempts'] ?? 0) + 1;
            
            $data = ['login_attempts' => $attempts];
            
            // Lock after 5 failed attempts
            if ($attempts >= 5) {
                $data['locked_until'] = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            }
            
            Database::update('users', $data, 'id = ?', [$userId]);
        }
    }

    /**
     * Check if account is locked
     */
    public static function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) return false;
        return strtotime($user['locked_until']) > time();
    }

    /**
     * Generate password reset token
     */
    public static function createPasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Invalidate previous tokens
        Database::delete('password_resets', 'user_id = ?', [$userId]);
        
        Database::insert('password_resets', [
            'user_id'    => $userId,
            'token'      => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);
        
        return $token;
    }

    /**
     * Verify password reset token
     */
    public static function verifyResetToken(string $token): array|false
    {
        $hashedToken = hash('sha256', $token);
        
        $reset = Database::fetch(
            "SELECT pr.*, u.email, u.full_name 
             FROM password_resets pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL",
            [$hashedToken]
        );
        
        return $reset;
    }

    /**
     * Get users count by school
     */
    public static function countBySchool(int $schoolId, ?string $userType = null): int
    {
        $where = 'school_id = ? AND is_active = 1';
        $params = [$schoolId];
        
        if ($userType) {
            $where .= ' AND user_type = ?';
            $params[] = $userType;
        }
        
        return Database::count('users', $where, $params);
    }

    /**
     * Get user with school data
     */
    public static function findWithSchool(int $id): array|false
    {
        return Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name,
                    s.name as school_name, s.logo as school_logo, 
                    s.primary_color, s.secondary_color, s.code as school_code,
                    sd.class_id, sd.section_id, sd.admission_no,
                    c.name as class_name, sec.name as section_name
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             LEFT JOIN schools s ON u.school_id = s.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.id = ?",
            [$id]
        );
    }
}
