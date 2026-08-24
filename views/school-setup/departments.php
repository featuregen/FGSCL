<!-- Departments Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;">Configure departments before assigning staff</p>
    <button onclick="document.getElementById('addDeptModal').style.display='flex'" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Department
    </button>
</div>

<?php if (!empty($departments)): ?>
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-building" style="color: #1f9e8b;"></i> Departments (<?= count($departments) ?>)
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="min-width: 200px;">Department Name</th>
                        <th style="min-width: 100px;">Code</th>
                        <th style="min-width: 100px; text-align: center;">Staff Count</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $i => $d): ?>
                        <tr>
                            <td style="text-align: center; color: var(--gray-400);"><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                            <td>
                                <?php if ($d['code']): ?>
                                    <code style="font-size: 11px; padding: 2px 8px; background: var(--gray-50); border-radius: 4px;"><?= htmlspecialchars($d['code']) ?></code>
                                <?php else: ?>
                                    <span style="color: var(--gray-300);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="display: inline-block; font-size: 12px; padding: 2px 12px; border-radius: 6px; font-weight: 700; background: #E0F2F1; color: #1f9e8b;">
                                    <?= $d['staff_count'] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <button onclick='editDept(<?= json_encode($d) ?>)' class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 6px 10px; border-radius: 6px;">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= APP_URL ?>/school-setup/delete-department/<?= $d['id'] ?>" onclick="return confirm('Delete this department?')" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 6px 10px; border-radius: 6px;">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body" style="text-align: center; padding: 48px;">
        <i class="bi bi-building" style="font-size: 48px; color: var(--gray-300);"></i>
        <h3 style="margin-top: 16px;">No Departments Yet</h3>
        <p style="color: var(--gray-500);">Add departments like "Mathematics", "Science", "Administration", etc.</p>
        <button onclick="document.getElementById('addDeptModal').style.display='flex'" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add First Department
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Add Department Modal -->
<div id="addDeptModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 420px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-building" style="color: #1f9e8b;"></i> Add Department</h3>
            <button onclick="document.getElementById('addDeptModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-department" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Department Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. Mathematics, Science, Administration" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" placeholder="e.g. MATH, SCI, ADMIN" maxlength="20">
                    <span style="font-size: 11px; color: var(--gray-400);">Optional short code for the department</span>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addDeptModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div id="editDeptModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 420px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit Department</h3>
            <button onclick="document.getElementById('editDeptModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editDeptForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Department Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="editDeptName" name="name" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" id="editDeptCode" name="code" maxlength="20">
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editDeptModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editDept(d) {
    document.getElementById('editDeptForm').action = '<?= APP_URL ?>/school-setup/update-department/' + d.id;
    document.getElementById('editDeptName').value = d.name;
    document.getElementById('editDeptCode').value = d.code || '';
    document.getElementById('editDeptModal').style.display = 'flex';
}
</script>
