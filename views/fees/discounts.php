<!-- Fee Discounts -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <span style="font-size: 13px; color: var(--gray-500);"><?= count($discounts) ?> discount schemes</span>
    <button onclick="document.getElementById('addDiscModal').style.display='flex'" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Discount
    </button>
</div>

<?php if (!empty($discounts)): ?>
    <?php foreach ($discounts as $d): ?>
        <div class="card mb-3">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0;">
                        <i class="bi bi-percent" style="color: #7B1FA2;"></i> <?= htmlspecialchars($d['name']) ?>
                    </h3>
                    <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= ucfirst($d['type']) ?></span>
                    <span style="font-weight: 700; color: #7B1FA2; font-size: 15px;">
                        <?= $d['type'] === 'percentage' ? $d['value'] . '%' : '₹' . number_format($d['value'], 2) ?>
                    </span>
                    <?= $d['is_active'] ? '<span class="badge" style="background:#E0F2F1;color:#1f9e8b;">Active</span>' : '<span class="badge" style="background:var(--gray-100);color:var(--gray-500);">Inactive</span>' ?>
                    <?php 
                        $appHeads = $d['applicable_heads'] ? json_decode($d['applicable_heads'], true) : [];
                        if (!empty($appHeads)):
                            $headNames = [];
                            foreach ($feeHeads as $fh) {
                                if (in_array($fh['id'], $appHeads)) $headNames[] = $fh['name'];
                            }
                    ?>
                        <span style="font-size: 11px; color: var(--gray-500);">→</span>
                        <?php foreach ($headNames as $hn): ?>
                            <span class="badge" style="background: #FFF3E0; color: #E65100; font-size: 10px;"><?= htmlspecialchars($hn) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="badge" style="background: var(--gray-100); color: var(--gray-500); font-size: 10px;">All Heads</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 6px;">
                    <button onclick="showAssignModal(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['name'])) ?>')" 
                            class="btn btn-sm" style="background: #F3E5F5; color: #7B1FA2; border: none; font-weight: 600;">
                        <i class="bi bi-person-plus"></i> Apply to Student
                    </button>
                    <button onclick="editDiscount(<?= htmlspecialchars(json_encode($d)) ?>)" 
                            class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 6px 10px; border-radius: 6px;">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="<?= APP_URL ?>/fees/delete-discount/<?= $d['id'] ?>"
                          style="display:inline;" onsubmit="return confirm('Delete: <?= htmlspecialchars($d['name']) ?>?')">
                        <button type="submit" class="btn btn-sm" style="background:#FFEBEE;color:#C62828;border:none;padding:6px 10px;border-radius:6px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <!-- Applied Students -->
            <div class="card-body" style="padding: 0;">
                <?php $applied = $concessionsByDiscount[$d['id']] ?? []; ?>
                <?php if (!empty($applied)): ?>
                    <table style="width: 100%; font-size: 13px;">
                        <thead>
                            <tr style="background: #F5F3FF;">
                                <th style="padding: 8px 16px; font-weight: 600; color: var(--gray-500); font-size: 11px; text-transform: uppercase;">Student</th>
                                <th style="padding: 8px 16px; font-weight: 600; color: var(--gray-500); font-size: 11px; text-transform: uppercase;">Admission No</th>
                                <th style="padding: 8px 16px; font-weight: 600; color: var(--gray-500); font-size: 11px; text-transform: uppercase;">Class</th>
                                <th style="padding: 8px 16px; font-weight: 600; color: var(--gray-500); font-size: 11px; text-transform: uppercase; width: 60px;">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applied as $a): ?>
                                <tr style="border-bottom: 1px solid var(--gray-50);">
                                    <td style="padding: 8px 16px; font-weight: 600;"><?= htmlspecialchars($a['student_name']) ?></td>
                                    <td style="padding: 8px 16px;"><code><?= htmlspecialchars($a['admission_no'] ?? '—') ?></code></td>
                                    <td style="padding: 8px 16px;"><?= htmlspecialchars(($a['class_name'] ?? '') . ($a['section_name'] ? '-' . $a['section_name'] : '')) ?></td>
                                    <td style="padding: 8px 16px;">
                                        <form method="POST" action="<?= APP_URL ?>/fees/remove-discount/<?= $a['id'] ?>" 
                                              style="display:inline;" onsubmit="return confirm('Remove discount from this student?')">
                                            <button type="submit" class="btn btn-sm" style="background:#FFEBEE;color:#C62828;border:none;padding:2px 6px;border-radius:4px;font-size:11px;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 16px; text-align: center; color: var(--gray-400); font-size: 12px;">
                        <i class="bi bi-person-x"></i> No students assigned — click "Apply to Student" to add
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body empty-state" style="padding: 48px;">
            <i class="bi bi-percent" style="font-size: 40px; color: var(--gray-300);"></i>
            <h3>No discounts</h3>
            <p>Add discounts like Sibling Discount, Merit Scholarship, etc.</p>
        </div>
    </div>
