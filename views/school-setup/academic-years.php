<!-- Academic Years Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;">Manage academic year periods for your school</p>
    <button class="btn btn-primary" onclick="document.getElementById('addYearModal').style.display='flex'">
        <i class="bi bi-plus-lg"></i> Add Academic Year
    </button>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($years)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Classes</th>
                            <th>Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $i => $year): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <span style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($year['name']) ?></span>
                                    <?php if ($year['is_current']): ?>
                                        <span class="badge" style="background: #E0F2F1; color: #1f9e8b; margin-left: 6px;">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($year['start_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($year['end_date'])) ?></td>
                                <td>
                                    <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= $year['class_count'] ?> classes</span>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= $year['status'] === 'active' ? '#E0F2F1' : '#F5F5F5' ?>; 
                                                             color: <?= $year['status'] === 'active' ? '#1f9e8b' : '#999' ?>;">
                                        <?= ucfirst($year['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <?php if (!$year['is_current']): ?>
                                            <form method="POST" action="<?= APP_URL ?>/school-setup/set-current-year/<?= $year['id'] ?>" style="display:inline;">
                                                <button type="submit" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Set as Current">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="<?= APP_URL ?>/school-setup/delete-academic-year/<?= $year['id'] ?>" 
                                              style="display:inline;" onsubmit="return confirm('Delete this academic year? This will also delete all classes and sections in it.')">
                                            <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-calendar-plus" style="font-size: 48px; color: var(--gray-300); margin-bottom: 12px;"></i>
                <h3>No academic years yet</h3>
                <p>Create your first academic year to get started</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Academic Year Modal -->
<div id="addYearModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 480px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Add Academic Year</h3>
            <button onclick="document.getElementById('addYearModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-academic-year" method="POST">
                <?= Session::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">Year Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. 2025-26" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Start Date <span style="color: var(--danger);">*</span></label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date <span style="color: var(--danger);">*</span></label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_current" value="1" checked style="accent-color: #1f9e8b;">
                        <span style="font-size: 13px;">Set as current academic year</span>
                    </label>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addYearModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
