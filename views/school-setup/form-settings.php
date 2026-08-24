<!-- Form Settings Page -->
<style>
    .settings-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--gray-100); margin-bottom: 24px; }
    .settings-tab { padding: 10px 20px; font-size: 13px; font-weight: 600; color: var(--gray-500); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.15s; background: none; border-top: none; border-left: none; border-right: none; }
    .settings-tab:hover { color: var(--gray-700); }
    .settings-tab.active { color: #1f9e8b; border-bottom-color: #1f9e8b; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .field-row { display: flex; align-items: center; padding: 10px 16px; border-bottom: 1px solid var(--gray-50); }
    .field-row:hover { background: var(--gray-50); }
    .field-row:last-child { border-bottom: none; }
    .field-row .field-name { flex: 1; font-size: 13px; font-weight: 500; }
    .field-row .field-group { font-size: 11px; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.5px; width: 80px; }
    .field-row .field-toggle { width: 100px; text-align: center; }
    .toggle-switch { position: relative; display: inline-block; width: 38px; height: 20px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 20px; transition: 0.2s; }
    .toggle-slider:before { content: ""; position: absolute; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; border-radius: 50%; transition: 0.2s; }
    .toggle-switch input:checked + .toggle-slider { background-color: #1f9e8b; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(18px); }
</style>

<!-- Tabs -->
<div class="settings-tabs">
    <button class="settings-tab active" onclick="showTab('admission')">
        <i class="bi bi-hash"></i> Admission Settings
    </button>
    <button class="settings-tab" onclick="showTab('fields')">
        <i class="bi bi-list-check"></i> Base Fields
    </button>
    <button class="settings-tab" onclick="showTab('custom')">
        <i class="bi bi-plus-square"></i> Custom Fields
    </button>
    <button class="settings-tab" onclick="showTab('attendance')">
        <i class="bi bi-clipboard-check"></i> Attendance
    </button>
    <button class="settings-tab" onclick="showTab('staff_fields')">
        <i class="bi bi-person-lines-fill"></i> Staff Fields
    </button>
</div>

<!-- Tab 1: Admission Settings -->
<div id="tab-admission" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-hash" style="color: var(--primary);"></i> Admission Number Configuration
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/save-admission-settings" method="POST">
                <?= Session::csrfField() ?>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Admission Prefix</label>
                        <input type="text" class="form-control" name="admission_prefix" 
                               value="<?= htmlspecialchars($admissionPrefix) ?>" 
                               placeholder="e.g. ADM, DPS, STU" maxlength="10" style="text-transform: uppercase;"
                               oninput="updatePreview()">
                        <span style="font-size: 11px; color: var(--gray-400);">Max 10 characters, auto-uppercased</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Separator</label>
                        <select class="form-control" name="admission_format" onchange="updatePreview()">
                            <option value="{PREFIX}-{YEAR}-{SEQ}" <?= $admissionFormat === '{PREFIX}-{YEAR}-{SEQ}' ? 'selected' : '' ?>>Hyphen (-)</option>
                            <option value="{PREFIX}/{YEAR}/{SEQ}" <?= $admissionFormat === '{PREFIX}/{YEAR}/{SEQ}' ? 'selected' : '' ?>>Slash (/)</option>
                            <option value="{PREFIX}{YEAR}{SEQ}" <?= $admissionFormat === '{PREFIX}{YEAR}{SEQ}' ? 'selected' : '' ?>>No separator</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 4px;">
                    <div class="form-group">
                        <label class="form-label">Include Year</label>
                        <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0;">
                            <label class="toggle-switch">
                                <input type="checkbox" name="include_year" value="1" 
                                       <?= ($includeYear ?? '1') === '1' ? 'checked' : '' ?> onchange="updatePreview()">
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-size: 13px; color: var(--gray-500);">Add current year to admission number</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Starting Number</label>
                        <input type="number" class="form-control" name="start_number" 
                               value="<?= htmlspecialchars($startNumber ?? '1') ?>" 
                               min="1" max="99999" oninput="updatePreview()" style="max-width: 200px;">
                        <span style="font-size: 11px; color: var(--gray-400);">Sequence starts from this number</span>
                    </div>
                </div>

                <div style="background: var(--gray-50); border-radius: 8px; padding: 16px; margin: 16px 0;">
                    <span style="font-size: 12px; color: var(--gray-500);">Preview:</span>
                    <span id="admissionPreview" style="font-size: 18px; font-weight: 700; color: #1f9e8b; margin-left: 8px;"></span>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Employee ID Config -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person-vcard" style="color: #7B1FA2;"></i> Employee ID Configuration
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/save-employee-id-settings" method="POST">
                <?= Session::csrfField() ?>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Employee ID Prefix</label>
                        <input type="text" class="form-control" name="employee_id_prefix"
                               value="<?= htmlspecialchars($employeeIdPrefix ?? 'EMP') ?>"
                               placeholder="e.g. EMP, STF, TCH" maxlength="10" style="text-transform: uppercase;"
                               oninput="updateEmpPreview()">
                        <span style="font-size: 11px; color: var(--gray-400);">Prefix for auto-generated employee IDs</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Starting Number</label>
                        <input type="number" class="form-control" name="employee_id_start"
                               value="<?= htmlspecialchars($employeeIdStart ?? '1') ?>"
                               min="1" max="99999" oninput="updateEmpPreview()" style="max-width: 200px;">
                        <span style="font-size: 11px; color: var(--gray-400);">Sequence starts from this number</span>
                    </div>
                </div>

                <div style="background: var(--gray-50); border-radius: 8px; padding: 16px; margin: 16px 0;">
                    <span style="font-size: 12px; color: var(--gray-500);">Preview:</span>
                    <span id="empIdPreview" style="font-size: 18px; font-weight: 700; color: #7B1FA2; margin-left: 8px;"></span>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tab 2: Base Field Config -->
<div id="tab-fields" class="tab-content">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-list-check" style="color: #E65100;"></i> Base Field Configuration
            </h3>
            <span style="font-size: 12px; color: var(--gray-400);">Configure which fields appear on the student form</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <form action="<?= APP_URL ?>/school-setup/save-field-config" method="POST">
                <?= Session::csrfField() ?>

                <!-- Header -->
                <div style="display: flex; padding: 8px 16px; background: var(--gray-50); font-size: 11px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px;">
                    <div style="flex: 1;">Field</div>
                    <div style="width: 80px;">Group</div>
                    <div style="width: 100px; text-align: center;">Visible</div>
                    <div style="width: 100px; text-align: center;">Required</div>
                </div>

                <?php foreach ($baseFields as $field): 
                    $config = $fieldConfigMap[$field['name']] ?? null;
                    $visible = $config ? ($config['visibility'] === 'show') : true;
                    $required = $config ? (bool)$config['is_required'] : false;
                    $isLocked = $field['locked'];
                ?>
                    <div class="field-row">
                        <div class="field-name">
                            <?= htmlspecialchars($field['label']) ?>
                            <?php if ($isLocked): ?>
                                <i class="bi bi-lock-fill" style="font-size: 10px; color: var(--gray-300); margin-left: 4px;" title="Always visible & required"></i>
                            <?php endif; ?>
                        </div>
                        <div class="field-group">
                            <span class="badge" style="background: <?= $field['group'] === 'basic' ? '#E3F2FD' : ($field['group'] === 'parent' ? '#FFF3E0' : '#E8F5E9') ?>; 
                                                       color: <?= $field['group'] === 'basic' ? '#1565C0' : ($field['group'] === 'parent' ? '#E65100' : '#2E7D32') ?>; font-size: 10px;">
                                <?= ucfirst($field['group']) ?>
                            </span>
                        </div>
                        <div class="field-toggle">
                            <?php if ($isLocked): ?>
                                <input type="hidden" name="fields[<?= $field['name'] ?>][visibility]" value="show">
                                <label class="toggle-switch"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
                            <?php else: ?>
                                <input type="hidden" name="fields[<?= $field['name'] ?>][visibility]" value="<?= $visible ? 'show' : 'hide' ?>">
                                <label class="toggle-switch">
                                    <input type="checkbox" onchange="this.previousElementSibling.value=this.checked?'show':'hide'" <?= $visible ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            <?php endif; ?>
                        </div>
                        <div class="field-toggle">
                            <?php if ($isLocked): ?>
                                <input type="hidden" name="fields[<?= $field['name'] ?>][is_required]" value="1">
                                <label class="toggle-switch"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
                            <?php else: ?>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="fields[<?= $field['name'] ?>][is_required]" value="1" <?= $required ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="padding: 16px; display: flex; justify-content: flex-end; border-top: 1px solid var(--gray-100);">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tab 3: Custom Fields -->
<div id="tab-custom" class="tab-content">
    <!-- Add Custom Field -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-plus-square" style="color: #7B1FA2;"></i> Add Custom Field
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-custom-field" method="POST">
                <?= Session::csrfField() ?>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Field Label <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="field_label" placeholder="e.g. Aadhar Number, Blood Type" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Field Type</label>
                        <select class="form-control" name="field_type" id="cfType" onchange="toggleOptions()">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Dropdown</option>
                            <option value="textarea">Text Area</option>
                            <option value="checkbox">Multi Checkbox</option>
                            <option value="radio">Radio (Single Select)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Placeholder</label>
                        <input type="text" class="form-control" name="placeholder" placeholder="Optional hint text">
                    </div>
                </div>

                <div class="form-group" id="optionsGroup" style="display: none;">
                    <label class="form-label">Options (comma separated)</label>
                    <input type="text" class="form-control" name="options" placeholder="e.g. Cricket, Football, Reading, Drawing, Music">
                    <span style="font-size: 11px; color: var(--gray-400);">Required for Dropdown, Multi Checkbox, and Radio types</span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_required" value="1" style="accent-color: #1f9e8b;">
                        <span style="font-size: 13px;">This field is required</span>
                    </label>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Field</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Custom Fields -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Custom Fields</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($customFields)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Label</th>
                                <th>Type</th>
                                <th>Options</th>
                                <th>Required</th>
                                <th style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customFields as $i => $cf): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($cf['field_label']) ?></td>
                                    <td>
                                        <?php
                                            $typeIcons = ['text' => 'bi-fonts', 'number' => 'bi-123', 'date' => 'bi-calendar', 'select' => 'bi-list', 'textarea' => 'bi-text-paragraph', 'checkbox' => 'bi-check-square', 'radio' => 'bi-record-circle'];
                                            $typeLabels = ['text' => 'Text', 'number' => 'Number', 'date' => 'Date', 'select' => 'Dropdown', 'textarea' => 'Text Area', 'checkbox' => 'Multi Checkbox', 'radio' => 'Radio'];
                                        ?>
                                        <span style="font-size: 13px;">
                                            <i class="bi <?= $typeIcons[$cf['field_type']] ?? 'bi-dash' ?>"></i>
                                            <?= $typeLabels[$cf['field_type']] ?? ucfirst($cf['field_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cf['options']): 
                                            $opts = json_decode($cf['options'], true);
                                        ?>
                                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                                <?php foreach (array_slice($opts ?? [], 0, 5) as $opt): ?>
                                                    <span class="badge" style="background: var(--gray-100); color: var(--gray-600); font-size: 10px;"><?= htmlspecialchars($opt) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($opts ?? []) > 5): ?>
                                                    <span style="font-size: 11px; color: var(--gray-400);">+<?= count($opts) - 5 ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--gray-400);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cf['is_required']): ?>
                                            <span class="badge" style="background: #FFEBEE; color: #C62828;">Required</span>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--gray-400);">Optional</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <button type="button" class="btn btn-sm" 
                                                    style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;"
                                                    onclick="openEditField(<?= htmlspecialchars(json_encode($cf)) ?>)" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="<?= APP_URL ?>/school-setup/delete-custom-field/<?= $cf['id'] ?>" 
                                                  style="display:inline;" onsubmit="return confirm('Delete custom field: <?= htmlspecialchars($cf['field_label']) ?>?')">
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
                    <i class="bi bi-plus-square-dotted" style="font-size: 40px; color: var(--gray-300); margin-bottom: 8px;"></i>
                    <h3>No custom fields</h3>
                    <p>Add custom fields above to extend the student form</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Custom Field Modal -->
