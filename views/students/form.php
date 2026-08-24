<?php
    $isEdit = !empty($student);
    $action = $isEdit ? APP_URL . '/students/update/' . $student['id'] : APP_URL . '/students/store';

    // Field config helper
    $fieldConfigMap = $fieldConfigMap ?? [];
    $customFields = $customFields ?? [];
    $customValues = $customValues ?? [];

    function isFieldVisible(string $name, array $configMap): bool {
        if (in_array($name, ['admission_no', 'full_name'])) return true; // locked
        $config = $configMap[$name] ?? null;
        return !$config || $config['visibility'] === 'show';
    }

    function isFieldRequired(string $name, array $configMap): bool {
        if (in_array($name, ['admission_no', 'full_name'])) return true; // locked
        $config = $configMap[$name] ?? null;
        return $config && $config['is_required'];
    }

    function reqStar(string $name, array $configMap): string {
        return isFieldRequired($name, $configMap) ? ' <span style="color: var(--danger);">*</span>' : '';
    }

    function reqAttr(string $name, array $configMap): string {
        return isFieldRequired($name, $configMap) ? 'required' : '';
    }
?>

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
    <?= Session::csrfField() ?>

    <!-- ─── Basic Information ─── -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person" style="color: var(--primary);"></i> Basic Information
            </h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('admission_no', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Admission No<?= reqStar('admission_no', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="admission_no" 
                           value="<?= htmlspecialchars($student['admission_no'] ?? $admissionNo ?? '') ?>"
                           <?= $isEdit ? 'readonly style="background: var(--gray-50);"' : '' ?> <?= reqAttr('admission_no', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('admission_date', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Admission Date<?= reqStar('admission_date', $fieldConfigMap) ?></label>
                    <input type="date" class="form-control" name="admission_date" 
                           value="<?= htmlspecialchars($student['admission_date'] ?? date('Y-m-d')) ?>" <?= reqAttr('admission_date', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('roll_number', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Roll Number<?= reqStar('roll_number', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="roll_number" 
                           value="<?= htmlspecialchars($student['roll_number'] ?? '') ?>" placeholder="e.g. 01" <?= reqAttr('roll_number', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('full_name', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Full Name<?= reqStar('full_name', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="full_name" 
                           value="<?= htmlspecialchars($student['full_name'] ?? '') ?>" required placeholder="Student full name">
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('gender', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Gender<?= reqStar('gender', $fieldConfigMap) ?></label>
                    <select class="form-control" name="gender" <?= reqAttr('gender', $fieldConfigMap) ?>>
                        <option value="">Select</option>
                        <option value="male" <?= ($student['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= ($student['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('date_of_birth', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Date of Birth<?= reqStar('date_of_birth', $fieldConfigMap) ?></label>
                    <input type="date" class="form-control" name="date_of_birth" 
                           value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>" <?= reqAttr('date_of_birth', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('class_id', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Class<?= reqStar('class_id', $fieldConfigMap) ?></label>
                    <select class="form-control" name="class_id" id="classSelect" onchange="loadSections(this.value)" <?= reqAttr('class_id', $fieldConfigMap) ?>>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?= $cls['id'] ?>" 
                                    data-sections="<?= htmlspecialchars($cls['sections_data'] ?? '') ?>"
                                    <?= ($student['class_id'] ?? '') == $cls['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cls['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('section_id', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Section<?= reqStar('section_id', $fieldConfigMap) ?></label>
                    <select class="form-control" name="section_id" id="sectionSelect" <?= reqAttr('section_id', $fieldConfigMap) ?>>
                        <option value="">Select Section</option>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('blood_group', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Blood Group<?= reqStar('blood_group', $fieldConfigMap) ?></label>
                    <select class="form-control" name="blood_group" <?= reqAttr('blood_group', $fieldConfigMap) ?>>
                        <option value="">Select</option>
                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= ($student['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('phone', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Phone<?= reqStar('phone', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="phone" 
                           value="<?= htmlspecialchars($student['phone'] ?? '') ?>" placeholder="Student phone" <?= reqAttr('phone', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('email', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Email<?= reqStar('email', $fieldConfigMap) ?></label>
                    <input type="email" class="form-control" name="email" 
                           value="<?= htmlspecialchars($student['email'] ?? '') ?>" placeholder="Optional" <?= reqAttr('email', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('category', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Category<?= reqStar('category', $fieldConfigMap) ?></label>
                    <select class="form-control" name="category" <?= reqAttr('category', $fieldConfigMap) ?>>
                        <?php foreach (['general' => 'General', 'obc' => 'OBC', 'sc' => 'SC', 'st' => 'ST', 'ews' => 'EWS', 'other' => 'Other'] as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($student['category'] ?? 'general') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('religion', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Religion<?= reqStar('religion', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="religion" 
                           value="<?= htmlspecialchars($student['religion'] ?? '') ?>" <?= reqAttr('religion', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('nationality', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Nationality<?= reqStar('nationality', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="nationality" 
                           value="<?= htmlspecialchars($student['nationality'] ?? 'Indian') ?>" <?= reqAttr('nationality', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>

                <?php if (isFieldVisible('previous_school', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Previous School<?= reqStar('previous_school', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="previous_school" 
                           value="<?= htmlspecialchars($student['previous_school'] ?? '') ?>" <?= reqAttr('previous_school', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!$isEdit): ?>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding-top: 16px; border-top: 1px solid var(--gray-100); margin-top: 8px;">
                <div class="form-group">
                    <label class="form-label">Login Password</label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" name="password" value="student@123" placeholder="Default: student@123">
                    </div>
                    <span style="font-size: 11px; color: var(--gray-400);">Auto-generated login credentials</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ─── Parent / Guardian ─── -->
    <?php
        $parentVisible = isFieldVisible('father_name', $fieldConfigMap) || isFieldVisible('father_phone', $fieldConfigMap) ||
                         isFieldVisible('mother_name', $fieldConfigMap) || isFieldVisible('guardian_name', $fieldConfigMap);
    ?>
    <?php if ($parentVisible): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-people" style="color: #E65100;"></i> Parent / Guardian Details
            </h3>
        </div>
        <div class="card-body">
            <?php if (isFieldVisible('father_name', $fieldConfigMap) || isFieldVisible('father_phone', $fieldConfigMap) || isFieldVisible('father_occupation', $fieldConfigMap)): ?>
            <h4 style="font-size: 13px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Father</h4>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                <?php if (isFieldVisible('father_name', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Father's Name<?= reqStar('father_name', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="father_name" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>" <?= reqAttr('father_name', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('father_phone', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Father's Phone<?= reqStar('father_phone', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="father_phone" value="<?= htmlspecialchars($student['father_phone'] ?? '') ?>" <?= reqAttr('father_phone', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('father_occupation', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Father's Occupation<?= reqStar('father_occupation', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="father_occupation" value="<?= htmlspecialchars($student['father_occupation'] ?? '') ?>" <?= reqAttr('father_occupation', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (isFieldVisible('mother_name', $fieldConfigMap) || isFieldVisible('mother_phone', $fieldConfigMap) || isFieldVisible('mother_occupation', $fieldConfigMap)): ?>
            <h4 style="font-size: 13px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Mother</h4>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                <?php if (isFieldVisible('mother_name', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Mother's Name<?= reqStar('mother_name', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="mother_name" value="<?= htmlspecialchars($student['mother_name'] ?? '') ?>" <?= reqAttr('mother_name', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('mother_phone', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Mother's Phone<?= reqStar('mother_phone', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="mother_phone" value="<?= htmlspecialchars($student['mother_phone'] ?? '') ?>" <?= reqAttr('mother_phone', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('mother_occupation', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Mother's Occupation<?= reqStar('mother_occupation', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="mother_occupation" value="<?= htmlspecialchars($student['mother_occupation'] ?? '') ?>" <?= reqAttr('mother_occupation', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (isFieldVisible('guardian_name', $fieldConfigMap) || isFieldVisible('guardian_phone', $fieldConfigMap) || isFieldVisible('guardian_relation', $fieldConfigMap)): ?>
            <h4 style="font-size: 13px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Guardian (if different)</h4>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('guardian_name', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Guardian Name<?= reqStar('guardian_name', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="guardian_name" value="<?= htmlspecialchars($student['guardian_name'] ?? '') ?>" <?= reqAttr('guardian_name', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('guardian_phone', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Guardian Phone<?= reqStar('guardian_phone', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="guardian_phone" value="<?= htmlspecialchars($student['guardian_phone'] ?? '') ?>" <?= reqAttr('guardian_phone', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('guardian_relation', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Relation<?= reqStar('guardian_relation', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="guardian_relation" value="<?= htmlspecialchars($student['guardian_relation'] ?? '') ?>" placeholder="e.g. Uncle, Grandparent" <?= reqAttr('guardian_relation', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ─── Address & Other ─── -->
    <?php
        $addrVisible = isFieldVisible('address', $fieldConfigMap) || isFieldVisible('city', $fieldConfigMap) ||
                       isFieldVisible('emergency_contact', $fieldConfigMap) || isFieldVisible('medical_conditions', $fieldConfigMap);
    ?>
    <?php if ($addrVisible): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-geo-alt" style="color: #1565C0;"></i> Address & Other Details
            </h3>
        </div>
        <div class="card-body">
            <?php if (isFieldVisible('address', $fieldConfigMap)): ?>
            <div class="form-group">
                <label class="form-label">Address<?= reqStar('address', $fieldConfigMap) ?></label>
                <textarea class="form-control" name="address" rows="2" placeholder="Full address" <?= reqAttr('address', $fieldConfigMap) ?>><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
            </div>
            <?php endif; ?>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php if (isFieldVisible('city', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">City<?= reqStar('city', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($student['city'] ?? '') ?>" <?= reqAttr('city', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('state', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">State<?= reqStar('state', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($student['state'] ?? '') ?>" <?= reqAttr('state', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('pincode', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Pincode<?= reqStar('pincode', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="pincode" value="<?= htmlspecialchars($student['pincode'] ?? '') ?>" maxlength="10" <?= reqAttr('pincode', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <?php if (isFieldVisible('emergency_contact', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Emergency Contact<?= reqStar('emergency_contact', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="emergency_contact" value="<?= htmlspecialchars($student['emergency_contact'] ?? '') ?>" <?= reqAttr('emergency_contact', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
                <?php if (isFieldVisible('medical_conditions', $fieldConfigMap)): ?>
                <div class="form-group">
                    <label class="form-label">Medical Conditions<?= reqStar('medical_conditions', $fieldConfigMap) ?></label>
                    <input type="text" class="form-control" name="medical_conditions" value="<?= htmlspecialchars($student['medical_conditions'] ?? '') ?>" placeholder="Any allergies or medical conditions" <?= reqAttr('medical_conditions', $fieldConfigMap) ?>>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ─── Custom Fields ─── -->
    <?php if (!empty($customFields)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-plus-square" style="color: #7B1FA2;"></i> Additional Information
            </h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php foreach ($customFields as $cf): 
                    $cfVal = $customValues[$cf['id']] ?? '';
                ?>
                    <div class="form-group">
                        <label class="form-label">
                            <?= htmlspecialchars($cf['field_label']) ?>
                            <?= $cf['is_required'] ? ' <span style="color: var(--danger);">*</span>' : '' ?>
                        </label>

                        <?php if ($cf['field_type'] === 'text'): ?>
                            <input type="text" class="form-control" name="cf_<?= $cf['id'] ?>" 
                                   value="<?= htmlspecialchars($cfVal) ?>" 
                                   placeholder="<?= htmlspecialchars($cf['placeholder'] ?? '') ?>"
                                   <?= $cf['is_required'] ? 'required' : '' ?>>

                        <?php elseif ($cf['field_type'] === 'number'): ?>
                            <input type="number" class="form-control" name="cf_<?= $cf['id'] ?>" 
                                   value="<?= htmlspecialchars($cfVal) ?>" 
                                   placeholder="<?= htmlspecialchars($cf['placeholder'] ?? '') ?>"
                                   <?= $cf['is_required'] ? 'required' : '' ?>>

                        <?php elseif ($cf['field_type'] === 'date'): ?>
                            <input type="date" class="form-control" name="cf_<?= $cf['id'] ?>" 
                                   value="<?= htmlspecialchars($cfVal) ?>"
                                   <?= $cf['is_required'] ? 'required' : '' ?>>

                        <?php elseif ($cf['field_type'] === 'select'): 
                            $options = json_decode($cf['options'] ?? '[]', true) ?: [];
                        ?>
                            <select class="form-control" name="cf_<?= $cf['id'] ?>" <?= $cf['is_required'] ? 'required' : '' ?>>
                                <option value="">Select</option>
                                <?php foreach ($options as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>" <?= $cfVal === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($cf['field_type'] === 'textarea'): ?>
                            <textarea class="form-control" name="cf_<?= $cf['id'] ?>" rows="2"
                                      placeholder="<?= htmlspecialchars($cf['placeholder'] ?? '') ?>"
                                      <?= $cf['is_required'] ? 'required' : '' ?>><?= htmlspecialchars($cfVal) ?></textarea>

                        <?php elseif ($cf['field_type'] === 'checkbox'):
                            $options = json_decode($cf['options'] ?? '[]', true) ?: [];
                            $selectedVals = array_map('trim', explode(',', $cfVal));
                        ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 12px; padding: 8px 0;">
                                <?php foreach ($options as $opt): ?>
                                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px;">
                                        <input type="checkbox" name="cf_<?= $cf['id'] ?>[]" value="<?= htmlspecialchars($opt) ?>" 
                                               <?= in_array($opt, $selectedVals) ? 'checked' : '' ?> style="accent-color: #1f9e8b;">
                                        <?= htmlspecialchars($opt) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($cf['field_type'] === 'radio'):
                            $options = json_decode($cf['options'] ?? '[]', true) ?: [];
                        ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 12px; padding: 8px 0;">
                                <?php foreach ($options as $opt): ?>
                                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px;">
                                        <input type="radio" name="cf_<?= $cf['id'] ?>" value="<?= htmlspecialchars($opt) ?>" 
                                               <?= $cfVal === $opt ? 'checked' : '' ?> style="accent-color: #1f9e8b;"
                                               <?= $cf['is_required'] ? 'required' : '' ?>>
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

    <!-- Submit -->
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="<?= APP_URL ?>/students" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Update Student' : 'Admit Student' ?>
        </button>
    </div>
</form>

<script>
const selectedSection = '<?= $student['section_id'] ?? '' ?>';

function loadSections(classId) {
    const select = document.getElementById('sectionSelect');
    if (!select) return;
    select.innerHTML = '<option value="">Select Section</option>';
    if (!classId) return;

    const option = document.querySelector('#classSelect option[value="' + classId + '"]');
    const data = option?.dataset?.sections || '';
    
    if (data) {
        data.split('|').forEach(item => {
            const [id, name] = item.split(':');
            if (id && name) {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = 'Section ' + name;
                if (id === selectedSection) opt.selected = true;
                select.appendChild(opt);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('classSelect');
    if (classSelect && classSelect.value) {
        loadSections(classSelect.value);
    }
});
</script>
