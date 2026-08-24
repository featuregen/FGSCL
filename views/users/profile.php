<!-- User Profile View -->
<?php
    $u = $profileUser;
    $typeLabels = [
        'super_admin' => 'Super Admin',
        'school_admin' => 'School Admin',
        'teacher' => 'Teacher',
        'student' => 'Student',
        'staff' => 'Staff',
        'accountant' => 'Accountant',
        'librarian' => 'Librarian',
        'transport_manager' => 'Transport Manager',
        'parent' => 'Parent',
    ];
    $typeColors = [
        'super_admin' => ['#F3E5F5','#7B1FA2'],
        'school_admin' => ['#E3F2FD','#1565C0'],
        'teacher' => ['#E0F2F1','#1f9e8b'],
        'student' => ['#FFF3E0','#E65100'],
        'staff' => ['#F3E5F5','#7B1FA2'],
        'accountant' => ['#FFF3E0','#E65100'],
        'librarian' => ['#E8F5E9','#2E7D32'],
        'transport_manager' => ['#E3F2FD','#1565C0'],
        'parent' => ['#FCE4EC','#C62828'],
    ];
    $tc = $typeColors[$u['user_type']] ?? ['#F5F5F5','#666'];
    $initials = strtoupper(substr($u['full_name'], 0, 1) . (strpos($u['full_name'], ' ') ? substr($u['full_name'], strpos($u['full_name'], ' ') + 1, 1) : ''));
?>

