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
