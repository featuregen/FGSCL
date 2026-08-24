<!-- Fee Collection -->
<style>
    .student-search { position: relative; }
    .student-list { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--gray-200); border-radius: 8px; max-height: 250px; overflow-y: auto; z-index: 999; display: none; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
    .student-list.show { display: block; }
    .student-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid var(--gray-50); font-size: 13px; }
    .student-item:hover { background: var(--gray-50); }
    .fee-row { display: flex; align-items: center; padding: 12px 20px; border-bottom: 1px solid var(--gray-50); gap: 16px; }
    .fee-row:last-child { border-bottom: none; }
    .fee-check { accent-color: #1f9e8b; width: 18px; height: 18px; cursor: pointer; }
</style>

<!-- Student Search -->
<div class="card mb-4" style="overflow: visible !important; position: relative; z-index: 10;">
    <div class="card-header">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-search" style="color: #1f9e8b;"></i> Select Student
        </h3>
    </div>
    <div class="card-body" style="overflow: visible !important; padding-bottom: 20px;">
        <div class="student-search" style="position: relative;">
            <input type="text" class="form-control" id="studentSearch" 
                   placeholder="🔍 Search by name or admission number..." 
                   value="<?= $selectedStudent ? htmlspecialchars($selectedStudent['full_name'] . ' (' . ($selectedStudent['admission_no'] ?? '') . ')') : '' ?>"
                   autocomplete="off" style="font-size: 14px; padding: 10px 16px;">
            <div class="student-list" id="studentList">
                <?php foreach ($students as $s): ?>
                    <div class="student-item" onclick="selectStudent(<?= $s['id'] ?>, '<?= htmlspecialchars($s['full_name']) ?>')">
                        <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                        <span style="color: var(--gray-400); margin-left: 8px;"><?= htmlspecialchars($s['admission_no'] ?? '') ?></span>
                        <span style="float: right; font-size: 12px; color: var(--gray-500);"><?= htmlspecialchars(($s['class_name'] ?? '') . ($s['section_name'] ? ' - ' . $s['section_name'] : '')) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($selectedStudent): ?>
    <!-- Student Info Bar -->
    <div class="card mb-4" style="border-left: 4px solid #1f9e8b;">
        <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="font-size: 16px;"><?= htmlspecialchars($selectedStudent['full_name']) ?></strong>
                <span style="margin-left: 12px;">
                    <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= htmlspecialchars($selectedStudent['class_name'] ?? '') ?> - <?= htmlspecialchars($selectedStudent['section_name'] ?? '') ?></span>
                    <?php if ($selectedStudent['admission_no']): ?>
                        <code style="margin-left: 6px;"><?= $selectedStudent['admission_no'] ?></code>
                    <?php endif; ?>
                </span>
                <?php if (!empty($appliedDiscounts)): ?>
                    <span style="margin-left: 12px;">
                        <?php foreach ($appliedDiscounts as $ad): ?>
                            <span class="badge" style="background: #F3E5F5; color: #7B1FA2; font-size: 10px;">
                                <i class="bi bi-percent"></i> <?= htmlspecialchars($ad['discount_name']) ?>
                                <?= $ad['discount_type'] === 'percentage' ? $ad['discount_value'] . '%' : '₹' . number_format($ad['discount_value'], 2) ?>
                            </span>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="<?= APP_URL ?>/fees/student-ledger/<?= $selectedStudent['id'] ?>" class="btn btn-sm" style="background: #F3E5F5; color: #7B1FA2; border: none;">
                <i class="bi bi-journal-text"></i> View Ledger
            </a>
        </div>
    </div>

    <?php if (!empty($feeStructure)): ?>
        <!-- Fee Collection Form -->
        <form method="POST" action="<?= APP_URL ?>/fees/process-payment">
            <?= Session::csrfField() ?>
            <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">

            <div class="card mb-4">
                <div class="card-header">
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                        <i class="bi bi-cash-stack" style="color: #1f9e8b;"></i> Fee Items
                    </h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; padding: 10px 20px; background: var(--gray-50); font-size: 11px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px;">
                        <div style="width: 36px;"></div>
                        <div style="flex: 2;">Fee Head</div>
                        <div style="flex: 1; text-align: center;">Amount</div>
                        <div style="flex: 1; text-align: center;">Paid</div>
                        <div style="flex: 1; text-align: center;">Balance</div>
                        <div style="width: 90px; text-align: center;">Discount</div>
                        <div style="width: 100px;">Pay Amt</div>
                        <div style="width: 100px;">Period</div>
                    </div>

                    <?php 
                    $grandTotal = 0; $grandDiscount = 0; $grandNet = 0;
                    foreach ($feeStructure as $idx => $fs): 
                        $totalPaid = $paidMap[$fs['fee_head_id'] . '-total'] ?? 0;
                        $balance = max(0, $fs['amount'] - $totalPaid);
                        
                        // Use pre-computed per-head discount from controller
                        $headDiscount = $perHeadDiscounts[$fs['fee_head_id']] ?? 0;
                        $headDiscount = min($headDiscount, $balance); // Don't exceed balance
                        $netBalance = max(0, $balance - $headDiscount);
                        
                        $grandTotal += $balance;
                        $grandDiscount += $headDiscount;
                        $grandNet += $netBalance;
                    ?>
                        <div class="fee-row" id="feeRow<?= $idx ?>">
                            <div style="width: 36px;">
                                <input type="checkbox" class="fee-check" id="feeCheck<?= $idx ?>" onchange="toggleFeeRow(<?= $idx ?>)" <?= $balance > 0 ? '' : 'disabled' ?>>
                            </div>
                            <div style="flex: 2;">
                                <strong style="font-size: 13px;"><?= htmlspecialchars($fs['head_name']) ?></strong>
                                <?php if ($fs['head_code']): ?>
                                    <code style="font-size: 10px; margin-left: 4px;"><?= $fs['head_code'] ?></code>
                                <?php endif; ?>
                                <?php if (($fs['head_type'] ?? 'mandatory') === 'optional'): ?>
                                    <span class="badge" style="background: #FFF3E0; color: #E65100; font-size: 9px; margin-left: 4px;">Optional</span>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1; text-align: center; font-weight: 600; font-size: 13px;">₹<?= number_format($fs['amount'], 2) ?></div>
                            <div style="flex: 1; text-align: center; color: #1f9e8b; font-weight: 600; font-size: 13px;">₹<?= number_format($totalPaid, 2) ?></div>
                            <div style="flex: 1; text-align: center; font-weight: 700; font-size: 13px; color: <?= $balance > 0 ? '#C62828' : '#1f9e8b' ?>;" id="balDisp<?= $idx ?>">
                                ₹<?= number_format($balance, 2) ?>
                            </div>
                            <div style="width: 90px;">
                                <input type="number" class="form-control" 
                                       name="items[<?= $idx ?>][discount]" id="feeDisc<?= $idx ?>"
                                       value="<?= $headDiscount ?>" min="0" max="<?= $balance ?>" step="0.01"
                                       data-balance="<?= $balance ?>"
                                       onchange="updateHeadNet(<?= $idx ?>)" oninput="updateHeadNet(<?= $idx ?>)"
                                       disabled
                                       style="padding: 4px 6px; font-size: 12px; width: 80px; color: #7B1FA2; border-color: #E1BEE7; text-align: center;">
                            </div>
                            <div style="width: 100px;">
                                <input type="hidden" name="items[<?= $idx ?>][fee_head_id]" value="<?= $fs['fee_head_id'] ?>" disabled id="feeHeadId<?= $idx ?>">
                                <input type="number" class="form-control" name="items[<?= $idx ?>][amount]" 
                                       id="feeAmt<?= $idx ?>" step="0.01" min="0" max="<?= $balance ?>"
                                       value="<?= max(0, $netBalance) ?>" disabled
                                       data-balance="<?= $balance ?>"
                                       onchange="recalcTotal()" oninput="recalcTotal()"
                                       style="padding: 4px 6px; font-size: 12px; width: 90px;">
                            </div>
                            <div style="width: 100px;">
                                <input type="text" class="form-control" name="items[<?= $idx ?>][period_label]" 
                                       id="feePeriod<?= $idx ?>" disabled
                                       placeholder="<?= $fs['is_recurring'] ? date('Y-m') : 'N/A' ?>"
                                       value="<?= $fs['is_recurring'] ? date('Y-m') : '' ?>"
                                       style="padding: 4px 6px; font-size: 12px;">
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Total Summary -->
                    <div style="background: #F0FDF9; border-top: 2px solid #1f9e8b; padding: 14px 20px;">
                        <div style="display: flex; justify-content: flex-end; gap: 40px; font-size: 13px;">
                            <div>
                                <span style="color: var(--gray-500);">Subtotal:</span>
                                <span id="subtotalDisplay" style="font-weight: 700; margin-left: 6px;">₹<?= number_format($grandTotal, 2) ?></span>
                            </div>
                            <div>
                                <span style="color: #7B1FA2;">Discount:</span>
                                <span id="discountDisplay" style="font-weight: 700; color: #7B1FA2; margin-left: 6px;">-₹<?= number_format($grandDiscount, 2) ?></span>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #1f9e8b;">
                            <span style="font-weight: 800; font-size: 16px;">Net Payable:</span>
                            <span id="totalDisplay" style="color: #1f9e8b; font-weight: 800; font-size: 18px;">₹<?= number_format($grandNet, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                        <i class="bi bi-credit-card" style="color: #6366F1;"></i> Payment Details
                    </h3>
                </div>
                <div class="card-body">
                    <input type="hidden" name="discount_amount" value="<?= $autoDiscountAmount ?? 0 ?>">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Mode</label>
                            <select class="form-control" name="payment_mode" id="paymentMode" onchange="toggleRef()">
                                <option value="cash">💵 Cash</option>
                                <option value="upi">📱 UPI</option>
                                <option value="online">🌐 Online</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group" id="refGroup" style="display: none;">
                            <label class="form-label">Transaction Ref</label>
                            <input type="text" class="form-control" name="transaction_ref" placeholder="Txn/Cheque No.">
                        </div>
                    </div>
                    <?php if (!empty($appliedDiscounts)): ?>
                        <div style="padding: 10px 14px; background: #F3E5F5; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-percent" style="color: #7B1FA2;"></i>
                            <span style="font-size: 12px; color: #7B1FA2; font-weight: 600;">
                                Applied Concessions:
                                <?php foreach ($appliedDiscounts as $ad): ?>
                                    <?= htmlspecialchars($ad['discount_name']) ?>
                                    (<?= $ad['discount_type'] === 'percentage' ? $ad['discount_value'] . '%' : '₹' . number_format($ad['discount_value'], 2) ?>)
                                <?php endforeach; ?>
                                = <strong>₹<?= number_format($autoDiscountAmount, 2) ?> off</strong>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <input type="text" class="form-control" name="remarks" placeholder="Optional remarks">
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <a href="<?= APP_URL ?>/fees" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="font-size: 15px; padding: 10px 28px;">
                    <i class="bi bi-cash-coin"></i> Collect Payment
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="card">
            <div class="card-body empty-state" style="padding: 48px;">
                <i class="bi bi-exclamation-triangle" style="font-size: 40px; color: var(--gray-300);"></i>
                <h3>No fee structure found</h3>
                <p>Set up fee structure for this student's class first.</p>
                <a href="<?= APP_URL ?>/fees/structures" class="btn btn-primary"><i class="bi bi-grid"></i> Fee Structure</a>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
// Student search
const searchInput = document.getElementById('studentSearch');
const studentList = document.getElementById('studentList');

if (searchInput) {
    searchInput.addEventListener('focus', () => studentList.classList.add('show'));
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        const items = studentList.querySelectorAll('.student-item');
        items.forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        studentList.classList.add('show');
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.student-search')) studentList.classList.remove('show');
    });
}