<div id="editFieldModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 520px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-pencil" style="color: var(--primary);"></i> Edit Custom Field</h3>
            <button onclick="document.getElementById('editFieldModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <form id="editFieldForm" method="POST">
                <?= Session::csrfField() ?>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Field Label <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="field_label" id="editLabel" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Field Type</label>
                        <select class="form-control" name="field_type" id="editType" onchange="toggleEditOptions()">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Dropdown</option>
                            <option value="textarea">Text Area</option>
                            <option value="checkbox">Multi Checkbox</option>
                            <option value="radio">Radio (Single Select)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Placeholder</label>
                    <input type="text" class="form-control" name="placeholder" id="editPlaceholder" placeholder="Optional hint text">
                </div>

                <div class="form-group" id="editOptionsGroup" style="display: none;">
                    <label class="form-label">Options (comma separated)</label>
                    <input type="text" class="form-control" name="options" id="editOptions" placeholder="e.g. Bus, Van, Self">
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_required" id="editRequired" value="1" style="accent-color: #1f9e8b;">
                        <span style="font-size: 13px;">This field is required</span>
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('editFieldModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tab 4: Attendance Settings -->
<div id="tab-attendance" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-clipboard-check" style="color: #1565C0;"></i> Attendance Configuration
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/save-attendance-settings" method="POST">
                <?= Session::csrfField() ?>

                <?php $currentMode = $attendanceType ?? 'morning'; ?>
                <?php $currentClassMarker = $attendanceAccess[0] ?? 'class_teacher'; ?>
                <?php $currentSubjectMarker = $attendanceAccess[1] ?? 'subject_teacher'; ?>
                <?php $principalAccess = in_array('principal', $attendanceAccess ?? []); ?>

                <!-- SECTION 1: Attendance Mode -->
                <div style="margin-bottom: 28px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 4px;">
                        <i class="bi bi-clock-history" style="color: #7B1FA2;"></i> Attendance Mode
                    </h4>
                    <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">How often is attendance taken per day?</p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                        <?php
                            $modes = [
                                'morning' => ['Morning Only', 'bi-sunrise', '#E65100', '#FFF3E0', 'One attendance at the start of the day'],
                                'morning_evening' => ['Morning + Evening', 'bi-arrow-left-right', '#1565C0', '#E3F2FD', 'Two attendances: start and end of day'],
                                'subject' => ['Subject-wise', 'bi-journal-bookmark', '#7B1FA2', '#F3E5F5', 'Attendance taken per subject period'],
                            ];
                        ?>
                        <?php foreach ($modes as $val => [$label, $icon, $color, $bg, $desc]): ?>
                            <label data-att-card style="display: flex; flex-direction: column; gap: 8px; padding: 16px; border: 2px solid <?= $currentMode === $val ? $color : 'var(--gray-100)' ?>; border-radius: 12px; cursor: pointer; transition: all 0.2s; background: <?= $currentMode === $val ? $bg : 'white' ?>;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="radio" name="attendance_mode" value="<?= $val ?>" <?= $currentMode === $val ? 'checked' : '' ?> style="accent-color: <?= $color ?>;"
                                           onchange="handleModeChange(this.value)">
                                    <i class="bi <?= $icon ?>" style="color: <?= $color ?>; font-size: 18px;"></i>
                                    <span style="font-weight: 700; font-size: 14px;"><?= $label ?></span>
                                </div>
                                <span style="font-size: 11px; color: var(--gray-500); line-height: 1.4; padding-left: 26px;"><?= $desc ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--gray-100); margin: 24px 0;">

                <!-- SECTION 2A: Who Marks (Class-based: morning / morning_evening) -->
                <div id="class-marker-section" style="margin-bottom: 28px; <?= $currentMode === 'subject' ? 'display:none;' : '' ?>">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 4px;">
                        <i class="bi bi-person-check" style="color: #2E7D32;"></i> Who Marks Attendance?
                    </h4>
                    <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">
                        Select who is responsible for marking <strong id="class-mode-label"><?= $currentMode === 'morning_evening' ? 'morning & evening' : 'morning' ?></strong> attendance
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php
                            $classOptions = [
                                'class_teacher' => ['Class Teacher', 'Only the assigned class teacher can mark attendance for their class', 'bi-person-badge', '#1f9e8b', '#E0F2F1'],
                                'any_subject_teacher' => ['Any Subject Teacher', 'Any teacher who teaches a subject in that class can mark attendance', 'bi-people', '#1565C0', '#E3F2FD'],
                                'attendance_teacher' => ['Dedicated Attendance Teacher', 'A specific teacher assigned as the attendance-in-charge for each class', 'bi-person-lines-fill', '#E65100', '#FFF3E0'],
                            ];
                        ?>
                        <?php foreach ($classOptions as $val => [$label, $desc, $icon, $color, $bg]): ?>
                            <label style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 2px solid <?= $currentClassMarker === $val ? $color : 'var(--gray-100)' ?>; border-radius: 10px; cursor: pointer; transition: all 0.15s; background: <?= $currentClassMarker === $val ? $bg : 'white' ?>;"
                                   data-marker-card="class">
                                <input type="radio" name="class_marker" value="<?= $val ?>" <?= $currentClassMarker === $val ? 'checked' : '' ?>
                                       style="accent-color: <?= $color ?>; flex-shrink: 0;"
                                       onchange="highlightMarkerCard('class', this)">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: <?= $bg ?>; color: <?= $color ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi <?= $icon ?>" style="font-size: 16px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 13px; color: var(--gray-800);"><?= $label ?></div>
                                    <div style="font-size: 11px; color: var(--gray-500); line-height: 1.4;"><?= $desc ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- SECTION 2B: Who Marks (Subject-wise) -->
                <div id="subject-marker-section" style="margin-bottom: 28px; <?= $currentMode !== 'subject' ? 'display:none;' : '' ?>">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 4px;">
                        <i class="bi bi-person-check" style="color: #7B1FA2;"></i> Who Marks Subject Attendance?
                    </h4>
                    <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">
                        Select who is responsible for marking attendance each period
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php
                            $subjectOptions = [
                                'subject_teacher' => ['Subject Teacher', 'The teacher assigned to each subject marks their own period attendance', 'bi-book', '#7B1FA2', '#F3E5F5'],
                                'attendance_teacher' => ['Dedicated Attendance Teacher', 'A specific attendance-in-charge marks all period attendance for the class', 'bi-person-lines-fill', '#E65100', '#FFF3E0'],
                            ];
                        ?>
                        <?php foreach ($subjectOptions as $val => [$label, $desc, $icon, $color, $bg]): ?>
                            <label style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 2px solid <?= $currentSubjectMarker === $val ? $color : 'var(--gray-100)' ?>; border-radius: 10px; cursor: pointer; transition: all 0.15s; background: <?= $currentSubjectMarker === $val ? $bg : 'white' ?>;"
                                   data-marker-card="subject">
                                <input type="radio" name="subject_marker" value="<?= $val ?>" <?= $currentSubjectMarker === $val ? 'checked' : '' ?>
                                       style="accent-color: <?= $color ?>; flex-shrink: 0;"
                                       onchange="highlightMarkerCard('subject', this)">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: <?= $bg ?>; color: <?= $color ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi <?= $icon ?>" style="font-size: 16px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 13px; color: var(--gray-800);"><?= $label ?></div>
                                    <div style="font-size: 11px; color: var(--gray-500); line-height: 1.4;"><?= $desc ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--gray-100); margin: 24px 0;">

                <!-- SECTION 3: Admin Override -->
                <div style="margin-bottom: 28px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 4px;">
                        <i class="bi bi-shield-lock" style="color: #1565C0;"></i> Admin Override Access
                    </h4>
                    <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">
                        These roles can always mark/edit attendance regardless of the above setting
                    </p>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; border: 1px solid #E0F2F1; border-radius: 10px; background: #F0FDF9;">
                            <input type="checkbox" name="admin_access[]" value="school_admin" checked disabled style="accent-color: #1f9e8b;">
                            <i class="bi bi-person-gear" style="color: #1f9e8b;"></i>
                            <span style="font-size: 13px; font-weight: 600;">School Admin</span>
                            <span style="font-size: 10px; color: var(--gray-400); background: var(--gray-100); padding: 2px 8px; border-radius: 4px;">Always</span>
                        </div>
                        <label style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; border: 1px solid var(--gray-100); border-radius: 10px; cursor: pointer;">
                            <input type="checkbox" name="admin_access[]" value="principal" <?= $principalAccess ? 'checked' : '' ?> style="accent-color: #7B1FA2;">
                            <i class="bi bi-mortarboard" style="color: #7B1FA2;"></i>
                            <span style="font-size: 13px; font-weight: 600;">Principal</span>
                        </label>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--gray-100); margin: 24px 0;">

                <!-- SECTION 4: Status Options -->
                <div style="margin-bottom: 24px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 4px;">
                        <i class="bi bi-toggles" style="color: #1f9e8b;"></i> Attendance Statuses
                    </h4>
                    <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">
                        <span style="display: inline-flex; align-items: center; gap: 4px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #4CAF50;"></span> Present</span> &amp;
                        <span style="display: inline-flex; align-items: center; gap: 4px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #F44336;"></span> Absent</span>
                        are always available. Enable additional statuses:
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border: 1px solid var(--gray-100); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #FF9800;"></span>
                                <div><div style="font-size: 13px; font-weight: 600;">Late</div><div style="font-size: 10px; color: var(--gray-400);">Arrived late</div></div>
                            </div>
                            <label class="toggle-switch"><input type="checkbox" name="attendance_late" <?= ($attendanceLateAllowed ?? '1') === '1' ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border: 1px solid var(--gray-100); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #2196F3;"></span>
                                <div><div style="font-size: 13px; font-weight: 600;">Half Day</div><div style="font-size: 10px; color: var(--gray-400);">Present half day</div></div>
                            </div>
                            <label class="toggle-switch"><input type="checkbox" name="attendance_half_day" <?= ($attendanceHalfDay ?? '0') === '1' ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid var(--gray-200); border-radius: 8px;">
                            <div>
                                <div style="font-weight: 600; font-size: 13px;">Excused Status</div>
                                <div style="font-size: 12px; color: var(--gray-500);">Allow marking students as 'Excused'</div>
                            </div>
                            <label class="toggle-switch"><input type="checkbox" name="attendance_excused" <?= ($attendanceExcused ?? '1') === '1' ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid var(--gray-200); border-radius: 8px;">
                            <div>
                                <div style="font-weight: 600; font-size: 13px;">Allow Editing Past Attendance</div>
                                <div style="font-size: 12px; color: var(--gray-500);">Allow teachers to edit attendance for previous days</div>
                            </div>
                            <label class="toggle-switch"><input type="checkbox" name="allow_past_attendance" <?= ($allowPastAttendance ?? '1') === '1' ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Attendance Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tab 5: Staff Custom Fields -->
