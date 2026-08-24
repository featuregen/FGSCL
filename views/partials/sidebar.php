<?php
/**
 * Sidebar Navigation
 * Dynamic menu based on user role and permissions
 */

$segments = $GLOBALS['_segments'] ?? $segments ?? [];
$currentModule = $segments[0] ?? 'dashboard';

// Build navigation based on role
$navigation = [];

// Dashboard — visible to all
$navigation[] = [
    'section' => 'Main',
    'items' => [
        ['icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view'],
    ],
];

// Super Admin menus
if ($role === ROLE_SUPER_ADMIN) {
    $navigation[] = [
        'section' => 'Platform',
        'items' => [
            ['icon' => 'bi-building', 'label' => 'Schools', 'route' => 'schools', 'permission' => 'schools.view'],
            ['icon' => 'bi-credit-card', 'label' => 'Subscriptions', 'route' => 'subscriptions', 'permission' => 'schools.manage_subscription'],
            ['icon' => 'bi-people-fill', 'label' => 'All Users', 'route' => 'users', 'permission' => 'users.view'],
        ],
    ];
}

// School-level menus
if (in_array($role, [ROLE_SUPER_ADMIN, ROLE_SCHOOL_ADMIN, ROLE_PRINCIPAL, ROLE_TEACHER, ROLE_ACCOUNTANT, ROLE_LIBRARIAN, ROLE_TRANSPORT_MANAGER, ROLE_STAFF])) {
    // Setup
    if (Session::hasPermission('school_setup.view') || Session::hasPermission('academic.view')) {
        $navigation[] = [
            'section' => 'Setup',
            'items' => [
                ['icon' => 'bi-gear-fill', 'label' => 'School Setup', 'route' => 'school-setup', 'permission' => 'school_setup.view'],
                ['icon' => 'bi-calendar3', 'label' => 'Academic', 'route' => 'academic', 'permission' => 'academic.view'],
                ['icon' => 'bi-database', 'label' => 'Master Data', 'route' => 'masters', 'permission' => 'masters.view'],
            ],
        ];
    }

    // People
    $peopleItems = [];
    if (Session::hasPermission('students.view')) $peopleItems[] = ['icon' => 'bi-mortarboard-fill', 'label' => 'Students', 'route' => 'students', 'permission' => 'students.view'];
    if (Session::hasPermission('staff.view')) $peopleItems[] = ['icon' => 'bi-person-badge-fill', 'label' => 'Staff', 'route' => 'staff', 'permission' => 'staff.view'];
    if (Session::hasPermission('users.view')) $peopleItems[] = ['icon' => 'bi-people-fill', 'label' => 'Users', 'route' => 'users', 'permission' => 'users.view'];
    if (!empty($peopleItems)) {
        $navigation[] = ['section' => 'People', 'items' => $peopleItems];
    }

    // Academics
    $academicItems = [];
    if (Session::hasPermission('attendance.view')) $academicItems[] = ['icon' => 'bi-clipboard-check', 'label' => 'Attendance', 'route' => 'attendance', 'permission' => 'attendance.view'];
    if (Session::hasPermission('timetable.view')) $academicItems[] = ['icon' => 'bi-calendar-week', 'label' => 'Timetable', 'route' => 'timetable', 'permission' => 'timetable.view'];
    if (Session::hasPermission('exams.view')) $academicItems[] = ['icon' => 'bi-journal-text', 'label' => 'Exams', 'route' => 'exams', 'permission' => 'exams.view'];
    if (Session::hasPermission('homework.view')) $academicItems[] = ['icon' => 'bi-book-half', 'label' => 'Homework', 'route' => 'homework', 'permission' => 'homework.view'];
    if (!empty($academicItems)) {
        $navigation[] = ['section' => 'Academics', 'items' => $academicItems];
    }

    // Finance
    $financeItems = [];
    if (Session::hasPermission('fees.view')) $financeItems[] = ['icon' => 'bi-wallet2', 'label' => 'Fees', 'route' => 'fees', 'permission' => 'fees.view'];
    if (Session::hasPermission('payroll.view')) $financeItems[] = ['icon' => 'bi-cash-stack', 'label' => 'Payroll', 'route' => 'payroll', 'permission' => 'payroll.view'];
    if (!empty($financeItems)) {
        $navigation[] = ['section' => 'Finance', 'items' => $financeItems];
    }

    // Resources
    $resourceItems = [];
    if (Session::hasPermission('library.view')) $resourceItems[] = ['icon' => 'bi-book', 'label' => 'Library', 'route' => 'library', 'permission' => 'library.view'];
    if (Session::hasPermission('transport.view')) $resourceItems[] = ['icon' => 'bi-bus-front', 'label' => 'Transport', 'route' => 'transport', 'permission' => 'transport.view'];
    if (Session::hasPermission('hostel.view')) $resourceItems[] = ['icon' => 'bi-house-door', 'label' => 'Hostel', 'route' => 'hostel', 'permission' => 'hostel.view'];
    if (Session::hasPermission('inventory.view')) $resourceItems[] = ['icon' => 'bi-box-seam', 'label' => 'Inventory', 'route' => 'inventory', 'permission' => 'inventory.view'];
    if (!empty($resourceItems)) {
        $navigation[] = ['section' => 'Resources', 'items' => $resourceItems];
    }

    // Communication
    $commItems = [];
    if (Session::hasPermission('communication.view')) $commItems[] = ['icon' => 'bi-megaphone-fill', 'label' => 'Communication', 'route' => 'communication', 'permission' => 'communication.view'];
    if (Session::hasPermission('visitors.view')) $commItems[] = ['icon' => 'bi-person-walking', 'label' => 'Visitors', 'route' => 'visitors', 'permission' => 'visitors.view'];
    if (!empty($commItems)) {
        $navigation[] = ['section' => 'Communication', 'items' => $commItems];
    }

    // Reports
    $reportItems = [];
    if (Session::hasPermission('reports.view')) $reportItems[] = ['icon' => 'bi-bar-chart-line-fill', 'label' => 'Reports', 'route' => 'reports', 'permission' => 'reports.view'];
    if (Session::hasPermission('certificates.view')) $reportItems[] = ['icon' => 'bi-award', 'label' => 'Certificates', 'route' => 'certificates', 'permission' => 'certificates.view'];
    if (!empty($reportItems)) {
        $navigation[] = ['section' => 'Reports', 'items' => $reportItems];
    }
}

