<!-- Fee Structure - Class-wise fee setup -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <span style="font-size: 13px; color: var(--gray-500);">
        Academic Year: <strong><?= htmlspecialchars($currentYear['year_label'] ?? 'Not set') ?></strong>
    </span>
    <?php if (!empty($heads)): ?>
        <button onclick="document.getElementById('addStructModal').style.display='flex'" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Assign Fee to Class
        </button>
    <?php endif; ?>
</div>

<?php if (empty($heads)): ?>
    <div class="card">
        <div class="card-body empty-state" style="padding: 48px;">
            <i class="bi bi-tags" style="font-size: 48px; color: var(--gray-300);"></i>
            <h3>No fee heads configured</h3>
            <p>Create fee heads first, then assign them to classes.</p>
            <a href="<?= APP_URL ?>/fees/heads" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Fee Heads</a>
        </div>
    </div>
<?php else: ?>
    <!-- Structure per class -->
    <?php foreach ($classes as $cls): ?>
        <?php $classStructures = $structuresByClass[$cls['id']] ?? []; ?>
        <div class="card mb-4">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 15px; font-weight: 700; margin: 0;">
                    <i class="bi bi-mortarboard" style="color: #1f9e8b;"></i>
                    Class <?= htmlspecialchars($cls['name']) ?>
                    <span class="badge" style="background: var(--gray-100); color: var(--gray-600); font-size: 11px; margin-left: 8px;">
                        <?= count($classStructures) ?> fee<?= count($classStructures) !== 1 ? 's' : '' ?>
                    </span>
                </h3>
                <?php if (!empty($classStructures)): ?>
                    <span style="font-size: 14px; font-weight: 700; color: #1f9e8b;">
                        Total: ₹<?= number_format(array_sum(array_column($classStructures, 'amount')), 2) ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if (!empty($classStructures)): ?>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Fee Head</th><th>Amount</th><th>Frequency</th><th>Due Day</th><th style="width:80px;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classStructures as $fs): ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <?= htmlspecialchars($fs['head_name']) ?>
                                        <?php if ($fs['head_code']): ?>
                                            <code style="font-size: 10px; margin-left: 4px;"><?= $fs['head_code'] ?></code>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 700; color: #1f9e8b;">₹<?= number_format($fs['amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $freqLabels = ['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'half_yearly' => 'Half Yearly', 'yearly' => 'Yearly', 'one_time' => 'One Time'];
                                            $freqColors = ['monthly' => '#1f9e8b', 'quarterly' => '#6366F1', 'half_yearly' => '#E65100', 'yearly' => '#7B1FA2', 'one_time' => '#1565C0'];
                                        ?>
                                        <span class="badge" style="background: <?= $freqColors[$fs['frequency']] ?? '#999' ?>15; color: <?= $freqColors[$fs['frequency']] ?? '#999' ?>;">
                                            <?= $freqLabels[$fs['frequency']] ?? ucfirst($fs['frequency']) ?>
                                        </span>
                                    </td>
                                    <td><?= $fs['due_day'] ?>th</td>
                                    <td>
                                        <div style="display: flex; gap: 4px;">
                                            <button onclick="editStructure(<?= htmlspecialchars(json_encode($fs)) ?>)" class="btn btn-sm" 
                                                    style="background:#E0F2F1;color:#1f9e8b;border:none;padding:4px 8px;border-radius:6px;">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="<?= APP_URL ?>/fees/delete-structure/<?= $fs['id'] ?>"
                                                  style="display:inline;" onsubmit="return confirm('Remove this fee?')">
                                                <button type="submit" class="btn btn-sm" style="background:#FFEBEE;color:#C62828;border:none;padding:4px 8px;border-radius:6px;">
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
                <div class="card-body" style="text-align: center; padding: 24px; color: var(--gray-400);">
                    <p style="margin: 0; font-size: 13px;">No fees assigned to this class yet</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Add Structure Modal -->
<div id="addStructModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 520px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-grid" style="color: #1f9e8b;"></i> Assign Fee to Class</h3>
            <button onclick="document.getElementById('addStructModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/fees/save-structure">
                <?= Session::csrfField() ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Class <span style="color: var(--danger);">*</span></label>
                        <select class="form-control" name="class_id" required>
                            <option value="">— Select —</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>">Class <?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fee Head <span style="color: var(--danger);">*</span></label>
                        <select class="form-control" name="fee_head_id" id="addFeeHeadSelect" required>
                            <option value="">— Select —</option>
                            <?php foreach ($heads as $h): ?>
                                <option value="<?= $h['id'] ?>">
                                    <?= htmlspecialchars($h['name']) ?>
                                    <?= $h['code'] ? '(' . $h['code'] . ')' : '' ?>
                                    — <?= ucfirst($h['type'] ?? 'mandatory') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Amount (₹) <span style="color: var(--danger);">*</span></label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frequency</label>
                        <select class="form-control" name="frequency">
                            <option value="one_time" selected>One Time</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Day</label>
                        <input type="number" class="form-control" name="due_day" value="10" min="1" max="28">
                    </div>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('addStructModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Structure Modal -->
<div id="editStructModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 480px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-pencil" style="color: #1f9e8b;"></i> Edit Fee Structure</h3>
            <button onclick="document.getElementById('editStructModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <div id="editStructInfo" style="padding: 10px 14px; background: var(--gray-50); border-radius: 8px; margin-bottom: 16px; font-size: 13px;"></div>
            <form id="editStructForm" method="POST">
                <?= Session::csrfField() ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Amount (₹) <span style="color: var(--danger);">*</span></label>
                        <input type="number" class="form-control" name="amount" id="editStructAmount" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frequency</label>
                        <select class="form-control" name="frequency" id="editStructFreq">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="yearly">Yearly</option>
                            <option value="one_time">One Time</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Day</label>
                        <input type="number" class="form-control" name="due_day" id="editStructDueDay" min="1" max="28">
                    </div>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('editStructModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStructure(fs) {
    document.getElementById('editStructForm').action = '<?= APP_URL ?>/fees/update-structure/' + fs.id;
    document.getElementById('editStructAmount').value = fs.amount;
    document.getElementById('editStructFreq').value = fs.frequency;
    document.getElementById('editStructDueDay').value = fs.due_day;
    document.getElementById('editStructInfo').innerHTML = '<strong>' + fs.head_name + '</strong> — Class ' + fs.class_name;
    document.getElementById('editStructModal').style.display = 'flex';
}
</script>
