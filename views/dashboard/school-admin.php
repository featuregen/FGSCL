<!-- School Admin / Principal Dashboard -->
<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Students -->
    <div class="stat-card">
        <div class="stat-icon primary"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="stat-value"><?= $totalStudents ?? 0 ?></div>
        <div class="stat-label">Active Students</div>
        <?php if (!empty($currentYear)): ?>
            <div style="font-size: 11px; color: var(--gray-400); margin-top: 2px;"><?= htmlspecialchars($currentYear['name']) ?></div>
        <?php endif; ?>
    </div>

    <!-- Teachers -->
    <div class="stat-card">
        <div class="stat-icon success"><i class="bi bi-person-workspace"></i></div>
        <div class="stat-value"><?= $totalTeachers ?? 0 ?></div>
        <div class="stat-label">Teachers</div>
    </div>

    <!-- Staff -->
    <div class="stat-card">
        <div class="stat-icon info"><i class="bi bi-people-fill"></i></div>
        <div class="stat-value"><?= $totalStaff ?? 0 ?></div>
        <div class="stat-label">Total Staff</div>
    </div>

    <!-- Parents -->
    <div class="stat-card">
        <div class="stat-icon warning"><i class="bi bi-person-hearts"></i></div>
        <div class="stat-value"><?= $totalParents ?? 0 ?></div>
        <div class="stat-label">Parents</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-lightning-charge me-2"></i> Quick Actions</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <a href="<?= APP_URL ?>/students/create" class="btn btn-outline-primary w-100">
                    <i class="bi bi-person-plus"></i> New Student
                </a>
                <a href="<?= APP_URL ?>/attendance" class="btn btn-outline-primary w-100">
                    <i class="bi bi-clipboard-check"></i> Attendance
                </a>
                <a href="<?= APP_URL ?>/fees/collection" class="btn btn-outline-primary w-100">
                    <i class="bi bi-wallet2"></i> Collect Fee
                </a>
                <a href="<?= APP_URL ?>/communication" class="btn btn-outline-primary w-100">
                    <i class="bi bi-megaphone"></i> Broadcast
                </a>
                <a href="<?= APP_URL ?>/exams" class="btn btn-outline-primary w-100">
                    <i class="bi bi-journal-text"></i> Exams
                </a>
                <a href="<?= APP_URL ?>/reports" class="btn btn-outline-primary w-100">
                    <i class="bi bi-bar-chart"></i> Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clock-history me-2"></i> Recent Activity</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($recentActivity)): ?>
                <ul class="activity-list">
                    <?php foreach (array_slice($recentActivity, 0, 6) as $log): ?>
                        <li class="activity-item">
                            <div class="activity-avatar">
                                <?= strtoupper(substr($log['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text"><?= htmlspecialchars($log['description'] ?? $log['action']) ?></div>
                                <div class="activity-time"><?= date('d M, h:i A', strtotime($log['created_at'])) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state" style="padding: 24px;">
                    <div class="empty-icon"><i class="bi bi-clock"></i></div>
                    <div class="empty-text">No recent activity</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
