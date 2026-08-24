<!-- Cancellation Approvals -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <?php if ($pendingCount > 0): ?>
            <span class="badge" style="background: #FFF3E0; color: #E65100; padding: 6px 14px; font-size: 13px;">
                <i class="bi bi-clock"></i> <?= $pendingCount ?> pending request<?= $pendingCount > 1 ? 's' : '' ?>
            </span>
        <?php else: ?>
            <span style="font-size: 13px; color: var(--gray-500);">No pending requests</span>
        <?php endif; ?>
    </div>
    <a href="<?= APP_URL ?>/fees" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Fees</a>
</div>

<?php if (!empty($requests)): ?>
    <?php foreach ($requests as $r): ?>
        <div class="card mb-3" style="border-left: 4px solid <?= $r['status'] === 'pending' ? '#E65100' : ($r['status'] === 'approved' ? '#C62828' : 'var(--gray-300)') ?>;">
            <div class="card-body" style="padding: 16px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <!-- Left: Request Info -->
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <code style="font-weight: 700; color: #1f9e8b; font-size: 14px;"><?= $r['receipt_number'] ?></code>
                            <?php if ($r['status'] === 'pending'): ?>
                                <span class="badge" style="background: #FFF3E0; color: #E65100; font-size: 11px;">⏳ Pending Approval</span>
                            <?php elseif ($r['status'] === 'approved'): ?>
                                <span class="badge" style="background: #FFEBEE; color: #C62828; font-size: 11px;">🚫 Cancelled</span>
                            <?php else: ?>
                                <span class="badge" style="background: var(--gray-100); color: var(--gray-500); font-size: 11px;">✕ Rejected</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; font-size: 13px;">
                            <div>
                                <span style="color: var(--gray-400); font-size: 10px; text-transform: uppercase; display: block;">Student</span>
                                <strong><?= htmlspecialchars($r['student_name']) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--gray-400); font-size: 10px; text-transform: uppercase; display: block;">Class</span>
                                <?= htmlspecialchars(($r['class_name'] ?? '') . ($r['section_name'] ? '-' . $r['section_name'] : '')) ?>
                            </div>
                            <div>
                                <span style="color: var(--gray-400); font-size: 10px; text-transform: uppercase; display: block;">Amount</span>
                                <strong style="color: #C62828;">₹<?= number_format($r['net_amount'], 2) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--gray-400); font-size: 10px; text-transform: uppercase; display: block;">Payment Date</span>
                                <?= date('d M Y', strtotime($r['payment_date'])) ?>
                            </div>
                        </div>
                        <div style="margin-top: 10px; padding: 8px 12px; background: #FFF8E1; border-radius: 8px; font-size: 12px;">
                            <strong style="color: #E65100;">Reason:</strong> <?= htmlspecialchars($r['reason']) ?>
                        </div>
                        <div style="font-size: 11px; color: var(--gray-400); margin-top: 6px;">
                            Requested by <strong><?= htmlspecialchars($r['requested_by_name']) ?></strong> on <?= date('d M Y, h:i A', strtotime($r['created_at'])) ?>
                        </div>

                        <?php if ($r['status'] !== 'pending'): ?>
                            <div style="font-size: 11px; color: var(--gray-400); margin-top: 4px;">
                                <?= $r['status'] === 'approved' ? '✅ Approved' : '❌ Rejected' ?> by <strong><?= htmlspecialchars($r['reviewed_by_name'] ?? '—') ?></strong>
                                on <?= $r['reviewed_at'] ? date('d M Y, h:i A', strtotime($r['reviewed_at'])) : '—' ?>
                                <?php if ($r['review_remarks']): ?>
                                    — "<?= htmlspecialchars($r['review_remarks']) ?>"
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Action Buttons (only for pending) -->
                    <?php if ($r['status'] === 'pending'): ?>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-left: 20px;">
                            <button onclick="showApproveModal(<?= $r['id'] ?>, '<?= $r['receipt_number'] ?>')" 
                                    class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; font-weight: 700; padding: 8px 16px; border-radius: 8px; white-space: nowrap;">
                                <i class="bi bi-check-circle"></i> Approve Cancel
                            </button>
                            <button onclick="showRejectModal(<?= $r['id'] ?>, '<?= $r['receipt_number'] ?>')"
                                    class="btn btn-sm" style="background: var(--gray-100); color: var(--gray-600); border: none; padding: 8px 16px; border-radius: 8px; white-space: nowrap;">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                            <a href="<?= APP_URL ?>/fees/receipt/<?= $r['fee_payment_id'] ?>" class="btn btn-sm" 
                               style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 8px 16px; border-radius: 8px; text-align: center; white-space: nowrap;">
                                <i class="bi bi-receipt"></i> View Receipt
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body empty-state" style="padding: 48px;">
            <i class="bi bi-shield-check" style="font-size: 48px; color: var(--gray-300);"></i>
            <h3>No cancellation requests</h3>
            <p>All clear — no pending approvals</p>
        </div>
    </div>
<?php endif; ?>

<!-- Approve Modal -->
<div id="approveModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 440px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #C62828;">
                <i class="bi bi-exclamation-triangle"></i> Approve Cancellation
            </h3>
            <button onclick="document.getElementById('approveModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <div style="padding: 12px; background: #FFEBEE; border-radius: 8px; margin-bottom: 16px; font-size: 13px; color: #C62828;">
                <strong>⚠️ Warning:</strong> This will permanently cancel Receipt <strong id="approveReceiptNo"></strong>. The payment record will be voided.
            </div>
            <form id="approveForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Review Remarks (optional)</label>
                    <textarea class="form-control" name="review_remarks" rows="2" placeholder="Optional remarks..."></textarea>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('approveModal').style.display='none'" class="btn btn-secondary">Go Back</button>
                    <button type="submit" class="btn" style="background: #C62828; color: white; font-weight: 700;">
                        <i class="bi bi-check-circle"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 440px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-x-circle" style="color: var(--gray-500);"></i> Reject Cancellation
            </h3>
            <button onclick="document.getElementById('rejectModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: var(--gray-500);">Rejecting will keep Receipt <strong id="rejectReceiptNo"></strong> active.</p>
            <form id="rejectForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Reason for Rejection <span style="color: var(--danger);">*</span></label>
                    <textarea class="form-control" name="review_remarks" rows="2" placeholder="Why are you rejecting this request?" required></textarea>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-x-circle"></i> Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showApproveModal(id, receiptNo) {
    document.getElementById('approveForm').action = '<?= APP_URL ?>/fees/approve-cancel/' + id;
    document.getElementById('approveReceiptNo').textContent = '#' + receiptNo;
    document.getElementById('approveModal').style.display = 'flex';
}
function showRejectModal(id, receiptNo) {
    document.getElementById('rejectForm').action = '<?= APP_URL ?>/fees/reject-cancel/' + id;
    document.getElementById('rejectReceiptNo').textContent = '#' + receiptNo;
    document.getElementById('rejectModal').style.display = 'flex';
}
</script>
