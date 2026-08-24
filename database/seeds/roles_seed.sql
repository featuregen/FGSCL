-- ============================================
-- Seed: Default Roles
-- ============================================

INSERT INTO `roles` (`name`, `slug`, `description`, `is_system`, `school_id`) VALUES
('Super Admin', 'super_admin', 'Platform owner with full system access. Manages schools, subscriptions, and platform settings.', 1, NULL),
('School Admin', 'school_admin', 'School-level administrator with full access to their school.', 1, NULL),
('Principal', 'principal', 'School principal with oversight of academic and administrative functions.', 1, NULL),
('Staff', 'staff', 'Non-teaching staff member with limited module access.', 1, NULL),
('Teacher', 'teacher', 'Teaching staff with access to academic modules.', 1, NULL),
('Student', 'student', 'Student with view-only access to their own data.', 1, NULL),
('Parent', 'parent_user', 'Parent/Guardian with access to their child\'s information.', 1, NULL),
('Accountant', 'accountant', 'Financial staff managing fees, payroll, and accounts.', 1, NULL),
('Librarian', 'librarian', 'Library staff managing books, issue/return.', 1, NULL),
('Transport Manager', 'transport_manager', 'Transport department staff managing buses, routes, and drivers.', 1, NULL);
