<!-- Class Timetable View -->
<style>
    .tt-grid { width: 100%; border-collapse: collapse; }
    .tt-grid th, .tt-grid td { border: 1px solid var(--gray-100); padding: 0; text-align: center; vertical-align: top; }
    .tt-grid th { padding: 10px 8px; font-size: 12px; font-weight: 700; color: var(--gray-600); background: var(--gray-50); }
    .tt-grid th.day-header { width: 70px; }
    .tt-cell { min-height: 60px; padding: 6px; cursor: pointer; transition: all 0.15s; position: relative; }
    .tt-cell:hover { background: #F0FDF9; }
    .tt-cell.filled { background: #F8FFFE; }
    .tt-cell .subject { font-size: 11px; font-weight: 700; color: #1f9e8b; line-height: 1.3; }
    .tt-cell .teacher { font-size: 10px; color: var(--gray-400); margin-top: 2px; }
    .tt-cell.break-cell { background: #FFF8E1; cursor: default; }
    .tt-cell.break-cell:hover { background: #FFF8E1; }
    .tt-break-label { font-size: 10px; color: #F9A825; font-weight: 600; }
    .tt-cell .edit-icon { position: absolute; top: 2px; right: 2px; font-size: 10px; color: var(--gray-300); opacity: 0; transition: opacity 0.15s; }
    .tt-cell:hover .edit-icon { opacity: 1; }
</style>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-calendar-week" style="color: #7B1FA2;"></i>
            <?= htmlspecialchars($class['name']) ?> – Section <?= htmlspecialchars($section['name']) ?>
        </h3>
        <div style="display: flex; gap: 8px;">
            <button id="fastModeBtn" type="button" class="btn btn-sm btn-outline-primary" onclick="toggleFastMode()">
                <i class="bi bi-lightning"></i> Fast Entry Mode
            </button>
            <a href="<?= APP_URL ?>/timetable" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <?php
            $classPeriods = array_filter($periods, fn($p) => $p['period_type'] === 'class');
            $allPeriods = $periods;
            $dayNames = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6];
        ?>
        <table class="tt-grid">
            <thead>
                <tr>
                    <th class="day-header">Day</th>
                    <?php foreach ($allPeriods as $p): ?>
                        <th style="min-width: 90px;">
                            <div><?= htmlspecialchars($p['short_name'] ?? $p['name']) ?></div>
                            <div style="font-weight: 400; font-size: 10px; color: var(--gray-400);">
                                <?= date('g:i', strtotime($p['start_time'])) ?>–<?= date('g:i', strtotime($p['end_time'])) ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dayNames as $dayLabel => $dayNum): ?>
                    <tr>
                        <td style="padding: 10px; font-weight: 700; font-size: 13px; background: var(--gray-50); color: var(--gray-600);">
                            <?= $dayLabel ?>
                        </td>
                        <?php foreach ($allPeriods as $p): ?>
                            <?php if ($p['period_type'] !== 'class'): ?>
                                <td>
                                    <div class="tt-cell break-cell">
                                        <span class="tt-break-label">
                                            <?= $p['period_type'] === 'lunch' ? '🍽 Lunch' : ($p['period_type'] === 'assembly' ? '🏫 Assembly' : '☕ Break') ?>
                                        </span>
                                    </div>
                                </td>
                            <?php else: ?>
                                <?php $entry = $schedule[$dayNum][$p['id']] ?? null; ?>
                                <td>
                                    <div class="tt-cell <?= $entry ? 'filled' : '' ?>"
                                         onclick="openSlotEditor(<?= $dayNum ?>, <?= $p['id'] ?>, <?= $entry ? htmlspecialchars(json_encode($entry)) : 'null' ?>)">
                                        
                                        <!-- Static Display -->
                                        <div class="tt-static">
                                            <?php if ($entry): ?>
                                                <div class="subject"><?= htmlspecialchars($entry['subject_code'] ?? $entry['subject_name']) ?></div>
                                                <?php if ($entry['teacher_name']): ?>
                                                    <div class="teacher"><?= htmlspecialchars(explode(' ', $entry['teacher_name'])[0]) ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div style="font-size: 18px; color: var(--gray-200);">+</div>
                                            <?php endif; ?>
                                            <span class="edit-icon"><i class="bi bi-pencil"></i></span>
                                        </div>

                                        <!-- Fast Entry Select -->
                                        <select class="tt-fast-select" style="display: none; width: 100%; border: 1px solid var(--gray-200); border-radius: 4px; padding: 4px; font-size: 11px; background: white;" onchange="quickSave(<?= $dayNum ?>, <?= $p['id'] ?>, this)">
                                            <option value="0">—</option>
                                            <?php foreach ($subjects as $sub): ?>
                                                <option value="<?= $sub['subject_id'] ?>" data-teacher="<?= $sub['teacher_id'] ?>" <?= ($entry['subject_id'] ?? 0) == $sub['subject_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($sub['code'] ?? $sub['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Slot Editor Modal -->
<div id="slotModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 380px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 id="slotTitle" style="font-size: 16px; font-weight: 700; margin: 0;">Edit Slot</h3>
            <button onclick="document.getElementById('slotModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/timetable/save-slot" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
                <input type="hidden" name="day_of_week" id="slotDay">
                <input type="hidden" name="period_id" id="slotPeriod">

                <div class="form-group">
                    <label class="form-label">Subject <span style="color: var(--danger);">*</span></label>
                    <select class="form-control" name="subject_id" id="slotSubject" onchange="autoFillTeacher(this.value)">
                        <option value="0">— Clear Slot —</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['subject_id'] ?>" data-teacher="<?= $sub['teacher_id'] ?? '' ?>"><?= htmlspecialchars($sub['name']) ?><?= $sub['code'] ? " ({$sub['code']})" : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Teacher</label>
                    <select class="form-control" name="teacher_id" id="slotTeacher">
                        <option value="">— Auto from subject —</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span style="font-size: 11px; color: var(--gray-400);">Leave empty to use the subject's assigned teacher</span>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('slotModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subjects Legend -->
<div class="card" style="margin-top: 16px;">
    <div class="card-header">
        <h3 style="font-size: 14px; font-weight: 700; margin: 0;"><i class="bi bi-palette" style="color: #E65100;"></i> Subjects assigned to this class</h3>
    </div>
    <div class="card-body" style="padding: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
        <?php foreach ($subjects as $s): ?>
            <span class="badge" style="background: #E0F2F1; color: #1f9e8b; padding: 6px 12px; font-size: 12px;">
                <?= htmlspecialchars($s['name']) ?> <?= $s['code'] ? "({$s['code']})" : '' ?>
                <span style="font-weight: normal; color: var(--gray-500); margin-left: 4px;">– <?= htmlspecialchars(explode(' ', $s['teacher_name'] ?? 'No Teacher')[0]) ?></span>
            </span>
        <?php endforeach; ?>
        <?php if (empty($subjects)): ?>
            <p style="font-size: 12px; color: var(--gray-400); margin: 0;">No subjects assigned to this class yet. Go to Academics > Class Subjects.</p>
        <?php endif; ?>
    </div>
</div>

<script>
let fastMode = false;
function toggleFastMode() {
    fastMode = !fastMode;
    document.querySelectorAll('.tt-static').forEach(el => el.style.display = fastMode ? 'none' : 'block');
    document.querySelectorAll('.tt-fast-select').forEach(el => el.style.display = fastMode ? 'block' : 'none');
    document.getElementById('fastModeBtn').innerHTML = fastMode ? '<i class="bi bi-x-circle"></i> Exit Fast Mode' : '<i class="bi bi-lightning"></i> Fast Entry Mode';
    document.getElementById('fastModeBtn').className = fastMode ? 'btn btn-sm btn-danger' : 'btn btn-sm btn-outline-primary';
}

function quickSave(day, period, selectEl) {
    const subjectId = selectEl.value;
    const teacherId = selectEl.options[selectEl.selectedIndex].dataset.teacher || '';
    
    selectEl.style.opacity = '0.5';

    const formData = new FormData();
    formData.append('csrf_token', '<?= Session::csrfToken() ?>');
    formData.append('class_id', '<?= $class['id'] ?>');
    formData.append('section_id', '<?= $section['id'] ?>');
    formData.append('day_of_week', day);
    formData.append('period_id', period);
    formData.append('subject_id', subjectId);
    formData.append('teacher_id', teacherId);

    fetch('<?= APP_URL ?>/timetable/save-slot', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        selectEl.style.opacity = '1';
        if (data.success) {
            const cell = selectEl.closest('.tt-cell');
            if (subjectId === '0') {
                cell.querySelector('.tt-static').innerHTML = '<div style="font-size: 18px; color: var(--gray-200);">+</div><span class="edit-icon"><i class="bi bi-pencil"></i></span>';
                cell.classList.remove('filled');
            } else {
                const text = selectEl.options[selectEl.selectedIndex].text;
                cell.querySelector('.tt-static').innerHTML = '<div class="subject">' + text + '</div><span class="edit-icon"><i class="bi bi-pencil"></i></span>';
                cell.classList.add('filled');
            }
        } else {
            alert('Error saving slot: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Failed to save slot');
        selectEl.style.opacity = '1';
    });
}

function openSlotEditor(day, period, entry) {
    try {
        if (fastMode) return;
        
        // Populate modal fields
        const slotDayEl = document.getElementById('slotDay');
        const slotPeriodEl = document.getElementById('slotPeriod');
        const slotSubjectEl = document.getElementById('slotSubject');
        const slotTeacherEl = document.getElementById('slotTeacher');
        const slotModalEl = document.getElementById('slotModal');

        if (!slotDayEl || !slotPeriodEl || !slotSubjectEl || !slotTeacherEl || !slotModalEl) {
            console.error('Modal elements not found!');
            return;
        }

        slotDayEl.value = day;
        slotPeriodEl.value = period;
        
        if (entry && typeof entry === 'object') {
            slotSubjectEl.value = entry.subject_id || '0';
            slotTeacherEl.value = entry.teacher_id || '';
        } else {
            slotSubjectEl.value = '0';
            slotTeacherEl.value = '';
        }
        
        // Show modal
        slotModalEl.style.display = 'flex';
        
    } catch (e) {
        console.error('openSlotEditor error:', e);
        alert('Could not open editor. Please check the console for details.');
    }
}

function autoFillTeacher(subjectId) {
    try {
        if (subjectId == '0') {
            document.getElementById('slotTeacher').value = '';
            return;
        }
        const select = document.getElementById('slotSubject');
        const option = select.options[select.selectedIndex];
        const teacherId = option.getAttribute('data-teacher');
        if (teacherId) {
            document.getElementById('slotTeacher').value = teacherId;
        }
    } catch (e) {
        console.error('autoFillTeacher error:', e);
    }
}
</script>
