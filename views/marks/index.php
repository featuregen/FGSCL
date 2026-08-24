
<div class="content-header">
    <h2 class="content-title">Marks Entry</h2>
</div>

<!-- Filters -->
<div class="card mb-4">
    <form action="" method="GET" class="row align-items-end">
        <div class="col-md-3 mb-3 mb-md-0">
            <label class="form-label">Exam</label>
            <select name="exam_id" class="form-control" onchange="this.form.submit()">
                <?php foreach ($exams as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= $e['id'] == $examId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-3 mb-md-0">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-control" onchange="this.form.submit()">
                <option value="">Select Class</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $classId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-3 mb-md-0">
            <label class="form-label">Section</label>
            <select name="section_id" class="form-control" onchange="this.form.submit()">
                <option value="">Select Section</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $sectionId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-control" onchange="this.form.submit()">
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $subjectId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($examId && $classId && $sectionId && $subjectId): ?>
    <?php if (!$schedule): ?>
        <div class="alert alert-warning">
            <i class="bi-exclamation-triangle"></i> This subject has not been scheduled for the selected exam. <a href="<?= APP_URL ?>/exams/schedule/<?= $examId ?>?class_id=<?= $classId ?>">Go to Schedule</a>
        </div>
    <?php else: ?>
        <form action="<?= APP_URL ?>/marks/store" method="POST">
            <?= Session::csrfField() ?>
            <input type="hidden" name="exam_id" value="<?= $examId ?>">
            <input type="hidden" name="class_id" value="<?= $classId ?>">
            <input type="hidden" name="section_id" value="<?= $sectionId ?>">
            <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Max Marks:</strong> <?= $schedule['max_marks'] ?> &nbsp;|&nbsp;
                        <strong>Passing Marks:</strong> <?= $schedule['passing_marks'] ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Roll No</th>
                                <th>Student Name</th>
                                <th style="width: 150px;">Marks Obtained</th>
                                <th style="width: 100px;">Absent</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $stu): ?>
                            <?php $m = $marksMap[$stu['id']] ?? null; ?>
                            <tr>
                                <td><?= htmlspecialchars($stu['roll_number']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($stu['full_name']) ?></strong>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="marks[<?= $stu['id'] ?>]" 
                                           class="form-control marks-input" 
                                           value="<?= $m ? htmlspecialchars($m['marks_obtained']) : '' ?>"
                                           max="<?= $schedule['max_marks'] ?>"
                                           <?= ($m && $m['is_absent']) ? 'disabled' : '' ?>>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="absent[<?= $stu['id'] ?>]" 
                                               class="absent-toggle"
                                               value="1" <?= ($m && $m['is_absent']) ? 'checked' : '' ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="remarks[<?= $stu['id'] ?>]" class="form-control" value="<?= $m ? htmlspecialchars($m['remarks']) : '' ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No students found in this section.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($students)): ?>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Save Marks</button>
                </div>
                <?php endif; ?>
            </div>
        </form>

        <script>
        document.querySelectorAll('.absent-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const tr = this.closest('tr');
                const marksInput = tr.querySelector('.marks-input');
                if (this.checked) {
                    marksInput.value = '';
                    marksInput.disabled = true;
                } else {
                    marksInput.disabled = false;
                }
            });
        });
        </script>
    <?php endif; ?>
<?php endif; ?>
