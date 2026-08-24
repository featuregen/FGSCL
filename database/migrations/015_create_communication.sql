-- Communication & Alerts Module Tables
-- Migration: 015_create_communication.sql

-- Communications (Broadcast messages, SMS, Notices)
CREATE TABLE IF NOT EXISTS communications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    type ENUM('email', 'sms', 'notice', 'push') NOT NULL DEFAULT 'notice',
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    target_roles JSON NOT NULL, -- e.g. ["student", "parent", "teacher"]
    target_classes JSON DEFAULT NULL, -- e.g. [1, 2, 3] or null for all
    sent_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Communication Recipients (Tracking read status and delivery)
CREATE TABLE IF NOT EXISTS communication_recipients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    communication_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'read') DEFAULT 'pending',
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (communication_id) REFERENCES communications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_comm_recipient (communication_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
