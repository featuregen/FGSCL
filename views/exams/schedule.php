
<div class="content-header">
    <h2 class="content-title"><?= htmlspecialchars($exam['name']) ?> - Schedule</h2>
    <a href="<?= APP_URL ?>/exams" class="btn btn-outline">
        <i class="bi-arrow-left"></i> Back to Exams
    </a>
</div>

<!-- Class Filter -->
<div class="mb-4">
    <h3 style="font-size: 14px; color: var(--gray-500); margin-bottom: 12px; font-weight: 600;">Select Class</h3>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php foreach ($classes as $c): ?>
            <a href="?class_id=<?= $c['id'] ?>" 
               style="<?= $c['id'] == $classId ? 'background: var(--primary); color: white; border: 1px solid var(--primary);' : 'background: white; color: var(--gray-600); border: 1px solid var(--gray-300);' ?> padding: 6px 16px; font-size: 13px; text-decoration: none; border-radius: 20px; transition: all 0.2s;"
               onmouseover="this.style.opacity='0.8'" 
               onmouseout="this.style.opacity='1'">
                <?= htmlspecialchars($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($classId && !empty($subjects)): ?>
<form action="<?= APP_URL ?>/exams/save-schedule/<?= $exam['id'] ?>" method="POST">
    <?= Session::csrfField() ?>
    <input type="hidden" name="class_id" value="<?= $classId ?>">
    
    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Max Marks</th>
                        <th>Pass Marks</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $s): ?>
                    <?php $sch = $scheduleMap[$s['id']] ?? null; ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($s['name']) ?></strong>
                            <div class="text-xs text-muted"><?= htmlspecialchars($s['code']) ?></div>
                        </td>
                        <td>
                            <input type="date" name="exam_date[<?= $s['id'] ?>]" class="form-control" value="<?= $sch['exam_date'] ?? '' ?>">
                        </td>
                        <td>
                            <input type="time" name="start_time[<?= $s['id'] ?>]" class="form-control" value="<?= $sch['start_time'] ?? '' ?>">
                        </td>
                        <td>
                            <input type="time" name="end_time[<?= $s['id'] ?>]" class="form-control" value="<?= $sch['end_time'] ?? '' ?>">
                        </td>
                        <td>
                            <input type="number" name="max_marks[<?= $s['id'] ?>]" class="form-control" value="<?= $sch['max_marks'] ?? '100' ?>" style="width: 80px;">
                        </td>
                        <td>
                            <input type="number" name="passing_marks[<?= $s['id'] ?>]" class="form-control" value="<?= $sch['passing_marks'] ?? '33' ?>" style="width: 80px;">
                        </td>
                        <td>
                            <input type="text" name="room_no[<?= $s['id'] ?>]" class="form-control" value="<?= htmlspecialchars($sch['room_no'] ?? '') ?>" placeholder="e.g. 101" style="width: 100px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Save Schedule</button>
        </div>
    </div>
</form>
<?php elseif ($classId): ?>
    <div class="alert alert-warning">
        <i class="bi-exclamation-triangle"></i> No subjects found for this class. Please assign subjects in School Setup first.
    </div>
<?php endif; ?>
