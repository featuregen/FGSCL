<!-- Default Dashboard for other roles -->
<div style="text-align: center; padding: 60px 24px;">
    <div style="width: 80px; height: 80px; border-radius: 20px; background: linear-gradient(135deg, var(--primary), var(--secondary)); 
                display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(79, 70, 229, 0.3);">
        <i class="bi bi-hand-wave" style="font-size: 36px; color: white;"></i>
    </div>
    <h2 style="font-size: 24px; font-weight: 700; color: var(--gray-800); margin-bottom: 8px;">
        Welcome, <?= htmlspecialchars($user['full_name'] ?? 'User') ?>!
    </h2>
    <p style="color: var(--gray-500); font-size: 15px; margin-bottom: 32px;">
        You're logged in as <strong><?= htmlspecialchars($user['role_name'] ?? '') ?></strong>. Use the sidebar to navigate.
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; max-width: 600px; margin: 0 auto;">
        <?php if (Session::hasPermission('attendance.view')): ?>
            <a href="<?= APP_URL ?>/attendance" class="stat-card" style="text-decoration: none; text-align: center;">
                <div class="stat-icon primary" style="margin: 0 auto 12px;"><i class="bi bi-clipboard-check"></i></div>
                <div style="font-weight: 600; color: var(--gray-700);">Attendance</div>
            </a>
        <?php endif; ?>
        
        <?php if (Session::hasPermission('timetable.view')): ?>
            <a href="<?= APP_URL ?>/timetable" class="stat-card" style="text-decoration: none; text-align: center;">
                <div class="stat-icon info" style="margin: 0 auto 12px;"><i class="bi bi-calendar-week"></i></div>
                <div style="font-weight: 600; color: var(--gray-700);">Timetable</div>
            </a>
        <?php endif; ?>
        
        <?php if (Session::hasPermission('exams.view')): ?>
            <a href="<?= APP_URL ?>/exams" class="stat-card" style="text-decoration: none; text-align: center;">
                <div class="stat-icon warning" style="margin: 0 auto 12px;"><i class="bi bi-journal-text"></i></div>
                <div style="font-weight: 600; color: var(--gray-700);">Exams</div>
            </a>
        <?php endif; ?>
    </div>
</div>
