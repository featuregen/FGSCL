<!-- School Management (Super Admin) -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p style="color: var(--gray-500); margin: 0;">Manage all registered schools and their subscriptions</p>
    </div>
    <a href="<?= APP_URL ?>/schools/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add School
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= APP_URL ?>/schools" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" class="form-control" name="search" placeholder="Search schools..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div style="width: 150px;">
                <select class="form-control" name="status">
                    <option value="">All Status</option>
                    <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= APP_URL ?>/schools" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Clear</a>
        </form>
    </div>
</div>

<!-- Schools Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($schools)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Students</th>
                            <th>Teachers</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Expires</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schools as $school): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 40px; height: 40px; border-radius: 10px; 
                                                     background: linear-gradient(135deg, <?= htmlspecialchars($school['primary_color'] ?? '#4F46E5') ?>, <?= htmlspecialchars($school['secondary_color'] ?? '#7C3AED') ?>); 
                                                     display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                                            <?php if (!empty($school['logo'])): ?>
                                                <img src="<?= APP_URL ?>/uploads/logos/<?= htmlspecialchars($school['logo']) ?>" alt="" style="width:100%; height:100%; border-radius:10px; object-fit:cover;">
                                            <?php else: ?>
                                                <?= strtoupper(substr($school['name'], 0, 2)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= APP_URL ?>/schools/view/<?= $school['id'] ?>" style="font-weight: 600; color: var(--gray-800);">
                                                <?= htmlspecialchars($school['name']) ?>
                                            </a>
                                            <div style="font-size: 11px; color: var(--gray-400);"><?= htmlspecialchars($school['city'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><code style="background: var(--gray-100); padding: 2px 8px; border-radius: 4px; font-size: 12px;"><?= htmlspecialchars($school['code'] ?? 'N/A') ?></code></td>
                                <td>
                                    <div style="font-size: 12px;"><?= htmlspecialchars($school['email'] ?? '') ?></div>
                                    <div style="font-size: 11px; color: var(--gray-400);"><?= htmlspecialchars($school['phone'] ?? '') ?></div>
                                </td>
                                <td style="font-weight: 600;"><?= $school['student_count'] ?? 0 ?></td>
                                <td style="font-weight: 600;"><?= $school['teacher_count'] ?? 0 ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= htmlspecialchars($school['plan_name'] ?? 'No Plan') ?></span>
                                    <?php if (!empty($school['sub_pricing_type'])): ?>
                                        <div style="font-size: 10px; color: var(--gray-400); margin-top: 2px;">
                                            <?php if ($school['sub_pricing_type'] === 'per_student'): ?>
                                                <i class="bi bi-people"></i> ₹<?= number_format($school['sub_amount'] ?? 0) ?>/mo
                                            <?php else: ?>
                                                <i class="bi bi-tag"></i> ₹<?= number_format($school['sub_amount'] ?? 0) ?>/mo
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($school['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px; color: var(--gray-500);">
                                    <?php if (!empty($school['end_date'])): ?>
                                        <?php 
                                        $daysLeft = (int)((strtotime($school['end_date']) - time()) / 86400);
                                        $color = $daysLeft <= 7 ? 'var(--danger)' : ($daysLeft <= 30 ? 'var(--warning)' : 'var(--gray-500)');
                                        ?>
                                        <span style="color: <?= $color ?>;"><?= date('d M Y', strtotime($school['end_date'])) ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <a href="<?= APP_URL ?>/schools/view/<?= $school['id'] ?>" class="btn btn-icon btn-sm btn-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/schools/edit/<?= $school['id'] ?>" class="btn btn-icon btn-sm btn-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/schools/delete/<?= $school['id'] ?>" class="btn btn-icon btn-sm btn-secondary" 
                                           data-confirm-delete data-name="<?= htmlspecialchars($school['name']) ?>" title="Deactivate">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-building"></i></div>
                <div class="empty-title">No schools found</div>
                <div class="empty-text">Get started by adding your first school</div>
                <a href="<?= APP_URL ?>/schools/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add School
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
