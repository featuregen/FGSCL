<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = Database::pdo();
    
    // Clean up existing superadmin if any
    $pdo->exec("DELETE FROM users WHERE email = 'superadmin@example.com'");
    
    // Create Super Admin user
    $pwd = password_hash('password123', PASSWORD_DEFAULT);
    $userStmt = $pdo->prepare("INSERT INTO users (school_id, username, full_name, email, password, user_type, is_active) VALUES (NULL, 'superadmin', 'Super Admin', 'superadmin@example.com', ?, 'super_admin', 1)");
    $userStmt->execute([$pwd]);
    $userId = $pdo->lastInsertId();
    
    // Assign Role 1 (Super Admin)
    $roleStmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, 1)");
    $roleStmt->execute([$userId]);
    
    echo "<h1>Super Admin Restored!</h1><p>Email: superadmin@example.com <br> Password: password123</p>";
    echo "<p><a href='" . APP_URL . "/auth/login' style='font-size: 20px;'>Go to Login</a></p>";
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
