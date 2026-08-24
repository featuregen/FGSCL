<!-- Student Bulk Import -->
<div class="card mb-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-file-earmark-spreadsheet" style="color: var(--primary);"></i> Bulk Import Students
        </h3>
        <a href="<?= APP_URL ?>/students/download-template" class="btn btn-secondary" style="display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-download"></i> Download Template
        </a>
    </div>
    <div class="card-body">
        <!-- Instructions -->
        <div style="background: #E0F2F1; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #1f9e8b; margin-bottom: 10px;">
                <i class="bi bi-info-circle"></i> How to import
            </h4>
            <ol style="font-size: 13px; color: var(--gray-700); margin: 0; padding-left: 20px; line-height: 2;">
                <li><strong>Download the template</strong> — Click "Download Template" button above to get a CSV file with the correct headers</li>
                <li><strong>Fill in data</strong> — Open in Excel/Google Sheets and fill student data (one student per row)</li>
                <li><strong>Save as CSV</strong> — Save the file as <strong>.csv</strong> format (UTF-8)</li>
                <li><strong>Upload below</strong> — Select the filled CSV and click Import</li>
            </ol>
        </div>

        <!-- Column Guide -->
        <div style="margin-bottom: 24px;">
            <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px;">
                <i class="bi bi-list-columns" style="color: #1565C0;"></i> Template Columns
            </h4>
            <div class="table-responsive">
                <table class="data-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Required</th>
                            <th>Example</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 600;">full_name</td>
                            <td><span class="badge" style="background: #FFEBEE; color: #C62828;">Required</span></td>
                            <td>Rahul Sharma</td>
                            <td>Student's full name</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">admission_no</td>
                            <td><span style="font-size: 11px; color: var(--gray-400);">Optional</span></td>
                            <td>DPS-2026-0001</td>
                            <td>Leave blank to auto-generate</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">class_name</td>
                            <td><span style="font-size: 11px; color: var(--gray-400);">Optional</span></td>
                            <td>Class 5</td>
                            <td>Must match existing class name</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">section_name</td>
                            <td><span style="font-size: 11px; color: var(--gray-400);">Optional</span></td>
                            <td>A</td>
                            <td>Must match existing section</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">gender</td>
                            <td><span style="font-size: 11px; color: var(--gray-400);">Optional</span></td>
                            <td>male / female / other</td>
                            <td>Lowercase</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">date_of_birth</td>
                            <td><span style="font-size: 11px; color: var(--gray-400);">Optional</span></td>
                            <td>2015-06-15</td>
                            <td>YYYY-MM-DD format</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">phone, father_name, etc.</td>
                            <td><span style="font-size: 11px; color: var(--gray-400);">Optional</span></td>
                            <td>—</td>
                            <td>All other base fields</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload Form -->
        <form action="<?= APP_URL ?>/students/process-import" method="POST" enctype="multipart/form-data">
            <?= Session::csrfField() ?>

            <div style="border: 2px dashed var(--gray-200); border-radius: 12px; padding: 40px; text-align: center; background: var(--gray-50); transition: all 0.2s;" 
                 id="dropZone"
                 ondragover="event.preventDefault(); this.style.borderColor='#1f9e8b'; this.style.background='#E0F2F1';"
                 ondragleave="this.style.borderColor='var(--gray-200)'; this.style.background='var(--gray-50)';"
                 ondrop="event.preventDefault(); document.getElementById('csvFile').files = event.dataTransfer.files; updateFileName(); this.style.borderColor='#1f9e8b';">
                
                <i class="bi bi-cloud-upload" style="font-size: 40px; color: var(--gray-300); margin-bottom: 8px;"></i>
                <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">Drop CSV file here or click to browse</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">Supports .csv files only (max 5MB)</p>
                
                <input type="file" name="csv_file" id="csvFile" accept=".csv" style="display: none;" onchange="updateFileName()" required>
                <label for="csvFile" class="btn btn-secondary" style="cursor: pointer;">
                    <i class="bi bi-folder2-open"></i> Choose File
                </label>
                
                <div id="fileName" style="margin-top: 12px; font-size: 13px; font-weight: 600; color: #1f9e8b; display: none;">
                    <i class="bi bi-file-earmark-check"></i> <span id="fileNameText"></span>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <a href="<?= APP_URL ?>/students" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="importBtn" disabled>
                    <i class="bi bi-upload"></i> Import Students
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($importResult)): ?>
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-clipboard-check" style="color: <?= $importResult['errors'] ? '#E65100' : '#1f9e8b' ?>;"></i> Import Results
        </h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">
            <div style="background: #E0F2F1; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #1f9e8b;"><?= $importResult['success'] ?></div>
                <div style="font-size: 12px; color: var(--gray-500);">Imported</div>
            </div>
            <div style="background: #FFEBEE; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #C62828;"><?= count($importResult['errors']) ?></div>
                <div style="font-size: 12px; color: var(--gray-500);">Errors</div>
            </div>
            <div style="background: #E3F2FD; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #1565C0;"><?= $importResult['total'] ?></div>
                <div style="font-size: 12px; color: var(--gray-500);">Total Rows</div>
            </div>
        </div>

        <?php if (!empty($importResult['errors'])): ?>
            <div style="max-height: 300px; overflow-y: auto; border: 1px solid var(--gray-100); border-radius: 8px;">
                <table class="data-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Name</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($importResult['errors'] as $err): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= $err['row'] ?></td>
                                <td><?= htmlspecialchars($err['name'] ?? '—') ?></td>
                                <td style="color: #C62828;"><?= htmlspecialchars($err['error']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function updateFileName() {
    const input = document.getElementById('csvFile');
    const nameDiv = document.getElementById('fileName');
    const nameText = document.getElementById('fileNameText');
    const btn = document.getElementById('importBtn');
    
    if (input.files.length > 0) {
        nameDiv.style.display = 'block';
        nameText.textContent = input.files[0].name;
        btn.disabled = false;
    } else {
        nameDiv.style.display = 'none';
        btn.disabled = true;
    }
}
</script>
