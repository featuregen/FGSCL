<div class="content-header">
    <h2 class="content-title">Payroll Management</h2>
    <?php if (Session::hasPermission('payroll.process')): ?>
    <form action="<?= APP_URL ?>/payroll/generate" method="POST" class="d-inline" onsubmit="return confirm('Generate payslips for all staff for this month?');">
        <?= Session::csrfField() ?>
        <input type="hidden" name="month" value="<?= $month ?>">
        <input type="hidden" name="year" value="<?= $year ?>">
        <button type="submit" class="btn btn-primary">
            <i class="bi-magic"></i> Generate Payslips
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="mb-4" style="border-bottom: 1px solid var(--gray-200);">
    <div style="display: flex; gap: 20px;">
        <a href="<?= APP_URL ?>/payroll" style="padding-bottom: 12px; font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--primary); text-decoration: none;">Monthly Payroll</a>
        <a href="<?= APP_URL ?>/payroll/structures" style="padding-bottom: 12px; font-weight: 600; color: var(--gray-500); text-decoration: none;">Salary Structures</a>
    </div>
</div>

<div class="card mb-4">
    <form action="" method="GET" class="d-flex align-items-end gap-3">
        <div class="form-group mb-0" style="min-width: 150px;">
            <label class="form-label">Month</label>
            <select name="month" class="form-control" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group mb-0" style="min-width: 150px;">
            <label class="form-label">Year</label>
            <select name="year" class="form-control" onchange="this.form.submit()">
                <?php $currentYear = date('Y'); for ($y = $currentYear - 2; $y <= $currentYear; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Designation</th>
                    <th>Basic Salary</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payrolls as $p): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($p['staff_name']) ?></strong>
                        <div class="text-xs text-muted"><?= htmlspecialchars($p['staff_email'] ?? '') ?></div>
                    </td>
                    <td>
                        <?= htmlspecialchars($p['designation_name'] ?? 'N/A') ?>
                        <div class="text-xs text-muted"><?= htmlspecialchars($p['department_name'] ?? '') ?></div>
                    </td>
                    <td><?= htmlspecialchars($currency) ?><?= number_format($p['basic_salary'], 2) ?></td>
                    <td><strong><?= htmlspecialchars($currency) ?><?= number_format($p['net_salary'], 2) ?></strong></td>
                    <td>
                        <?php if ($p['status'] === 'paid'): ?>
                            <span class="badge badge-success">Paid</span>
                            <div class="text-xs text-muted mt-1"><?= date('d M Y', strtotime($p['payment_date'])) ?></div>
                        <?php else: ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['status'] === 'generated'): ?>
                            <a href="<?= APP_URL ?>/payroll/regenerate/<?= $p['id'] ?>" class="btn-icon text-warning mr-2" title="Regenerate from Salary Structure">
                                <i class="bi-arrow-clockwise"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-success" onclick="markPaid(<?= $p['id'] ?>)">
                                <i class="bi-check-circle"></i> Mark Paid
                            </button>
                        <?php endif; ?>
                        <a href="<?= APP_URL ?>/payroll/payslip/<?= $p['id'] ?>" target="_blank" class="btn-icon text-primary" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i class="bi-file-earmark-pdf"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payrolls)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No payslips generated for this month. Click "Generate Payslips" to calculate them based on staff salary structures.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mark Paid Modal -->
<div id="markPaidModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="modal-title" style="font-size: 16px; font-weight: 700; margin: 0;">Record Payment</h3>
            <button class="btn-icon" onclick="closeModal('markPaidModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);"><i class="bi-x"></i></button>
        </div>
        <div class="card-body">
            <form id="markPaidForm" action="" method="POST">
                <?= Session::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Transaction Reference (Optional)</label>
                    <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. UTR Number">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('markPaidModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Paid</button>
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
function markPaid(id) {
    document.getElementById('markPaidForm').action = '<?= APP_URL ?>/payroll/mark-paid/' + id;
    openModal('markPaidModal');
}
</script>
