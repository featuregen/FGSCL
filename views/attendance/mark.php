<!-- Mark Attendance -->
<style>
    .student-row { display: flex; align-items: center; padding: 10px 16px; border-bottom: 1px solid var(--gray-50); transition: background 0.1s; }
    .student-row:hover { background: var(--gray-50); }
    .student-row:last-child { border-bottom: none; }
    .student-row .student-info { flex: 1; display: flex; align-items: center; gap: 12px; }
    .student-row .avatar { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: white; flex-shrink: 0; }
    .student-row .status-group { display: flex; gap: 4px; }
    .status-btn { border: none; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; background: var(--gray-100); color: var(--gray-500); }
    .status-btn:hover { opacity: 0.85; }
    .status-btn.active-present { background: #4CAF50; color: white; }
    .status-btn.active-absent { background: #F44336; color: white; }
    .status-btn.active-late { background: #FF9800; color: white; }
    .status-btn.active-half_day { background: #2196F3; color: white; }
    .status-btn.active-excused { background: #9C27B0; color: white; }
    .remarks-input { width: 120px; font-size: 11px; padding: 4px 8px; border: 1px solid var(--gray-100); border-radius: 6px; }
    .period-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border: 2px solid var(--gray-100); border-radius: 10px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.15s; text-decoration: none; color: var(--gray-600); }
    .period-pill:hover { border-color: #7B1FA2; }
    .period-pill.active { border-color: #7B1FA2; background: #F3E5F5; color: #7B1FA2; }
    .period-pill.done { border-color: #1f9e8b; }
    .summary-box { display: flex; gap: 20px; padding: 12px 16px; background: var(--gray-50); border-radius: 10px; }
    .summary-item { text-align: center; }
    .summary-item .num { font-size: 20px; font-weight: 800; }
    .summary-item .lbl { font-size: 10px; color: var(--gray-400); text-transform: uppercase; }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <?= htmlspecialchars($class['name']) ?> – <?= htmlspecialchars($section['name']) ?>
            <?php if ($mode === 'subject' && $subjectName): ?>
                · <span style="color: #7B1FA2;"><?= htmlspecialchars($subjectName) ?></span>
            <?php endif; ?>
        </h3>
        <p style="font-size: 13px; color: var(--gray-400); margin: 4px 0 0;">
            <?= date('l, d M Y', strtotime($date)) ?>
            <?php if ($mode !== 'subject'): ?>
                · <?= $sessionType === 'evening' ? '🌙 Evening Session' : '☀️ Morning Session' ?>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= APP_URL ?>/attendance" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<?php if ($mode === 'subject' && !empty($periods)): ?>
<!-- Period Selector for Subject-wise -->
<div class="card mb-4">
    <div class="card-body" style="padding: 12px 16px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 12px; font-weight: 700; color: var(--gray-500); margin-right: 8px;">SELECT PERIOD:</span>
            <?php foreach ($periods as $p): ?>
                <?php
                    $isActive = ($periodId == $p['id']);
                    $url = APP_URL . "/attendance/mark?class_id={$class['id']}&section_id={$section['id']}&date={$date}&session=period&period_id={$p['id']}";
                ?>
                <a href="<?= $url ?>" class="period-pill <?= $isActive ? 'active' : '' ?>">
                    <span><?= htmlspecialchars($p['short_name'] ?? $p['name']) ?></span>
                    <?php if ($p['subject_name']): ?>
                        <span style="font-size: 10px; color: <?= $isActive ? '#7B1FA2' : 'var(--gray-400)' ?>;">
                            <?= htmlspecialchars($p['subject_code'] ?? $p['subject_name']) ?>
                        </span>
                    <?php else: ?>
                        <span style="font-size: 10px; color: var(--gray-300);">—</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($mode === 'subject' && !$periodId): ?>
    <!-- Prompt to select period -->
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 48px;">
            <i class="bi bi-clock" style="font-size: 48px; color: #7B1FA2;"></i>
            <h3 style="margin-top: 16px; color: var(--gray-700);">Select a Period</h3>
            <p style="color: var(--gray-500);">Choose a period from above to mark subject-wise attendance</p>
        </div>
    </div>
<?php else: ?>

<!-- Quick Actions -->
<?php if ($canEdit): ?>
<div class="card mb-3">
    <div class="card-body" style="padding: 10px 16px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 8px; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; color: var(--gray-500);">QUICK:</span>
            <button type="button" onclick="markAllStatus('present')" class="btn btn-sm" style="background: #E8F5E9; color: #2E7D32; border: none; font-weight: 600; font-size: 12px; border-radius: 6px;">
                <i class="bi bi-check-all"></i> All Present
            </button>
            <button type="button" onclick="markAllStatus('absent')" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; font-weight: 600; font-size: 12px; border-radius: 6px;">
                <i class="bi bi-x-lg"></i> All Absent
            </button>
        </div>
        <div class="summary-box" id="liveSummary">
            <div class="summary-item"><div class="num" id="sumTotal"><?= count($students) ?></div><div class="lbl">Total</div></div>
            <div class="summary-item"><div class="num" style="color: #4CAF50;" id="sumPresent">0</div><div class="lbl">Present</div></div>
            <div class="summary-item"><div class="num" style="color: #F44336;" id="sumAbsent">0</div><div class="lbl">Absent</div></div>
            <?php if ($lateEnabled): ?><div class="summary-item"><div class="num" style="color: #FF9800;" id="sumLate">0</div><div class="lbl">Late</div></div><?php endif; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning" style="font-size: 13px; margin-bottom: 16px;">
    <i class="bi bi-exclamation-triangle-fill"></i> <strong>Editing Restricted:</strong> You cannot modify attendance for past dates.
</div>
<?php endif; ?>

<!-- Student List -->
<form action="<?= APP_URL ?>/attendance/store" method="POST" id="attendanceForm">
    <?= Session::csrfField() ?>
    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
    <input type="hidden" name="attendance_date" value="<?= $date ?>">
    <input type="hidden" name="session_type" value="<?= $sessionType ?>">
    <?php if ($periodId): ?><input type="hidden" name="period_id" value="<?= $periodId ?>"><?php endif; ?>
    <?php if ($subjectId): ?><input type="hidden" name="subject_id" value="<?= $subjectId ?>"><?php endif; ?>

    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 14px; font-weight: 700; margin: 0;">
                <i class="bi bi-people" style="color: #1f9e8b;"></i> Students (<?= count($students) ?>)
            </h3>
            <div style="display: flex; gap: 6px; font-size: 11px;">
                <span style="display: flex; align-items: center; gap: 4px;"><span class="dot" style="width:8px;height:8px;border-radius:50%;background:#4CAF50;display:inline-block;"></span>Present</span>
                <span style="display: flex; align-items: center; gap: 4px;"><span class="dot" style="width:8px;height:8px;border-radius:50%;background:#F44336;display:inline-block;"></span>Absent</span>
                <?php if ($lateEnabled): ?><span style="display: flex; align-items: center; gap: 4px;"><span class="dot" style="width:8px;height:8px;border-radius:50%;background:#FF9800;display:inline-block;"></span>Late</span><?php endif; ?>
                <?php if ($halfDayEnabled): ?><span style="display: flex; align-items: center; gap: 4px;"><span class="dot" style="width:8px;height:8px;border-radius:50%;background:#2196F3;display:inline-block;"></span>Half Day</span><?php endif; ?>
                <?php if ($excusedEnabled): ?><span style="display: flex; align-items: center; gap: 4px;"><span class="dot" style="width:8px;height:8px;border-radius:50%;background:#9C27B0;display:inline-block;"></span>Excused</span><?php endif; ?>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $i => $s): ?>
                    <?php
                        $existing = $attendanceMap[$s['id']] ?? null;
                        $currentStatus = $existing['status'] ?? '';
                        $colors = ['male' => ['#E3F2FD','#1565C0'], 'female' => ['#FCE4EC','#AD1457']];
                        $gColor = $colors[$s['gender'] ?? ''] ?? ['#E0F2F1','#1f9e8b'];
                        $initials = strtoupper(substr($s['full_name'], 0, 1));
                    ?>
                    <div class="student-row" data-student="<?= $s['id'] ?>">
                        <div class="student-info">
                            <span style="font-size: 12px; color: var(--gray-400); width: 24px; text-align: center;"><?= $i + 1 ?></span>
                            <div class="avatar" style="background: linear-gradient(135deg, <?= $gColor[0] ?>, <?= $gColor[1] ?>20); color: <?= $gColor[1] ?>;">
                                <?= $initials ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($s['full_name']) ?></div>
                                <div style="font-size: 11px; color: var(--gray-400);">
                                    <?= $s['roll_number'] ? 'Roll ' . $s['roll_number'] : '' ?>
                                    <?= $s['admission_number'] ? ' · ' . $s['admission_number'] : '' ?>
                                </div>
                            </div>
                        </div>

                        <div class="status-group">
                            <input type="hidden" name="status[<?= $s['id'] ?>]" id="status_<?= $s['id'] ?>" value="<?= $currentStatus ?: 'present' ?>">

                            <button type="button" class="status-btn <?= $currentStatus === 'present' || !$currentStatus ? 'active-present' : '' ?>"
                                    data-status="present" data-student="<?= $s['id'] ?>" <?= !$canEdit ? 'disabled' : 'onclick="setStatus(' . $s['id'] . ', \'present\', this)"' ?>>P</button>
                            <button type="button" class="status-btn <?= $currentStatus === 'absent' ? 'active-absent' : '' ?>"
                                    data-status="absent" data-student="<?= $s['id'] ?>" <?= !$canEdit ? 'disabled' : 'onclick="setStatus(' . $s['id'] . ', \'absent\', this)"' ?>>A</button>
                            <?php if ($lateEnabled): ?>
                                <button type="button" class="status-btn <?= $currentStatus === 'late' ? 'active-late' : '' ?>"
                                        data-status="late" data-student="<?= $s['id'] ?>" <?= !$canEdit ? 'disabled' : 'onclick="setStatus(' . $s['id'] . ', \'late\', this)"' ?>>L</button>
                            <?php endif; ?>
                            <?php if ($halfDayEnabled): ?>
                                <button type="button" class="status-btn <?= $currentStatus === 'half_day' ? 'active-half_day' : '' ?>"
                                        data-status="half_day" data-student="<?= $s['id'] ?>" <?= !$canEdit ? 'disabled' : 'onclick="setStatus(' . $s['id'] . ', \'half_day\', this)"' ?>>H</button>
                            <?php endif; ?>
                            <?php if ($excusedEnabled): ?>
                                <button type="button" class="status-btn <?= $currentStatus === 'excused' ? 'active-excused' : '' ?>"
                                        data-status="excused" data-student="<?= $s['id'] ?>" <?= !$canEdit ? 'disabled' : 'onclick="setStatus(' . $s['id'] . ', \'excused\', this)"' ?>>E</button>
                            <?php endif; ?>
                        </div>

                        <input type="text" class="remarks-input" name="remarks[<?= $s['id'] ?>]"
                               value="<?= htmlspecialchars($existing['remarks'] ?? '') ?>" placeholder="Remarks" style="margin-left: 12px;" <?= !$canEdit ? 'disabled' : '' ?>>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 48px; color: var(--gray-400);">
                    <i class="bi bi-people" style="font-size: 32px;"></i>
                    <p style="margin-top: 12px;">No students found in this class.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($students) && $canEdit): ?>
    <div style="margin-top: 24px; text-align: right;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 32px;">
            <i class="bi bi-check-lg"></i> Save Attendance
        </button>
    </div>
    <?php endif; ?>
</form>

<?php endif; ?>

<script>
function setStatus(studentId, status, btn) {
    document.getElementById('status_' + studentId).value = status;

    // Reset all buttons for this student
    const row = btn.closest('.student-row');
    row.querySelectorAll('.status-btn').forEach(b => {
        b.className = 'status-btn';
    });
    btn.classList.add('active-' + status);

    updateSummary();
}

function markAllStatus(status) {
    document.querySelectorAll('.student-row').forEach(row => {
        const input = row.querySelector('input[type="hidden"][name^="status"]');
        if (input) {
            input.value = status;
            row.querySelectorAll('.status-btn').forEach(b => b.className = 'status-btn');
            const btn = row.querySelector('[data-status="' + status + '"]');
            if (btn) btn.classList.add('active-' + status);
        }
    });
    updateSummary();
}

function updateSummary() {
    let present = 0, absent = 0, late = 0;
    document.querySelectorAll('input[type="hidden"][name^="status"]').forEach(input => {
        if (input.value === 'present') present++;
        else if (input.value === 'absent') absent++;
        else if (input.value === 'late') late++;
    });
    const el = (id) => document.getElementById(id);
    if (el('sumPresent')) el('sumPresent').textContent = present;
    if (el('sumAbsent')) el('sumAbsent').textContent = absent;
    if (el('sumLate')) el('sumLate').textContent = late;
}

// Run on page load
document.addEventListener('DOMContentLoaded', updateSummary);
</script>
