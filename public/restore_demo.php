<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = Database::pdo();
    
    // 1. Create a school
    $schoolStmt = $pdo->prepare("INSERT INTO schools (name, email, phone, address, is_active) VALUES ('FG Public School', 'fg@example.com', '1234567890', '123 Main St', 1)");
    $schoolStmt->execute();
    $schoolId = $pdo->lastInsertId();
    
    // 2. Create School Admin user
    $pwd = password_hash('password123', PASSWORD_DEFAULT);
    $userStmt = $pdo->prepare("INSERT INTO users (school_id, username, full_name, email, password, user_type, is_active) VALUES (?, 'admin', 'School Admin', 'admin@example.com', ?, 'school_admin', 1)");
    $userStmt->execute([$schoolId, $pwd]);
    $userId = $pdo->lastInsertId();
    
    // 3. Assign Role (school_admin is role ID 2 usually, let's just make sure it's created)
    // Actually, seeds/roles_seed.sql creates Super Admin (1), School Admin (2).
    $roleStmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, 2)");
    $roleStmt->execute([$userId]);
    
    // 4. Enable All Modules
    $modules = ['students', 'staff', 'academic', 'fees', 'attendance', 'exams', 'library', 'transport', 'hostel', 'homework', 'payroll', 'leave', 'communication', 'inventory', 'certificates', 'reports', 'visitors'];
    
    $modStmt = $pdo->prepare("INSERT INTO school_modules (school_id, module_name, is_enabled) VALUES (?, ?, 1)");
    foreach ($modules as $mod) {
        try {
            $modStmt->execute([$schoolId, $mod]);
        } catch (Exception $e) {}
    }
    
    // 5. Update Session
    session_start();
    $_SESSION['user'] = [
        'id' => $userId,
        'school_id' => $schoolId,
        'full_name' => 'School Admin',
        'email' => 'admin@example.com',
        'user_type' => 'school_admin',
        'role_slug' => 'school_admin',
        'school_name' => 'FG Public School'
    ];
    
    echo "<h1>Restore Successful!</h1><p>Demo school, admin account, and all modules restored.</p>";
    echo "<p><a href='" . APP_URL . "/dashboard' style='font-size: 20px;'>Go to Dashboard</a></p>";
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
