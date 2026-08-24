<?php
/**
 * Main Application Layout
 * Sidebar + Header + Content Area
 */

$user = Session::user() ?? [];
$role = $user['role_slug'] ?? $user['user_type'] ?? '';
$schoolName = $user['school_name'] ?? APP_NAME;
$schoolLogo = !empty($user['school_logo']) ? APP_URL . '/uploads/logos/' . $user['school_logo'] : '';
$primaryColor = $user['primary_color'] ?? '#1f9e8b';
$userInitials = '';
if (!empty($user['full_name'])) {
    $parts = explode(' ', $user['full_name']);
    $userInitials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<script>
    // Apply theme early to prevent flash
    (function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'EduGen - Complete School Management System' ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= htmlspecialchars($schoolName) ?></title>
    
    <!-- Dynamic brand color -->
    <style>:root { --primary: <?= htmlspecialchars($primaryColor) ?>; }</style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/images/favicon.png">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/images/favicon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <?php require VIEW_PATH . '/partials/sidebar.php'; ?>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Main Content Area -->
        <div class="app-content" id="appContent">
            <!-- Header -->
            <?php require VIEW_PATH . '/partials/header.php'; ?>
            
            <!-- Page Content -->
            <main class="main-content">
                <!-- Alerts -->
                <?php require VIEW_PATH . '/partials/alerts.php'; ?>
                
                <!-- Breadcrumb -->
                <?php if (!empty($breadcrumb)): ?>
                    <?php require VIEW_PATH . '/partials/breadcrumb.php'; ?>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
    
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= APP_URL ?>/assets/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($inlineJs)): ?>
        <script><?= $inlineJs ?></script>
    <?php endif; ?>
</body>
</html>
