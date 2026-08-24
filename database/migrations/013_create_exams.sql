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
