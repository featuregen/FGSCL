-- Payroll & HR Module Tables
-- Migration: 016_create_payroll.sql

-- Payroll Structures (Base Salary setup for each staff member)
CREATE TABLE IF NOT EXISTS payroll_structures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances_json JSON DEFAULT NULL,
    deductions_json JSON DEFAULT NULL,
    net_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_payroll_structure (school_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Monthly Payrolls (Generated Payslips)
CREATE TABLE IF NOT EXISTS payrolls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    month TINYINT NOT NULL,
    year YEAR NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances_json JSON DEFAULT NULL,
    deductions_json JSON DEFAULT NULL,
    net_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('generated', 'paid') DEFAULT 'generated',
    payment_method ENUM('cash', 'bank_transfer', 'cheque', 'upi') DEFAULT 'bank_transfer',
    payment_date DATE DEFAULT NULL,
    transaction_ref VARCHAR(100) DEFAULT NULL,
    generated_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_payroll_month (school_id, user_id, month, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
