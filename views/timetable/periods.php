<!-- Period Setup -->
<style>
    .period-card { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 1px solid var(--gray-100); border-radius: 10px; margin-bottom: 8px; transition: all 0.15s; }
    .period-card:hover { border-color: var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .period-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
    .period-info { flex: 1; }
    .period-name { font-weight: 700; font-size: 14px; }
    .period-time { font-size: 12px; color: var(--gray-400); }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;">Define the daily period structure for your school</p>
    <div style="display: flex; gap: 8px;">
        <button onclick="document.getElementById('templateModal').style.display='flex'" class="btn btn-secondary">
            <i class="bi bi-lightning"></i> Quick Template
        </button>
        <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Period
        </button>
    </div>
</div>

<?php if (!empty($periods)): ?>
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-clock" style="color: #7B1FA2;"></i> Periods (<?= count($periods) ?>)
        </h3>
    </div>
    <div class="card-body">
        <?php
            $typeColors = ['class' => '#1f9e8b', 'break' => '#FF9800', 'lunch' => '#F9A825', 'assembly' => '#3F51B5'];
            $typeLabels = ['class' => 'Class', 'break' => 'Break', 'lunch' => 'Lunch', 'assembly' => 'Assembly'];
        ?>
        <?php foreach ($periods as $p): ?>
            <div class="period-card">
                <span class="period-dot" style="background: <?= $typeColors[$p['period_type']] ?? '#999' ?>;"></span>
                <div class="period-info">
                    <div class="period-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="period-time">
                        <?= date('g:i A', strtotime($p['start_time'])) ?> – <?= date('g:i A', strtotime($p['end_time'])) ?>
                        · <span style="font-weight: 600; color: <?= $typeColors[$p['period_type']] ?? '#999' ?>;"><?= $typeLabels[$p['period_type']] ?? $p['period_type'] ?></span>
                        <?php if ($p['short_name']): ?> · <code style="font-size: 11px;"><?= htmlspecialchars($p['short_name']) ?></code><?php endif; ?>
                    </div>
                </div>
                <div style="display: flex; gap: 4px;">
                    <button onclick="editPeriod(<?= htmlspecialchars(json_encode($p)) ?>)" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="<?= APP_URL ?>/timetable/delete-period/<?= $p['id'] ?>" onclick="return confirm('Delete this period?')" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 4px 8px; border-radius: 6px;">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body" style="text-align: center; padding: 48px;">
        <i class="bi bi-clock" style="font-size: 48px; color: var(--gray-300);"></i>
        <h3 style="margin-top: 16px;">No Periods Defined</h3>
        <p style="color: var(--gray-500);">Use a template or add periods manually.</p>
    </div>
</div>
<?php endif; ?>

<!-- Add Period Modal -->
<div id="addModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 420px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Add Period</h3>
            <button onclick="document.getElementById('addModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/timetable/store-period" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group"><label class="form-label">Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. Period 1, Break" required></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Short Name</label>
                        <input type="text" class="form-control" name="short_name" placeholder="e.g. P1" maxlength="10"></div>
                    <div class="form-group"><label class="form-label">Type</label>
                        <select class="form-control" name="period_type">
                            <option value="class">Class Period</option><option value="break">Break</option>
                            <option value="lunch">Lunch</option><option value="assembly">Assembly</option>
                        </select></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Start Time</label>
                        <input type="time" class="form-control" name="start_time" required></div>
                    <div class="form-group"><label class="form-label">End Time</label>
                        <input type="time" class="form-control" name="end_time" required></div>
                </div>
                <div class="form-group"><label class="form-label">Display Order</label>
                    <input type="number" class="form-control" name="display_order" value="<?= count($periods) + 1 ?>"></div>
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Period Modal -->
<div id="editModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 420px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit Period</h3>
            <button onclick="document.getElementById('editModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group"><label class="form-label">Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="editName" name="name" required></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Short Name</label>
                        <input type="text" class="form-control" id="editShort" name="short_name" maxlength="10"></div>
                    <div class="form-group"><label class="form-label">Type</label>
                        <select class="form-control" id="editType" name="period_type">
                            <option value="class">Class Period</option><option value="break">Break</option>
                            <option value="lunch">Lunch</option><option value="assembly">Assembly</option>
                        </select></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="editStart" name="start_time" required></div>
                    <div class="form-group"><label class="form-label">End Time</label>
                        <input type="time" class="form-control" id="editEnd" name="end_time" required></div>
                </div>
                <div class="form-group"><label class="form-label">Display Order</label>
                    <input type="number" class="form-control" id="editOrder" name="display_order"></div>
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div id="templateModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 480px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-lightning" style="color: #FF9800;"></i> Quick Templates</h3>
            <button onclick="document.getElementById('templateModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;"><strong>Warning:</strong> This will replace all existing periods.</p>
            <form action="<?= APP_URL ?>/timetable/bulk-periods" method="POST">
                <?= Session::csrfField() ?>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid #1f9e8b; border-radius: 10px; cursor: pointer; background: #F0FDF9;">
                        <input type="radio" name="template" value="standard" checked style="accent-color: #1f9e8b;">
                        <div>
                            <div style="font-weight: 700; font-size: 14px;">Standard (8 Periods)</div>
                            <div style="font-size: 11px; color: var(--gray-500);">8:00 AM – 3:30 PM · Assembly + 8 periods + Break + Lunch</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid var(--gray-100); border-radius: 10px; cursor: pointer;">
                        <input type="radio" name="template" value="compact" style="accent-color: #1565C0;">
                        <div>
                            <div style="font-weight: 700; font-size: 14px;">Compact (7 Periods)</div>
                            <div style="font-size: 11px; color: var(--gray-500);">8:00 AM – 2:15 PM · 7 periods + Break + Lunch</div>
                        </div>
                    </label>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('templateModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('This will replace all existing periods. Continue?')"><i class="bi bi-lightning"></i> Apply Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPeriod(p) {
    document.getElementById('editForm').action = '<?= APP_URL ?>/timetable/update-period/' + p.id;
    document.getElementById('editName').value = p.name;
    document.getElementById('editShort').value = p.short_name || '';
    document.getElementById('editType').value = p.period_type;
    document.getElementById('editStart').value = p.start_time;
    document.getElementById('editEnd').value = p.end_time;
    document.getElementById('editOrder').value = p.display_order;
    document.getElementById('editModal').style.display = 'flex';
}
</script>
