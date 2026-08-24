<!-- Fee Dashboard -->
<style>
    .fee-stat { padding: 24px; border-radius: 16px; position: relative; overflow: hidden; }
    .fee-stat::before { content: ''; position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.1); }
    .fee-stat .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
    .fee-stat .stat-value { font-size: 28px; font-weight: 800; margin-bottom: 2px; }
    .fee-stat .stat-label { font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }
</style>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;">
    <div class="fee-stat" style="background: linear-gradient(135deg, #1f9e8b, #0d7377); color: white;">
        <div class="stat-icon" style="background: rgba(255,255,255,0.15);"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-value">₹<?= number_format($totalCollected, 0) ?></div>
        <div class="stat-label">Total Collected (This Year)</div>
    </div>
    <div class="fee-stat" style="background: linear-gradient(135deg, #6366F1, #4338CA); color: white;">
        <div class="stat-icon" style="background: rgba(255,255,255,0.15);"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-value">₹<?= number_format($todayCollected, 0) ?></div>
        <div class="stat-label">Today's Collection</div>
    </div>
    <div class="fee-stat" style="background: linear-gradient(135deg, #E65100, #F57C00); color: white;">
        <div class="stat-icon" style="background: rgba(255,255,255,0.15);"><i class="bi bi-receipt"></i></div>
        <div class="stat-value"><?= number_format($totalReceipts) ?></div>
        <div class="stat-label">Total Receipts</div>
    </div>
    <div class="fee-stat" style="background: linear-gradient(135deg, #7B1FA2, #9C27B0); color: white;">
        <div class="stat-icon" style="background: rgba(255,255,255,0.15);"><i class="bi bi-tags"></i></div>
        <div class="stat-value"><?= $headsCount ?></div>
        <div class="stat-label">Fee Heads</div>
    </div>
</div>

<!-- Quick Actions -->
<div style="display: flex; gap: 12px; margin-bottom: 24px;">
    <a href="<?= APP_URL ?>/fees/collect" class="btn btn-primary" style="font-weight: 700;">
        <i class="bi bi-cash-coin"></i> Collect Fee
    </a>
    <a href="<?= APP_URL ?>/fees/heads" class="btn btn-secondary">
        <i class="bi bi-tags"></i> Fee Heads
    </a>
    <a href="<?= APP_URL ?>/fees/structures" class="btn btn-secondary">
        <i class="bi bi-grid"></i> Fee Structure
    </a>
    <a href="<?= APP_URL ?>/fees/discounts" class="btn btn-secondary">
        <i class="bi bi-percent"></i> Discounts
    </a>
    <a href="<?= APP_URL ?>/fees/report" class="btn btn-secondary">
        <i class="bi bi-bar-chart"></i> Reports
    </a>
    <?php if (Session::hasPermission('fees.approve')): ?>
        <a href="<?= APP_URL ?>/fees/approvals" class="btn" style="background: <?= ($pendingApprovals ?? 0) > 0 ? '#FFF3E0' : 'var(--gray-100)' ?>; color: <?= ($pendingApprovals ?? 0) > 0 ? '#E65100' : 'var(--gray-600)' ?>; font-weight: 600; position: relative;">
            <i class="bi bi-shield-check"></i> Approvals
            <?php if (($pendingApprovals ?? 0) > 0): ?>
                <span style="position: absolute; top: -6px; right: -6px; background: #C62828; color: white; font-size: 10px; font-weight: 800; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <?= $pendingApprovals ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-clock-history" style="color: #1f9e8b;"></i> Recent Payments
            </h3>
            <a href="<?= APP_URL ?>/fees/report" style="font-size: 12px; color: #1f9e8b; text-decoration: none;">View All →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($recentPayments)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr onclick="window.location='<?= APP_URL ?>/fees/receipt/<?= $p['id'] ?>'" style="cursor: pointer;">
                                    <td><code style="color: #1f9e8b; font-weight: 600;"><?= $p['receipt_number'] ?></code></td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($p['student_name']) ?></td>
                                    <td><?= htmlspecialchars(($p['class_name'] ?? '') . ($p['section_name'] ? ' - ' . $p['section_name'] : '')) ?></td>
                                    <td style="font-weight: 700; color: #1f9e8b;">₹<?= number_format($p['net_amount'], 2) ?></td>
                                    <td style="font-size: 12px; color: var(--gray-500);">
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
                                    <td style="font-size: 12px; color: var(--gray-500);"><?= date('d M', strtotime($p['payment_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 48px;">
                    <i class="bi bi-cash-coin" style="font-size: 40px; color: var(--gray-300);"></i>
                    <h3>No payments yet</h3>
                    <p>Start collecting fees to see payment history</p>
                    <a href="<?= APP_URL ?>/fees/collect" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Collect Fee</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Mode Breakdown -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-pie-chart" style="color: #6366F1;"></i> Payment Modes
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($modeBreakdown)): ?>
                <?php
                    $modeColors = ['cash' => '#1f9e8b', 'cheque' => '#6366F1', 'online' => '#E65100', 'upi' => '#7B1FA2', 'bank_transfer' => '#1565C0'];
                    $totalAll = array_sum(array_column($modeBreakdown, 'total'));
                ?>
                <?php foreach ($modeBreakdown as $mb): ?>
                    <?php $pct = $totalAll > 0 ? round(($mb['total'] / $totalAll) * 100) : 0; ?>
                    <div style="padding: 14px 20px; border-bottom: 1px solid var(--gray-50);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 13px; font-weight: 600; text-transform: capitalize;">
                                <?= str_replace('_', ' ', $mb['payment_mode']) ?>
                            </span>
                            <span style="font-size: 13px; font-weight: 700;">₹<?= number_format($mb['total'], 0) ?></span>
                        </div>
                        <div style="height: 6px; background: var(--gray-100); border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $pct ?>%; background: <?= $modeColors[$mb['payment_mode']] ?? '#999' ?>; border-radius: 3px; transition: width 0.5s;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                            <span style="font-size: 11px; color: var(--gray-400);"><?= $mb['cnt'] ?> receipts</span>
                            <span style="font-size: 11px; color: var(--gray-400);"><?= $pct ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 32px; color: var(--gray-400);">
                    <i class="bi bi-pie-chart" style="font-size: 28px; display: block; margin-bottom: 8px;"></i>
                    <p style="margin: 0; font-size: 13px;">No data yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
