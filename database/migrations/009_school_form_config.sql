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
