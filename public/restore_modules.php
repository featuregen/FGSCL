<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = Database::pdo();
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("TRUNCATE TABLE modules");
    $pdo->exec("TRUNCATE TABLE school_modules");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    $sql = "INSERT INTO `modules` (`name`, `slug`, `description`, `icon`, `category`, `is_core`, `sort_order`) VALUES
    ('Dashboard',       'dashboard',       'Main dashboard and analytics',              'bi-speedometer2',     'core',       1, 1),
    ('User Management', 'users',           'Staff, teacher, and user accounts',         'bi-people',           'core',       1, 2),
    ('Settings',        'settings',        'School configuration and preferences',      'bi-gear',             'core',       1, 3),

    ('Students',        'students',        'Student admission, profiles, and records',  'bi-mortarboard',      'academic',   0, 10),
    ('Staff/Employees', 'staff',           'Staff profiles, documents, and management', 'bi-person-badge',     'academic',   0, 11),
    ('Attendance',      'attendance',      'Student and staff attendance tracking',     'bi-calendar-check',   'academic',   0, 12),
    ('Timetable',       'timetable',       'Class timetable and scheduling',            'bi-calendar-week',    'academic',   0, 13),
    ('Exams',           'exams',           'Exam management, marks, and report cards',  'bi-journal-check',    'academic',   0, 14),
    ('Homework',        'homework',        'Homework assignment and tracking',          'bi-book',             'academic',   0, 15),
    ('Online Classes',  'online_classes',  'Virtual classroom and video sessions',      'bi-camera-video',     'academic',   0, 16),
    ('Assignments',     'assignments',     'Assignment submission and grading',         'bi-file-earmark-text','academic',   0, 17),

    ('Communication',   'communication',   'Notices, circulars, and messaging',         'bi-chat-dots',        'communication', 0, 20),
    ('SMS/Email',       'sms_email',       'Bulk SMS and email notifications',          'bi-envelope',         'communication', 0, 21),
    ('Events',          'events',          'School events and calendar',                'bi-calendar-event',   'communication', 0, 22),

    ('Fees',            'fees',            'Fee collection, receipts, and tracking',    'bi-currency-rupee',   'finance',    0, 30),
    ('Payroll',         'payroll',         'Staff salary and payslip management',       'bi-wallet2',          'finance',    0, 31),
    ('Expenses',        'expenses',        'Expense tracking and budgeting',            'bi-receipt',          'finance',    0, 32),

    ('Library',         'library',         'Book catalog, issue, and return',           'bi-bookmark-star',    'resources',  0, 40),
    ('Transport',       'transport',       'Routes, vehicles, and driver management',   'bi-bus-front',        'resources',  0, 41),
    ('Hostel',          'hostel',          'Hostel rooms, allocation, and fees',        'bi-house-door',       'resources',  0, 42),
    ('Inventory',       'inventory',       'Assets and inventory management',           'bi-box-seam',         'resources',  0, 43),

    ('Visitors',        'visitors',        'Visitor gate pass and log',                 'bi-person-video2',    'other',      0, 50),
    ('Certificates',    'certificates',    'TC, bonafide, and certificate generation',  'bi-award',            'other',      0, 51),
    ('Reports',         'reports',         'Analytics, custom reports, and exports',    'bi-graph-up',         'other',      0, 52),
    ('Mobile App',      'mobile_app',      'Mobile app access for parents/students',    'bi-phone',            'other',      0, 53),

    ('AI Features',     'ai_features',     'AI-powered insights and predictions',       'bi-robot',            'premium',    0, 60),
    ('Data Migration',  'data_migration',  'Import data from other systems',            'bi-database-up',      'premium',    0, 61),
    ('API Access',      'api_access',      'REST API access for integrations',          'bi-plug',             'premium',    0, 62),
    ('Custom Branding', 'custom_branding', 'White-label with school branding',          'bi-palette',          'premium',    0, 63);";
    
    $pdo->exec($sql);
    echo "<h1>Success!</h1><p>29 Feature Modules have been perfectly restored to the database!</p>";
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