<div id="tab-staff_fields" class="tab-content">
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person-lines-fill" style="color: #7B1FA2;"></i> Add Staff Custom Field
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/school-setup/store-custom-field" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="form_type" value="staff">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Field Label <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="field_label" placeholder="e.g. PAN Number, Bank Account, Aadhaar" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Field Type</label>
                        <select class="form-control" name="field_type" id="staffCfType" onchange="toggleStaffOptions()">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Dropdown</option>
                            <option value="textarea">Text Area</option>
                            <option value="checkbox">Multi Checkbox</option>
                            <option value="radio">Radio (Single Select)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Placeholder</label>
                        <input type="text" class="form-control" name="placeholder" placeholder="Optional hint text">
                    </div>
                </div>
                <div class="form-group" id="staffOptionsGroup" style="display: none;">
                    <label class="form-label">Options (comma separated)</label>
                    <input type="text" class="form-control" name="options" placeholder="e.g. SBI, HDFC, ICICI, Axis">
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_required" value="1" style="accent-color: #7B1FA2;">
                        <span style="font-size: 13px;">This field is required</span>
                    </label>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Field</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Staff Custom Fields (<?= count($staffCustomFields ?? []) ?>)</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($staffCustomFields)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Label</th><th>Type</th><th>Required</th><th style="width:80px;">Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($staffCustomFields as $i => $cf): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($cf['field_label']) ?></td>
                                    <td style="font-size: 13px;"><?= ucfirst($cf['field_type']) ?></td>
                                    <td><?= $cf['is_required'] ? '<span class="badge" style="background:#FFEBEE;color:#C62828;">Required</span>' : '<span style="color:var(--gray-400);font-size:12px;">Optional</span>' ?></td>
                                    <td>
                                        <form method="POST" action="<?= APP_URL ?>/school-setup/delete-custom-field/<?= $cf['id'] ?>"
                                              style="display:inline;" onsubmit="return confirm('Delete: <?= htmlspecialchars($cf['field_label']) ?>?')">
                                            <button type="submit" class="btn btn-sm" style="background:#FFEBEE;color:#C62828;border:none;padding:4px 8px;border-radius:6px;"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 40px;">
                    <i class="bi bi-person-lines-fill" style="font-size: 40px; color: var(--gray-300); margin-bottom: 8px;"></i>
                    <h3>No staff custom fields</h3>
                    <p>Add fields like PAN Number, Bank Account, Aadhaar No, etc.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(name) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.target.closest('.settings-tab').classList.add('active');
    // Update URL so refresh stays on same tab
    const url = new URL(window.location);
    url.searchParams.set('tab', name);
    history.replaceState(null, '', url);
}

