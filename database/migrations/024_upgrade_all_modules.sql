-- ======================================================
-- Migration 024: Full Schema Upgrade for All 9 Modules
-- ======================================================

-- 1. Leave Management
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) DEFAULT NULL,
  `days_per_year` INT NOT NULL DEFAULT 12,
  `is_paid` TINYINT(1) NOT NULL DEFAULT 1,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_types_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE NOT NULL,
  `total_days` DECIMAL(4,1) NOT NULL DEFAULT 1.0,
  `reason` TEXT NOT NULL,
  `document` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `action_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_req_school` (`school_id`),
  KEY `idx_leave_req_user` (`user_id`),
  KEY `idx_leave_req_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Library Management
CREATE TABLE IF NOT EXISTS `library_books` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `author` VARCHAR(200) NOT NULL,
  `isbn` VARCHAR(50) DEFAULT NULL,
  `publisher` VARCHAR(150) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'General',
  `rack_no` VARCHAR(50) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `available_quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) DEFAULT '0.00',
  `edition` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_books_school` (`school_id`),
  KEY `idx_books_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `library_issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `book_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `fine_amount` DECIMAL(10,2) DEFAULT '0.00',
  `fine_paid` DECIMAL(10,2) DEFAULT '0.00',
  `status` ENUM('issued', 'returned', 'overdue', 'lost') NOT NULL DEFAULT 'issued',
  `remarks` TEXT DEFAULT NULL,
  `issued_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_issues_school` (`school_id`),
  KEY `idx_issues_book` (`book_id`),
  KEY `idx_issues_user` (`user_id`),
  KEY `idx_issues_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Transport Management
CREATE TABLE IF NOT EXISTS `transport_vehicles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `vehicle_no` VARCHAR(50) NOT NULL,
  `model` VARCHAR(100) DEFAULT NULL,
  `capacity` INT NOT NULL DEFAULT 30,
  `driver_name` VARCHAR(100) DEFAULT NULL,
  `driver_phone` VARCHAR(30) DEFAULT NULL,
  `driver_license` VARCHAR(100) DEFAULT NULL,
  `insurance_expiry` DATE DEFAULT NULL,
  `fitness_expiry` DATE DEFAULT NULL,
  `status` ENUM('active', 'maintenance', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicles_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_routes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `route_title` VARCHAR(150) NOT NULL,
  `vehicle_id` INT UNSIGNED DEFAULT NULL,
  `start_point` VARCHAR(150) DEFAULT NULL,
  `end_point` VARCHAR(150) DEFAULT NULL,
  `fare` DECIMAL(10,2) DEFAULT '0.00',
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_routes_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_stops` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `route_id` INT UNSIGNED NOT NULL,
  `stop_name` VARCHAR(150) NOT NULL,
  `pickup_time` TIME DEFAULT NULL,
  `drop_time` TIME DEFAULT NULL,
  `fare` DECIMAL(10,2) DEFAULT '0.00',
  `order_no` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stops_route` (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_allocations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `route_id` INT UNSIGNED NOT NULL,
  `stop_id` INT UNSIGNED DEFAULT NULL,
  `academic_year_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alloc_student` (`student_id`),
  KEY `idx_alloc_route` (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Hostel Management
CREATE TABLE IF NOT EXISTS `hostels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `type` ENUM('boys', 'girls', 'co_ed') NOT NULL DEFAULT 'boys',
  `address` TEXT DEFAULT NULL,
  `intake_capacity` INT DEFAULT 100,
  `warden_name` VARCHAR(100) DEFAULT NULL,
  `warden_phone` VARCHAR(30) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hostels_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hostel_rooms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `hostel_id` INT UNSIGNED NOT NULL,
  `room_no` VARCHAR(50) NOT NULL,
  `room_type` ENUM('ac', 'non_ac', 'single', 'double', 'dormitory') NOT NULL DEFAULT 'non_ac',
  `number_of_beds` INT NOT NULL DEFAULT 4,
  `cost_per_bed` DECIMAL(10,2) DEFAULT '0.00',
  `description` TEXT DEFAULT NULL,
  `status` ENUM('available', 'full', 'maintenance') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rooms_hostel` (`hostel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hostel_allocations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `hostel_id` INT UNSIGNED NOT NULL,
  `room_id` INT UNSIGNED NOT NULL,
  `bed_number` VARCHAR(20) DEFAULT NULL,
  `academic_year_id` INT UNSIGNED DEFAULT NULL,
  `checkin_date` DATE NOT NULL,
  `checkout_date` DATE DEFAULT NULL,
  `status` ENUM('active', 'vacated') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hostel_alloc_student` (`student_id`),
  KEY `idx_hostel_alloc_room` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Inventory Management
CREATE TABLE IF NOT EXISTS `inventory_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_cat_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_suppliers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_sup_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `supplier_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `unit` VARCHAR(30) DEFAULT 'pcs',
  `unit_price` DECIMAL(10,2) DEFAULT '0.00',
  `quantity` INT NOT NULL DEFAULT 0,
  `available_quantity` INT NOT NULL DEFAULT 0,
  `min_quantity_alert` INT DEFAULT 5,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_items_school` (`school_id`),
  KEY `idx_inv_items_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NOT NULL,
  `issued_to_type` ENUM('staff', 'department', 'classroom') NOT NULL DEFAULT 'staff',
  `issued_to_id` INT UNSIGNED DEFAULT NULL,
  `issued_to_name` VARCHAR(150) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `issue_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `status` ENUM('issued', 'returned', 'consumed') NOT NULL DEFAULT 'issued',
  `remarks` TEXT DEFAULT NULL,
  `issued_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_issue_school` (`school_id`),
  KEY `idx_inv_issue_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Visitor Management
CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `visitor_name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `id_proof_type` VARCHAR(50) DEFAULT 'National ID',
  `id_proof_number` VARCHAR(100) DEFAULT NULL,
  `purpose` VARCHAR(255) NOT NULL,
  `to_meet_user_id` INT UNSIGNED DEFAULT NULL,
  `to_meet_name` VARCHAR(120) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `number_of_persons` INT DEFAULT 1,
  `visitor_card_no` VARCHAR(50) DEFAULT NULL,
  `in_time` DATETIME NOT NULL,
  `out_time` DATETIME DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `status` ENUM('inside', 'exited') NOT NULL DEFAULT 'inside',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visitors_school` (`school_id`),
  KEY `idx_visitors_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Certificate & ID Cards
CREATE TABLE IF NOT EXISTS `issued_certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `certificate_type` VARCHAR(50) NOT NULL,
  `certificate_no` VARCHAR(100) NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED DEFAULT NULL,
  `issue_date` DATE NOT NULL,
  `data_json` LONGTEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('issued', 'cancelled') NOT NULL DEFAULT 'issued',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cert_school` (`school_id`),
  KEY `idx_cert_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Master Data Hub
CREATE TABLE IF NOT EXISTS `master_data` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_master_school_cat` (`school_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Seed Leave Permissions
INSERT IGNORE INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES 
('leave', 'view', 'leave.view', 'View leave requests'),
('leave', 'apply', 'leave.apply', 'Apply for staff leave'),
('leave', 'manage', 'leave.manage', 'Manage and approve leaves');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE p.module = 'leave' AND r.slug IN ('school_admin', 'principal', 'super_admin');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE p.slug IN ('leave.view', 'leave.apply') AND r.slug IN ('teacher', 'accountant', 'librarian', 'transport_manager', 'staff');

