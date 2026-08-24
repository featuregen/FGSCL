<!-- Fee Heads Management -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <span style="font-size: 13px; color: var(--gray-500);"><?= count($heads) ?> fee heads configured</span>
    </div>
    <button onclick="document.getElementById('addHeadModal').style.display='flex'" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Fee Head
    </button>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($heads)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Recurring</th>
                            <th>Structures</th>
                            <th>Status</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($heads as $i => $h): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td style="font-weight: 700;"><?= htmlspecialchars($h['name']) ?></td>
                                <td><code><?= htmlspecialchars($h['code'] ?? '—') ?></code></td>
                                <td>
                                    <?php if ($h['type'] === 'mandatory'): ?>
                                        <span class="badge" style="background: #FFEBEE; color: #C62828;">Mandatory</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #E3F2FD; color: #1565C0;">Optional</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $h['is_recurring'] ? '<i class="bi bi-arrow-repeat" style="color: #1f9e8b;"></i> Yes' : 'One-time' ?>
                                </td>
                                <td>
                                    <span class="badge" style="background: #F3E5F5; color: #7B1FA2;"><?= $h['structure_count'] ?> classes</span>
                                </td>
                                <td>
                                    <?php if ($h['is_active']): ?>
                                        <span class="badge" style="background: #E0F2F1; color: #1f9e8b;">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--gray-100); color: var(--gray-500);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button onclick="editHead(<?= htmlspecialchars(json_encode($h)) ?>)" class="btn btn-sm"
                                                style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($h['structure_count'] == 0): ?>
                                            <form method="POST" action="<?= APP_URL ?>/fees/delete-head/<?= $h['id'] ?>"
                                                  style="display:inline;" onsubmit="return confirm('Delete fee head: <?= htmlspecialchars($h['name']) ?>?')">
                                                <button type="submit" class="btn btn-sm"
                                                        style="background: #FFEBEE; color: #C62828; border: none; padding: 4px 8px; border-radius: 6px;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 48px;">
                <i class="bi bi-tags" style="font-size: 48px; color: var(--gray-300);"></i>
                <h3>No fee heads</h3>
                <p>Create fee heads like Tuition Fee, Transport, Lab Fee, etc.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Fee Head Modal -->
<div id="addHeadModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 500px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-plus-square" style="color: #1f9e8b;"></i> Add Fee Head</h3>
            <button onclick="document.getElementById('addHeadModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/fees/store-head">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. Tuition Fee" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. TF" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type">
                            <option value="mandatory">Mandatory</option>
                            <option value="optional">Optional</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description" placeholder="Optional description">
                </div>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 16px;">
                    <input type="checkbox" name="is_recurring" value="1" checked style="accent-color: #1f9e8b;">
                    <span style="font-size: 13px;">Recurring (monthly/periodic)</span>
                </label>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('addHeadModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Add Fee Head</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Fee Head Modal -->
<div id="editHeadModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 500px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-pencil" style="color: #1f9e8b;"></i> Edit Fee Head</h3>
            <button onclick="document.getElementById('editHeadModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editHeadForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" id="editName" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" id="editCode" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type" id="editType">
                            <option value="mandatory">Mandatory</option>
                            <option value="optional">Optional</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description" id="editDesc">
                </div>
                <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_recurring" value="1" id="editRecurring" style="accent-color: #1f9e8b;">
                        <span style="font-size: 13px;">Recurring</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" id="editActive" style="accent-color: #1f9e8b;">
                        <span style="font-size: 13px;">Active</span>
                    </label>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('editHeadModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editHead(h) {
    document.getElementById('editHeadForm').action = '<?= APP_URL ?>/fees/update-head/' + h.id;
    document.getElementById('editName').value = h.name;
    document.getElementById('editCode').value = h.code || '';
    document.getElementById('editType').value = h.type;
    document.getElementById('editDesc').value = h.description || '';
    document.getElementById('editRecurring').checked = h.is_recurring == 1;
    document.getElementById('editActive').checked = h.is_active == 1;
    document.getElementById('editHeadModal').style.display = 'flex';
}
</script>