// Attendance: mode change
function handleModeChange(mode) {
    const classSection = document.getElementById('class-marker-section');
    const subjectSection = document.getElementById('subject-marker-section');
    const modeLabel = document.getElementById('class-mode-label');

    if (mode === 'subject') {
        classSection.style.display = 'none';
        subjectSection.style.display = 'block';
    } else {
        classSection.style.display = 'block';
        subjectSection.style.display = 'none';
        modeLabel.textContent = mode === 'morning_evening' ? 'morning & evening' : 'morning';
    }

    // Update mode card styles
    const colors = { morning: '#E65100', morning_evening: '#1565C0', subject: '#7B1FA2' };
    const bgs = { morning: '#FFF3E0', morning_evening: '#E3F2FD', subject: '#F3E5F5' };
    document.querySelectorAll('[data-att-card]').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            card.style.borderColor = colors[radio.value] || 'var(--gray-100)';
            card.style.background = bgs[radio.value] || 'white';
        } else {
            card.style.borderColor = 'var(--gray-100)';
            card.style.background = 'white';
        }
    });
}

// Attendance: highlight marker card
function highlightMarkerCard(group, radio) {
    const cards = document.querySelectorAll('[data-marker-card="' + group + '"]');
    cards.forEach(c => {
        c.style.borderColor = 'var(--gray-100)';
        c.style.background = 'white';
    });
    const card = radio.closest('[data-marker-card]');
    if (card) {
        const color = getComputedStyle(radio).accentColor || '#1f9e8b';
        card.style.borderColor = color;
        // Light tint
        const bgMap = {'#1f9e8b':'#E0F2F1','#1565c0':'#E3F2FD','#e65100':'#FFF3E0','rgb(31, 158, 139)':'#E0F2F1','rgb(21, 101, 192)':'#E3F2FD','rgb(230, 81, 0)':'#FFF3E0'};
        card.style.background = bgMap[color] || '#F5F5F5';
    }
}

