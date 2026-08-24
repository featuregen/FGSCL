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
