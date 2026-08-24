<!-- Student Fee Ledger -->
<div class="card mb-4" style="border-left: 4px solid #7B1FA2;">
    <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <strong style="font-size: 18px;"><?= htmlspecialchars($student['full_name']) ?></strong>
            <span style="margin-left: 12px;">
                <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= htmlspecialchars($student['class_name'] ?? '') ?> - <?= htmlspecialchars($student['section_name'] ?? '') ?></span>
                <?php if ($student['admission_no']): ?>
                    <code style="margin-left: 6px;"><?= $student['admission_no'] ?></code>
                <?php endif; ?>
            </span>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 12px; color: var(--gray-500);">Total Paid</div>
            <div style="font-size: 22px; font-weight: 800; color: #1f9e8b;">₹<?= number_format($totalPaid, 2) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-journal-text" style="color: #7B1FA2;"></i> Payment History
        </h3>
        <a href="<?= APP_URL ?>/fees/collect/<?= $student['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-cash-coin"></i> Collect Fee
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($payments)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Fee Heads</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><code style="font-weight: 600; color: #1f9e8b;"><?= $p['receipt_number'] ?></code></td>
                            <td style="font-size: 13px;"><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                            <td style="font-size: 12px; max-width: 200px;">
                                <?php 
                                    $heads = explode(', ', $p['head_names'] ?? '');
                                    foreach (array_slice($heads, 0, 3) as $h): 
                                ?>
                                    <span class="badge" style="background: var(--gray-100); color: var(--gray-600); font-size: 10px; margin-right: 2px;"><?= htmlspecialchars($h) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($heads) > 3): ?>
                                    <span style="font-size: 10px; color: var(--gray-400);">+<?= count($heads) - 3 ?> more</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; color: #1f9e8b;">₹<?= number_format($p['net_amount'], 2) ?></td>
                            <td style="font-size: 12px;"><?= ucfirst(str_replace('_', ' ', $p['payment_mode'])) ?></td>
                            <td>
                                <?php if (($p['status'] ?? 'active') === 'cancelled'): ?>
                                    <span class="badge" style="background: #FFEBEE; color: #C62828; font-size: 10px;">Cancelled</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #E8F5E9; color: #2E7D32; font-size: 10px;">Paid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/fees/receipt/<?= $p['id'] ?>" class="btn btn-sm" style="background:#E0F2F1;color:#1f9e8b;border:none;padding:4px 8px;border-radius:6px;">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state" style="padding: 48px;">
                <i class="bi bi-journal-x" style="font-size: 40px; color: var(--gray-300);"></i>
                <h3>No payments recorded</h3>
                <p>No fee payments have been made for this student yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