function toggleOptions() {
    const type = document.getElementById('cfType').value;
    document.getElementById('optionsGroup').style.display = ['select','checkbox','radio'].includes(type) ? 'block' : 'none';
}

function toggleStaffOptions() {
    const type = document.getElementById('staffCfType').value;
    document.getElementById('staffOptionsGroup').style.display = ['select','checkbox','radio'].includes(type) ? 'block' : 'none';
}

function toggleEditOptions() {
    const type = document.getElementById('editType').value;
    document.getElementById('editOptionsGroup').style.display = ['select','checkbox','radio'].includes(type) ? 'block' : 'none';
}

function updatePreview() {
    const prefix = document.querySelector('[name="admission_prefix"]').value.toUpperCase() || 'ADM';
    const format = document.querySelector('[name="admission_format"]').value;
    const includeYear = document.querySelector('[name="include_year"]')?.checked ?? true;
    const startNum = parseInt(document.querySelector('[name="start_number"]')?.value) || 1;
    const year = new Date().getFullYear();
    const seq = String(startNum).padStart(4, '0');

    let preview = format;
    preview = preview.replace('{PREFIX}', prefix);
    if (includeYear) {
        preview = preview.replace('{YEAR}', year);
    } else {
        // Remove year and its separator
        preview = preview.replace('-{YEAR}', '').replace('/{YEAR}', '').replace('{YEAR}', '');
    }
    preview = preview.replace('{SEQ}', seq);
    document.getElementById('admissionPreview').textContent = preview;
}

