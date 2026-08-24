<!-- Class Subject Assignment -->
<div class="card mb-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-plus-circle" style="color: var(--primary);"></i> Assign Subject to <?= htmlspecialchars($class['name']) ?>
        </h3>
        <span class="badge" style="background: #E3F2FD; color: #1565C0; font-size: 12px;"><?= htmlspecialchars($class['year_name']) ?></span>
    </div>
    <div class="card-body">
        <?php
            $unassigned = array_filter($allSubjects, fn($s) => !in_array($s['id'], $assignedIds));
        ?>
        <?php if (!empty($unassigned)): ?>
            <form action="<?= APP_URL ?>/academic/assign-subject" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                
                <div style="display: grid; grid-template-columns: 2fr 2fr 1fr auto; gap: 12px; align-items: end;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Subject <span style="color: var(--danger);">*</span></label>
                        <select class="form-control" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($unassigned as $sub): ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?> <?= $sub['code'] ? "({$sub['code']})" : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Teacher</label>
                        <select class="form-control" name="teacher_id">
                            <option value="">No teacher assigned</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Periods/Week</label>
                        <input type="number" class="form-control" name="periods_per_week" value="5" min="1" max="20">
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 38px;"><i class="bi bi-plus-lg"></i> Assign</button>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 16px; color: var(--gray-400); font-size: 13px;">
                <i class="bi bi-check-circle" style="color: #1f9e8b;"></i> All subjects have been assigned to this class.
                <a href="<?= APP_URL ?>/academic/subjects" style="color: #1f9e8b;">Add more subjects</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Assigned Subjects -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-book" style="color: #1565C0;"></i> Assigned Subjects
        </h3>
        <span style="font-size: 12px; color: var(--gray-400);"><?= count($assigned) ?> subject<?= count($assigned) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($assigned)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Teacher</th>
                            <th>Periods/Week</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assigned as $i => $cs): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($cs['subject_name']) ?></td>
                                <td>
                                    <?php if ($cs['subject_code']): ?>
                                        <span class="badge" style="background: var(--gray-100); color: var(--gray-600);"><?= htmlspecialchars($cs['subject_code']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-300);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $tc = ['theory' => ['#E3F2FD','#1565C0'], 'practical' => ['#FFF3E0','#E65100'], 'both' => ['#E8F5E9','#2E7D32']];
                                        $c = $tc[$cs['subject_type']] ?? $tc['theory'];
                                    ?>
                                    <span class="badge" style="background: <?= $c[0] ?>; color: <?= $c[1] ?>;"><?= ucfirst($cs['subject_type']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($cs['teacher_name'])): ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 28px; height: 28px; border-radius: 50%; background: #E8F5E9; color: #2E7D32; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                                <?= strtoupper(substr($cs['teacher_name'], 0, 2)) ?>
                                            </div>
                                            <span style="font-size: 13px;"><?= htmlspecialchars($cs['teacher_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--gray-400);">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight: 600;"><?= $cs['periods_per_week'] ?></span>
                                    <span style="font-size: 11px; color: var(--gray-400);">/week</span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="btn btn-sm"
                                                style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;"
                                                onclick="openEditAssignment(<?= htmlspecialchars(json_encode($cs)) ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="<?= APP_URL ?>/academic/remove-subject/<?= $cs['id'] ?>"
                                              style="display:inline;" onsubmit="return confirm('Remove <?= htmlspecialchars($cs['subject_name']) ?> from this class?')">
                                            <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;">
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
            <div class="empty-state" style="padding: 40px;">
                <i class="bi bi-book" style="font-size: 40px; color: var(--gray-300); margin-bottom: 8px;"></i>
                <h3>No subjects assigned</h3>
                <p>Assign subjects to this class using the form above</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div id="editAssignModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 440px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-pencil" style="color: var(--primary);"></i> Edit Assignment</h3>
            <button onclick="document.getElementById('editAssignModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editAssignForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Teacher</label>
                    <select class="form-control" name="teacher_id" id="editTeacher">
                        <option value="">No teacher assigned</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Periods per Week</label>
                    <input type="number" class="form-control" name="periods_per_week" id="editPeriods" min="1" max="20">
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 12px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editAssignModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditAssignment(cs) {
    document.getElementById('editAssignForm').action = '<?= APP_URL ?>/academic/update-assignment/' + cs.id;
    document.getElementById('editTeacher').value = cs.teacher_id || '';
    document.getElementById('editPeriods').value = cs.periods_per_week;
    document.getElementById('editAssignModal').style.display = 'flex';
}
</script>
