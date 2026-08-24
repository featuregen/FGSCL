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
