<!-- Staff List -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;"><?= $total ?> staff member<?= $total !== 1 ? 's' : '' ?></p>
    <div style="display: flex; gap: 8px;">
        <a href="<?= APP_URL ?>/staff/import" class="btn btn-secondary">
            <i class="bi bi-upload"></i> Import CSV
        </a>
        <a href="<?= APP_URL ?>/staff/create" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add Staff
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= APP_URL ?>/staff" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" class="form-control" name="search" placeholder="Search name, phone, employee ID..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div style="width: 160px;">
                <select class="form-control" name="user_type">
                    <option value="">All Types</option>
                    <?php foreach (['teacher' => 'Teacher', 'staff' => 'Staff', 'accountant' => 'Accountant', 'librarian' => 'Librarian', 'transport_manager' => 'Transport'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $userType === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width: 130px;">
                <select class="form-control" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= APP_URL ?>/staff" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Clear</a>
        </form>
    </div>
</div>

<!-- Staff Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($staff)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff Member</th>
                            <th>Employee ID</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $i => $s): ?>
                            <tr>
                                <td><?= (($page - 1) * $perPage) + $i + 1 ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 10px;
                                                     background: linear-gradient(135deg, <?= $s['user_type'] === 'teacher' ? '#1f9e8b, #0d7377' : '#6366F1, #4338CA' ?>);
                                                     display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                            <?php
                                                $parts = explode(' ', $s['full_name']);
                                                echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                            ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($s['full_name']) ?></div>
                                            <div style="font-size: 11px; color: var(--gray-400);">
                                                <?= htmlspecialchars($s['designation'] ?? '') ?>
                                                <?php if ($s['user_type'] === 'teacher' && ($s['subject_count'] ?? 0) > 0): ?>
                                                    · <?= $s['subject_count'] ?> subject<?= $s['subject_count'] > 1 ? 's' : '' ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($s['employee_id']): ?>
                                        <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= htmlspecialchars($s['employee_id']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-300);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $typeLabels = ['teacher' => 'Teacher', 'staff' => 'Staff', 'accountant' => 'Accountant', 'librarian' => 'Librarian', 'transport_manager' => 'Transport'];
                                        $typeColors = ['teacher' => ['#E0F2F1','#1f9e8b'], 'staff' => ['#F3E5F5','#7B1FA2'], 'accountant' => ['#FFF3E0','#E65100'], 'librarian' => ['#E8F5E9','#2E7D32'], 'transport_manager' => ['#E3F2FD','#1565C0']];
                                        $tc = $typeColors[$s['user_type']] ?? ['#F5F5F5','#666'];
                                    ?>
                                    <span class="badge" style="background: <?= $tc[0] ?>; color: <?= $tc[1] ?>;"><?= $typeLabels[$s['user_type']] ?? ucfirst($s['user_type']) ?></span>
                                </td>
                                <td style="font-size: 13px;"><?= htmlspecialchars($s['department'] ?? '—') ?></td>
                                <td style="font-size: 13px;"><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                                <td style="font-size: 12px; color: var(--gray-500);">
                                    <?= $s['date_of_joining'] ? date('d M Y', strtotime($s['date_of_joining'])) : '—' ?>
                                </td>
                                <td>
                                    <?php if ($s['is_active']): ?>
                                        <span class="badge" style="background: #E0F2F1; color: #1f9e8b;">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #FFEBEE; color: #C62828;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <a href="<?= APP_URL ?>/staff/view/<?= $s['id'] ?>" class="btn btn-sm" style="background: #E3F2FD; color: #1565C0; border: none; padding: 4px 8px; border-radius: 6px;" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/staff/edit/<?= $s['id'] ?>" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/staff/delete/<?= $s['id'] ?>" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 4px 8px; border-radius: 6px;"
                                           onclick="return confirm('Deactivate this staff member?')" title="Deactivate">
                                            <i class="bi bi-person-dash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 48px;">
                <i class="bi bi-people" style="font-size: 48px; color: var(--gray-300); margin-bottom: 12px;"></i>
                <h3>No staff members found</h3>
                <p>Add your first staff member to get started</p>
                <a href="<?= APP_URL ?>/staff/create" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add Staff</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<?php
    $qp = array_filter(['search' => $search, 'user_type' => $userType, 'status' => $status]);
    $base = APP_URL . '/staff?' . http_build_query($qp);
    $sep = empty($qp) ? '?' : '&';
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
    <div style="font-size: 13px; color: var(--gray-500);">
        Showing <?= (($page - 1) * $perPage) + 1 ?>–<?= min($page * $perPage, $total) ?> of <?= $total ?>
    </div>
    <div style="display: flex; gap: 4px; align-items: center;">
        <?php if ($page > 1): ?>
            <a href="<?= $base . $sep ?>page=<?= $page - 1 ?>" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--gray-200); color: var(--gray-600); font-size: 13px; text-decoration: none;"><i class="bi bi-chevron-left"></i> Prev</a>
        <?php endif; ?>
        <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
            <?php if ($p == $page): ?>
                <span style="padding: 6px 12px; border-radius: 6px; background: #1f9e8b; color: white; font-size: 13px; font-weight: 600;"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= $base . $sep ?>page=<?= $p ?>" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--gray-200); color: var(--gray-600); font-size: 13px; text-decoration: none;"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?= $base . $sep ?>page=<?= $page + 1 ?>" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--gray-200); color: var(--gray-600); font-size: 13px; text-decoration: none;">Next <i class="bi bi-chevron-right"></i></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
