<!-- Designations Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;">Configure designations with teaching/non-teaching categories</p>
    <button onclick="document.getElementById('addDesigModal').style.display='flex'" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Designation
    </button>
</div>

<?php
    $teaching = array_filter($designations, fn($d) => $d['staff_category'] === 'teaching');
    $nonTeaching = array_filter($designations, fn($d) => $d['staff_category'] === 'non_teaching');
?>

<!-- Teaching Staff Designations -->
<div class="card mb-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-mortarboard" style="color: #1f9e8b;"></i> Teaching Staff
        </h3>
        <span style="font-size: 12px; color: var(--gray-400);"><?= count($teaching) ?> designation(s)</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($teaching)): ?>
            <div class="table-responsive">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th style="min-width: 220px;">Designation</th>
                            <th style="min-width: 100px; text-align: center;">Level</th>
                            <th style="min-width: 100px; text-align: center;">Staff Count</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_values($teaching) as $i => $d): ?>
                            <tr>
                                <td style="text-align: center; color: var(--gray-400);"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                                <td style="text-align: center;">
                                    <?php if ($d['level']): ?>
                                        <span style="font-size: 11px; padding: 2px 10px; background: var(--gray-50); border-radius: 4px;">Level <?= $d['level'] ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-300);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; font-size: 12px; padding: 2px 12px; border-radius: 6px; font-weight: 700; background: #E0F2F1; color: #1f9e8b;"><?= $d['staff_count'] ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <button onclick='editDesig(<?= json_encode($d) ?>)' class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 6px 10px; border-radius: 6px;"><i class="bi bi-pencil"></i></button>
                                        <a href="<?= APP_URL ?>/school-setup/delete-designation/<?= $d['id'] ?>" onclick="return confirm('Delete?')" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 6px 10px; border-radius: 6px;"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 32px; color: var(--gray-400);">
                <p style="margin: 0;">No teaching designations yet. Add roles like "Principal", "Vice Principal", "PGT", "TGT", "PRT" etc.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Non-Teaching Staff Designations -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-person-badge" style="color: #E65100;"></i> Non-Teaching Staff
        </h3>
        <span style="font-size: 12px; color: var(--gray-400);"><?= count($nonTeaching) ?> designation(s)</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($nonTeaching)): ?>
            <div class="table-responsive">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th style="min-width: 220px;">Designation</th>
                            <th style="min-width: 100px; text-align: center;">Level</th>
                            <th style="min-width: 100px; text-align: center;">Staff Count</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_values($nonTeaching) as $i => $d): ?>
                            <tr>
                                <td style="text-align: center; color: var(--gray-400);"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                                <td style="text-align: center;">
                                    <?php if ($d['level']): ?>
                                        <span style="font-size: 11px; padding: 2px 10px; background: var(--gray-50); border-radius: 4px;">Level <?= $d['level'] ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-300);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; font-size: 12px; padding: 2px 12px; border-radius: 6px; font-weight: 700; background: #FFF3E0; color: #E65100;"><?= $d['staff_count'] ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <button onclick='editDesig(<?= json_encode($d) ?>)' class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 6px 10px; border-radius: 6px;"><i class="bi bi-pencil"></i></button>
                                        <a href="<?= APP_URL ?>/school-setup/delete-designation/<?= $d['id'] ?>" onclick="return confirm('Delete?')" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 6px 10px; border-radius: 6px;"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 32px; color: var(--gray-400);">
                <p style="margin: 0;">No non-teaching designations yet. Add roles like "Accountant", "Office Clerk", "Lab Assistant", "Peon", "Driver" etc.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Modal -->
<div id="addDesigModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 420px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-person-badge" style="color: #7B1FA2;"></i> Add Designation</h3>
            <button onclick="document.getElementById('addDesigModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-designation" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Designation Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. PGT, TGT, Lab Assistant" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Staff Category <span style="color:var(--danger)">*</span></label>
                    <select class="form-control" name="staff_category">
                        <option value="teaching">👨‍🏫 Teaching Staff</option>
                        <option value="non_teaching">👷 Non-Teaching Staff</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Hierarchy Level</label>
                    <input type="number" class="form-control" name="level" placeholder="e.g. 1 = highest" min="0" value="0">
                    <span style="font-size: 11px; color: var(--gray-400);">Optional. 1 = Principal, 2 = VP, 3 = HOD, etc.</span>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addDesigModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editDesigModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 420px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit Designation</h3>
            <button onclick="document.getElementById('editDesigModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editDesigForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Designation Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="editDesigName" name="name" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Staff Category</label>
                    <select class="form-control" id="editDesigCat" name="staff_category">
                        <option value="teaching">👨‍🏫 Teaching Staff</option>
                        <option value="non_teaching">👷 Non-Teaching Staff</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Hierarchy Level</label>
                    <input type="number" class="form-control" id="editDesigLevel" name="level" min="0">
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editDesigModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editDesig(d) {
    document.getElementById('editDesigForm').action = '<?= APP_URL ?>/school-setup/update-designation/' + d.id;
    document.getElementById('editDesigName').value = d.name;
    document.getElementById('editDesigCat').value = d.staff_category;
    document.getElementById('editDesigLevel').value = d.level || 0;
    document.getElementById('editDesigModal').style.display = 'flex';
}
</script>
