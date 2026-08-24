<!-- Subscription Management (Super Admin) -->

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--primary-50); color: var(--primary);">
            <i class="bi bi-credit-card"></i>
        </div>
        <div>
            <div class="stat-label">Total Subscriptions</div>
            <div class="stat-value"><?= $stats['total'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--success-light); color: var(--success);">
            <i class="bi bi-check-circle"></i>
        </div>
        <div>
            <div class="stat-label">Active</div>
            <div class="stat-value"><?= $stats['active'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--primary-50); color: var(--primary);">
            <i class="bi bi-currency-rupee"></i>
        </div>
        <div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹<?= number_format($stats['total_revenue']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);">
            <i class="bi bi-clock-history"></i>
        </div>
        <div>
            <div class="stat-label">Pending Amount</div>
            <div class="stat-value">₹<?= number_format($stats['pending_amount']) ?></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= APP_URL ?>/subscriptions" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" class="form-control" name="search" placeholder="Search by school name or plan..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div style="width: 150px;">
                <select class="form-control" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="expired" <?= ($status ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div style="width: 150px;">
                <select class="form-control" name="payment_status">
                    <option value="">All Payments</option>
                    <option value="pending" <?= ($paymentStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= ($paymentStatus ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="failed" <?= ($paymentStatus ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= APP_URL ?>/subscriptions" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Clear</a>
        </form>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($subscriptions)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>School</th>
                            <th>Plan</th>
                            <th>Billing</th>
                            <th>Amount</th>
                            <th>Period</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $i => $sub): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 8px; 
                                                     background: <?= htmlspecialchars($sub['primary_color'] ?? 'var(--primary)') ?>; 
                                                     display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                            <?php if (!empty($sub['school_logo'])): ?>
                                                <img src="<?= APP_URL ?>/uploads/logos/<?= htmlspecialchars($sub['school_logo']) ?>" alt="" style="width:100%; height:100%; border-radius:8px; object-fit:cover;">
                                            <?php else: ?>
                                                <?= strtoupper(substr($sub['school_name'], 0, 2)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($sub['school_name']) ?></div>
                                            <div style="font-size: 11px; color: var(--gray-400);"><?= htmlspecialchars($sub['school_code'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($sub['plan_name'] ?? 'N/A') ?></div>
                                    <div style="font-size: 11px; color: var(--gray-400);">
                                        <?= $sub['pricing_type'] === 'per_student' ? 'Per Student' : 'Fixed' ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; text-transform: capitalize;">
                                        <?= str_replace('_', ' ', $sub['billing_cycle']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-size: 14px;">₹<?= number_format($sub['amount'], 2) ?></div>
                                    <?php if ($sub['pricing_type'] === 'per_student' && $sub['student_count'] > 0): ?>
                                        <div style="font-size: 11px; color: var(--gray-400);">
                                            <?= $sub['student_count'] ?> students × ₹<?= number_format($sub['unit_price'], 2) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 12px;"><?= date('d M Y', strtotime($sub['start_date'])) ?></div>
                                    <div style="font-size: 11px; color: var(--gray-400);">to <?= date('d M Y', strtotime($sub['end_date'])) ?></div>
                                    <?php if (strtotime($sub['end_date']) < time() && $sub['status'] === 'active'): ?>
                                        <span class="badge" style="background: var(--danger-light); color: var(--danger); font-size: 10px;">Overdue</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $pColors = [
                                            'paid' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)'],
                                            'pending' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning-dark)'],
                                            'failed' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)'],
                                            'refunded' => ['bg' => 'var(--info-light)', 'color' => 'var(--info)'],
                                        ];
                                        $pc = $pColors[$sub['payment_status']] ?? $pColors['pending'];
                                    ?>
                                    <span class="badge" style="background: <?= $pc['bg'] ?>; color: <?= $pc['color'] ?>;">
                                        <?= ucfirst($sub['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $sColors = [
                                            'active' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)'],
                                            'expired' => ['bg' => 'var(--gray-100)', 'color' => 'var(--gray-500)'],
                                            'cancelled' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)'],
                                            'suspended' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning-dark)'],
                                        ];
                                        $sc = $sColors[$sub['status']] ?? $sColors['active'];
                                    ?>
                                    <span class="badge" style="background: <?= $sc['bg'] ?>; color: <?= $sc['color'] ?>;">
                                        <?= ucfirst($sub['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <!-- Mark Paid -->
                                        <?php if ($sub['payment_status'] !== 'paid'): ?>
                                            <form method="POST" action="<?= APP_URL ?>/subscriptions/record-payment" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                                <input type="hidden" name="payment_status" value="paid">
                                                <button type="submit" class="btn btn-sm" style="background: var(--success-light); color: var(--success); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Mark as Paid">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Suspend/Activate -->
                                        <?php if ($sub['status'] === 'active'): ?>
                                            <form method="POST" action="<?= APP_URL ?>/subscriptions/update-status" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                                <input type="hidden" name="status" value="suspended">
                                                <button type="submit" class="btn btn-sm" style="background: var(--warning-light); color: var(--warning-dark); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Suspend">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($sub['status'] === 'suspended'): ?>
                                            <form method="POST" action="<?= APP_URL ?>/subscriptions/update-status" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm" style="background: var(--success-light); color: var(--success); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Reactivate">
                                                    <i class="bi bi-play-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Cancel -->
                                        <?php if ($sub['status'] !== 'cancelled'): ?>
                                            <form method="POST" action="<?= APP_URL ?>/subscriptions/update-status" style="display:inline;" onsubmit="return confirm('Cancel this subscription?')">
                                                <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;" title="Cancel">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-credit-card" style="font-size: 48px; color: var(--gray-300); margin-bottom: 12px;"></i>
                <h3>No subscriptions found</h3>
                <p>Subscriptions are created when you add a school with a plan</p>
                <a href="<?= APP_URL ?>/schools/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add School
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
