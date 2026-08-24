<!-- Fee Receipt - Printable -->
<style>
    @media print {
        .no-print { display: none !important; }
        .receipt-wrapper { box-shadow: none !important; border: 1px solid #000 !important; }
        body { background: white !important; }
    }
    .receipt-wrapper { max-width: 700px; margin: 0 auto; background: white; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; }
    .receipt-header { padding: 24px 32px; background: linear-gradient(135deg, #1f9e8b, #0d7377); color: white; text-align: center; }
    .receipt-body { padding: 24px 32px; }
    .receipt-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
    .receipt-table th, .receipt-table td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
    .receipt-table th { background: #f8f9fa; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
    .receipt-footer { padding: 16px 32px; background: #f8f9fa; text-align: center; font-size: 12px; color: #666; }
    .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
    .info-row .label { color: #666; }
    .info-row .value { font-weight: 600; }
</style>

<!-- Print Button -->
<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn btn-primary" style="font-size: 15px; padding: 10px 32px;">
        <i class="bi bi-printer"></i> Print Receipt
    </button>
    <a href="<?= APP_URL ?>/fees" class="btn btn-secondary" style="margin-left: 8px;">
        <i class="bi bi-arrow-left"></i> Back to Fees
    </a>
    <?php if (($payment['status'] ?? 'active') === 'active'): ?>
        <button onclick="document.getElementById('cancelModal').style.display='flex'" class="btn" style="margin-left: 8px; background: #FFEBEE; color: #C62828; font-weight: 600;">
            <i class="bi bi-x-circle"></i> Request Cancellation
        </button>
    <?php endif; ?>
    <?php if (Session::hasPermission('fees.approve')): ?>
        <a href="<?= APP_URL ?>/fees/approvals" class="btn" style="margin-left: 8px; background: #FFF3E0; color: #E65100;">
            <i class="bi bi-shield-check"></i> Approvals
        </a>
    <?php endif; ?>
</div>

<?php if (($payment['status'] ?? 'active') === 'cancelled'): ?>
    <div style="max-width: 700px; margin: 0 auto 16px; padding: 14px 20px; background: #FFEBEE; border: 2px solid #C62828; border-radius: 12px; text-align: center;">
        <strong style="color: #C62828; font-size: 16px;"><i class="bi bi-x-circle-fill"></i> CANCELLED</strong>
        <p style="margin: 4px 0 0; font-size: 12px; color: #C62828;">
            Cancelled on <?= $payment['cancelled_at'] ? date('d M Y', strtotime($payment['cancelled_at'])) : '—' ?>
            <?php if ($payment['cancel_reason']): ?> — Reason: <?= htmlspecialchars($payment['cancel_reason']) ?><?php endif; ?>
        </p>
    </div>
<?php endif; ?>

<div class="receipt-wrapper">
    <!-- Header -->
    <div class="receipt-header">
        <h2 style="margin: 0; font-size: 22px; font-weight: 800;"><?= htmlspecialchars($payment['school_name']) ?></h2>
        <?php if ($payment['school_address']): ?>
            <p style="margin: 4px 0 0; font-size: 12px; opacity: 0.85;"><?= htmlspecialchars($payment['school_address']) ?></p>
        <?php endif; ?>
        <?php if ($payment['school_phone'] || $payment['school_email']): ?>
            <p style="margin: 2px 0 0; font-size: 11px; opacity: 0.7;">
                <?= $payment['school_phone'] ? 'Ph: ' . $payment['school_phone'] : '' ?>
                <?= $payment['school_email'] ? ' | ' . $payment['school_email'] : '' ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="receipt-body">
        <!-- Receipt Title -->
        <div style="text-align: center; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #333;">FEE RECEIPT</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: #666;">Receipt No: <strong style="color: #1f9e8b;"><?= $payment['receipt_number'] ?></strong></p>
            <?php if (($payment['status'] ?? 'active') === 'cancelled'): ?>
                <div style="margin-top: 8px; display: inline-block; padding: 4px 16px; background: #C62828; color: white; font-weight: 800; font-size: 14px; border-radius: 4px; letter-spacing: 2px; transform: rotate(-3deg);">CANCELLED</div>
            <?php endif; ?>
        </div>

        <!-- Student & Payment Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 16px; padding: 16px; background: #f8f9fa; border-radius: 10px;">
            <div>
                <div class="info-row"><span class="label">Student Name:</span><span class="value"><?= htmlspecialchars($payment['student_name']) ?></span></div>
                <div class="info-row"><span class="label">Admission No:</span><span class="value"><?= htmlspecialchars($payment['admission_no'] ?? '—') ?></span></div>
                <div class="info-row"><span class="label">Class:</span><span class="value"><?= htmlspecialchars(($payment['class_name'] ?? '') . ($payment['section_name'] ? ' - ' . $payment['section_name'] : '')) ?></span></div>
            </div>
            <div>
                <div class="info-row"><span class="label">Date:</span><span class="value"><?= date('d M Y', strtotime($payment['payment_date'])) ?></span></div>
                <div class="info-row"><span class="label">Mode:</span><span class="value"><?= ucfirst(str_replace('_', ' ', $payment['payment_mode'])) ?></span></div>
                <?php if ($payment['transaction_ref']): ?>
                    <div class="info-row"><span class="label">Ref:</span><span class="value"><?= htmlspecialchars($payment['transaction_ref']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fee Items -->
        <table class="receipt-table">
            <thead>
                <tr><th style="width: 50px;">#</th><th>Particulars</th><th>Period</th><th style="text-align: right;">Amount (₹)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($item['head_name']) ?></td>
                        <td style="color: #666;"><?= htmlspecialchars($item['period_label'] ?? '—') ?></td>
                        <td style="text-align: right; font-weight: 600;">₹<?= number_format($item['amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <?php if ($payment['discount_amount'] > 0): ?>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: 600;">Subtotal:</td>
                        <td style="text-align: right;">₹<?= number_format($payment['total_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: 600; color: #7B1FA2;">Discount:</td>
                        <td style="text-align: right; color: #7B1FA2;">- ₹<?= number_format($payment['discount_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="border-top: 2px solid #1f9e8b;">
                    <td colspan="3" style="text-align: right; font-weight: 800; font-size: 15px;">Net Amount:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 15px; color: #1f9e8b;">₹<?= number_format($payment['net_amount'], 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <?php if ($payment['remarks']): ?>
            <div style="font-size: 12px; color: #666; padding: 8px 0; border-top: 1px dashed #ddd; margin-top: 8px;">
                <strong>Remarks:</strong> <?= htmlspecialchars($payment['remarks']) ?>
            </div>
        <?php endif; ?>

        <!-- Signature -->
        <div style="display: flex; justify-content: space-between; margin-top: 40px; padding-top: 8px;">
            <div style="text-align: center;">
                <div style="border-top: 1px solid #999; width: 150px; padding-top: 4px; font-size: 11px; color: #666;">Parent/Guardian</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 12px; margin-bottom: 20px;"><?= htmlspecialchars($payment['collected_by_name'] ?? '') ?></div>
                <div style="border-top: 1px solid #999; width: 150px; padding-top: 4px; font-size: 11px; color: #666;">Authorized Signatory</div>
            </div>
        </div>
    </div>

    <div class="receipt-footer">
        This is a computer-generated receipt. • <?= htmlspecialchars($payment['school_name']) ?>
    </div>
</div>

<!-- Cancel Request Modal -->
<?php if (($payment['status'] ?? 'active') === 'active'): ?>
<div id="cancelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 460px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #C62828;">
                <i class="bi bi-x-circle"></i> Request Cancellation
            </h3>
            <button onclick="document.getElementById('cancelModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div class="card-body">
            <div style="padding: 10px 14px; background: #FFF8E1; border-radius: 8px; margin-bottom: 16px; font-size: 12px; color: #E65100;">
                <i class="bi bi-info-circle"></i> This request will be sent for approval. The receipt will only be cancelled after an authorized person approves it.
            </div>
            <form method="POST" action="<?= APP_URL ?>/fees/request-cancel/<?= $payment['id'] ?>">
                <?= Session::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Reason for Cancellation <span style="color: var(--danger);">*</span></label>
                    <textarea class="form-control" name="reason" rows="3" placeholder="Why should this receipt be cancelled?" required></textarea>
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('cancelModal').style.display='none'" class="btn btn-secondary">Go Back</button>
                    <button type="submit" class="btn" style="background: #C62828; color: white; font-weight: 700;">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