function selectStudent(id, name) {
    window.location.href = '<?= APP_URL ?>/fees/collect/' + id;
}

function toggleFeeRow(idx) {
    const checked = document.getElementById('feeCheck' + idx).checked;
    ['feeHeadId', 'feeAmt', 'feePeriod', 'feeDisc'].forEach(prefix => {
        const el = document.getElementById(prefix + idx);
        if (el) el.disabled = !checked;
    });
    recalcTotal();
}

function updateHeadNet(idx) {
    const discEl = document.getElementById('feeDisc' + idx);
    const amtEl = document.getElementById('feeAmt' + idx);
    if (!discEl || !amtEl) return;
    
    const balance = parseFloat(discEl.dataset.balance || 0);
    const disc = Math.min(parseFloat(discEl.value || 0), balance);
    const net = Math.max(0, balance - disc);
    
    amtEl.value = net.toFixed(2);
    amtEl.max = balance;
    recalcTotal();
}

function recalcTotal() {
    let subtotal = 0, discount = 0, net = 0;
    document.querySelectorAll('.fee-check:checked').forEach(cb => {
        const idx = cb.id.replace('feeCheck', '');
        const amt = parseFloat(document.getElementById('feeAmt' + idx)?.value || 0);
        const disc = parseFloat(document.getElementById('feeDisc' + idx)?.value || 0);
        const bal = parseFloat(document.getElementById('feeDisc' + idx)?.dataset.balance || 0);
        subtotal += bal;
        discount += disc;
        net += amt;
    });
    
    const subtotalEl = document.getElementById('subtotalDisplay');
    const discountEl = document.getElementById('discountDisplay');
    const totalEl = document.getElementById('totalDisplay');
    
    if (subtotalEl) subtotalEl.textContent = '₹' + subtotal.toFixed(2);
    if (discountEl) discountEl.textContent = '-₹' + discount.toFixed(2);
    if (totalEl) totalEl.textContent = '₹' + net.toFixed(2);
    
    // Update hidden discount_amount
    const hiddenDisc = document.querySelector('[name="discount_amount"]');
    if (hiddenDisc) hiddenDisc.value = discount.toFixed(2);
}

function toggleRef() {
    const mode = document.getElementById('paymentMode').value;
    document.getElementById('refGroup').style.display = (mode !== 'cash') ? 'block' : 'none';
}
</script>
