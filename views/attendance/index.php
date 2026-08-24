<!-- Student Attendance Hub -->
<style>
    .att-class-card { border: 1px solid var(--gray-100); border-radius: 12px; padding: 16px; transition: all 0.15s; }
    .att-class-card:hover { border-color: var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .att-stat { display: flex; align-items: center; gap: 6px; font-size: 12px; }
    .att-stat .dot { width: 8px; height: 8px; border-radius: 50%; }
    .att-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 6px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p style="color: var(--gray-500); margin: 0;">
            <?= date('l, d M Y') ?> ·
            Mode: <span class="att-badge" style="background: <?= $mode === 'subject' ? '#F3E5F5' : ($mode === 'morning_evening' ? '#E3F2FD' : '#FFF3E0') ?>;
                                                     color: <?= $mode === 'subject' ? '#7B1FA2' : ($mode === 'morning_evening' ? '#1565C0' : '#E65100') ?>;">
                <?= $mode === 'subject' ? 'Subject-wise' : ($mode === 'morning_evening' ? 'Morning + Evening' : 'Morning Only') ?>
            </span>
        </p>
    </div>
    <a href="<?= APP_URL ?>/attendance/report" class="btn btn-secondary">
        <i class="bi bi-bar-chart"></i> Reports
    </a>
</div>

<!-- Date Selector -->
<div class="card mb-4">
    <div class="card-body" style="padding: 12px 20px; display: flex; align-items: center; gap: 16px;">
        <i class="bi bi-calendar3" style="color: #7B1FA2; font-size: 18px;"></i>
        <span style="font-size: 13px; font-weight: 600;">Date:</span>
        <input type="date" id="attDate" value="<?= $today ?>" class="form-control" style="width: 180px;" onchange="updateDateLinks()">
        <?php if ($mode === 'morning_evening'): ?>
            <span style="font-size: 13px; font-weight: 600; margin-left: 8px;">Session:</span>
            <select id="attSession" class="form-control" style="width: 160px;" onchange="updateDateLinks()">
                <option value="morning" <?= $selectedSession === 'morning' ? 'selected' : '' ?>>☀️ Morning</option>
                <option value="evening" <?= $selectedSession === 'evening' ? 'selected' : '' ?>>🌙 Evening</option>
            </select>
        <?php endif; ?>
    </div>
</div>

<!-- Class Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
    <?php foreach ($classes as $c): ?>
        <?php if (!empty($c['sections'])): ?>
            <?php foreach ($c['sections'] as $sec): ?>
                <?php
                    $key = $c['id'] . '-' . $sec['id'] . '-' . $selectedSession;
                    $summary = $summaryMap[$key] ?? null;
                    $isMarked = !empty($summary);
                    $presentPct = $summary ? round(($summary['present_count'] / max(1, $summary['total'])) * 100) : 0;
                ?>
                <div class="att-class-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <h4 style="font-size: 15px; font-weight: 700; margin: 0;">
                                <?= htmlspecialchars($c['name']) ?> – <?= htmlspecialchars($sec['name']) ?>
                            </h4>
                        </div>
                        <?php if ($isMarked): ?>
                            <span class="att-badge" style="background: #E0F2F1; color: #1f9e8b;">
                                <i class="bi bi-check-circle"></i> Done
                            </span>
                        <?php else: ?>
                            <span class="att-badge" style="background: #FFEBEE; color: #C62828;">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($isMarked): ?>
                        <!-- Stats -->
                        <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                            <div class="att-stat"><span class="dot" style="background: #4CAF50;"></span> <?= $summary['present_count'] ?> Present</div>
                            <div class="att-stat"><span class="dot" style="background: #F44336;"></span> <?= $summary['absent_count'] ?> Absent</div>
                            <?php if (($summary['late_count'] ?? 0) > 0): ?>
                                <div class="att-stat"><span class="dot" style="background: #FF9800;"></span> <?= $summary['late_count'] ?> Late</div>
                            <?php endif; ?>
                        </div>
                        <!-- Progress bar -->
                        <div style="height: 4px; background: var(--gray-100); border-radius: 4px; overflow: hidden; margin-bottom: 12px;">
                            <div style="height: 100%; width: <?= $presentPct ?>%; background: #4CAF50; border-radius: 4px;"></div>
                        </div>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>/attendance/mark?class_id=<?= $c['id'] ?>&section_id=<?= $sec['id'] ?>&date=<?= $today ?>&session=<?= $selectedSession ?>"
                       class="btn btn-sm att-mark-link"
                       style="background: <?= $isMarked ? '#E0F2F1' : '#1f9e8b' ?>; color: <?= $isMarked ? '#1f9e8b' : 'white' ?>; border: none; width: 100%; text-align: center; font-weight: 600; border-radius: 8px; padding: 8px;">
                        <i class="bi bi-<?= $isMarked ? 'pencil' : 'clipboard-check' ?>"></i>
                        <?= $isMarked ? 'Edit Attendance' : 'Mark Attendance' ?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php if (empty($classes)): ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 48px;">
            <i class="bi bi-people" style="font-size: 48px; color: var(--gray-300);"></i>
            <h3 style="margin-top: 16px;">No Classes Found</h3>
            <p style="color: var(--gray-500);">Set up classes and sections in School Setup first.</p>
        </div>
    </div>
<?php endif; ?>

<script>
function updateDateLinks() {
    const date = document.getElementById('attDate').value;
    const sessionEl = document.getElementById('attSession');
    const session = sessionEl ? sessionEl.value : 'morning';

    window.location.href = '<?= APP_URL ?>/attendance?date=' + date + '&session=' + session;
}
</script>
