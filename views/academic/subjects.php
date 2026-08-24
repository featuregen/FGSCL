<!-- Subjects Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;"><?= count($subjects) ?> subject<?= count($subjects) !== 1 ? 's' : '' ?></p>
</div>

<!-- Add Subject -->
<div class="card mb-4">
    <div class="card-header">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-plus-square" style="color: #7B1FA2;"></i> Add Subject
        </h3>
    </div>
    <div class="card-body">
        <form action="<?= APP_URL ?>/academic/store-subject" method="POST">
            <?= Session::csrfField() ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Subject Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. Mathematics, English, Science" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" placeholder="e.g. MATH, ENG" maxlength="20" style="text-transform: uppercase;">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Type</label>
                    <select class="form-control" name="type">
                        <option value="theory">Theory</option>
                        <option value="practical">Practical</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="height: 38px;"><i class="bi bi-plus-lg"></i> Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Subjects List -->
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-book" style="color: #1565C0;"></i> All Subjects
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($subjects)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Classes</th>
                            <th>Status</th>
                            <th style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $i => $sub): ?>
                            <tr style="<?= !$sub['is_active'] ? 'opacity: 0.5;' : '' ?>">
                                <td><?= $i + 1 ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($sub['name']) ?></td>
                                <td>
                                    <?php if ($sub['code']): ?>
                                        <span class="badge" style="background: var(--gray-100); color: var(--gray-600); font-size: 11px;"><?= htmlspecialchars($sub['code']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-300);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $typeColors = ['theory' => ['#E3F2FD','#1565C0'], 'practical' => ['#FFF3E0','#E65100'], 'both' => ['#E8F5E9','#2E7D32']];
                                        $tc = $typeColors[$sub['type']] ?? $typeColors['theory'];
                                    ?>
                                    <span class="badge" style="background: <?= $tc[0] ?>; color: <?= $tc[1] ?>;"><?= ucfirst($sub['type']) ?></span>
                                </td>
                                <td>
                                    <?php if ($sub['class_count'] > 0): ?>
                                        <span style="font-weight: 600; color: #1f9e8b;"><?= $sub['class_count'] ?></span>
                                        <span style="font-size: 11px; color: var(--gray-400);"> class<?= $sub['class_count'] != 1 ? 'es' : '' ?></span>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--gray-400);">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sub['is_active']): ?>
                                        <span class="badge" style="background: #E0F2F1; color: #1f9e8b;">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #F5F5F5; color: #999;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="btn btn-sm" 
                                                style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;"
                                                onclick="openEditSubject(<?= htmlspecialchars(json_encode($sub)) ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="<?= APP_URL ?>/academic/toggle-subject/<?= $sub['id'] ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-sm" 
                                                    style="background: <?= $sub['is_active'] ? '#FFF3E0' : '#E8F5E9' ?>; color: <?= $sub['is_active'] ? '#E65100' : '#2E7D32' ?>; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;"
                                                    title="<?= $sub['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <i class="bi bi-<?= $sub['is_active'] ? 'pause-circle' : 'play-circle' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= APP_URL ?>/academic/delete-subject/<?= $sub['id'] ?>" 
                                              style="display:inline;" onsubmit="return confirm('Delete <?= htmlspecialchars($sub['name']) ?>?')">
                                            <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;">
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
            <div class="empty-state" style="padding: 40px;">
                <i class="bi bi-book" style="font-size: 40px; color: var(--gray-300); margin-bottom: 8px;"></i>
                <h3>No subjects yet</h3>
                <p>Add your first subject above</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 480px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-pencil" style="color: var(--primary);"></i> Edit Subject</h3>
            <button onclick="document.getElementById('editSubjectModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editSubjectForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Subject Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" id="editSubName" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" id="editSubCode" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type" id="editSubType">
                            <option value="theory">Theory</option>
                            <option value="practical">Practical</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 12px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editSubjectModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditSubject(sub) {
    document.getElementById('editSubjectForm').action = '<?= APP_URL ?>/academic/update-subject/' + sub.id;
    document.getElementById('editSubName').value = sub.name;
    document.getElementById('editSubCode').value = sub.code || '';
    document.getElementById('editSubType').value = sub.type;
    document.getElementById('editSubjectModal').style.display = 'flex';
}
</script>
