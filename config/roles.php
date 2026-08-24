<?php
/**
 * Default Role Definitions
 * Used for seeding the database with default roles
 */

return [
    [
        'name'        => 'Super Admin',
        'slug'        => 'super_admin',
        'description' => 'Platform owner with full system access. Manages schools, subscriptions, and platform settings.',
        'is_system'   => true,
    ],
    [
        'name'        => 'School Admin',
        'slug'        => 'school_admin',
        'description' => 'School-level administrator with full access to their school.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Principal',
        'slug'        => 'principal',
        'description' => 'School principal with oversight of academic and administrative functions.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Staff',
        'slug'        => 'staff',
        'description' => 'Non-teaching staff member with limited module access.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Teacher',
        'slug'        => 'teacher',
        'description' => 'Teaching staff with access to academic modules.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Student',
        'slug'        => 'student',
        'description' => 'Student with view-only access to their own data.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Parent',
        'slug'        => 'parent_user',
        'description' => 'Parent/Guardian with access to their child\'s information.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Accountant',
        'slug'        => 'accountant',
        'description' => 'Financial staff managing fees, payroll, and accounts.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Librarian',
        'slug'        => 'librarian',
        'description' => 'Library staff managing books, issue/return.',
        'is_system'   => true,
    ],
    [
        'name'        => 'Transport Manager',
        'slug'        => 'transport_manager',
        'description' => 'Transport department staff managing buses, routes, and drivers.',
        'is_system'   => true,
    ],
];
