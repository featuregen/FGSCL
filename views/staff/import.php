<!-- Staff Import -->

<?php if ($importResult): ?>
    <!-- Results -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-check-circle" style="color: #1f9e8b;"></i> Import Results</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 24px; margin-bottom: 16px;">
                <div style="text-align: center; padding: 16px 24px; background: var(--gray-50); border-radius: 10px;">
                    <div style="font-size: 24px; font-weight: 800;"><?= $importResult['total'] ?></div>
                    <div style="font-size: 11px; color: var(--gray-400); text-transform: uppercase;">Total Rows</div>
                </div>
                <div style="text-align: center; padding: 16px 24px; background: #E8F5E9; border-radius: 10px;">
                    <div style="font-size: 24px; font-weight: 800; color: #4CAF50;"><?= $importResult['success'] ?></div>
                    <div style="font-size: 11px; color: #4CAF50; text-transform: uppercase;">Imported</div>
                </div>
                <?php if (!empty($importResult['errors'])): ?>
                <div style="text-align: center; padding: 16px 24px; background: #FFEBEE; border-radius: 10px;">
                    <div style="font-size: 24px; font-weight: 800; color: #F44336;"><?= count($importResult['errors']) ?></div>
                    <div style="font-size: 11px; color: #F44336; text-transform: uppercase;">Errors</div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($importResult['errors'])): ?>
                <h4 style="font-size: 13px; font-weight: 700; margin: 12px 0 8px;">Error Details:</h4>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--gray-100); border-radius: 8px;">
                    <table class="table" style="margin: 0;">
                        <thead><tr><th>Row</th><th>Name</th><th>Error</th></tr></thead>
                        <tbody>
                            <?php foreach ($importResult['errors'] as $err): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= $err['row'] ?></td>
                                    <td><?= htmlspecialchars($err['name']) ?></td>
                                    <td style="color: #C62828; font-size: 12px;"><?= htmlspecialchars($err['error']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div style="margin-top: 16px;">
                <a href="<?= APP_URL ?>/staff" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Go to Staff List</a>
                <a href="<?= APP_URL ?>/staff/import" class="btn btn-secondary" style="margin-left: 8px;"><i class="bi bi-upload"></i> Import More</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Upload Form -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Upload Card -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                    <i class="bi bi-upload" style="color: #1f9e8b;"></i> Upload CSV File
                </h3>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/staff/process-import" method="POST" enctype="multipart/form-data">
                    <?= Session::csrfField() ?>

                    <div style="border: 2px dashed var(--gray-200); border-radius: 12px; padding: 40px 20px; text-align: center; margin-bottom: 16px; transition: all 0.2s;" id="dropZone">
                        <i class="bi bi-file-earmark-spreadsheet" style="font-size: 48px; color: #1f9e8b; display: block; margin-bottom: 12px;"></i>
                        <p style="font-weight: 600; margin-bottom: 4px;">Drop CSV file here or click to browse</p>
                        <p style="font-size: 12px; color: var(--gray-400);">Supports .csv and .txt files</p>
                        <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" required
                               style="position: absolute; opacity: 0; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer;"
                               onchange="document.getElementById('fileName').textContent = this.files[0]?.name || 'No file selected'">
                    </div>
                    <p id="fileName" style="font-size: 12px; color: var(--gray-500); text-align: center; margin-bottom: 16px;">No file selected</p>

                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="bi bi-upload"></i> Import Staff</button>
                </form>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                    <i class="bi bi-info-circle" style="color: #7B1FA2;"></i> Instructions
                </h3>
            </div>
            <div class="card-body">
                <ol style="font-size: 13px; padding-left: 20px; line-height: 2;">
                    <li><strong>Download template</strong> first and fill in staff data</li>
                    <li><strong>Configure</strong> Departments & Designations in School Setup before importing</li>
                    <li><strong>staff_category</strong>: <code>teaching</code> or <code>non_teaching</code></li>
                    <li><strong>user_type</strong>: <code>teacher</code>, <code>staff</code>, <code>accountant</code>, <code>librarian</code>, <code>transport_manager</code></li>
                    <li><strong>department</strong>: Must match configured department name exactly</li>
                    <li><strong>designation</strong>: Must match configured designation name exactly</li>
                    <li><strong>gender</strong>: <code>male</code>, <code>female</code>, <code>other</code></li>
                    <li><strong>date_of_birth</strong>: YYYY-MM-DD format</li>
                    <li><strong>blood_group</strong>: A+, A-, B+, B-, AB+, AB-, O+, O-</li>
                </ol>

                <div style="margin-top: 16px; padding: 12px 16px; background: #FFF3E0; border-radius: 8px;">
                    <p style="font-size: 12px; color: #E65100; margin: 0;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Default password:</strong> <code>staff@123</code> for all imported staff
                    </p>
                </div>

                <a href="<?= APP_URL ?>/staff/download-template" class="btn btn-secondary" style="width: 100%; margin-top: 16px;">
                    <i class="bi bi-download"></i> Download CSV Template
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    #dropZone { position: relative; }
    #dropZone:hover { border-color: #1f9e8b; background: #F0FDF9; }
</style>
