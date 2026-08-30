
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Certificates & ID Cards</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Generate Transfer Certificates (TC), Bonafide & Character Certificates, and printable Student ID Cards</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openTcModal()">
            <i class="bi bi-file-earmark-text"></i> Generate TC
        </button>
        <button class="btn btn-secondary" onclick="openBonafideModal()">
            <i class="bi bi-award"></i> Generate Bonafide
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-award-fill"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Certificates Issued</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['total_issued']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-file-earmark-arrow-up"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Transfer Certificates</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['tc_count']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-patch-check"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Bonafide Certificates</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['bonafide']) ?></div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/certificates?tab=generators" class="btn" style="background: <?= $tab === 'generators' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'generators' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-card-checklist"></i> Certificate Generator Hub
    </a>
    <a href="<?= APP_URL ?>/certificates?tab=idcards" class="btn" style="background: <?= $tab === 'idcards' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'idcards' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-person-badge"></i> Student ID Card Batch Generator
    </a>
    <a href="<?= APP_URL ?>/certificates?tab=history" class="btn" style="background: <?= $tab === 'history' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'history' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-clock-history"></i> Issued History (<?= count($issued) ?>)
    </a>
</div>

<?php if ($tab === 'generators'): ?>
<!-- Generator Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <!-- TC Card -->
    <div class="card" style="padding: 24px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(14, 165, 233, 0.12); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 14px;">
                <i class="bi bi-file-earmark-arrow-up"></i>
            </div>
            <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">Transfer Certificate (TC)</h3>
            <p style="color: var(--text-muted, #64748b); font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                Generate official School Leaving / Transfer Certificate with student admission record, conduct, class passed, and date of leaving.
            </p>
        </div>
        <button class="btn btn-primary" onclick="openTcModal()" style="width: 100%;">
            <i class="bi bi-file-earmark-plus"></i> Generate TC
        </button>
    </div>

    <!-- Bonafide Card -->
    <div class="card" style="padding: 24px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 14px;">
                <i class="bi bi-award"></i>
            </div>
            <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">Bonafide Certificate</h3>
            <p style="color: var(--text-muted, #64748b); font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                Issue official Bonafide / Study certificate certifying that the student is a genuine enrolled student of the institution.
            </p>
        </div>
        <button class="btn btn-secondary" onclick="openBonafideModal('bonafide')" style="width: 100%;">
            <i class="bi bi-patch-check"></i> Generate Bonafide
        </button>
    </div>

    <!-- Character Certificate Card -->
    <div class="card" style="padding: 24px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.12); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 14px;">
                <i class="bi bi-star"></i>
            </div>
            <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">Character & Conduct Certificate</h3>
            <p style="color: var(--text-muted, #64748b); font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                Generate official testimonial certifying the moral conduct, character, and discipline of the student.
            </p>
        </div>
        <button class="btn btn-secondary" onclick="openBonafideModal('character')" style="width: 100%;">
            <i class="bi bi-star-fill"></i> Generate Character Cert
        </button>
    </div>
</div>

<?php elseif ($tab === 'idcards'): ?>
<!-- ID Cards Generator Selector -->
<div class="card" style="padding: 24px; border-radius: 12px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700;">Student ID Card Batch Print</h3>
    <p class="text-muted" style="margin-bottom: 20px; font-size: 14px;">Select a Class and Section to preview and batch-print high quality student identity cards with barcodes and school details.</p>
    
    <form method="GET" action="<?= APP_URL ?>/certificates/generate-id-card" target="_blank" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div style="min-width: 220px;">
            <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Class:</label>
            <select name="class_id" class="form-control" required>
                <option value="">-- All Classes --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['student_count'] ?> students)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
            <i class="bi bi-printer"></i> Generate & Print ID Cards
        </button>
    </form>
</div>

<?php elseif ($tab === 'history'): ?>
<!-- Issued Certificates Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Certificate No</th>
                    <th style="padding: 14px 18px;">Type</th>
                    <th style="padding: 14px 18px;">Student Name</th>
                    <th style="padding: 14px 18px;">Class & Section</th>
                    <th style="padding: 14px 18px;">Issue Date</th>
                    <th style="padding: 14px 18px;">Issued By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($issued)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No certificates generated yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($issued as $iss): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px; font-weight: 700; color: #0f766e;">
                                <?= htmlspecialchars($iss['certificate_no']) ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5; text-transform: uppercase; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                    <?= htmlspecialchars($iss['certificate_type']) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 18px; font-weight: 600; color: var(--text-primary, #1e293b);">
                                <?= htmlspecialchars($iss['student_name']) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                Class <?= htmlspecialchars($iss['class_name'] ?? 'N/A') ?> - <?= htmlspecialchars($iss['section_name'] ?? '') ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= date('d M Y', strtotime($iss['issue_date'])) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted, #64748b);">
                                <?= htmlspecialchars($iss['issued_by_name'] ?: 'Administration') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- TC Modal -->
<div id="tcModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 580px; max-height: 90vh; display: flex; flex-direction: column; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Generate Transfer Certificate (TC)</h3>
            <button class="btn-icon" onclick="closeModal('tcModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/certificates/generate-tc" target="_blank">
            <?= Session::csrfField() ?>
            <div class="card-body" style="padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Student *</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>">
                                <?= htmlspecialchars($st['full_name']) ?> (Adm: <?= htmlspecialchars($st['admission_no'] ?? 'N/A') ?> - Class <?= htmlspecialchars($st['class_name'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">TC Serial Number *</label>
                        <input type="text" name="tc_no" class="form-control" value="TC-<?= date('Y') ?>-<?= rand(100, 999) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Issue Date *</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Date of Leaving School</label>
                        <input type="date" name="leaving_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Conduct & Behaviour</label>
                        <input type="text" name="conduct" class="form-control" value="Good / Exemplary">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Reason for Leaving</label>
                    <input type="text" name="reason_leaving" class="form-control" value="Parent Transfer / Completed Course">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('tcModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-printer"></i> Generate & Print TC</button>
            </div>
        </form>
    </div>
</div>

<!-- Bonafide Modal -->
<div id="bonafideModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 540px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="bonafideModalTitle">Generate Certificate</h3>
            <button class="btn-icon" onclick="closeModal('bonafideModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/certificates/generate-bonafide" target="_blank">
            <?= Session::csrfField() ?>
            <input type="hidden" name="cert_type" id="cert_type_field" value="bonafide">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Student *</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>">
                                <?= htmlspecialchars($st['full_name']) ?> (Class <?= htmlspecialchars($st['class_name'] ?? '') ?> - <?= htmlspecialchars($st['section_name'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Certificate Number</label>
                        <input type="text" name="cert_no" class="form-control" value="CERT-<?= date('Y') ?>-<?= rand(100, 999) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Issue Date *</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Purpose / Applying For</label>
                    <input type="text" name="purpose" class="form-control" value="Passport / Visa / Scholarship / General Purpose">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bonafideModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-printer"></i> Generate & Print</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTcModal() {
    document.getElementById('tcModal').style.display = 'flex';
}
function openBonafideModal(type) {
    document.getElementById('cert_type_field').value = type || 'bonafide';
    document.getElementById('bonafideModalTitle').innerText = type === 'character' ? 'Generate Character Certificate' : 'Generate Bonafide Certificate';
    document.getElementById('bonafideModal').style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