// Run on load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
    updateEmpPreview();
});

function updateEmpPreview() {
    const prefixEl = document.querySelector('[name="employee_id_prefix"]');
    const startEl = document.querySelector('[name="employee_id_start"]');
    if (!prefixEl || !startEl) return;
    const prefix = prefixEl.value.toUpperCase() || 'EMP';
    const start = parseInt(startEl.value) || 1;
    const preview = prefix + String(start).padStart(4, '0');
    document.getElementById('empIdPreview').textContent = preview;
}

function openEditField(cf) {
    document.getElementById('editFieldForm').action = '<?= APP_URL ?>/school-setup/update-custom-field/' + cf.id;
    document.getElementById('editLabel').value = cf.field_label;
    document.getElementById('editType').value = cf.field_type;
    document.getElementById('editPlaceholder').value = cf.placeholder || '';
    document.getElementById('editRequired').checked = cf.is_required == 1;

    // Options
    if (['select','checkbox','radio'].includes(cf.field_type) && cf.options) {
        const opts = JSON.parse(cf.options);
        document.getElementById('editOptions').value = opts.join(', ');
        document.getElementById('editOptionsGroup').style.display = 'block';
    } else {
        document.getElementById('editOptions').value = '';
        document.getElementById('editOptionsGroup').style.display = ['select','checkbox','radio'].includes(cf.field_type) ? 'block' : 'none';
    }

    document.getElementById('editFieldModal').style.display = 'flex';
}

// Auto-switch to correct tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab && document.getElementById('tab-' + tab)) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        // Activate matching tab button
        const tabs = document.querySelectorAll('.settings-tab');
        const tabMap = {admission: 0, fields: 1, custom: 2, attendance: 3, staff_fields: 4};
        if (tabMap[tab] !== undefined && tabs[tabMap[tab]]) {
            tabs[tabMap[tab]].classList.add('active');
        }
    }
});
</script>
