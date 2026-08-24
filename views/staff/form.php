<?php
    $isEdit = !empty($staff);
    $action = $isEdit ? APP_URL . '/staff/update/' . $staff['id'] : APP_URL . '/staff/store';
?>

<form action="<?= $action ?>" method="POST">
    <?= Session::csrfField() ?>

    <!-- Personal Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person" style="color: #1f9e8b;"></i> Personal Information
            </h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Full Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($staff['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Staff Type <span style="color: var(--danger);">*</span></label>
                    <select class="form-control" name="user_type" required>
                        <?php foreach (['teacher' => 'Teacher', 'staff' => 'Staff', 'accountant' => 'Accountant', 'librarian' => 'Librarian', 'transport_manager' => 'Transport Manager'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($staff['user_type'] ?? 'teacher') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select class="form-control" name="gender">
                        <option value="">Select</option>
                        <option value="male" <?= ($staff['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($staff['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= ($staff['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($staff['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($staff['email'] ?? '') ?>" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="date_of_birth" value="<?= $staff['date_of_birth'] ?? '' ?>">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Blood Group</label>
                    <select class="form-control" name="blood_group">
                        <option value="">Select</option>
                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= ($staff['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" class="form-control" name="emergency_contact" value="<?= htmlspecialchars($staff['emergency_contact'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-briefcase" style="color: #7B1FA2;"></i> Professional Details
            </h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Employee ID <span style="font-size: 11px; color: var(--gray-400);">(= Login Username)</span></label>
                    <input type="text" class="form-control" name="employee_id" 
                           value="<?= htmlspecialchars($staff['employee_id'] ?? '') ?>" 
                           placeholder="Auto-generated if blank" style="text-transform: uppercase;">
                    <?php if ($isEdit && !empty($staff['username'])): ?>
                        <span style="font-size: 11px; color: var(--gray-400);">Current login: <code><?= htmlspecialchars($staff['username']) ?></code></span>
                    <?php else: ?>
                        <span style="font-size: 11px; color: var(--gray-400);">Leave blank to auto-generate</span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Password <?= $isEdit ? '' : '<span style="font-size: 11px; color: var(--gray-400);">(Default: Emp@123)</span>' ?></label>
                    <input type="text" class="form-control" name="password" placeholder="<?= $isEdit ? 'Leave blank to keep current' : 'Emp@123' ?>">
                    <span style="font-size: 11px; color: var(--gray-400);"><?= $isEdit ? 'Leave blank to keep unchanged' : 'Default password used if not given' ?></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Staff Category <span style="color: var(--danger);">*</span></label>
                    <div style="display: flex; gap: 8px; margin-top: 4px;">
                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; border: 2px solid <?= ($staff['staff_category'] ?? 'teaching') === 'teaching' ? '#1f9e8b' : 'var(--gray-100)' ?>; border-radius: 8px; cursor: pointer; font-size: 13px; background: <?= ($staff['staff_category'] ?? 'teaching') === 'teaching' ? '#E0F2F1' : 'white' ?>;" onclick="updateCatStyle(this, 'teaching')">
                            <input type="radio" name="staff_category" value="teaching" <?= ($staff['staff_category'] ?? 'teaching') === 'teaching' ? 'checked' : '' ?> style="accent-color: #1f9e8b;" onchange="filterDesignations('teaching')">
                            👨‍🏫 Teaching
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; border: 2px solid <?= ($staff['staff_category'] ?? '') === 'non_teaching' ? '#E65100' : 'var(--gray-100)' ?>; border-radius: 8px; cursor: pointer; font-size: 13px; background: <?= ($staff['staff_category'] ?? '') === 'non_teaching' ? '#FFF3E0' : 'white' ?>;" onclick="updateCatStyle(this, 'non_teaching')">
                            <input type="radio" name="staff_category" value="non_teaching" <?= ($staff['staff_category'] ?? '') === 'non_teaching' ? 'checked' : '' ?> style="accent-color: #E65100;" onchange="filterDesignations('non_teaching')">
                            👷 Non-Teaching
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <div style="display: flex; gap: 6px;">
                        <select class="form-control" name="department_id" id="departmentSelect" style="flex: 1;">
                            <option value="">— Select Department —</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ($staff['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="toggleDeck('dept')" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; border-radius: 8px; padding: 6px 10px; white-space: nowrap; font-weight: 700;" title="Add new department">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <!-- Inline Deck: Add Department -->
                    <div id="deptDeck" style="display: none; margin-top: 8px; padding: 12px; border: 2px solid #1f9e8b; border-radius: 10px; background: #F0FDF9;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: #1f9e8b;"><i class="bi bi-building"></i> Quick Add Department</span>
                            <button type="button" onclick="toggleDeck('dept')" style="background:none;border:none;font-size:16px;cursor:pointer;color:var(--gray-400);line-height:1;">&times;</button>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="newDeptName" placeholder="Department name" class="form-control" style="flex: 1; font-size: 13px; padding: 6px 10px;">
                            <input type="text" id="newDeptCode" placeholder="Code" class="form-control" style="width: 70px; font-size: 13px; padding: 6px 10px;">
                            <button type="button" onclick="saveDepartment()" class="btn btn-sm" style="background: #1f9e8b; color: white; border: none; border-radius: 8px; padding: 6px 14px; font-weight: 600; white-space: nowrap;">
                                <i class="bi bi-check-lg"></i> Add
                            </button>
                        </div>
                        <div id="deptDeckMsg" style="font-size: 11px; margin-top: 4px; display: none;"></div>
                    </div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <div style="display: flex; gap: 6px;">
                        <select class="form-control" name="designation_id" id="designationSelect" style="flex: 1;">
                            <option value="">— Select Designation —</option>
                            <?php foreach ($designations ?? [] as $desig): ?>
                                <option value="<?= $desig['id'] ?>" data-category="<?= $desig['staff_category'] ?>"
                                    <?= ($staff['designation_id'] ?? '') == $desig['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($desig['name']) ?>
                                    (<?= $desig['staff_category'] === 'teaching' ? 'Teaching' : 'Non-Teaching' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="toggleDeck('desig')" class="btn btn-sm" style="background: #F3E5F5; color: #7B1FA2; border: none; border-radius: 8px; padding: 6px 10px; white-space: nowrap; font-weight: 700;" title="Add new designation">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <!-- Inline Deck: Add Designation -->
                    <div id="desigDeck" style="display: none; margin-top: 8px; padding: 12px; border: 2px solid #7B1FA2; border-radius: 10px; background: #FBF3FF;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: #7B1FA2;"><i class="bi bi-person-badge"></i> Quick Add Designation</span>
                            <button type="button" onclick="toggleDeck('desig')" style="background:none;border:none;font-size:16px;cursor:pointer;color:var(--gray-400);line-height:1;">&times;</button>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" id="newDesigName" placeholder="Designation name" class="form-control" style="flex: 1; font-size: 13px; padding: 6px 10px;">
                            <select id="newDesigCategory" class="form-control" style="width: 140px; font-size: 13px; padding: 6px 10px;">
                                <option value="teaching">Teaching</option>
                                <option value="non_teaching">Non-Teaching</option>
                            </select>
                            <button type="button" onclick="saveDesignation()" class="btn btn-sm" style="background: #7B1FA2; color: white; border: none; border-radius: 8px; padding: 6px 14px; font-weight: 600; white-space: nowrap;">
                                <i class="bi bi-check-lg"></i> Add
                            </button>
                        </div>
                        <div id="desigDeckMsg" style="font-size: 11px; margin-top: 4px; display: none;"></div>
                    </div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Qualification</label>
                    <input type="text" class="form-control" name="qualification" value="<?= htmlspecialchars($staff['qualification'] ?? '') ?>" placeholder="e.g. M.Ed, B.Tech">
                </div>
                <div class="form-group">
                    <label class="form-label">Experience (Years)</label>
                    <input type="number" class="form-control" name="experience_years" value="<?= $staff['experience_years'] ?? 0 ?>" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Joining</label>
                    <input type="date" class="form-control" name="date_of_joining" value="<?= $staff['date_of_joining'] ?? '' ?>">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Salary</label>
                    <input type="number" class="form-control" name="salary" value="<?= $staff['salary'] ?? '' ?>" placeholder="Monthly salary" step="0.01">
                </div>
            </div>
        </div>
    </div>

    <!-- Address -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-geo-alt" style="color: #E65100;"></i> Address
            </h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" rows="2" placeholder="Full address"><?= htmlspecialchars($staff['address'] ?? '') ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($staff['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">State</label>
                    <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($staff['state'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" class="form-control" name="pincode" value="<?= htmlspecialchars($staff['pincode'] ?? '') ?>" maxlength="10">
                </div>
            </div>
        </div>
    </div>

    <?php if ($isEdit && !empty($assignments)): ?>
    <!-- Subject Assignments (view only for teachers) -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-book" style="color: #1565C0;"></i> Subject Assignments
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr><th>Class</th><th>Subject</th><th>Periods/Week</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($a['class_name']) ?></td>
                            <td><?= htmlspecialchars($a['subject_name']) ?></td>
                            <td><?= $a['periods_per_week'] ?>/week</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Custom Fields (dynamic) -->
    <?php if (!empty($customFields)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person-lines-fill" style="color: #7B1FA2;"></i> Additional Information
            </h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php foreach ($customFields as $cf): 
                    $cfName = 'cf_' . $cf['id'];
                    $cfVal = $customValues[$cf['id']] ?? '';
                ?>
                <div class="form-group">
                    <label class="form-label">
                        <?= htmlspecialchars($cf['field_label']) ?>
                        <?php if ($cf['is_required']): ?>
                            <span style="color: var(--danger);">*</span>
                        <?php endif; ?>
                    </label>
                    <?php if ($cf['field_type'] === 'text'): ?>
                        <input type="text" class="form-control" name="<?= $cfName ?>" 
                               value="<?= htmlspecialchars($cfVal) ?>" 
                               placeholder="<?= htmlspecialchars($cf['placeholder'] ?? '') ?>"
                               <?= $cf['is_required'] ? 'required' : '' ?>>
                    <?php elseif ($cf['field_type'] === 'number'): ?>
                        <input type="number" class="form-control" name="<?= $cfName ?>" 
                               value="<?= htmlspecialchars($cfVal) ?>"
                               placeholder="<?= htmlspecialchars($cf['placeholder'] ?? '') ?>"
                               <?= $cf['is_required'] ? 'required' : '' ?>>
                    <?php elseif ($cf['field_type'] === 'date'): ?>
                        <input type="date" class="form-control" name="<?= $cfName ?>" 
                               value="<?= htmlspecialchars($cfVal) ?>"
                               <?= $cf['is_required'] ? 'required' : '' ?>>
                    <?php elseif ($cf['field_type'] === 'textarea'): ?>
                        <textarea class="form-control" name="<?= $cfName ?>" rows="3"
                                  placeholder="<?= htmlspecialchars($cf['placeholder'] ?? '') ?>"
                                  <?= $cf['is_required'] ? 'required' : '' ?>><?= htmlspecialchars($cfVal) ?></textarea>
                    <?php elseif ($cf['field_type'] === 'select'): ?>
                        <select class="form-control" name="<?= $cfName ?>" <?= $cf['is_required'] ? 'required' : '' ?>>
                            <option value="">— Select —</option>
                            <?php foreach (json_decode($cf['options'] ?? '[]', true) as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>" <?= $cfVal === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($cf['field_type'] === 'radio'): ?>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; padding-top: 6px;">
                            <?php foreach (json_decode($cf['options'] ?? '[]', true) as $opt): ?>
                                <label style="display: flex; align-items: center; gap: 4px; font-size: 13px; cursor: pointer;">
                                    <input type="radio" name="<?= $cfName ?>" value="<?= htmlspecialchars($opt) ?>" 
                                           <?= $cfVal === $opt ? 'checked' : '' ?> style="accent-color: #7B1FA2;">
                                    <?= htmlspecialchars($opt) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($cf['field_type'] === 'checkbox'): ?>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; padding-top: 6px;">
                            <?php 
                                $checkedVals = $cfVal ? explode(',', $cfVal) : [];
                                foreach (json_decode($cf['options'] ?? '[]', true) as $opt): 
                            ?>
                                <label style="display: flex; align-items: center; gap: 4px; font-size: 13px; cursor: pointer;">
                                    <input type="checkbox" name="<?= $cfName ?>[]" value="<?= htmlspecialchars($opt) ?>"
                                           <?= in_array($opt, $checkedVals) ? 'checked' : '' ?> style="accent-color: #7B1FA2;">
                                    <?= htmlspecialchars($opt) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="<?= APP_URL ?>/staff" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Update Staff' : 'Add Staff' ?>
        </button>
    </div>
</form>

<script>
function filterDesignations(category) {
    const sel = document.getElementById('designationSelect');
    sel.querySelectorAll('option').forEach(opt => {
        if (!opt.value) return; // keep "select" option
        opt.style.display = (opt.dataset.category === category) ? '' : 'none';
        // If hidden and selected, deselect
        if (opt.style.display === 'none' && opt.selected) {
            sel.value = '';
        }
    });
}

function updateCatStyle(label, category) {
    // Reset all labels
    label.parentElement.querySelectorAll('label').forEach(l => {
        l.style.borderColor = 'var(--gray-100)';
        l.style.background = 'white';
    });
    // Highlight selected
    const colors = { teaching: ['#1f9e8b','#E0F2F1'], non_teaching: ['#E65100','#FFF3E0'] };
    label.style.borderColor = colors[category][0];
    label.style.background = colors[category][1];
}

// Run filter on page load
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="staff_category"]:checked');
    if (checked) filterDesignations(checked.value);
});

// ─── Inline Deck Toggle ────────────────────
function toggleDeck(type) {
    const deck = document.getElementById(type + 'Deck');
    deck.style.display = deck.style.display === 'none' ? 'block' : 'none';
    if (deck.style.display === 'block') {
        deck.querySelector('input[type="text"]').focus();
    }
}

function showDeckMsg(type, msg, isError) {
    const el = document.getElementById(type + 'DeckMsg');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.color = isError ? '#C62828' : '#1f9e8b';
    if (!isError) setTimeout(() => { el.style.display = 'none'; }, 2000);
}

// ─── AJAX Save Department ──────────────────
function saveDepartment() {
    const name = document.getElementById('newDeptName').value.trim();
    const code = document.getElementById('newDeptCode').value.trim();
    if (!name) { showDeckMsg('dept', 'Name is required', true); return; }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('code', code);

    fetch('<?= APP_URL ?>/school-setup/ajax-store-department', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Add to dropdown and select it
            const sel = document.getElementById('departmentSelect');
            const opt = new Option(data.name, data.id, true, true);
            sel.appendChild(opt);
            // Clear and close
            document.getElementById('newDeptName').value = '';
            document.getElementById('newDeptCode').value = '';
            showDeckMsg('dept', '✓ Added!', false);
            setTimeout(() => toggleDeck('dept'), 800);
        } else {
            showDeckMsg('dept', data.error || 'Failed to add', true);
        }
    })
    .catch(() => showDeckMsg('dept', 'Network error', true));
}

// ─── AJAX Save Designation ─────────────────
function saveDesignation() {
    const name = document.getElementById('newDesigName').value.trim();
    const category = document.getElementById('newDesigCategory').value;
    if (!name) { showDeckMsg('desig', 'Name is required', true); return; }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('staff_category', category);

    fetch('<?= APP_URL ?>/school-setup/ajax-store-designation', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const sel = document.getElementById('designationSelect');
            const label = data.name + ' (' + (data.staff_category === 'teaching' ? 'Teaching' : 'Non-Teaching') + ')';
            const opt = new Option(label, data.id, true, true);
            opt.dataset.category = data.staff_category;
            sel.appendChild(opt);
            // Apply current filter
            const checked = document.querySelector('input[name="staff_category"]:checked');
            if (checked) filterDesignations(checked.value);
            // Clear and close
            document.getElementById('newDesigName').value = '';
            showDeckMsg('desig', '✓ Added!', false);
            setTimeout(() => toggleDeck('desig'), 800);
        } else {
            showDeckMsg('desig', data.error || 'Failed to add', true);
        }
    })
    .catch(() => showDeckMsg('desig', 'Network error', true));
}
</script>
