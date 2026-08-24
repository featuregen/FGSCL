<!-- User List -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p style="color: var(--gray-500); margin: 0;">Manage user accounts and role assignments</p>
    </div>
    <?php if (Session::hasPermission('users.create')): ?>
        <a href="<?= APP_URL ?>/users/create" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add User
        </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= APP_URL ?>/users" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" class="form-control" name="search" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div style="width: 180px;">
                <select class="form-control" name="user_type">
                    <option value="">All Roles</option>
                    <?php foreach (['school_admin' => 'School Admin', 'principal' => 'Principal', 'teacher' => 'Teacher', 'staff' => 'Staff', 'student' => 'Student', 'parent' => 'Parent', 'accountant' => 'Accountant', 'librarian' => 'Librarian', 'transport_manager' => 'Transport Manager'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($filters['user_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= APP_URL ?>/users" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Users (<?= $total ?? 0 ?>)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <?php if (Session::userRole() === ROLE_SUPER_ADMIN): ?>
                                <th>School</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 10px; 
                                                     background: linear-gradient(135deg, var(--primary), var(--secondary));
                                                     display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                            <?php
                                            $parts = explode(' ', $u['full_name']);
                                            echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                            ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--gray-800);"><?= htmlspecialchars($u['full_name']) ?></div>
                                            <div style="font-size: 11px; color: var(--gray-400);">@<?= htmlspecialchars($u['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 13px;"><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                                <td style="font-size: 13px;"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                                <td>
                                    <?php
                                    $roleColors = [
                                        'super_admin' => 'danger', 'school_admin' => 'primary', 'principal' => 'info',
                                        'teacher' => 'success', 'student' => 'warning', 'parent_user' => 'secondary',
                                        'accountant' => 'info', 'librarian' => 'secondary', 'transport_manager' => 'secondary',
                                    ];
                                    $roleColor = $roleColors[$u['role_slug'] ?? ''] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $roleColor ?>"><?= htmlspecialchars($u['role_name'] ?? $u['user_type']) ?></span>
                                </td>
                                <?php if (Session::userRole() === ROLE_SUPER_ADMIN): ?>
                                    <td style="font-size: 12px; color: var(--gray-500);"><?= htmlspecialchars($u['school_name'] ?? 'Platform') ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($u['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px; color: var(--gray-400);">
                                    <?= $u['last_login_at'] ? date('d M, h:i A', strtotime($u['last_login_at'])) : 'Never' ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <a href="<?= APP_URL ?>/users/view/<?= $u['id'] ?>" class="btn btn-icon btn-sm btn-secondary" title="View"><i class="bi bi-eye"></i></a>
                                        <?php if (Session::hasPermission('users.edit')): ?>
                                            <a href="<?= APP_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-icon btn-sm btn-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if (Session::hasPermission('users.delete') && $u['id'] !== Session::userId()): ?>
                                            <a href="<?= APP_URL ?>/users/delete/<?= $u['id'] ?>" class="btn btn-icon btn-sm btn-secondary" 
                                               data-confirm-delete data-name="<?= htmlspecialchars($u['full_name']) ?>" title="Deactivate"><i class="bi bi-trash"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (($pages ?? 1) > 1): ?>
                <div style="padding: 16px 20px; display: flex; justify-content: center;">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&user_type=<?= urlencode($filters['user_type'] ?? '') ?>">‹</a>
                        </li>
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&user_type=<?= urlencode($filters['user_type'] ?? '') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&user_type=<?= urlencode($filters['user_type'] ?? '') ?>">›</a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-people"></i></div>
                <div class="empty-title">No users found</div>
                <div class="empty-text">Add users or adjust your search filters</div>
            </div>
        <?php endif; ?>
    </div>
</div>