<?php endif; ?>

<!-- Add Discount Modal -->
<div id="addDiscModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 440px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-percent" style="color: #7B1FA2;"></i> Add Discount</h3>
            <button onclick="document.getElementById('addDiscModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/fees/store-discount">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. Sibling Discount" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Value <span style="color: var(--danger);">*</span></label>
                        <input type="number" class="form-control" name="value" step="0.01" min="0" required placeholder="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Applicable Fee Heads</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; background: var(--gray-50); border-radius: 8px; max-height: 150px; overflow-y: auto;">
                        <?php foreach ($feeHeads as $fh): ?>
                            <label style="display: flex; align-items: center; gap: 4px; font-size: 12px; cursor: pointer; padding: 4px 8px; background: white; border-radius: 6px; border: 1px solid var(--gray-200);">
                                <input type="checkbox" name="applicable_heads[]" value="<?= $fh['id'] ?>" style="accent-color: #7B1FA2;">
                                <?= htmlspecialchars($fh['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="font-size: 10px; color: var(--gray-400); margin-top: 3px;">Leave unchecked to apply on all heads</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description" placeholder="Optional">
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('addDiscModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Discount to Student Modal -->
<div id="assignModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 440px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person-plus" style="color: #7B1FA2;"></i> Apply: <span id="assignDiscName"></span>
            </h3>
            <button onclick="document.getElementById('assignModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/fees/assign-discount">
                <?= Session::csrfField() ?>
                <input type="hidden" name="discount_id" id="assignDiscId">
                <div class="form-group">
                    <label class="form-label">Select Student <span style="color: var(--danger);">*</span></label>
                    <select class="form-control" name="student_id" required style="font-size: 13px;">
                        <option value="">— Choose a student —</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['full_name']) ?> — <?= $s['admission_no'] ?? '' ?> (<?= $s['class_name'] ?? '' ?><?= $s['section_name'] ? '-' . $s['section_name'] : '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('assignModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn" style="background: #7B1FA2; color: white; font-weight: 700;">
                        <i class="bi bi-check-lg"></i> Apply Discount
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAssignModal(discountId, discountName) {
    document.getElementById('assignDiscId').value = discountId;
    document.getElementById('assignDiscName').textContent = discountName;
    document.getElementById('assignModal').style.display = 'flex';
}

function editDiscount(d) {
    document.getElementById('editDiscForm').action = '<?= APP_URL ?>/fees/update-discount/' + d.id;
    document.getElementById('editDiscName').value = d.name;
    document.getElementById('editDiscType').value = d.type;
    document.getElementById('editDiscValue').value = d.value;
    document.getElementById('editDiscDesc').value = d.description || '';
    document.getElementById('editDiscActive').checked = d.is_active == 1;
    
    // Set applicable heads checkboxes
    const heads = d.applicable_heads ? (typeof d.applicable_heads === 'string' ? JSON.parse(d.applicable_heads) : d.applicable_heads) : [];
    document.querySelectorAll('#editHeadsContainer input[type=checkbox]').forEach(cb => {
        cb.checked = heads.includes(parseInt(cb.value));
    });
    
    document.getElementById('editDiscModal').style.display = 'flex';
}
</script>

<!-- Edit Discount Modal -->
<div id="editDiscModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 440px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-pencil" style="color: #1f9e8b;"></i> Edit Discount</h3>
            <button onclick="document.getElementById('editDiscModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editDiscForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" id="editDiscName" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type" id="editDiscType">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Value <span style="color: var(--danger);">*</span></label>
                        <input type="number" class="form-control" name="value" id="editDiscValue" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Applicable Fee Heads</label>
                    <div id="editHeadsContainer" style="display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; background: var(--gray-50); border-radius: 8px; max-height: 150px; overflow-y: auto;">
                        <?php foreach ($feeHeads as $fh): ?>
                            <label style="display: flex; align-items: center; gap: 4px; font-size: 12px; cursor: pointer; padding: 4px 8px; background: white; border-radius: 6px; border: 1px solid var(--gray-200);">
                                <input type="checkbox" name="applicable_heads[]" value="<?= $fh['id'] ?>" style="accent-color: #7B1FA2;">
                                <?= htmlspecialchars($fh['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="font-size: 10px; color: var(--gray-400); margin-top: 3px;">Leave unchecked to apply on all heads</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description" id="editDiscDesc">
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_active" id="editDiscActive" value="1" style="accent-color: #1f9e8b; width: 16px; height: 16px;">
                    <label for="editDiscActive" style="margin: 0; font-size: 13px;">Active</label>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('editDiscModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
