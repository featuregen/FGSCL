<!-- Fee Reports -->
<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar, .app-header, .main-content > nav { display: none !important; }
        .app-content { margin-left: 0 !important; }
        .main-content { padding: 0 !important; }
        body { background: white !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>

<div class="no-print" style="display: flex; justify-content: flex-end; margin-bottom: 12px;">
    <button onclick="window.print()" class="btn" style="background: #E3F2FD; color: #1565C0; font-weight: 600;">
        <i class="bi bi-printer"></i> Print Report
    </button>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/fees/report" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">From Date</label>
                <input type="date" class="form-control" name="date_from" value="<?= $dateFrom ?>" style="width: 150px;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">To Date</label>
                <input type="date" class="form-control" name="date_to" value="<?= $dateTo ?>" style="width: 150px;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Class</label>
                <select class="form-control" name="class_id" style="width: 140px;">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>>Class <?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Payment Mode</label>
                <select class="form-control" name="mode" style="width: 140px;">
                    <option value="">All Modes</option>
                    <?php foreach (['cash','upi','online','cheque','bank_transfer'] as $m): ?>
                        <option value="<?= $m ?>" <?= $mode === $m ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $m)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-bottom: 0;"><i class="bi bi-funnel"></i> Filter</button>
        </form>
    </div>
</div>

<!-- Summary -->
<div style="display: flex; gap: 16px; margin-bottom: 20px;">
    <div style="padding: 16px 24px; background: linear-gradient(135deg, #1f9e8b, #0d7377); border-radius: 12px; color: white; flex: 1;">
        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Total Collection</div>
        <div style="font-size: 24px; font-weight: 800;">₹<?= number_format($totalAmount, 2) ?></div>
    </div>
    <div style="padding: 16px 24px; background: linear-gradient(135deg, #6366F1, #4338CA); border-radius: 12px; color: white; flex: 1;">
        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Total Receipts</div>
        <div style="font-size: 24px; font-weight: 800;"><?= count($payments) ?></div>
    </div>
</div>

<!-- Payment List -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-list-ul" style="color: #1f9e8b;"></i> Payment Records
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($payments)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Amount</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><code style="font-weight: 600; color: #1f9e8b;"><?= $p['receipt_number'] ?></code></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($p['student_name']) ?></td>
                                <td style="font-size: 12px; color: var(--gray-500);"><?= htmlspecialchars($p['admission_no'] ?? '—') ?></td>
                                <td><?= htmlspecialchars(($p['class_name'] ?? '') . ($p['section_name'] ? '-' . $p['section_name'] : '')) ?></td>
                                <td style="font-weight: 700; color: #1f9e8b;">₹<?= number_format($p['net_amount'], 2) ?></td>
                                <td style="font-size: 12px;">
                                    <?php $modeIcons = ['cash' => '💵', 'cheque' => '📝', 'online' => '🌐', 'upi' => '📱', 'bank_transfer' => '🏦']; ?>
                                    <?= ($modeIcons[$p['payment_mode']] ?? '') . ' ' . ucfirst(str_replace('_', ' ', $p['payment_mode'])) ?>
                                </td>
                                <td>
                                    <?php if (($p['status'] ?? 'active') === 'cancelled'): ?>
                                        <span class="badge" style="background: #FFEBEE; color: #C62828; font-size: 10px;">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #E8F5E9; color: #2E7D32; font-size: 10px;">Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px;"><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/fees/receipt/<?= $p['id'] ?>" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 48px;">
                <i class="bi bi-search" style="font-size: 40px; color: var(--gray-300);"></i>
                <h3>No records found</h3>
                <p>Try adjusting your filters</p>
            </div>
        <?php endif; ?>
    </div>
</div>
