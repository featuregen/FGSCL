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
