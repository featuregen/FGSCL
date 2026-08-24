<!-- Subjects Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;">Manage subjects and assign them to classes</p>
    <button class="btn btn-primary" onclick="document.getElementById('addSubjectModal').style.display='flex'">
        <i class="bi bi-plus-lg"></i> Add Subject
    </button>
</div>

<div class="card">
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
                            <th>Elective</th>
                            <th>Classes</th>
                            <th>Status</th>
                            <th style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $i => $subject): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($subject['name']) ?></td>
                                <td>
                                    <?php if ($subject['code']): ?>
                                        <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= htmlspecialchars($subject['code']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $typeIcons = ['theory' => 'bi-journal-text', 'practical' => 'bi-tools', 'both' => 'bi-journal-code'];
                                        $typeColors = ['theory' => '#1565C0', 'practical' => '#E65100', 'both' => '#7B1FA2'];
                                    ?>
                                    <span style="color: <?= $typeColors[$subject['type']] ?? '#666' ?>; font-size: 13px;">
                                        <i class="bi <?= $typeIcons[$subject['type']] ?? '' ?>"></i>
                                        <?= ucfirst($subject['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($subject['is_elective']): ?>
                                        <span class="badge" style="background: #FFF3E0; color: #E65100;">Elective</span>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--gray-400);">Mandatory</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background: #F3E5F5; color: #7B1FA2;">
                                        <?= $subject['class_count'] ?> classes
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= $subject['is_active'] ? '#E0F2F1' : '#F5F5F5' ?>; 
                                                             color: <?= $subject['is_active'] ? '#1f9e8b' : '#999' ?>;">
                                        <?= $subject['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="<?= APP_URL ?>/school-setup/delete-subject/<?= $subject['id'] ?>" 
                                          style="display:inline;" onsubmit="return confirm('Delete subject: <?= htmlspecialchars($subject['name']) ?>?')">
                                        <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-book" style="font-size: 48px; color: var(--gray-300); margin-bottom: 12px;"></i>
                <h3>No subjects yet</h3>
                <p>Add subjects and assign them to classes</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 520px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Add Subject</h3>
            <button onclick="document.getElementById('addSubjectModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-subject" method="POST">
                <?= Session::csrfField() ?>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Subject Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. English, Mathematics" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. ENG" maxlength="20" style="text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type">
                            <option value="theory">Theory</option>
                            <option value="practical">Practical</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: end; padding-bottom: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_elective" value="1" style="accent-color: #1f9e8b;">
                            <span style="font-size: 13px;">This is an elective subject</span>
                        </label>
                    </div>
                </div>

                <?php if (!empty($classes)): ?>
                    <div class="form-group">
                        <label class="form-label">Assign to Classes</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; padding: 12px; background: var(--gray-50); border-radius: 8px;">
                            <?php foreach ($classes as $cls): ?>
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 4px 10px; border-radius: 6px; background: white; border: 1px solid var(--gray-200); font-size: 13px;">
                                    <input type="checkbox" name="class_ids[]" value="<?= $cls['id'] ?>" style="accent-color: #1f9e8b;">
                                    <?= htmlspecialchars($cls['name']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 6px;">
                            <button type="button" onclick="toggleAllClasses(true)" style="background: none; border: none; color: #1f9e8b; font-size: 12px; cursor: pointer; font-weight: 600;">Select All</button>
                            <span style="color: var(--gray-300); margin: 0 4px;">|</span>
                            <button type="button" onclick="toggleAllClasses(false)" style="background: none; border: none; color: var(--gray-500); font-size: 12px; cursor: pointer;">Clear</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addSubjectModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAllClasses(state) {
    document.querySelectorAll('input[name="class_ids[]"]').forEach(cb => cb.checked = state);
}
</script>
