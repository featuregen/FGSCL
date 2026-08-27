-- ============================================
-- Migration 001: Subscription Plans
-- ============================================
-- SaaS subscription model for multi-school platform
-- Supports two pricing models:
--   1. fixed    — flat monthly/yearly fee regardless of student count
--   2. per_student — charged per student per month/year

CREATE TABLE IF NOT EXISTS `plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,

    -- Pricing Model
    `pricing_type` ENUM('fixed','per_student') NOT NULL DEFAULT 'fixed' COMMENT 'fixed = flat rate, per_student = rate x student count',

    -- Fixed Pricing (used when pricing_type = fixed)
    `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed monthly price',
    `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed yearly price',

    -- Per-Student Pricing (used when pricing_type = per_student)
    `price_per_student_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Per student per month',
    `price_per_student_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Per student per year',
    `min_students` INT NOT NULL DEFAULT 0 COMMENT 'Minimum billable students (floor)',
    `max_students_limit` INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited students allowed',

    -- Limits
    `max_students` INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited (plan capacity limit)',
    `max_staff` INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    `max_branches` INT NOT NULL DEFAULT 1,

    -- Features
    `features` JSON COMMENT 'List of enabled feature modules',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index for quick lookups
CREATE INDEX idx_plans_slug ON plans(slug);
CREATE INDEX idx_plans_active ON plans(is_active);
-- ============================================
-- Migration 002: Schools & Subscriptions
-- ============================================

CREATE TABLE IF NOT EXISTS `schools` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) UNIQUE COMMENT 'Unique school code',
    `email` VARCHAR(255),
    `phone` VARCHAR(20),
    `website` VARCHAR(255),
    `address` TEXT,
    `city` VARCHAR(100),
    `state` VARCHAR(100),
    `country` VARCHAR(100) DEFAULT 'India',
    `pincode` VARCHAR(10),
    `logo` VARCHAR(500) COMMENT 'Path to logo file',
    `favicon` VARCHAR(500),
    `primary_color` VARCHAR(7) DEFAULT '#4F46E5' COMMENT 'Brand color hex',
    `secondary_color` VARCHAR(7) DEFAULT '#7C3AED',
    `tagline` VARCHAR(255),
    `established_year` YEAR,
    `board` VARCHAR(100) COMMENT 'CBSE, ICSE, State Board, etc.',
    `school_type` ENUM('primary','secondary','higher_secondary','k12','college','other') DEFAULT 'k12',
    `principal_name` VARCHAR(255),
    `registration_no` VARCHAR(100),
    `tax_id` VARCHAR(50) COMMENT 'GST or PAN number',
    `timezone` VARCHAR(50) DEFAULT 'Asia/Kolkata',
    `date_format` VARCHAR(20) DEFAULT 'd/m/Y',
    `currency` VARCHAR(10) DEFAULT 'INR',
    `currency_symbol` VARCHAR(5) DEFAULT '₹',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `setup_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscriptions table
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `billing_cycle` ENUM('monthly','yearly') DEFAULT 'monthly',
    `pricing_type` ENUM('fixed','per_student') NOT NULL DEFAULT 'fixed',
    `student_count` INT NOT NULL DEFAULT 0 COMMENT 'Number of students billed (for per_student pricing)',
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price per student (for per_student) or flat rate',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total amount = unit_price x student_count (per_student) or flat rate',
    `payment_status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    `transaction_id` VARCHAR(255),
    `razorpay_order_id` VARCHAR(255),
    `razorpay_payment_id` VARCHAR(255),
    `status` ENUM('active','expired','cancelled','suspended') DEFAULT 'active',
    `auto_renew` TINYINT(1) DEFAULT 1,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_schools_code ON schools(code);
CREATE INDEX idx_schools_active ON schools(is_active);
CREATE INDEX idx_subscriptions_school ON subscriptions(school_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_subscriptions_dates ON subscriptions(start_date, end_date);
-- ============================================
-- Migration 003: Users Table
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NULL COMMENT 'NULL for super admin',
    `username` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `avatar` VARCHAR(500),
    `gender` ENUM('male','female','other'),
    `date_of_birth` DATE,
    `user_type` ENUM('super_admin','school_admin','principal','staff','teacher','student','parent','accountant','librarian','transport_manager') NOT NULL,
    `otp` VARCHAR(6),
    `otp_expires_at` DATETIME,
    `email_verified_at` DATETIME,
    `password_changed_at` DATETIME,
    `last_login_at` DATETIME,
    `last_login_ip` VARCHAR(45),
    `login_attempts` INT DEFAULT 0,
    `locked_until` DATETIME,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `force_password_change` TINYINT(1) DEFAULT 0,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_email` (`email`),
    UNIQUE KEY `uk_username_school` (`username`, `school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_users_school ON users(school_id);
CREATE INDEX idx_users_type ON users(user_type);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_password_resets_token ON password_resets(token);
CREATE INDEX idx_password_resets_expires ON password_resets(expires_at);
-- ============================================
-- Migration 004: Roles & Permissions
-- ============================================

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NULL COMMENT 'NULL for system roles',
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `is_system` TINYINT(1) DEFAULT 0 COMMENT 'System roles cannot be deleted',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_role_slug_school` (`slug`, `school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions (granular module.action)
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL COMMENT 'e.g. students, staff, fees',
    `action` VARCHAR(50) NOT NULL COMMENT 'e.g. view, create, edit, delete',
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'e.g. students.view',
    `description` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-Permission mapping
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_role_permission` (`role_id`, `permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User-Role mapping
CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_user_role` (`user_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_roles_slug ON roles(slug);
CREATE INDEX idx_permissions_module ON permissions(module);
CREATE INDEX idx_permissions_slug ON permissions(slug);
-- ============================================
-- Migration 005: Sessions & Activity Logs
-- ============================================

CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `device_type` VARCHAR(50) COMMENT 'web, mobile, tablet',
    `last_activity` DATETIME,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED,
    `module` VARCHAR(50) NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `url` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration tracking table
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_sessions_expires ON user_sessions(expires_at);
CREATE INDEX idx_activity_user ON activity_logs(user_id);
CREATE INDEX idx_activity_module ON activity_logs(module);
CREATE INDEX idx_activity_created ON activity_logs(created_at);
-- ============================================
-- Migration 006: Billing System
-- ============================================
-- Invoices, Payments, and Billing Settings
-- Supports: fixed + per-student pricing, 4 billing cycles,
--           partial payments, dynamic tax, credit notes

-- ─── Billing Settings (Super Admin configurable) ───

CREATE TABLE IF NOT EXISTS `billing_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text','number','boolean','json') DEFAULT 'text',
    `description` VARCHAR(255),
    `updated_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default billing settings
INSERT INTO `billing_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('grace_period_days', '15', 'number', 'Days after due date before suspending a school'),
('auto_suspend_enabled', '1', 'boolean', 'Automatically suspend schools with overdue invoices'),
('tax_enabled', '1', 'boolean', 'Enable tax/GST on invoices'),
('tax_rate', '18.00', 'number', 'Tax/GST percentage'),
('tax_label', 'GST', 'text', 'Tax label on invoices (e.g. GST, VAT, Tax)'),
('tax_number', '', 'text', 'Company GST/Tax registration number'),
('invoice_prefix', 'INV', 'text', 'Invoice number prefix'),
('payment_prefix', 'PAY', 'text', 'Payment number prefix'),
('invoice_due_days', '15', 'number', 'Days from invoice generation to due date'),
('auto_generate_invoices', '1', 'boolean', 'Auto-generate invoices before subscription expires'),
('auto_generate_days_before', '5', 'number', 'Generate invoice N days before expiry'),
('email_invoice_on_generate', '1', 'boolean', 'Email invoice to school admin when generated'),
('email_payment_reminder', '1', 'boolean', 'Send payment reminder emails'),
('reminder_days_before_due', '3', 'number', 'Send reminder N days before due date'),
('reminder_days_after_due', '7', 'number', 'Send overdue reminder N days after due date'),
('company_name', 'FGSL Technologies', 'text', 'Company name on invoices'),
('company_address', '', 'text', 'Company address on invoices'),
('company_phone', '', 'text', 'Company phone on invoices'),
('company_email', '', 'text', 'Company email on invoices');

-- ─── Invoices ──────────────────────────────────

CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g. INV-2026-0001',
    `school_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED,

    -- Billing Period
    `billing_period_start` DATE NOT NULL,
    `billing_period_end` DATE NOT NULL,
    `billing_cycle` ENUM('monthly','quarterly','half_yearly','yearly') NOT NULL,

    -- Pricing Snapshot (captured at invoice time)
    `pricing_type` ENUM('fixed','per_student') NOT NULL,
    `plan_name` VARCHAR(100) COMMENT 'Snapshot of plan name',
    `active_students` INT NOT NULL DEFAULT 0 COMMENT 'Active student count at billing time',
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Per student rate or flat rate',

    -- Amounts
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'unit_price × students (or flat)',
    `discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_reason` VARCHAR(255),
    `taxable_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'subtotal - discount',
    `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tax % at time of invoice',
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'taxable + tax',
    `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `balance_due` DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Status & Dates
    `status` ENUM('draft','pending','paid','partially_paid','overdue','cancelled','void') NOT NULL DEFAULT 'pending',
    `due_date` DATE NOT NULL,
    `paid_at` DATETIME,
    `cancelled_at` DATETIME,

    -- Meta
    `notes` TEXT,
    `generated_by` ENUM('auto','manual') DEFAULT 'manual',
    `created_by` INT UNSIGNED COMMENT 'User who generated (null if auto)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Payments ──────────────────────────────────

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `payment_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g. PAY-2026-0001',
    `invoice_id` INT UNSIGNED NOT NULL,
    `school_id` INT UNSIGNED NOT NULL,

    -- Payment Details
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('razorpay','bank_transfer','cash','cheque','upi','other') NOT NULL DEFAULT 'other',
    `payment_date` DATE NOT NULL,

    -- Razorpay Fields
    `razorpay_payment_id` VARCHAR(255),
    `razorpay_order_id` VARCHAR(255),
    `razorpay_signature` VARCHAR(255),

    -- Other Gateway / Manual
    `transaction_ref` VARCHAR(255) COMMENT 'Bank ref, cheque no, UPI ref, etc.',

    -- Status
    `status` ENUM('success','failed','pending','refunded') NOT NULL DEFAULT 'success',

    -- Meta
    `notes` TEXT,
    `received_by` INT UNSIGNED COMMENT 'Admin who recorded payment',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Credit Notes ──────────────────────────────

CREATE TABLE IF NOT EXISTS `credit_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `credit_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g. CN-2026-0001',
    `school_id` INT UNSIGNED NOT NULL,
    `invoice_id` INT UNSIGNED COMMENT 'Related invoice (optional)',
    `amount` DECIMAL(10,2) NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('active','used','expired','cancelled') DEFAULT 'active',
    `used_against_invoice_id` INT UNSIGNED COMMENT 'Invoice where credit was applied',
    `expires_at` DATE,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Indexes ───────────────────────────────────

CREATE INDEX idx_invoices_school ON invoices(school_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
CREATE INDEX idx_invoices_number ON invoices(invoice_number);
CREATE INDEX idx_invoices_period ON invoices(billing_period_start, billing_period_end);
CREATE INDEX idx_payments_invoice ON payments(invoice_id);
CREATE INDEX idx_payments_school ON payments(school_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_credit_notes_school ON credit_notes(school_id);

-- ─── Feature Modules (Master List) ────────────

CREATE TABLE IF NOT EXISTS `modules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Used in features JSON and permission checks',
    `description` VARCHAR(255),
    `icon` VARCHAR(50) DEFAULT 'bi-box' COMMENT 'Bootstrap Icon class',
    `category` VARCHAR(50) NOT NULL COMMENT 'Group: core, academic, finance, etc.',
    `is_core` TINYINT(1) DEFAULT 0 COMMENT 'Core modules cannot be disabled',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── School Modules (Per-School Feature Toggle) ───

CREATE TABLE IF NOT EXISTS `school_modules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `module_id` INT UNSIGNED NOT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `enabled_by` INT UNSIGNED COMMENT 'Super Admin who toggled',
    `enabled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_school_module` (`school_id`, `module_id`),
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_school_modules_school ON school_modules(school_id);

-- ─── Seed: Feature Modules ────────────────────

INSERT INTO `modules` (`name`, `slug`, `description`, `icon`, `category`, `is_core`, `sort_order`) VALUES
-- Core (always enabled, cannot be turned off)
('Dashboard',       'dashboard',       'Main dashboard and analytics',              'bi-speedometer2',     'core',       1, 1),
('User Management', 'users',           'Staff, teacher, and user accounts',         'bi-people',           'core',       1, 2),
('Settings',        'settings',        'School configuration and preferences',      'bi-gear',             'core',       1, 3),

-- Academic
('Students',        'students',        'Student admission, profiles, and records',  'bi-mortarboard',      'academic',   0, 10),
('Staff/Employees', 'staff',           'Staff profiles, documents, and management', 'bi-person-badge',     'academic',   0, 11),
('Attendance',      'attendance',      'Student and staff attendance tracking',     'bi-calendar-check',   'academic',   0, 12),
('Timetable',       'timetable',       'Class timetable and scheduling',            'bi-calendar-week',    'academic',   0, 13),
('Exams',           'exams',           'Exam management, marks, and report cards',  'bi-journal-check',    'academic',   0, 14),
('Homework',        'homework',        'Homework assignment and tracking',          'bi-book',             'academic',   0, 15),
('Online Classes',  'online_classes',  'Virtual classroom and video sessions',      'bi-camera-video',     'academic',   0, 16),
('Assignments',     'assignments',     'Assignment submission and grading',         'bi-file-earmark-text','academic',   0, 17),

-- Communication
('Communication',   'communication',   'Notices, circulars, and messaging',         'bi-chat-dots',        'communication', 0, 20),
('SMS/Email',       'sms_email',       'Bulk SMS and email notifications',          'bi-envelope',         'communication', 0, 21),
('Events',          'events',          'School events and calendar',                'bi-calendar-event',   'communication', 0, 22),

-- Finance
('Fees',            'fees',            'Fee collection, receipts, and tracking',    'bi-currency-rupee',   'finance',    0, 30),
('Payroll',         'payroll',         'Staff salary and payslip management',       'bi-wallet2',          'finance',    0, 31),
('Expenses',        'expenses',        'Expense tracking and budgeting',            'bi-receipt',          'finance',    0, 32),

-- Resources
('Library',         'library',         'Book catalog, issue, and return',           'bi-bookmark-star',    'resources',  0, 40),
('Transport',       'transport',       'Routes, vehicles, and driver management',   'bi-bus-front',        'resources',  0, 41),
('Hostel',          'hostel',          'Hostel rooms, allocation, and fees',        'bi-house-door',       'resources',  0, 42),
('Inventory',       'inventory',       'Assets and inventory management',           'bi-box-seam',         'resources',  0, 43),

-- Other
('Visitors',        'visitors',        'Visitor gate pass and log',                 'bi-person-video2',    'other',      0, 50),
('Certificates',    'certificates',    'TC, bonafide, and certificate generation',  'bi-award',            'other',      0, 51),
('Reports',         'reports',         'Analytics, custom reports, and exports',    'bi-graph-up',         'other',      0, 52),
('Mobile App',      'mobile_app',      'Mobile app access for parents/students',    'bi-phone',            'other',      0, 53),

-- Premium / Add-ons
('AI Features',     'ai_features',     'AI-powered insights and predictions',       'bi-robot',            'premium',    0, 60),
('Data Migration',  'data_migration',  'Import data from other systems',            'bi-database-up',      'premium',    0, 61),
('API Access',      'api_access',      'REST API access for integrations',          'bi-plug',             'premium',    0, 62),
('Custom Branding', 'custom_branding', 'White-label with school branding',          'bi-palette',          'premium',    0, 63);

-- ─── Alter existing tables ─────────────────────

ALTER TABLE `subscriptions`
    MODIFY `billing_cycle` ENUM('monthly','quarterly','half_yearly','yearly') DEFAULT 'monthly';

ALTER TABLE `plans`
    ADD COLUMN `price_quarterly` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed quarterly price' AFTER `price_yearly`,
    ADD COLUMN `price_half_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed half-yearly price' AFTER `price_quarterly`;

-- Update existing plan prices for quarterly/half-yearly
UPDATE `plans` SET `price_quarterly` = ROUND(`price_monthly` * 3 * 0.95, 2), `price_half_yearly` = ROUND(`price_monthly` * 6 * 0.90, 2) WHERE `pricing_type` = 'fixed' AND `price_monthly` > 0;

-- ============================================
-- Migration 007: School Setup Tables
-- Academic Years, Classes, Sections, Subjects
-- ============================================

-- Academic Years
CREATE TABLE IF NOT EXISTS `academic_years` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'e.g. 2025-26',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_current` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only one active per school',
    `status` ENUM('active','archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_academic_year` (`school_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes
CREATE TABLE IF NOT EXISTS `classes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `academic_year_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'e.g. Class 1, Class 10',
    `numeric_name` INT NOT NULL DEFAULT 0 COMMENT '1-12 for ordering',
    `display_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_class` (`school_id`, `academic_year_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sections
CREATE TABLE IF NOT EXISTS `sections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `class_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'e.g. A, B, C',
    `capacity` INT NOT NULL DEFAULT 40,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_section` (`class_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(20) COMMENT 'e.g. ENG, MAT, SCI',
    `type` ENUM('theory','practical','both') DEFAULT 'theory',
    `is_elective` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_subject` (`school_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class-Subject Mapping (which subjects are taught in which class)
CREATE TABLE IF NOT EXISTS `class_subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1,
    `periods_per_week` INT NOT NULL DEFAULT 5,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_class_subject` (`class_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_academic_years_school ON academic_years(school_id);
CREATE INDEX idx_academic_years_current ON academic_years(is_current);
CREATE INDEX idx_classes_school ON classes(school_id);
CREATE INDEX idx_classes_year ON classes(academic_year_id);
CREATE INDEX idx_sections_class ON sections(class_id);
CREATE INDEX idx_subjects_school ON subjects(school_id);
-- ============================================
-- Migration 008: Student Management
-- Student details, enrollment, parent mapping
-- ============================================

-- Student-specific details (extends users table)
CREATE TABLE IF NOT EXISTS `student_details` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'FK to users.id',
    `school_id` INT UNSIGNED NOT NULL,
    `admission_no` VARCHAR(50) NOT NULL,
    `admission_date` DATE,
    `class_id` INT UNSIGNED NULL,
    `section_id` INT UNSIGNED NULL,
    `academic_year_id` INT UNSIGNED NULL,
    `roll_number` VARCHAR(20),
    `blood_group` ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-'),
    `religion` VARCHAR(50),
    `caste` VARCHAR(50),
    `category` ENUM('general','obc','sc','st','ews','other') DEFAULT 'general',
    `nationality` VARCHAR(50) DEFAULT 'Indian',
    `mother_tongue` VARCHAR(50),
    `address` TEXT,
    `city` VARCHAR(100),
    `state` VARCHAR(100),
    `pincode` VARCHAR(10),
    `previous_school` VARCHAR(255),
    `previous_class` VARCHAR(50),
    `transfer_certificate` VARCHAR(255),

    -- Parent / Guardian
    `father_name` VARCHAR(255),
    `father_phone` VARCHAR(20),
    `father_occupation` VARCHAR(100),
    `father_email` VARCHAR(255),
    `mother_name` VARCHAR(255),
    `mother_phone` VARCHAR(20),
    `mother_occupation` VARCHAR(100),
    `mother_email` VARCHAR(255),
    `guardian_name` VARCHAR(255),
    `guardian_phone` VARCHAR(20),
    `guardian_relation` VARCHAR(50),
    `guardian_address` TEXT,

    `emergency_contact` VARCHAR(20),
    `medical_conditions` TEXT,
    `notes` TEXT,
    `status` ENUM('active','inactive','graduated','transferred','dropped') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_admission` (`school_id`, `admission_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_student_school ON student_details(school_id);
CREATE INDEX idx_student_class ON student_details(class_id);
CREATE INDEX idx_student_section ON student_details(section_id);
CREATE INDEX idx_student_year ON student_details(academic_year_id);
CREATE INDEX idx_student_status ON student_details(status);
CREATE INDEX idx_student_admission ON student_details(admission_no);
-- ============================================
-- Migration 009: School Form Configuration
-- Settings, Field Config, Custom Fields
-- ============================================

-- School Settings (key-value per school)
CREATE TABLE IF NOT EXISTS `school_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_school_setting` (`school_id`, `setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Base Field Config (show/hide, required per school)
CREATE TABLE IF NOT EXISTS `form_field_config` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `form_type` VARCHAR(50) NOT NULL DEFAULT 'student_admission',
    `field_name` VARCHAR(100) NOT NULL,
    `field_label` VARCHAR(255),
    `visibility` ENUM('show','hide') NOT NULL DEFAULT 'show',
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_field_config` (`school_id`, `form_type`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom Fields (school-defined extra fields)
CREATE TABLE IF NOT EXISTS `custom_fields` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL,
    `form_type` VARCHAR(50) NOT NULL DEFAULT 'student_admission',
    `field_label` VARCHAR(255) NOT NULL,
    `field_name` VARCHAR(100) NOT NULL,
    `field_type` ENUM('text','number','date','select','textarea','checkbox') NOT NULL DEFAULT 'text',
    `options` JSON COMMENT 'For select: ["Option1","Option2"]',
    `placeholder` VARCHAR(255),
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_custom_field` (`school_id`, `form_type`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom Field Values (stores actual data)
CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `custom_field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL DEFAULT 'student',
    `entity_id` INT UNSIGNED NOT NULL COMMENT 'user_id for students',
    `field_value` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_field_value` (`custom_field_id`, `entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_school_settings ON school_settings(school_id, setting_key);
CREATE INDEX idx_field_config ON form_field_config(school_id, form_type);
CREATE INDEX idx_custom_fields ON custom_fields(school_id, form_type);
CREATE INDEX idx_custom_values ON custom_field_values(entity_type, entity_id);
-- Migration: Create Timetable tables
-- Run after 003_create_academics.sql

-- Period definitions (school-level: Period 1 = 8:00-8:45, etc.)
CREATE TABLE IF NOT EXISTS timetable_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    short_name VARCHAR(10) DEFAULT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    period_type ENUM('class','break','lunch','assembly') DEFAULT 'class',
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Timetable entries (class + section + day + period → subject + teacher)
CREATE TABLE IF NOT EXISTS timetable (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '1=Mon, 2=Tue, ..., 6=Sat',
    period_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED DEFAULT NULL,
    room VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES timetable_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_slot (class_id, section_id, day_of_week, period_id),
    INDEX idx_timetable_class (class_id, section_id, day_of_week),
    INDEX idx_timetable_teacher (teacher_id, day_of_week)
) ENGINE=InnoDB;

-- Add period tracking to attendance for subject-wise attendance
ALTER TABLE attendance
    ADD COLUMN period_id INT UNSIGNED DEFAULT NULL AFTER attendance_date,
    ADD COLUMN subject_id INT UNSIGNED DEFAULT NULL AFTER period_id,
    ADD COLUMN session_type ENUM('morning','evening','period') DEFAULT 'morning' AFTER subject_id;

-- Update unique key: same student can have multiple records per day (per session/period)
-- Must drop FK first, then recreate
ALTER TABLE attendance DROP FOREIGN KEY attendance_ibfk_4;
ALTER TABLE attendance DROP INDEX unique_attendance;
ALTER TABLE attendance ADD UNIQUE KEY unique_attendance (student_id, attendance_date, session_type, period_id);
ALTER TABLE attendance ADD CONSTRAINT attendance_ibfk_4 FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE;
-- Migration: Departments, Designations, and Staff enhancements
-- Run after 009_school_form_config.sql

-- Departments master table
CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) DEFAULT NULL,
    head_id INT UNSIGNED DEFAULT NULL COMMENT 'HOD user_id',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (head_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_dept (school_id, name)
) ENGINE=InnoDB;

-- Designations master table
CREATE TABLE IF NOT EXISTS designations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    staff_category ENUM('teaching','non_teaching') DEFAULT 'teaching',
    level INT DEFAULT 0 COMMENT 'hierarchy: 1=highest',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY unique_desig (school_id, name)
) ENGINE=InnoDB;

-- Add department_id, designation_id, staff_category to staff_details
ALTER TABLE staff_details
    ADD COLUMN department_id INT UNSIGNED DEFAULT NULL AFTER employee_id,
    ADD COLUMN designation_id INT UNSIGNED DEFAULT NULL AFTER department_id,
    ADD COLUMN staff_category ENUM('teaching','non_teaching') DEFAULT 'teaching' AFTER designation_id;
-- Fee Management Tables
-- Migration: 012_create_fee_tables.sql

-- Fee Heads (categories like Tuition, Transport, Lab)
CREATE TABLE IF NOT EXISTS fee_heads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) DEFAULT NULL,
    type ENUM('mandatory','optional') DEFAULT 'mandatory',
    is_recurring TINYINT(1) DEFAULT 1,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_fee_head (school_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Structures (amounts per class per academic year)
CREATE TABLE IF NOT EXISTS fee_structures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    fee_head_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frequency ENUM('monthly','quarterly','half_yearly','yearly','one_time') DEFAULT 'monthly',
    due_day INT DEFAULT 10,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE CASCADE,
    UNIQUE KEY uk_fee_structure (school_id, academic_year_id, class_id, fee_head_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Discounts
CREATE TABLE IF NOT EXISTS fee_discounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('percentage','fixed') DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    applicable_heads JSON DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Payments (actual transactions)
CREATE TABLE IF NOT EXISTS fee_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    receipt_number VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_mode ENUM('cash','cheque','online','upi','bank_transfer') DEFAULT 'cash',
    transaction_ref VARCHAR(100) DEFAULT NULL,
    collected_by INT UNSIGNED DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    academic_year_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (collected_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_receipt (school_id, receipt_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Payment Items (line items per payment)
CREATE TABLE IF NOT EXISTS fee_payment_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fee_payment_id INT UNSIGNED NOT NULL,
    fee_head_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    period_label VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (fee_payment_id) REFERENCES fee_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Fee Concessions (per-student discounts)
CREATE TABLE IF NOT EXISTS student_fee_concessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    fee_discount_id INT UNSIGNED NOT NULL,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_discount_id) REFERENCES fee_discounts(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_concession (student_id, fee_discount_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Exams & Results Module Tables
-- Migration: 013_create_exams.sql

-- Exam Terms
CREATE TABLE IF NOT EXISTS exams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exam Schedules (Subject-wise dates and marks)
CREATE TABLE IF NOT EXISTS exam_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED DEFAULT NULL,
    subject_id INT UNSIGNED NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    room_no VARCHAR(50) DEFAULT NULL,
    max_marks DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    passing_marks DECIMAL(5,2) NOT NULL DEFAULT 33.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exam Marks
CREATE TABLE IF NOT EXISTS exam_marks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_schedule_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    marks_obtained DECIMAL(5,2) DEFAULT NULL,
    is_absent TINYINT(1) DEFAULT 0,
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_schedule_id) REFERENCES exam_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_exam_marks (exam_schedule_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Grading Scales
CREATE TABLE IF NOT EXISTS exam_grades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    min_percentage DECIMAL(5,2) NOT NULL,
    max_percentage DECIMAL(5,2) NOT NULL,
    grade_point DECIMAL(4,2) DEFAULT NULL,
    remarks VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Homework Module Tables
-- Migration: 014_create_homework.sql

-- Homework Assignments
CREATE TABLE IF NOT EXISTS homework (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED DEFAULT NULL,
    subject_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    attachment VARCHAR(255) DEFAULT NULL,
    assign_date DATE NOT NULL,
    due_date DATE NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Homework Submissions
CREATE TABLE IF NOT EXISTS homework_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    homework_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    submission_text TEXT DEFAULT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'submitted', 'graded', 'late') DEFAULT 'pending',
    marks DECIMAL(5,2) DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    submitted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_homework_submission (homework_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Communication & Alerts Module Tables
-- Migration: 015_create_communication.sql

-- Communications (Broadcast messages, SMS, Notices)
CREATE TABLE IF NOT EXISTS communications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    type ENUM('email', 'sms', 'notice', 'push') NOT NULL DEFAULT 'notice',
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    target_roles JSON NOT NULL, -- e.g. ["student", "parent", "teacher"]
    target_classes JSON DEFAULT NULL, -- e.g. [1, 2, 3] or null for all
    sent_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Communication Recipients (Tracking read status and delivery)
CREATE TABLE IF NOT EXISTS communication_recipients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    communication_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'read') DEFAULT 'pending',
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (communication_id) REFERENCES communications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_comm_recipient (communication_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Payroll & HR Module Tables
-- Migration: 016_create_payroll.sql

-- Payroll Structures (Base Salary setup for each staff member)
CREATE TABLE IF NOT EXISTS payroll_structures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances_json JSON DEFAULT NULL,
    deductions_json JSON DEFAULT NULL,
    net_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_payroll_structure (school_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Monthly Payrolls (Generated Payslips)
CREATE TABLE IF NOT EXISTS payrolls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    month TINYINT NOT NULL,
    year YEAR NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances_json JSON DEFAULT NULL,
    deductions_json JSON DEFAULT NULL,
    net_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('generated', 'paid') DEFAULT 'generated',
    payment_method ENUM('cash', 'bank_transfer', 'cheque', 'upi') DEFAULT 'bank_transfer',
    payment_date DATE DEFAULT NULL,
    transaction_ref VARCHAR(100) DEFAULT NULL,
    generated_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_payroll_month (school_id, user_id, month, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS leave_types (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, name VARCHAR(100), days INT);
CREATE TABLE IF NOT EXISTS leave_requests (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, user_id INT, leave_type_id INT, from_date DATE, to_date DATE, reason TEXT, status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);CREATE TABLE IF NOT EXISTS library_books (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, title VARCHAR(200), author VARCHAR(200), isbn VARCHAR(50), qty INT DEFAULT 1, available INT DEFAULT 1);
CREATE TABLE IF NOT EXISTS library_issues (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, book_id INT, user_id INT, issue_date DATE, due_date DATE, return_date DATE, status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued');CREATE TABLE IF NOT EXISTS transport_vehicles (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, vehicle_no VARCHAR(50), driver_name VARCHAR(100), phone VARCHAR(20), capacity INT);
CREATE TABLE IF NOT EXISTS transport_routes (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, name VARCHAR(100), fare DECIMAL(10,2));CREATE TABLE IF NOT EXISTS hostel_rooms (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, hostel_name VARCHAR(100), room_no VARCHAR(50), bed_count INT, cost DECIMAL(10,2));CREATE TABLE IF NOT EXISTS inventory_items (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, category VARCHAR(100), name VARCHAR(100), stock INT DEFAULT 0, price DECIMAL(10,2));CREATE TABLE IF NOT EXISTS visitor_logs (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, name VARCHAR(100), phone VARCHAR(20), purpose VARCHAR(200), to_meet VARCHAR(100), in_time DATETIME, out_time DATETIME);CREATE TABLE IF NOT EXISTS issued_certificates (id INT AUTO_INCREMENT PRIMARY KEY, school_id INT, user_id INT, type VARCHAR(100), issue_date DATE, reference_no VARCHAR(100));-- ======================================================
-- Migration 024: Full Schema Upgrade for All 9 Modules
-- ======================================================

-- 1. Leave Management
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) DEFAULT NULL,
  `days_per_year` INT NOT NULL DEFAULT 12,
  `is_paid` TINYINT(1) NOT NULL DEFAULT 1,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_types_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE NOT NULL,
  `total_days` DECIMAL(4,1) NOT NULL DEFAULT 1.0,
  `reason` TEXT NOT NULL,
  `document` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `action_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_req_school` (`school_id`),
  KEY `idx_leave_req_user` (`user_id`),
  KEY `idx_leave_req_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Library Management
CREATE TABLE IF NOT EXISTS `library_books` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `author` VARCHAR(200) NOT NULL,
  `isbn` VARCHAR(50) DEFAULT NULL,
  `publisher` VARCHAR(150) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'General',
  `rack_no` VARCHAR(50) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `available_quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) DEFAULT '0.00',
  `edition` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_books_school` (`school_id`),
  KEY `idx_books_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `library_issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `book_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `fine_amount` DECIMAL(10,2) DEFAULT '0.00',
  `fine_paid` DECIMAL(10,2) DEFAULT '0.00',
  `status` ENUM('issued', 'returned', 'overdue', 'lost') NOT NULL DEFAULT 'issued',
  `remarks` TEXT DEFAULT NULL,
  `issued_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_issues_school` (`school_id`),
  KEY `idx_issues_book` (`book_id`),
  KEY `idx_issues_user` (`user_id`),
  KEY `idx_issues_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Transport Management
CREATE TABLE IF NOT EXISTS `transport_vehicles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `vehicle_no` VARCHAR(50) NOT NULL,
  `model` VARCHAR(100) DEFAULT NULL,
  `capacity` INT NOT NULL DEFAULT 30,
  `driver_name` VARCHAR(100) DEFAULT NULL,
  `driver_phone` VARCHAR(30) DEFAULT NULL,
  `driver_license` VARCHAR(100) DEFAULT NULL,
  `insurance_expiry` DATE DEFAULT NULL,
  `fitness_expiry` DATE DEFAULT NULL,
  `status` ENUM('active', 'maintenance', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicles_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_routes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `route_title` VARCHAR(150) NOT NULL,
  `vehicle_id` INT UNSIGNED DEFAULT NULL,
  `start_point` VARCHAR(150) DEFAULT NULL,
  `end_point` VARCHAR(150) DEFAULT NULL,
  `fare` DECIMAL(10,2) DEFAULT '0.00',
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_routes_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_stops` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `route_id` INT UNSIGNED NOT NULL,
  `stop_name` VARCHAR(150) NOT NULL,
  `pickup_time` TIME DEFAULT NULL,
  `drop_time` TIME DEFAULT NULL,
  `fare` DECIMAL(10,2) DEFAULT '0.00',
  `order_no` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stops_route` (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_allocations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `route_id` INT UNSIGNED NOT NULL,
  `stop_id` INT UNSIGNED DEFAULT NULL,
  `academic_year_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alloc_student` (`student_id`),
  KEY `idx_alloc_route` (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Hostel Management
CREATE TABLE IF NOT EXISTS `hostels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `type` ENUM('boys', 'girls', 'co_ed') NOT NULL DEFAULT 'boys',
  `address` TEXT DEFAULT NULL,
  `intake_capacity` INT DEFAULT 100,
  `warden_name` VARCHAR(100) DEFAULT NULL,
  `warden_phone` VARCHAR(30) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hostels_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hostel_rooms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `hostel_id` INT UNSIGNED NOT NULL,
  `room_no` VARCHAR(50) NOT NULL,
  `room_type` ENUM('ac', 'non_ac', 'single', 'double', 'dormitory') NOT NULL DEFAULT 'non_ac',
  `number_of_beds` INT NOT NULL DEFAULT 4,
  `cost_per_bed` DECIMAL(10,2) DEFAULT '0.00',
  `description` TEXT DEFAULT NULL,
  `status` ENUM('available', 'full', 'maintenance') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rooms_hostel` (`hostel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hostel_allocations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `hostel_id` INT UNSIGNED NOT NULL,
  `room_id` INT UNSIGNED NOT NULL,
  `bed_number` VARCHAR(20) DEFAULT NULL,
  `academic_year_id` INT UNSIGNED DEFAULT NULL,
  `checkin_date` DATE NOT NULL,
  `checkout_date` DATE DEFAULT NULL,
  `status` ENUM('active', 'vacated') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hostel_alloc_student` (`student_id`),
  KEY `idx_hostel_alloc_room` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Inventory Management
CREATE TABLE IF NOT EXISTS `inventory_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_cat_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_suppliers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_sup_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `supplier_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `unit` VARCHAR(30) DEFAULT 'pcs',
  `unit_price` DECIMAL(10,2) DEFAULT '0.00',
  `quantity` INT NOT NULL DEFAULT 0,
  `available_quantity` INT NOT NULL DEFAULT 0,
  `min_quantity_alert` INT DEFAULT 5,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_items_school` (`school_id`),
  KEY `idx_inv_items_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NOT NULL,
  `issued_to_type` ENUM('staff', 'department', 'classroom') NOT NULL DEFAULT 'staff',
  `issued_to_id` INT UNSIGNED DEFAULT NULL,
  `issued_to_name` VARCHAR(150) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `issue_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `status` ENUM('issued', 'returned', 'consumed') NOT NULL DEFAULT 'issued',
  `remarks` TEXT DEFAULT NULL,
  `issued_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_issue_school` (`school_id`),
  KEY `idx_inv_issue_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Visitor Management
CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `visitor_name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `id_proof_type` VARCHAR(50) DEFAULT 'National ID',
  `id_proof_number` VARCHAR(100) DEFAULT NULL,
  `purpose` VARCHAR(255) NOT NULL,
  `to_meet_user_id` INT UNSIGNED DEFAULT NULL,
  `to_meet_name` VARCHAR(120) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `number_of_persons` INT DEFAULT 1,
  `visitor_card_no` VARCHAR(50) DEFAULT NULL,
  `in_time` DATETIME NOT NULL,
  `out_time` DATETIME DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `status` ENUM('inside', 'exited') NOT NULL DEFAULT 'inside',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visitors_school` (`school_id`),
  KEY `idx_visitors_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Certificate & ID Cards
CREATE TABLE IF NOT EXISTS `issued_certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `certificate_type` VARCHAR(50) NOT NULL,
  `certificate_no` VARCHAR(100) NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED DEFAULT NULL,
  `issue_date` DATE NOT NULL,
  `data_json` LONGTEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('issued', 'cancelled') NOT NULL DEFAULT 'issued',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cert_school` (`school_id`),
  KEY `idx_cert_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Master Data Hub
CREATE TABLE IF NOT EXISTS `master_data` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_master_school_cat` (`school_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Seed Leave Permissions
INSERT IGNORE INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES 
('leave', 'view', 'leave.view', 'View leave requests'),
('leave', 'apply', 'leave.apply', 'Apply for staff leave'),
('leave', 'manage', 'leave.manage', 'Manage and approve leaves');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE p.module = 'leave' AND r.slug IN ('school_admin', 'principal', 'super_admin');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE p.slug IN ('leave.view', 'leave.apply') AND r.slug IN ('teacher', 'accountant', 'librarian', 'transport_manager', 'staff');

-- ============================================
-- Seed: Default Permissions
-- ============================================

-- Dashboard
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('dashboard', 'view', 'dashboard.view', 'View dashboard');

-- Users
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('users', 'view', 'users.view', 'View user list'),
('users', 'create', 'users.create', 'Create new users'),
('users', 'edit', 'users.edit', 'Edit user details'),
('users', 'delete', 'users.delete', 'Delete users'),
('users', 'assign_role', 'users.assign_role', 'Assign roles to users');

-- Schools (Super Admin)
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('schools', 'view', 'schools.view', 'View schools list'),
('schools', 'create', 'schools.create', 'Create new schools'),
('schools', 'edit', 'schools.edit', 'Edit school details'),
('schools', 'delete', 'schools.delete', 'Delete schools'),
('schools', 'manage_subscription', 'schools.manage_subscription', 'Manage school subscriptions');

-- School Setup
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('school_setup', 'view', 'school_setup.view', 'View school setup'),
('school_setup', 'edit', 'school_setup.edit', 'Edit school settings'),
('academic', 'view', 'academic.view', 'View academic configuration'),
('academic', 'manage', 'academic.manage', 'Manage classes, sections, subjects'),
('masters', 'view', 'masters.view', 'View master data'),
('masters', 'manage', 'masters.manage', 'Manage master data');

-- Students
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('students', 'view', 'students.view', 'View student list'),
('students', 'create', 'students.create', 'Admit new students'),
('students', 'edit', 'students.edit', 'Edit student details'),
('students', 'delete', 'students.delete', 'Delete student records'),
('students', 'promote', 'students.promote', 'Promote students'),
('students', 'transfer', 'students.transfer', 'Transfer students'),
('students', 'import', 'students.import', 'Import student data');

-- Staff
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('staff', 'view', 'staff.view', 'View staff list'),
('staff', 'create', 'staff.create', 'Add new staff'),
('staff', 'edit', 'staff.edit', 'Edit staff details'),
('staff', 'delete', 'staff.delete', 'Delete staff records'),
('leave', 'view', 'leave.view', 'View leave requests'),
('leave', 'apply', 'leave.apply', 'Apply for staff leave'),
('leave', 'manage', 'leave.manage', 'Manage and approve leaves');

-- Attendance
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('attendance', 'view', 'attendance.view', 'View attendance records'),
('attendance', 'mark', 'attendance.mark', 'Mark student attendance'),
('attendance', 'report', 'attendance.report', 'View attendance reports'),
('staff_attendance', 'view', 'staff_attendance.view', 'View staff attendance'),
('staff_attendance', 'mark', 'staff_attendance.mark', 'Mark staff attendance');

-- Timetable
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('timetable', 'view', 'timetable.view', 'View timetable'),
('timetable', 'manage', 'timetable.manage', 'Create/edit timetable');

-- Exams
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('exams', 'view', 'exams.view', 'View exam schedules'),
('exams', 'manage', 'exams.manage', 'Create/edit exams'),
('marks', 'view', 'marks.view', 'View marks/results'),
('marks', 'entry', 'marks.entry', 'Enter student marks'),
('marks', 'report_card', 'marks.report_card', 'Generate report cards');

-- Fees
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('fees', 'view', 'fees.view', 'View fee structure'),
('fees', 'manage', 'fees.manage', 'Manage fee structure'),
('fees', 'collect', 'fees.collect', 'Collect fees'),
('fees', 'report', 'fees.report', 'View fee reports');

-- Payroll
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('payroll', 'view', 'payroll.view', 'View payroll'),
('payroll', 'process', 'payroll.process', 'Process payroll'),
('payroll', 'report', 'payroll.report', 'View payroll reports');

-- Homework
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('homework', 'view', 'homework.view', 'View homework'),
('homework', 'create', 'homework.create', 'Create homework'),
('homework', 'submit', 'homework.submit', 'Submit homework');

-- Communication
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('communication', 'view', 'communication.view', 'View messages'),
('communication', 'send', 'communication.send', 'Send messages/broadcasts');

-- Library
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('library', 'view', 'library.view', 'View library catalog'),
('library', 'manage', 'library.manage', 'Manage books, issue/return');

-- Transport
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('transport', 'view', 'transport.view', 'View transport details'),
('transport', 'manage', 'transport.manage', 'Manage transport');

-- Hostel
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('hostel', 'view', 'hostel.view', 'View hostel details'),
('hostel', 'manage', 'hostel.manage', 'Manage hostel');

-- Inventory
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('inventory', 'view', 'inventory.view', 'View inventory'),
('inventory', 'manage', 'inventory.manage', 'Manage inventory');

-- Visitors
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('visitors', 'view', 'visitors.view', 'View visitor log'),
('visitors', 'manage', 'visitors.manage', 'Manage visitors');

-- Certificates
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('certificates', 'view', 'certificates.view', 'View certificates'),
('certificates', 'generate', 'certificates.generate', 'Generate certificates');

-- Reports
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('reports', 'view', 'reports.view', 'View reports'),
('reports', 'export', 'reports.export', 'Export reports');

-- Settings
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('settings', 'view', 'settings.view', 'View settings'),
('settings', 'manage', 'settings.manage', 'Manage settings');

-- Activity Logs
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('activity_logs', 'view', 'activity_logs.view', 'View activity logs');

-- ============================================
-- Assign permissions to roles
-- ============================================

-- School Admin gets all school-level permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'school_admin'),
    p.id
FROM permissions p
WHERE p.module NOT IN ('schools');

-- Principal gets academic + staff oversight permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'principal'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'users.view',
    'students.view', 'students.create', 'students.edit', 'students.promote', 'students.transfer',
    'staff.view',
    'attendance.view', 'attendance.mark', 'attendance.report',
    'staff_attendance.view', 'staff_attendance.mark',
    'timetable.view', 'timetable.manage',
    'exams.view', 'exams.manage', 'marks.view', 'marks.report_card',
    'fees.view', 'fees.report',
    'homework.view',
    'communication.view', 'communication.send',
    'reports.view', 'reports.export',
    'certificates.view', 'certificates.generate',
    'activity_logs.view'
);

-- Teacher permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'teacher'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'attendance.view', 'attendance.mark',
    'timetable.view',
    'exams.view', 'marks.view', 'marks.entry',
    'homework.view', 'homework.create',
    'communication.view', 'communication.send',
    'library.view'
);

-- Student permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'student'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'attendance.view',
    'timetable.view',
    'exams.view', 'marks.view',
    'fees.view',
    'homework.view', 'homework.submit',
    'communication.view',
    'library.view',
    'transport.view',
    'hostel.view'
);

-- Parent permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'parent_user'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'attendance.view',
    'timetable.view',
    'exams.view', 'marks.view',
    'fees.view',
    'homework.view',
    'communication.view',
    'transport.view'
);

-- Accountant permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'accountant'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'fees.view', 'fees.manage', 'fees.collect', 'fees.report',
    'payroll.view', 'payroll.process', 'payroll.report',
    'reports.view', 'reports.export'
);

-- Librarian permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'librarian'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'library.view', 'library.manage',
    'reports.view'
);

-- Transport Manager permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'transport_manager'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'transport.view', 'transport.manage',
    'reports.view'
);
-- ============================================
-- Seed: Default Subscription Plans
-- ============================================
-- Two pricing types:
--   fixed       = flat monthly/yearly fee
--   per_student = rate × number of students per month/year

INSERT INTO `plans` (`name`, `slug`, `description`, `pricing_type`, `price_monthly`, `price_yearly`, `price_per_student_monthly`, `price_per_student_yearly`, `min_students`, `max_students_limit`, `max_students`, `max_staff`, `max_branches`, `features`, `is_active`, `sort_order`) VALUES
(
    'Free Trial',
    'free',
    '14-day free trial with basic features. Perfect for evaluating the platform.',
    'fixed',
    0.00,
    0.00,
    0.00,
    0.00,
    0,
    50,
    50,
    10,
    1,
    '["dashboard","students","staff","attendance","timetable","communication"]',
    1,
    1
),
(
    'Starter',
    'starter',
    'Flat-rate plan for small schools up to 200 students. All essential features included.',
    'fixed',
    1499.00,
    14990.00,
    0.00,
    0.00,
    0,
    200,
    200,
    30,
    1,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","reports"]',
    1,
    2
),
(
    'Growth',
    'growth',
    'Pay per student — scales with your school. Ideal for growing schools with 100–1000 students.',
    'per_student',
    0.00,
    0.00,
    15.00,
    150.00,
    100,
    1000,
    1000,
    100,
    2,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","library","transport","visitors","certificates","reports"]',
    1,
    3
),
(
    'Premium',
    'premium',
    'Pay per student with all features unlocked. Best value for large schools with 200+ students.',
    'per_student',
    0.00,
    0.00,
    10.00,
    100.00,
    200,
    5000,
    5000,
    500,
    5,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","library","transport","hostel","inventory","visitors","certificates","reports","mobile_app","payroll"]',
    1,
    4
),
(
    'Enterprise',
    'enterprise',
    'Flat-rate unlimited access with AI features, priority support, custom branding, and API access.',
    'fixed',
    9999.00,
    99990.00,
    0.00,
    0.00,
    0,
    0,
    0,
    0,
    0,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","library","transport","hostel","inventory","visitors","certificates","reports","mobile_app","payroll","ai_features","data_migration","priority_support","custom_branding","api_access"]',
    1,
    5
);
-- ============================================
-- Seed: Default Roles
-- ============================================

INSERT INTO `roles` (`name`, `slug`, `description`, `is_system`, `school_id`) VALUES
('Super Admin', 'super_admin', 'Platform owner with full system access. Manages schools, subscriptions, and platform settings.', 1, NULL),
('School Admin', 'school_admin', 'School-level administrator with full access to their school.', 1, NULL),
('Principal', 'principal', 'School principal with oversight of academic and administrative functions.', 1, NULL),
('Staff', 'staff', 'Non-teaching staff member with limited module access.', 1, NULL),
('Teacher', 'teacher', 'Teaching staff with access to academic modules.', 1, NULL),
('Student', 'student', 'Student with view-only access to their own data.', 1, NULL),
('Parent', 'parent_user', 'Parent/Guardian with access to their child\'s information.', 1, NULL),
('Accountant', 'accountant', 'Financial staff managing fees, payroll, and accounts.', 1, NULL),
('Librarian', 'librarian', 'Library staff managing books, issue/return.', 1, NULL),
('Transport Manager', 'transport_manager', 'Transport department staff managing buses, routes, and drivers.', 1, NULL);
