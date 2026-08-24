-- Fee Management Tables
-- Migration: 012_create_fee_tables.sql

-- Fee Heads (categories like Tuition, Transport, Lab)
CREATE TABLE IF NOT EXISTS fee_heads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) DEFAULT NULL,
    type ENUM('mandatory','optional') DEFAULT 'mandatory',
    is_recurring TINYINT(1) DEFAULT 1,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_fee_head (school_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Structures (amounts per class per academic year)
CREATE TABLE IF NOT EXISTS fee_structures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    fee_head_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frequency ENUM('monthly','quarterly','half_yearly','yearly','one_time') DEFAULT 'monthly',
    due_day INT DEFAULT 10,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE CASCADE,
    UNIQUE KEY uk_fee_structure (school_id, academic_year_id, class_id, fee_head_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Discounts
CREATE TABLE IF NOT EXISTS fee_discounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('percentage','fixed') DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    applicable_heads JSON DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Payments (actual transactions)
CREATE TABLE IF NOT EXISTS fee_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    receipt_number VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_mode ENUM('cash','cheque','online','upi','bank_transfer') DEFAULT 'cash',
    transaction_ref VARCHAR(100) DEFAULT NULL,
    collected_by INT UNSIGNED DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    academic_year_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (collected_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_receipt (school_id, receipt_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fee Payment Items (line items per payment)
CREATE TABLE IF NOT EXISTS fee_payment_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fee_payment_id INT UNSIGNED NOT NULL,
    fee_head_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    period_label VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (fee_payment_id) REFERENCES fee_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Fee Concessions (per-student discounts)
CREATE TABLE IF NOT EXISTS student_fee_concessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    fee_discount_id INT UNSIGNED NOT NULL,
    school_id INT UNSIGNED NOT NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_discount_id) REFERENCES fee_discounts(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_concession (student_id, fee_discount_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
