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
