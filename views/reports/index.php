<?php require_once VIEW_PATH . '/layouts/header.php'; ?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Reports & Analytics Center</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Generate academic report cards / marksheets, financial performance audits, and school demographics</p>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Enrolled Students</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['total_students']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Collections</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['total_collected'], 2) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-person-badge"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Faculty & Staff</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['total_staff']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #f59e0b; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 20px;">
            <i class="bi bi-journal-check"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Exams Conducted</div>
            <div style="font-size: 24px; font-weight: 700; color: #f59e0b;"><?= number_format($stats['total_exams']) ?></div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/reports?tab=academic" class="btn" style="background: <?= $tab === 'academic' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'academic' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-file-earmark-spreadsheet"></i> Report Card / Marksheet Generator
    </a>
    <a href="<?= APP_URL ?>/reports?tab=finance" class="btn" style="background: <?= $tab === 'finance' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'finance' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-graph-up-arrow"></i> Financial & Fee Reports
    </a>
</div>

<?php if ($tab === 'academic'): ?>
<!-- Report Card Generator Selector Form -->
<div class="card" style="padding: 24px; border-radius: 12px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">Student Progress Report Card Generator</h3>
    <p class="text-muted" style="margin-bottom: 20px; font-size: 14px;">Select the Examination and Student to generate and print official comprehensive Progress Report Cards with subject marks, grades, attendance, and remarks.</p>

    <form method="GET" action="<?= APP_URL ?>/reports/report-card" target="_blank" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: flex-end;">
        <div class="form-group">
            <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Examination *</label>
            <select name="exam_id" class="form-control" required>
                <option value="">-- Choose Exam Term --</option>
                <?php foreach ($exams as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?> (<?= date('d M Y', strtotime($e['start_date'])) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Class</label>
            <select name="class_id" id="rep_class_select" class="form-control" onchange="loadClassStudents(this.value)">
                <option value="">-- All Classes --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['student_count'] ?> students)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Student *</label>
            <select name="student_id" id="rep_student_select" class="form-control" required>
                <option value="">-- Choose Student --</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; width: 100%;">
                <i class="bi bi-printer"></i> Generate Report Card
            </button>
        </div>
    </form>
</div>

<?php elseif ($tab === 'finance'): ?>
<!-- Financial Breakdown View -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Collection by Payment Mode -->
    <div class="card" style="padding: 24px; border-radius: 12px;">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700;">Collections by Payment Mode</h3>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color, #e2e8f0); text-align: left;">
                        <th style="padding: 10px 8px;">Payment Mode</th>
                        <th style="padding: 10px 8px;">Transactions</th>
                        <th style="padding: 10px 8px; text-align: right;">Total Collected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paymentModes)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted, #94a3b8);">No payment records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($paymentModes as $pm): ?>
                            <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                                <td style="padding: 12px 8px; font-weight: 600; text-transform: uppercase;">
                                    <i class="bi bi-wallet2" style="color: #0f766e; margin-right: 6px;"></i>
                                    <?= htmlspecialchars($pm['payment_mode']) ?>
                                </td>
                                <td style="padding: 12px 8px;"><?= $pm['txn_count'] ?> payments</td>
                                <td style="padding: 12px 8px; text-align: right; font-weight: 700; color: #10b981;">
                                    <?= number_format($pm['total_amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Class-wise Fee Summary -->
    <div class="card" style="padding: 24px; border-radius: 12px;">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700;">Class-Wise Collection Summary</h3>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color, #e2e8f0); text-align: left;">
                        <th style="padding: 10px 8px;">Class Name</th>
                        <th style="padding: 10px 8px;">Students</th>
                        <th style="padding: 10px 8px; text-align: right;">Amount Collected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classFeeSummary)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted, #94a3b8);">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($classFeeSummary as $cfs): ?>
                            <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                                <td style="padding: 12px 8px; font-weight: 700; color: var(--text-primary, #1e293b);">Class <?= htmlspecialchars($cfs['class_name']) ?></td>
                                <td style="padding: 12px 8px;"><?= $cfs['student_count'] ?> students</td>
                                <td style="padding: 12px 8px; text-align: right; font-weight: 700; color: #0f766e;">
                                    <?= number_format($cfs['total_collected'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Dynamic Student Loader for Report Card
function loadClassStudents(classId) {
    const studentSelect = document.getElementById('rep_student_select');
    studentSelect.innerHTML = '<option value="">Loading students...</option>';

    fetch('<?= APP_URL ?>/students/ajax-by-class?class_id=' + classId)
        .then(r => r.json())
        .then(data => {
            studentSelect.innerHTML = '<option value="">-- Choose Student --</option>';
            if (data.students && data.students.length > 0) {
                data.students.forEach(st => {
                    const opt = document.createElement('option');
                    opt.value = st.id;
                    opt.textContent = st.full_name + ' (Adm: ' + (st.admission_no || 'N/A') + ' - Roll: ' + (st.roll_number || 'N/A') + ')';
                    studentSelect.appendChild(opt);
                });
            } else {
                studentSelect.innerHTML = '<option value="">No students found</option>';
            }
        })
        .catch(() => {
            studentSelect.innerHTML = '<option value="">-- Choose Student --</option>';
        });
}

// Initial load on page ready
document.addEventListener('DOMContentLoaded', () => {
    const classSel = document.getElementById('rep_class_select');
    if (classSel) {
        loadClassStudents(classSel.value);
    }
});
</script>

<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>