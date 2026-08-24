<header class="app-header" id="appHeader">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
        <?php if (($pageTitle ?? 'Dashboard') !== 'Dashboard'): 
            // Determine back URL from breadcrumbs or parent route
            $backUrl = APP_URL . '/dashboard';
            if (!empty($breadcrumbs) && count($breadcrumbs) >= 1) {
                // Use the last breadcrumb that has a URL
                for ($i = count($breadcrumbs) - 1; $i >= 0; $i--) {
                    if (!empty($breadcrumbs[$i]['url'])) {
                        $backUrl = $breadcrumbs[$i]['url'];
                        break;
                    }
                }
            } else {
                // Fallback: go to parent route segment
                $segments = $GLOBALS['_segments'] ?? [];
                if (count($segments) >= 2) {
                    $backUrl = APP_URL . '/' . $segments[0];
                }
            }
        ?>
            <a href="<?= $backUrl ?>" title="Go Back" 
               style="background: none; border: 1px solid var(--gray-200); border-radius: 8px; padding: 4px 10px; cursor: pointer; color: var(--gray-500); font-size: 16px; display: flex; align-items: center; margin-right: 8px; transition: all 0.2s; text-decoration: none;"
               onmouseover="this.style.background='var(--gray-100)';this.style.color='var(--gray-700)'" 
               onmouseout="this.style.background='none';this.style.color='var(--gray-500)'">
                <i class="bi bi-arrow-left"></i>
            </a>
        <?php endif; ?>
        <h1 class="page-title-header"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    </div>
    
    <div class="header-right">
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
            <i class="bi bi-moon-fill"></i>
            <i class="bi bi-sun-fill"></i>
        </button>

        <!-- Notifications -->
        <div class="dropdown">
            <button class="header-icon-btn" id="notifBtn" title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="badge-dot"></span>
            </button>
            <div class="dropdown-menu" id="notifDropdown">
                <div style="padding: 12px 16px; border-bottom: 1px solid var(--gray-100);">
                    <strong style="font-size: 14px;">Notifications</strong>
                </div>
                <div style="padding: 20px; text-align: center; color: var(--gray-400); font-size: 13px;">
                    <i class="bi bi-bell-slash" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                    No new notifications
                </div>
            </div>
        </div>
        
        <!-- Logout -->
        <a href="<?= APP_URL ?>/auth/logout" class="header-icon-btn" title="Logout" style="color: var(--gray-500);">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</header>