// Student/Parent minimal menu
if (in_array($role, [ROLE_STUDENT, ROLE_PARENT])) {
    $navigation[] = [
        'section' => 'My School',
        'items' => [
            ['icon' => 'bi-clipboard-check', 'label' => 'Attendance', 'route' => 'attendance', 'permission' => 'attendance.view'],
            ['icon' => 'bi-calendar-week', 'label' => 'Timetable', 'route' => 'timetable', 'permission' => 'timetable.view'],
            ['icon' => 'bi-journal-text', 'label' => 'Exams & Results', 'route' => 'exams', 'permission' => 'exams.view'],
            ['icon' => 'bi-wallet2', 'label' => 'Fees', 'route' => 'fees', 'permission' => 'fees.view'],
            ['icon' => 'bi-book-half', 'label' => 'Homework', 'route' => 'homework', 'permission' => 'homework.view'],
        ],
    ];
}
?>

<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-logo" style="width: auto; height: 32px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: transparent;">
            <?php if ($schoolLogo): ?>
                <img src="<?= htmlspecialchars($schoolLogo) ?>" alt="Logo" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            <?php else: ?>
                <img src="<?= APP_URL ?>/public/assets/images/logo.png" alt="ClassoraGen Logo" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            <?php endif; ?>
        </div>
        <div>
            <div class="brand-text"><?= htmlspecialchars($role === ROLE_SUPER_ADMIN ? APP_NAME : ($user['school_name'] ?? APP_NAME)) ?></div>
            <div class="brand-subtitle"><?= htmlspecialchars($role === ROLE_SUPER_ADMIN ? 'Super Admin' : 'School ERP') ?></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <?php foreach ($navigation as $section): ?>
            <div class="nav-section">
                <div class="nav-section-title"><?= htmlspecialchars($section['section']) ?></div>
                <?php foreach ($section['items'] as $item): ?>
                    <?php if (Session::hasPermission($item['permission'])): ?>
                        <div class="nav-item">
                            <a href="<?= APP_URL ?>/<?= $item['route'] ?>" 
                               class="nav-link <?= $currentModule === $item['route'] ? 'active' : '' ?>">
                                <span class="nav-icon"><i class="bi <?= $item['icon'] ?>"></i></span>
                                <span class="nav-text"><?= htmlspecialchars($item['label']) ?></span>
                                <?php if (!empty($item['badge'])): ?>
                                    <span class="nav-badge"><?= $item['badge'] ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= APP_URL ?>/profile" class="sidebar-user">
            <div class="user-avatar">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= APP_URL ?>/uploads/photos/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <?= $userInitials ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></div>
                <div class="user-role"><?= htmlspecialchars($user['role_name'] ?? '') ?></div>
            </div>
        </a>
    </div>

    <!-- Powered by -->
    <div class="sidebar-powered">
        Powered by <a href="#">Featuregen</a>
    </div>
</aside>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    
    const savedScroll = sessionStorage.getItem('sidebarScrollPos');
    if (savedScroll) {
        sidebar.scrollTop = parseInt(savedScroll, 10);
    } else {
        const activeLink = sidebar.querySelector('.nav-link.active');
        if (activeLink) {
            activeLink.scrollIntoView({ block: 'center' });
        }
    }
    
    sidebar.addEventListener('scroll', () => {
        sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
    }, { passive: true });
});
</script>
