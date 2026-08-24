
<div class="content-header">
    <h2 class="content-title">Exam Terms</h2>
    <button class="btn btn-primary" onclick="openModal('examModal')">
        <i class="bi-plus"></i> Create Exam
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Exam Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $e): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($e['name']) ?></strong></td>
                    <td><?= $e['start_date'] ? date('d M, Y', strtotime($e['start_date'])) : '-' ?></td>
                    <td><?= $e['end_date'] ? date('d M, Y', strtotime($e['end_date'])) : '-' ?></td>
                    <td><?= htmlspecialchars($e['remarks']) ?></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= APP_URL ?>/exams/schedule/<?= $e['id'] ?>" class="btn btn-sm" style="background: #E3F2FD; color: #1565C0; border: none; padding: 4px 8px; border-radius: 6px;" title="Schedule">
                                <i class="bi-calendar-event"></i>
                            </a>
                            <a href="<?= APP_URL ?>/exams/marks/<?= $e['id'] ?>" class="btn btn-sm" style="background: #FFF3E0; color: #E65100; border: none; padding: 4px 8px; border-radius: 6px;" title="Enter Marks">
                                <i class="bi-award"></i>
                            </a>
                            <button onclick="editExam(<?= htmlspecialchars(json_encode($e)) ?>)" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;" title="Edit">
                                <i class="bi-pencil"></i>
                            </button>
                            <form action="<?= APP_URL ?>/exams/delete/<?= $e['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam?');">
                                <?= Session::csrfField() ?>
                                <button type="submit" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 4px 8px; border-radius: 6px;" title="Delete">
                                    <i class="bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($exams)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No exams found for this academic year.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Exam Modal -->
<div id="examModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 500px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="modal-title" id="modalTitle" style="font-size: 16px; font-weight: 700; margin: 0;">Create Exam Term</h3>
            <button class="btn-icon" onclick="closeModal('examModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);"><i class="bi-x"></i></button>
        </div>
        <div class="card-body">
            <form id="examForm" action="<?= APP_URL ?>/exams/store" method="POST">
                <?= Session::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="examName" class="form-control" required placeholder="e.g. Term 1, Mid-Term, Final Exam">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="startDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="endDate" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('examModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'flex';
}
function closeModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'none';
}
function editExam(exam) {
    document.getElementById('modalTitle').textContent = 'Edit Exam Term';
    document.getElementById('examForm').action = '<?= APP_URL ?>/exams/update/' + exam.id;
    document.getElementById('examName').value = exam.name;
    document.getElementById('startDate').value = exam.start_date || '';
    document.getElementById('endDate').value = exam.end_date || '';
    document.getElementById('remarks').value = exam.remarks || '';
    openModal('examModal');
}
</script>