<!-- Profile Header -->
<div class="card mb-4">
    <div class="card-body" style="padding: 24px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <!-- Avatar -->
            <div style="width: 72px; height: 72px; border-radius: 16px;
                        background: linear-gradient(135deg, <?= $tc[1] ?>, <?= $tc[1] ?>CC);
                        display: flex; align-items: center; justify-content: center;
                        color: white; font-size: 26px; font-weight: 800; flex-shrink: 0;">
                <?= $initials ?>
            </div>
            <!-- Info -->
            <div style="flex: 1;">
                <h2 style="margin: 0; font-size: 22px; font-weight: 800;"><?= htmlspecialchars($u['full_name']) ?></h2>
                <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px; flex-wrap: wrap;">
                    <span class="badge" style="background: <?= $tc[0] ?>; color: <?= $tc[1] ?>; font-size: 12px;">
                        <?= $typeLabels[$u['user_type']] ?? ucfirst($u['user_type']) ?>
                    </span>
                    <?php if (!empty($u['role_name'])): ?>
                        <span class="badge" style="background: var(--gray-50); color: var(--gray-600); font-size: 11px;">
                            <i class="bi bi-shield-check"></i> <?= htmlspecialchars($u['role_name']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($u['is_active']): ?>
                        <span class="badge" style="background: #E8F5E9; color: #2E7D32;">● Active</span>
                    <?php else: ?>
                        <span class="badge" style="background: #FFEBEE; color: #C62828;">● Inactive</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($u['school_name'])): ?>
                    <div style="font-size: 13px; color: var(--gray-400); margin-top: 4px;">
                        <i class="bi bi-building"></i> <?= htmlspecialchars($u['school_name']) ?>
                        <?php if (!empty($u['school_code'])): ?>
                            <code style="font-size: 10px; margin-left: 4px;"><?= $u['school_code'] ?></code>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
                <a href="<?= APP_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-secondary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Details Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- User Details -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person" style="color: #1f9e8b;"></i> User Details
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table style="width: 100%;">
                <tbody>
                    <tr style="border-bottom: 1px solid var(--gray-100);">
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--gray-500); width: 40%; font-size: 13px;">Username</td>
                        <td style="padding: 12px 16px; font-size: 13px;"><code><?= htmlspecialchars($u['username']) ?></code></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-100);">
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--gray-500); font-size: 13px;">Email</td>
                        <td style="padding: 12px 16px; font-size: 13px;"><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-100);">
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--gray-500); font-size: 13px;">Phone</td>
                        <td style="padding: 12px 16px; font-size: 13px;"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-100);">
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--gray-500); font-size: 13px;">Gender</td>
                        <td style="padding: 12px 16px; font-size: 13px;"><?= ucfirst($u['gender'] ?? '—') ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-100);">
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--gray-500); font-size: 13px;">Date of Birth</td>
                        <td style="padding: 12px 16px; font-size: 13px;"><?= !empty($u['date_of_birth']) ? date('d M Y', strtotime($u['date_of_birth'])) : '—' ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--gray-500); font-size: 13px;">Created</td>
                        <td style="padding: 12px 16px; font-size: 13px;"><?= !empty($u['created_at']) ? date('d M Y, h:i A', strtotime($u['created_at'])) : '—' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-clock-history" style="color: #7B1FA2;"></i> Recent Activity
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($activityLogs)): ?>
                <div style="max-height: 350px; overflow-y: auto;">
                    <?php foreach ($activityLogs as $log): ?>
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--gray-100); display: flex; gap: 10px; align-items: flex-start;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #1f9e8b; flex-shrink: 0; margin-top: 6px;"></div>
                            <div style="flex: 1;">
                                <div style="font-size: 13px;"><?= htmlspecialchars($log['action'] ?? $log['description'] ?? 'Activity') ?></div>
                                <div style="font-size: 11px; color: var(--gray-400); margin-top: 2px;">
                                    <?= date('d M Y, h:i A', strtotime($log['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 32px; color: var(--gray-400);">
                    <i class="bi bi-clock" style="font-size: 28px; display: block; margin-bottom: 8px;"></i>
                    <p style="margin: 0; font-size: 13px;">No activity recorded yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fee Section (Students only) -->
<?php if (($u['user_type'] ?? '') === 'student' && !empty($feeStructure)): ?>

<!-- Fee Summary Cards -->
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 20px; margin-bottom: 20px;">
    <div style="padding: 16px 20px; background: linear-gradient(135deg, #1f9e8b, #0d7377); border-radius: 12px; color: white;">
        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Total Fee</div>
        <div style="font-size: 22px; font-weight: 800;">₹<?= number_format(($feeTotalPaid ?? 0) + ($feeTotalDue ?? 0), 2) ?></div>
    </div>
    <div style="padding: 16px 20px; background: linear-gradient(135deg, #2E7D32, #43A047); border-radius: 12px; color: white;">
        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Total Paid</div>
        <div style="font-size: 22px; font-weight: 800;">₹<?= number_format($feeTotalPaid ?? 0, 2) ?></div>
    </div>
    <div style="padding: 16px 20px; background: linear-gradient(135deg, <?= ($feeTotalDue ?? 0) > 0 ? '#C62828, #E53935' : '#2E7D32, #43A047' ?>); border-radius: 12px; color: white;">
        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Balance Due</div>
        <div style="font-size: 22px; font-weight: 800;">₹<?= number_format($feeTotalDue ?? 0, 2) ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Fee Structure -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-wallet2" style="color: #E65100;"></i> Fee Structure
            </h3>
            <a href="<?= APP_URL ?>/fees/collect/<?= $u['id'] ?>" class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; font-weight: 600;">
                <i class="bi bi-cash-coin"></i> Collect
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <table style="width: 100%;">
                <thead>
                    <tr style="background: var(--gray-50);">
                        <th style="padding: 10px 16px; font-size: 11px; text-transform: uppercase; color: var(--gray-500); font-weight: 700;">Fee Head</th>
                        <th style="padding: 10px 16px; font-size: 11px; text-transform: uppercase; color: var(--gray-500); font-weight: 700; text-align: right;">Amount</th>
                        <th style="padding: 10px 16px; font-size: 11px; text-transform: uppercase; color: var(--gray-500); font-weight: 700; text-align: right;">Paid</th>
                        <th style="padding: 10px 16px; font-size: 11px; text-transform: uppercase; color: var(--gray-500); font-weight: 700; text-align: right;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feeStructure as $fs): ?>
                        <tr style="border-bottom: 1px solid var(--gray-100);">
                            <td style="padding: 10px 16px; font-size: 13px; font-weight: 600;">
                                <?= htmlspecialchars($fs['head_name']) ?>
                                <?php if ($fs['head_type'] === 'optional'): ?>
                                    <span style="font-size: 9px; color: #1565C0; background: #E3F2FD; padding: 1px 5px; border-radius: 3px; margin-left: 4px;">Optional</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 16px; font-size: 13px; text-align: right;">₹<?= number_format($fs['amount'], 2) ?></td>
                            <td style="padding: 10px 16px; font-size: 13px; text-align: right; color: #2E7D32; font-weight: 600;">₹<?= number_format($fs['paid'], 2) ?></td>
                            <td style="padding: 10px 16px; font-size: 13px; text-align: right; font-weight: 700; color: <?= $fs['balance'] > 0 ? '#C62828' : '#2E7D32' ?>;">
                                <?= $fs['balance'] > 0 ? '₹' . number_format($fs['balance'], 2) : '✓ Paid' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #F0FDF9; border-top: 2px solid #1f9e8b;">
                        <td style="padding: 10px 16px; font-size: 13px; font-weight: 800;">Total</td>
                        <td style="padding: 10px 16px; font-size: 13px; text-align: right; font-weight: 700;">₹<?= number_format(($feeTotalPaid ?? 0) + ($feeTotalDue ?? 0), 2) ?></td>
                        <td style="padding: 10px 16px; font-size: 13px; text-align: right; font-weight: 700; color: #2E7D32;">₹<?= number_format($feeTotalPaid ?? 0, 2) ?></td>
                        <td style="padding: 10px 16px; font-size: 13px; text-align: right; font-weight: 800; color: <?= ($feeTotalDue ?? 0) > 0 ? '#C62828' : '#2E7D32' ?>;">
                            <?= ($feeTotalDue ?? 0) > 0 ? '₹' . number_format($feeTotalDue, 2) : '✓ All Paid' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-receipt" style="color: #6366F1;"></i> Payment History
            </h3>
            <a href="<?= APP_URL ?>/fees/student-ledger/<?= $u['id'] ?>" style="font-size: 12px; color: #1f9e8b; text-decoration: none;">View All →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($feePayments)): ?>
                <div style="max-height: 350px; overflow-y: auto;">
                    <?php foreach ($feePayments as $fp): ?>
                        <a href="<?= APP_URL ?>/fees/receipt/<?= $fp['id'] ?>" style="text-decoration: none; color: inherit; display: block; padding: 12px 16px; border-bottom: 1px solid var(--gray-100);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <code style="color: #1f9e8b; font-weight: 600; font-size: 12px;"><?= $fp['receipt_number'] ?></code>
                                    <div style="font-size: 11px; color: var(--gray-400); margin-top: 2px;">
                                        <?= date('d M Y', strtotime($fp['payment_date'])) ?> • <?= ucfirst(str_replace('_', ' ', $fp['payment_mode'])) ?>
                                    </div>
                                    <div style="font-size: 10px; color: var(--gray-400); margin-top: 2px;">
                                        <?php 
                                            $heads = explode(', ', $fp['head_names'] ?? '');
                                            echo htmlspecialchars(implode(', ', array_slice($heads, 0, 2)));
                                            if (count($heads) > 2) echo ' +' . (count($heads) - 2);
                                        ?>
                                    </div>
                                </div>
                                <div style="font-weight: 700; color: #1f9e8b; font-size: 14px;">
                                    ₹<?= number_format($fp['net_amount'], 2) ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 32px; color: var(--gray-400);">
                    <i class="bi bi-receipt" style="font-size: 28px; display: block; margin-bottom: 8px;"></i>
                    <p style="margin: 0; font-size: 13px;">No payments recorded</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Discounts & Concessions -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-percent" style="color: #7B1FA2;"></i> Discounts & Concessions
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <!-- Applied Discounts -->
        <?php if (!empty($appliedDiscounts)): ?>
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--gray-100);">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--gray-500); font-weight: 700; margin-bottom: 10px;">Applied Discounts</div>
                <?php foreach ($appliedDiscounts as $ad): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #F3E5F5; border-radius: 8px; margin-bottom: 6px;">
                        <div>
                            <span style="font-weight: 700; color: #7B1FA2;"><?= htmlspecialchars($ad['discount_name']) ?></span>
                            <span style="margin-left: 8px; font-size: 13px; color: var(--gray-600);">
                                <?= $ad['discount_type'] === 'percentage' ? $ad['discount_value'] . '%' : '₹' . number_format($ad['discount_value'], 2) ?>
                            </span>
                        </div>
                        <form method="POST" action="<?= APP_URL ?>/fees/remove-discount/<?= $ad['id'] ?>" 
                              style="display:inline;" onsubmit="return confirm('Remove this discount?')">
                            <button type="submit" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                                <i class="bi bi-x"></i> Remove
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Apply New Discount -->
        <?php if (!empty($availableDiscounts)): ?>
            <div style="padding: 16px 20px;">
                <form method="POST" action="<?= APP_URL ?>/fees/assign-discount" style="display: flex; gap: 10px; align-items: flex-end;">
                    <?= Session::csrfField() ?>
                    <input type="hidden" name="student_id" value="<?= $u['id'] ?>">
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: 600; color: var(--gray-500); display: block; margin-bottom: 4px;">Apply Discount</label>
                        <select name="discount_id" class="form-control" required style="font-size: 13px;">
                            <option value="">— Select discount —</option>
                            <?php foreach ($availableDiscounts as $disc): ?>
                                <option value="<?= $disc['id'] ?>">
                                    <?= htmlspecialchars($disc['name']) ?> (<?= $disc['type'] === 'percentage' ? $disc['value'] . '%' : '₹' . number_format($disc['value'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn" style="background: #7B1FA2; color: white; font-weight: 600; white-space: nowrap;">
                        <i class="bi bi-plus-lg"></i> Apply
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div style="padding: 20px; text-align: center; color: var(--gray-400); font-size: 13px;">
                No discounts configured. <a href="<?= APP_URL ?>/fees/discounts" style="color: #7B1FA2;">Add discounts →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Optional Fees -->
<?php if (!empty($optionalFeeHeads)): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-bus-front" style="color: #E65100;"></i> Optional Fees
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <!-- Enrolled Optional Fees -->
        <?php if (!empty($enrolledOptionalFees)): ?>
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--gray-100);">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--gray-500); font-weight: 700; margin-bottom: 10px;">Enrolled</div>
                <?php foreach ($enrolledOptionalFees as $eof): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #FFF3E0; border-radius: 8px; margin-bottom: 6px;">
                        <div>
                            <span style="font-weight: 700; color: #E65100;">
                                <i class="bi bi-check-circle-fill" style="color: #43A047; margin-right: 4px;"></i>
                                <?= htmlspecialchars($eof['head_name']) ?>
                            </span>
                            <?php if ($eof['head_code']): ?>
                                <code style="margin-left: 6px; font-size: 10px;"><?= $eof['head_code'] ?></code>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="<?= APP_URL ?>/fees/remove-optional-fee/<?= $eof['id'] ?>" 
                              style="display:inline;" onsubmit="return confirm('Remove this optional fee?')">
                            <?= Session::csrfField() ?>
                            <button type="submit" class="btn btn-sm" style="background: #FFEBEE; color: #C62828; border: none; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                                <i class="bi bi-x"></i> Remove
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Assign Optional Fee -->
        <?php 
            $enrolledIds = array_column($enrolledOptionalFees ?? [], 'fee_head_id');
            $unassigned = array_filter($optionalFeeHeads, fn($h) => !in_array($h['id'], $enrolledIds));
        ?>
        <?php if (!empty($unassigned)): ?>
            <div style="padding: 16px 20px;">
                <form method="POST" action="<?= APP_URL ?>/fees/assign-optional-fee" style="display: flex; gap: 10px; align-items: flex-end;">
                    <?= Session::csrfField() ?>
                    <input type="hidden" name="student_id" value="<?= $u['id'] ?>">
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: 600; color: var(--gray-500); display: block; margin-bottom: 4px;">Assign Optional Fee</label>
                        <select name="fee_head_id" class="form-control" required style="font-size: 13px;">
                            <option value="">— Select fee —</option>
                            <?php foreach ($unassigned as $ofh): ?>
                                <option value="<?= $ofh['id'] ?>"><?= htmlspecialchars($ofh['name']) ?> <?= $ofh['code'] ? '(' . $ofh['code'] . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn" style="background: #E65100; color: white; font-weight: 600; white-space: nowrap;">
                        <i class="bi bi-plus-lg"></i> Assign
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
