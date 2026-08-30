
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2 class="content-title"><?= htmlspecialchars($pageTitle) ?></h2>
        <p class="text-muted" style="margin-top: 5px;">Enter marks for students in the selected class and subject.</p>
    </div>
</div>

<div class="card mb-4" style="background: #f8f9fa; border: 1px solid var(--gray-200);">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/exams/marks/<?= $exam['id'] ?>" style="display: flex; gap: 16px; align-items: flex-end;">
            <div style="flex: 1;">
                <label class="form-label">Select Class</label>
                <select name="class_id" class="form-control" onchange="this.form.submit()">
                    <option value="0">-- Select Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $classId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label class="form-label">Select Subject</label>
                <select name="subject_id" class="form-control" onchange="this.form.submit()">
                    <?php if (empty($subjects)): ?>
                        <option value="0">-- No Subjects Scheduled --</option>
                    <?php else: ?>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $s['id'] == $subjectId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?> (Max: <?= $s['max_marks'] ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="padding-bottom: 2px;">
                <a href="<?= APP_URL ?>/exams" class="btn btn-outline">Back to Exams</a>
            </div>
        </form>
    </div>
</div>

<?php if ($classId > 0 && $subjectId > 0 && $schedule): ?>
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Student Marks Entry</h3>
            <div class="text-muted text-sm">
                Max Marks: <strong><?= $schedule['max_marks'] ?></strong> | Passing: <strong><?= $schedule['passing_marks'] ?></strong>
            </div>
        </div>
        <div class="table-responsive">
            <form method="POST" action="<?= APP_URL ?>/exams/save-marks/<?= $exam['id'] ?>">
                <?= Session::csrfField() ?>
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="schedule_id" value="<?= $schedule['schedule_id'] ?>">

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Roll No</th>
                            <th>Student Name</th>
                            <th style="width: 150px;">Marks Obtained</th>
                            <th style="width: 100px; text-align: center;">Absent</th>
                            <th>Remarks (Optional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): 
                            $mark = $marksMap[$student['id']] ?? null;
                        ?>
                        <tr>
                            <td class="text-muted"><?= htmlspecialchars($student['roll_number'] ?? '-') ?></td>
                            <td>
                                <div style="font-weight: 500;"><?= htmlspecialchars($student['full_name']) ?></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars($student['admission_no'] ?? '') ?></div>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" max="<?= $schedule['max_marks'] ?>" 
                                       name="marks[<?= $student['id'] ?>]" 
                                       class="form-control form-control-sm mark-input" 
                                       value="<?= $mark && !$mark['is_absent'] ? $mark['marks_obtained'] : '' ?>"
                                       <?= $mark && $mark['is_absent'] ? 'disabled' : '' ?>>
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" name="is_absent[<?= $student['id'] ?>]" 
                                       class="absent-checkbox" 
                                       value="1" 
                                       <?= $mark && $mark['is_absent'] ? 'checked' : '' ?>
                                       onchange="toggleMarksInput(this, '<?= $student['id'] ?>')">
                            </td>
                            <td>
                                <input type="text" name="remarks[<?= $student['id'] ?>]" 
                                       class="form-control form-control-sm" 
                                       value="<?= $mark ? htmlspecialchars($mark['remarks'] ?? '') : '' ?>"
                                       placeholder="e.g. Needs improvement">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No active students found in this class.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if (!empty($students)): ?>
                <div class="card-body" style="background: #f8f9fa; border-top: 1px solid var(--gray-200); text-align: right;">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi-check2-circle"></i> Save Marks
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
    function toggleMarksInput(checkbox, studentId) {
        const input = document.querySelector(`input[name="marks[${studentId}]"]`);
        if (checkbox.checked) {
            input.disabled = true;
            input.value = '';
        } else {
            input.disabled = false;
        }
    }
    </script>
<?php else: ?>
    <?php if ($classId > 0): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi-exclamation-circle text-warning" style="font-size: 48px;"></i>
            <h4 class="mt-3">No Subjects Scheduled</h4>
            <p class="text-muted">You haven't scheduled any subjects for this class in this exam term yet.</p>
            <a href="<?= APP_URL ?>/exams/schedule/<?= $exam['id'] ?>?class_id=<?= $classId ?>" class="btn btn-primary mt-2">
                Go to Scheduling
            </a>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

