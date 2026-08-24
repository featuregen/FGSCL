
<style>
@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>

<div class="content-header">
    <h2 class="content-title">Homework Assignments</h2>
    <?php if (Session::hasPermission('homework.create')): ?>
    <button class="btn btn-primary" onclick="openModal('homeworkModal')">
        <i class="bi-plus"></i> Create Homework
    </button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class & Section</th>
                    <th>Subject</th>
                    <th>Assign Date</th>
                    <th>Due Date</th>
                    <th>Assigned By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homework as $hw): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($hw['title']) ?></strong>
                        <?php if ($hw['attachment']): ?>
                            <br><a href="<?= APP_URL ?>/public/uploads/homework/<?= $hw['attachment'] ?>" target="_blank" class="text-xs text-primary"><i class="bi-paperclip"></i> Attachment</a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($hw['class_name']) ?> <?= $hw['section_name'] ? ' - ' . htmlspecialchars($hw['section_name']) : '' ?></td>
                    <td><?= htmlspecialchars($hw['subject_name']) ?></td>
                    <td><?= date('d M, Y', strtotime($hw['assign_date'])) ?></td>
                    <td>
                        <span class="<?= strtotime($hw['due_date']) < time() ? 'text-danger' : 'text-success' ?>">
                            <?= date('d M, Y', strtotime($hw['due_date'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($hw['created_by_name']) ?></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= APP_URL ?>/homework/submissions/<?= $hw['id'] ?>" class="btn btn-sm" style="background: #E3F2FD; color: #1565C0; border: none; padding: 4px 8px; border-radius: 6px;" title="Submissions">
                                <i class="bi-eye"></i>
                            </a>
                            <?php if (Session::hasPermission('homework.delete')): ?>
                            <form action="<?= APP_URL ?>/homework/delete/<?= $hw['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this assignment?');">
                                <?= Session::csrfField() ?>
                                <button type="submit" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 4px 8px; border-radius: 6px;" title="Delete">
                                    <i class="bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($homework)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No homework assignments found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Homework Drawer (Deck) -->
<div id="homeworkModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: flex-end;">
    <div class="card" style="width: 550px; max-width: 100vw; height: 100vh; margin: 0; border-radius: 0; display: flex; flex-direction: column; animation: slideIn 0.3s ease-out;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--gray-200);">
            <h3 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0;">Create Homework</h3>
            <button class="btn-icon" onclick="closeModal('homeworkModal')" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--gray-500);"><i class="bi-x"></i></button>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; padding: 20px;">
            <form action="<?= APP_URL ?>/homework/store" method="POST" enctype="multipart/form-data">
                <?= Session::csrfField() ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="classSelect" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sections</label>
                        <div id="sectionCheckboxes" style="display: flex; flex-wrap: wrap; gap: 8px; padding: 8px 0; min-height: 36px;">
                            <span class="text-muted text-sm">Select a class first</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Assign Date <span class="text-danger">*</span></label>
                        <input type="date" name="assign_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('homeworkModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

const allSections = <?= json_encode($sections ?? []) ?>;

document.getElementById('classSelect').addEventListener('change', function() {
    const classId = this.value;
    const container = document.getElementById('sectionCheckboxes');
    
    if (!classId) {
        container.innerHTML = '<span class="text-muted text-sm">Select a class first</span>';
        return;
    }
    
    const filtered = allSections.filter(s => s.class_id == classId);
    
    if (filtered.length === 0) {
        container.innerHTML = '<span class="text-muted text-sm">No sections for this class</span>';
        return;
    }
    
    let html = '<label style="display:flex; align-items:center; gap:6px; padding:6px 14px; border-radius:20px; background:var(--gray-100); cursor:pointer; font-size:13px; font-weight:500; transition:all .2s;">' +
        '<input type="checkbox" id="selectAllSections" onchange="toggleAllSections(this)" style="accent-color:#1f9e8b;"> All Sections</label>';
    
    filtered.forEach(section => {
        html += `<label style="display:flex; align-items:center; gap:6px; padding:6px 14px; border-radius:20px; background:var(--gray-100); cursor:pointer; font-size:13px; font-weight:500; transition:all .2s;">` +
            `<input type="checkbox" name="section_ids[]" value="${section.id}" class="section-cb" style="accent-color:#1f9e8b;"> ${section.name}</label>`;
    });
    
    container.innerHTML = html;
});

function toggleAllSections(master) {
    document.querySelectorAll('.section-cb').forEach(cb => cb.checked = master.checked);
}
</script>
