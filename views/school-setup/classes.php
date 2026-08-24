<!-- Classes & Sections Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div style="display: flex; align-items: center; gap: 12px;">
        <label style="font-size: 13px; font-weight: 600; color: var(--gray-500); white-space: nowrap;">Academic Year:</label>
        <select class="form-control" style="width: 180px; font-weight: 600;" onchange="window.location='<?= APP_URL ?>/school-setup/classes?year_id='+this.value">
            <?php foreach ($allYears as $y): ?>
                <option value="<?= $y['id'] ?>" <?= $y['id'] == $currentYear['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($y['name']) ?><?= $y['is_current'] ? ' ★' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($currentYear['is_current']): ?>
            <span class="badge" style="background: #E0F2F1; color: #1f9e8b;">Current</span>
        <?php endif; ?>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-secondary" onclick="document.getElementById('bulkModal').style.display='flex'">
            <i class="bi bi-lightning"></i> Bulk Create
        </button>
        <button class="btn btn-primary" onclick="document.getElementById('addClassModal').style.display='flex'">
            <i class="bi bi-plus-lg"></i> Add Class
        </button>
    </div>
</div>

<?php if (!empty($classes)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
        <?php foreach ($classes as $class): ?>
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; margin: 0;"><?= htmlspecialchars($class['name']) ?></h3>
                        <span style="font-size: 11px; color: var(--gray-400);">
                            <?= $class['section_count'] ?> sections · <?= $class['subject_count'] ?> subjects
                        </span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn btn-sm" 
                                style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;"
                                onclick="openAddSection(<?= $class['id'] ?>, '<?= htmlspecialchars($class['name']) ?>')" title="Add Section">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <form method="POST" action="<?= APP_URL ?>/school-setup/delete-class/<?= $class['id'] ?>" 
                              style="display:inline;" onsubmit="return confirm('Delete <?= htmlspecialchars($class['name']) ?> and all its sections?')">
                            <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body" style="padding: 12px 16px;">
                    <?php if (!empty($class['sections'])): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach ($class['sections'] as $section): ?>
                                <div style="display: flex; align-items: center; gap: 6px; background: #F5F8F8; border-radius: 8px; padding: 6px 12px;">
                                    <span style="font-weight: 600; font-size: 13px;">Section <?= htmlspecialchars($section['name']) ?></span>
                                    <span style="font-size: 11px; color: var(--gray-400);">(<?= $section['capacity'] ?>)</span>
                                    
                                    <form method="POST" action="<?= APP_URL ?>/school-setup/assign-teacher/<?= $section['id'] ?>" style="display: flex; gap: 4px; margin-left: 8px;">
                                        <?= Session::csrfField() ?>
                                        <select name="class_teacher_id" class="form-control form-control-sm" style="width: 140px; font-size: 11px; padding: 2px 6px; height: 24px;" onchange="this.form.submit()">
                                            <option value="">No Teacher</option>
                                            <?php foreach ($teachers ?? [] as $t): ?>
                                                <option value="<?= $t['id'] ?>" <?= $section['class_teacher_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['full_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>

                                    <form method="POST" action="<?= APP_URL ?>/school-setup/delete-section/<?= $section['id'] ?>" style="display:inline; margin-left: 8px;">
                                        <button type="submit" style="background: none; border: none; color: var(--gray-400); cursor: pointer; font-size: 14px; padding: 0 4px;"
                                                title="Delete Section"
                                                onclick="return confirm('Delete Section <?= $section['name'] ?>?')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 12px; color: var(--gray-400); margin: 0; font-style: italic;">No sections added</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 60px 40px;">
            <i class="bi bi-building" style="font-size: 48px; color: var(--gray-300); margin-bottom: 12px;"></i>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">No Classes Yet</h3>
            <p style="color: var(--gray-500); margin-bottom: 24px;">Use "Bulk Create" to quickly add classes 1-12 with sections</p>
            <button class="btn btn-primary" onclick="document.getElementById('bulkModal').style.display='flex'">
                <i class="bi bi-lightning"></i> Bulk Create Classes
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Bulk Create Modal -->
<div id="bulkModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 480px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-lightning" style="color: var(--warning);"></i> Bulk Create Classes</h3>
            <button onclick="document.getElementById('bulkModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/bulk-create-classes" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="year_id" value="<?= $currentYear['id'] ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">From Class</label>
                        <input type="number" class="form-control" name="from_class" value="1" min="1" max="12" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Class</label>
                        <input type="number" class="form-control" name="to_class" value="12" min="1" max="12" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Sections (comma separated)</label>
                        <input type="text" class="form-control" name="sections" value="A, B" placeholder="e.g. A, B, C">
                        <span style="font-size: 11px; color: var(--gray-400);">Each class will get these sections</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity per Section</label>
                        <input type="number" class="form-control" name="capacity" value="40" min="1" max="500">
                        <span style="font-size: 11px; color: var(--gray-400);">Max students per section</span>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('bulkModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-lightning"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Single Class Modal -->
<div id="addClassModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Add Class</h3>
            <button onclick="document.getElementById('addClassModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-class" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="year_id" value="<?= $currentYear['id'] ?>">
                <div class="form-group">
                    <label class="form-label">Class Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="e.g. Class 1, Nursery, LKG" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Numeric Order</label>
                    <input type="number" class="form-control" name="numeric_name" value="0" min="0" max="20">
                    <span style="font-size: 11px; color: var(--gray-400);">Used for sorting (1-12 for standard classes)</span>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addClassModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;" id="sectionModalTitle">Add Section</h3>
            <button onclick="document.getElementById('addSectionModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-section" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="class_id" id="sectionClassId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Section Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. A, B, C" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" value="40" min="1" max="200">
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addSectionModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddSection(classId, className) {
    document.getElementById('sectionClassId').value = classId;
    document.getElementById('sectionModalTitle').textContent = 'Add Section to ' + className;
    document.getElementById('addSectionModal').style.display = 'flex';
}
</script>
