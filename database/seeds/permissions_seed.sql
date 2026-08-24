-- ============================================
-- Seed: Default Permissions
-- ============================================

-- Dashboard
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('dashboard', 'view', 'dashboard.view', 'View dashboard');

-- Users
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('users', 'view', 'users.view', 'View user list'),
('users', 'create', 'users.create', 'Create new users'),
('users', 'edit', 'users.edit', 'Edit user details'),
('users', 'delete', 'users.delete', 'Delete users'),
('users', 'assign_role', 'users.assign_role', 'Assign roles to users');

-- Schools (Super Admin)
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('schools', 'view', 'schools.view', 'View schools list'),
('schools', 'create', 'schools.create', 'Create new schools'),
('schools', 'edit', 'schools.edit', 'Edit school details'),
('schools', 'delete', 'schools.delete', 'Delete schools'),
('schools', 'manage_subscription', 'schools.manage_subscription', 'Manage school subscriptions');

-- School Setup
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('school_setup', 'view', 'school_setup.view', 'View school setup'),
('school_setup', 'edit', 'school_setup.edit', 'Edit school settings'),
('academic', 'view', 'academic.view', 'View academic configuration'),
('academic', 'manage', 'academic.manage', 'Manage classes, sections, subjects'),
('masters', 'view', 'masters.view', 'View master data'),
('masters', 'manage', 'masters.manage', 'Manage master data');

-- Students
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('students', 'view', 'students.view', 'View student list'),
('students', 'create', 'students.create', 'Admit new students'),
('students', 'edit', 'students.edit', 'Edit student details'),
('students', 'delete', 'students.delete', 'Delete student records'),
('students', 'promote', 'students.promote', 'Promote students'),
('students', 'transfer', 'students.transfer', 'Transfer students'),
('students', 'import', 'students.import', 'Import student data');

-- Staff
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('staff', 'view', 'staff.view', 'View staff list'),
('staff', 'create', 'staff.create', 'Add new staff'),
('staff', 'edit', 'staff.edit', 'Edit staff details'),
('staff', 'delete', 'staff.delete', 'Delete staff records');

-- Attendance
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('attendance', 'view', 'attendance.view', 'View attendance records'),
('attendance', 'mark', 'attendance.mark', 'Mark student attendance'),
('attendance', 'report', 'attendance.report', 'View attendance reports'),
('staff_attendance', 'view', 'staff_attendance.view', 'View staff attendance'),
('staff_attendance', 'mark', 'staff_attendance.mark', 'Mark staff attendance');

-- Timetable
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('timetable', 'view', 'timetable.view', 'View timetable'),
('timetable', 'manage', 'timetable.manage', 'Create/edit timetable');

-- Exams
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('exams', 'view', 'exams.view', 'View exam schedules'),
('exams', 'manage', 'exams.manage', 'Create/edit exams'),
('marks', 'view', 'marks.view', 'View marks/results'),
('marks', 'entry', 'marks.entry', 'Enter student marks'),
('marks', 'report_card', 'marks.report_card', 'Generate report cards');

-- Fees
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('fees', 'view', 'fees.view', 'View fee structure'),
('fees', 'manage', 'fees.manage', 'Manage fee structure'),
('fees', 'collect', 'fees.collect', 'Collect fees'),
('fees', 'report', 'fees.report', 'View fee reports');

-- Payroll
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('payroll', 'view', 'payroll.view', 'View payroll'),
('payroll', 'process', 'payroll.process', 'Process payroll'),
('payroll', 'report', 'payroll.report', 'View payroll reports');

-- Homework
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('homework', 'view', 'homework.view', 'View homework'),
('homework', 'create', 'homework.create', 'Create homework'),
('homework', 'submit', 'homework.submit', 'Submit homework');

-- Communication
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('communication', 'view', 'communication.view', 'View messages'),
('communication', 'send', 'communication.send', 'Send messages/broadcasts');

-- Library
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('library', 'view', 'library.view', 'View library catalog'),
('library', 'manage', 'library.manage', 'Manage books, issue/return');

-- Transport
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('transport', 'view', 'transport.view', 'View transport details'),
('transport', 'manage', 'transport.manage', 'Manage transport');

-- Hostel
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('hostel', 'view', 'hostel.view', 'View hostel details'),
('hostel', 'manage', 'hostel.manage', 'Manage hostel');

-- Inventory
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('inventory', 'view', 'inventory.view', 'View inventory'),
('inventory', 'manage', 'inventory.manage', 'Manage inventory');

-- Visitors
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('visitors', 'view', 'visitors.view', 'View visitor log'),
('visitors', 'manage', 'visitors.manage', 'Manage visitors');

-- Certificates
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('certificates', 'view', 'certificates.view', 'View certificates'),
('certificates', 'generate', 'certificates.generate', 'Generate certificates');

-- Reports
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('reports', 'view', 'reports.view', 'View reports'),
('reports', 'export', 'reports.export', 'Export reports');

-- Settings
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('settings', 'view', 'settings.view', 'View settings'),
('settings', 'manage', 'settings.manage', 'Manage settings');

-- Activity Logs
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('activity_logs', 'view', 'activity_logs.view', 'View activity logs');

-- ============================================
-- Assign permissions to roles
-- ============================================

-- School Admin gets all school-level permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'school_admin'),
    p.id
FROM permissions p
WHERE p.module NOT IN ('schools');

-- Principal gets academic + staff oversight permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'principal'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'users.view',
    'students.view', 'students.create', 'students.edit', 'students.promote', 'students.transfer',
    'staff.view',
    'attendance.view', 'attendance.mark', 'attendance.report',
    'staff_attendance.view', 'staff_attendance.mark',
    'timetable.view', 'timetable.manage',
    'exams.view', 'exams.manage', 'marks.view', 'marks.report_card',
    'fees.view', 'fees.report',
    'homework.view',
    'communication.view', 'communication.send',
    'reports.view', 'reports.export',
    'certificates.view', 'certificates.generate',
    'activity_logs.view'
);

-- Teacher permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'teacher'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'attendance.view', 'attendance.mark',
    'timetable.view',
    'exams.view', 'marks.view', 'marks.entry',
    'homework.view', 'homework.create',
    'communication.view', 'communication.send',
    'library.view'
);

-- Student permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'student'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'attendance.view',
    'timetable.view',
    'exams.view', 'marks.view',
    'fees.view',
    'homework.view', 'homework.submit',
    'communication.view',
    'library.view',
    'transport.view',
    'hostel.view'
);

-- Parent permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'parent_user'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'attendance.view',
    'timetable.view',
    'exams.view', 'marks.view',
    'fees.view',
    'homework.view',
    'communication.view',
    'transport.view'
);

-- Accountant permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'accountant'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'fees.view', 'fees.manage', 'fees.collect', 'fees.report',
    'payroll.view', 'payroll.process', 'payroll.report',
    'reports.view', 'reports.export'
);

-- Librarian permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'librarian'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'library.view', 'library.manage',
    'reports.view'
);

-- Transport Manager permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE slug = 'transport_manager'),
    p.id
FROM permissions p
WHERE p.slug IN (
    'dashboard.view',
    'students.view',
    'transport.view', 'transport.manage',
    'reports.view'
);
