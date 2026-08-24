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
