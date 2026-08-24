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
