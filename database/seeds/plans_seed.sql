-- ============================================
-- Seed: Default Subscription Plans
-- ============================================
-- Two pricing types:
--   fixed       = flat monthly/yearly fee
--   per_student = rate × number of students per month/year

INSERT INTO `plans` (`name`, `slug`, `description`, `pricing_type`, `price_monthly`, `price_yearly`, `price_per_student_monthly`, `price_per_student_yearly`, `min_students`, `max_students_limit`, `max_students`, `max_staff`, `max_branches`, `features`, `is_active`, `sort_order`) VALUES
(
    'Free Trial',
    'free',
    '14-day free trial with basic features. Perfect for evaluating the platform.',
    'fixed',
    0.00,
    0.00,
    0.00,
    0.00,
    0,
    50,
    50,
    10,
    1,
    '["dashboard","students","staff","attendance","timetable","communication"]',
    1,
    1
),
(
    'Starter',
    'starter',
    'Flat-rate plan for small schools up to 200 students. All essential features included.',
    'fixed',
    1499.00,
    14990.00,
    0.00,
    0.00,
    0,
    200,
    200,
    30,
    1,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","reports"]',
    1,
    2
),
(
    'Growth',
    'growth',
    'Pay per student — scales with your school. Ideal for growing schools with 100–1000 students.',
    'per_student',
    0.00,
    0.00,
    15.00,
    150.00,
    100,
    1000,
    1000,
    100,
    2,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","library","transport","visitors","certificates","reports"]',
    1,
    3
),
(
    'Premium',
    'premium',
    'Pay per student with all features unlocked. Best value for large schools with 200+ students.',
    'per_student',
    0.00,
    0.00,
    10.00,
    100.00,
    200,
    5000,
    5000,
    500,
    5,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","library","transport","hostel","inventory","visitors","certificates","reports","mobile_app","payroll"]',
    1,
    4
),
(
    'Enterprise',
    'enterprise',
    'Flat-rate unlimited access with AI features, priority support, custom branding, and API access.',
    'fixed',
    9999.00,
    99990.00,
    0.00,
    0.00,
    0,
    0,
    0,
    0,
    0,
    '["dashboard","students","staff","attendance","timetable","exams","fees","homework","communication","library","transport","hostel","inventory","visitors","certificates","reports","mobile_app","payroll","ai_features","data_migration","priority_support","custom_branding","api_access"]',
    1,
    5
);
