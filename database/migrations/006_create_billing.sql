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

