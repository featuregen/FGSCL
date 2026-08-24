<div class="content-header">
    <h2 class="content-title">Payroll Management</h2>
</div>

<div class="mb-4" style="border-bottom: 1px solid var(--gray-200);">
    <div style="display: flex; gap: 20px;">
        <a href="<?= APP_URL ?>/payroll" style="padding-bottom: 12px; font-weight: 600; color: var(--gray-500); text-decoration: none;">Monthly Payroll</a>
        <a href="<?= APP_URL ?>/payroll/structures" style="padding-bottom: 12px; font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--primary); text-decoration: none;">Salary Structures</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Basic Salary</th>
                    <th>Net Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffList as $staff): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;"><?= htmlspecialchars($staff['full_name']) ?></div>
                        <div style="font-size: 11px; color: var(--gray-500);"><?= htmlspecialchars($staff['email'] ?? '') ?></div>
                    </td>
                    <td><?= htmlspecialchars($staff['department_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($staff['designation_name'] ?? '-') ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($currency) ?><?= number_format($staff['basic_salary'] ?? 0, 2) ?></td>
                    <td style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($currency) ?><?= number_format($staff['net_salary'] ?? 0, 2) ?></td>
                    <td>
                        <button onclick="editStructure(<?= htmlspecialchars(json_encode($staff)) ?>)" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;">
                            <i class="bi-pencil"></i> Manage Salary
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($staffList)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No staff found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Salary Structure Drawer -->
<div id="structureModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: flex-end;">
    <div class="card" style="width: 550px; max-width: 100vw; height: 100vh; margin: 0; border-radius: 0; display: flex; flex-direction: column; animation: slideIn 0.3s ease-out;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--gray-200);">
            <div>
                <h3 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0;">Salary Structure</h3>
                <p id="staffName" style="font-size: 12px; color: var(--gray-500); margin: 4px 0 0 0;"></p>
            </div>
            <button class="btn-icon" onclick="closeModal('structureModal')" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--gray-500);"><i class="bi-x"></i></button>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa;">
            <form id="structureForm" action="" method="POST">
                <?= Session::csrfField() ?>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <label class="form-label" style="font-weight: 600;">Basic Salary (<?= htmlspecialchars($currency) ?>)</label>
                        <input type="number" step="0.01" class="form-control" name="basic_salary" id="basic_salary" value="0.00" required>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 style="font-size: 14px; font-weight: 600; margin: 0;">Allowances (Increments)</h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="addAllowanceRow()"><i class="bi-plus"></i> Add</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="data-table" id="allowanceTable">
                            <thead>
                                <tr>
                                    <th>Name (e.g. Bonus, Transport)</th>
                                    <th style="width: 120px;">Amount (<?= htmlspecialchars($currency) ?>)</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="allowanceList"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 style="font-size: 14px; font-weight: 600; margin: 0;">Deductions</h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="addDeductionRow()"><i class="bi-plus"></i> Add</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="data-table" id="deductionTable">
                            <thead>
                                <tr>
                                    <th>Name (e.g. Tax, PF)</th>
                                    <th style="width: 120px;">Amount (<?= htmlspecialchars($currency) ?>)</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="deductionList"></tbody>
                        </table>
                    </div>
                </div>

                <div class="p-3" style="background: white; border: 1px solid var(--gray-200); border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 700;">
                        <span>Net Salary:</span>
                        <span id="calcNetSalary" style="color: var(--primary);"><?= htmlspecialchars($currency) ?>0.00</span>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('structureModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi-check-lg"></i> Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>

<script>
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function calcTotal() {
    let basic = parseFloat(document.getElementById('basic_salary').value) || 0;
    
    let alwTotal = 0;
    document.querySelectorAll('.alw-amt').forEach(el => {
        alwTotal += parseFloat(el.value) || 0;
    });

    let dedTotal = 0;
    document.querySelectorAll('.ded-amt').forEach(el => {
        dedTotal += parseFloat(el.value) || 0;
    });

    let net = basic + alwTotal - dedTotal;
    document.getElementById('calcNetSalary').textContent = '<?= htmlspecialchars($currency) ?>' + net.toFixed(2);
}

document.getElementById('basic_salary').addEventListener('input', calcTotal);

function addAllowanceRow(name = '', amount = '') {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="allowance_name[]" class="form-control" value="${name}" placeholder="Allowance Name"></td>
        <td><input type="number" step="0.01" name="allowance_amount[]" class="form-control alw-amt" value="${amount}" oninput="calcTotal()"></td>
        <td><button type="button" class="btn-icon text-danger" onclick="this.closest('tr').remove(); calcTotal()"><i class="bi-trash"></i></button></td>
    `;
    document.getElementById('allowanceList').appendChild(tr);
    calcTotal();
}

function addDeductionRow(name = '', amount = '') {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="deduction_name[]" class="form-control" value="${name}" placeholder="Deduction Name"></td>
        <td><input type="number" step="0.01" name="deduction_amount[]" class="form-control ded-amt" value="${amount}" oninput="calcTotal()"></td>
        <td><button type="button" class="btn-icon text-danger" onclick="this.closest('tr').remove(); calcTotal()"><i class="bi-trash"></i></button></td>
    `;
    document.getElementById('deductionList').appendChild(tr);
    calcTotal();
}

function editStructure(staff) {
    document.getElementById('structureForm').action = '<?= APP_URL ?>/payroll/save-structure/' + staff.id;
    document.getElementById('staffName').textContent = staff.full_name + ' | ' + (staff.designation_name || 'Staff');
    document.getElementById('basic_salary').value = staff.basic_salary || '0.00';
    
    document.getElementById('allowanceList').innerHTML = '';
    document.getElementById('deductionList').innerHTML = '';

    if (staff.allowances_json) {
        try {
            let alws = JSON.parse(staff.allowances_json);
            alws.forEach(a => addAllowanceRow(a.name, a.amount));
        } catch(e){}
    }
    
    if (staff.deductions_json) {
        try {
            let deds = JSON.parse(staff.deductions_json);
            deds.forEach(d => addDeductionRow(d.name, d.amount));
        } catch(e){}
    }

    calcTotal();
    document.getElementById('structureModal').style.display = 'flex';
}
</script>
