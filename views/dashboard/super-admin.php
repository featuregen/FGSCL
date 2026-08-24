<!-- Super Admin Dashboard -->
<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Total Schools -->
    <div class="stat-card-gradient gradient-primary">
        <div style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1;">
            <div>
                <div style="font-size: 32px; font-weight: 800; margin-bottom: 4px;"><?= $totalSchools ?? 0 ?></div>
                <div style="font-size: 13px; opacity: 0.8;">Total Schools</div>
            </div>
            <i class="bi bi-building" style="font-size: 36px; opacity: 0.3;"></i>
        </div>
    </div>

    <!-- Active Schools -->
    <div class="stat-card-gradient gradient-success">
        <div style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1;">
            <div>
                <div style="font-size: 32px; font-weight: 800; margin-bottom: 4px;"><?= $activeSchools ?? 0 ?></div>
                <div style="font-size: 13px; opacity: 0.8;">Active Schools</div>
            </div>
            <i class="bi bi-check-circle" style="font-size: 36px; opacity: 0.3;"></i>
        </div>
    </div>

    <!-- Total Users -->
    <div class="stat-card-gradient gradient-info">
        <div style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1;">
            <div>
                <div style="font-size: 32px; font-weight: 800; margin-bottom: 4px;"><?= $totalUsers ?? 0 ?></div>
                <div style="font-size: 13px; opacity: 0.8;">Total Users</div>
            </div>
            <i class="bi bi-people" style="font-size: 36px; opacity: 0.3;"></i>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="stat-card-gradient gradient-warning">
        <div style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1;">
            <div>
                <div style="font-size: 32px; font-weight: 800; margin-bottom: 4px;"><?= $activeSubscriptions ?? 0 ?></div>
                <div style="font-size: 13px; opacity: 0.8;">Active Subscriptions</div>
            </div>
            <i class="bi bi-credit-card" style="font-size: 36px; opacity: 0.3;"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Schools -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-building me-2"></i> Recent Schools</h3>
            <a href="<?= APP_URL ?>/schools" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($recentSchools)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSchools as $school): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--primary-50); 
                                                     display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 700; font-size: 13px;">
                                            <?= strtoupper(substr($school['name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--gray-800);"><?= htmlspecialchars($school['name']) ?></div>
                                            <div style="font-size: 11px; color: var(--gray-400);"><?= htmlspecialchars($school['city'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($school['plan_name'] ?? 'No Plan') ?></span></td>
                                <td>
                                    <?php if ($school['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px; color: var(--gray-500);">
                                    <?= $school['end_date'] ? date('d M Y', strtotime($school['end_date'])) : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-building"></i></div>
                    <div class="empty-title">No schools yet</div>
                    <div class="empty-text">Start by adding your first school</div>
                    <a href="<?= APP_URL ?>/schools/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add School
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clock-history me-2"></i> Activity</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($recentActivity)): ?>
                <ul class="activity-list">
                    <?php foreach (array_slice($recentActivity, 0, 8) as $log): ?>
                        <li class="activity-item">
                            <div class="activity-avatar">
                                <?= strtoupper(substr($log['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text"><?= htmlspecialchars($log['description'] ?? $log['action']) ?></div>
                                <div class="activity-time">
                                    <?= date('d M, h:i A', strtotime($log['created_at'])) ?>
                                </div>
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

<!-- Quick Actions -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-lightning-charge me-2"></i> Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
            <a href="<?= APP_URL ?>/schools/create" class="btn btn-outline-primary w-100">
                <i class="bi bi-building-add"></i> Add School
            </a>
            <a href="<?= APP_URL ?>/users/create" class="btn btn-outline-primary w-100">
                <i class="bi bi-person-plus"></i> Add User
            </a>
            <a href="<?= APP_URL ?>/subscriptions" class="btn btn-outline-primary w-100">
                <i class="bi bi-credit-card"></i> Manage Plans
            </a>
            <a href="<?= APP_URL ?>/reports" class="btn btn-outline-primary w-100">
                <i class="bi bi-bar-chart"></i> View Reports
            </a>
        </div>
    </div>
</div>
