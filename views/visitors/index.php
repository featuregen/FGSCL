<?php require_once VIEW_PATH . '/layouts/header.php'; ?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Visitor Management</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Campus front-desk visitor registration, security gate pass issuance, and visit history</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openCheckinModal()">
            <i class="bi bi-person-plus-fill"></i> Check-In New Visitor
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Inside Campus Now</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['currently_inside']) ?> Visitors</div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-person-walking"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Today's Total Visits</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['today_visits']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-calendar-month"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">This Month</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['month_visits']) ?> Visits</div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/visitors?tab=inside" class="btn" style="background: <?= $tab === 'inside' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'inside' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-person-check-fill"></i> Currently Inside (<?= count($insideVisitors) ?>)
    </a>
    <a href="<?= APP_URL ?>/visitors?tab=history" class="btn" style="background: <?= $tab === 'history' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'history' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-clock-history"></i> Visit History Log (<?= count($history) ?>)
    </a>
</div>

<?php if ($tab === 'inside'): ?>
<!-- Current Visitors Inside Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Visitor Info</th>
                    <th style="padding: 14px 18px;">Contact / ID Proof</th>
                    <th style="padding: 14px 18px;">Purpose of Visit</th>
                    <th style="padding: 14px 18px;">Meeting Person / Dept</th>
                    <th style="padding: 14px 18px;">Check-In Time</th>
                    <th style="padding: 14px 18px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($insideVisitors)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            <i class="bi bi-person-check" style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
                            No visitors currently inside the campus. Click "Check-In New Visitor" when a guest arrives.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($insideVisitors as $v): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; font-size: 14px; color: var(--text-primary, #1e293b);">
                                    <?= htmlspecialchars($v['visitor_name']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">
                                    Pass: <strong><?= htmlspecialchars($v['visitor_card_no']) ?></strong> &bull; <?= $v['number_of_persons'] ?> person(s)
                                </div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($v['phone']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($v['id_proof_type']) ?>: <?= htmlspecialchars($v['id_proof_number'] ?: 'N/A') ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; font-weight: 600; color: #0f766e;">
                                <?= htmlspecialchars($v['purpose']) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($v['to_meet_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($v['department'] ?: 'Staff') ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                    <i class="bi bi-box-arrow-in-right"></i> <?= date('h:i A', strtotime($v['in_time'])) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="<?= APP_URL ?>/visitors/pass?id=<?= $v['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="Print Visitor Pass">
                                        <i class="bi bi-printer"></i> Pass
                                    </a>
                                    <form method="POST" action="<?= APP_URL ?>/visitors/checkout" style="display: inline;">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary" style="background: #ef4444; border: none;" title="Check-Out Visitor">
                                            <i class="bi bi-box-arrow-right"></i> Check Out
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'history'): ?>
<!-- Filter bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= APP_URL ?>/visitors" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="tab" value="history">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="Search visitor name, phone, purpose..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="min-width: 180px;">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
        <?php if (!empty($search) || !empty($dateFilter)): ?>
            <a href="<?= APP_URL ?>/visitors?tab=history" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- History Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Visitor</th>
                    <th style="padding: 14px 18px;">Contact / ID</th>
                    <th style="padding: 14px 18px;">Met With</th>
                    <th style="padding: 14px 18px;">Purpose</th>
                    <th style="padding: 14px 18px;">In / Out Timings</th>
                    <th style="padding: 14px 18px;">Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Pass</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No visitor records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $h): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--text-primary, #1e293b);"><?= htmlspecialchars($h['visitor_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Pass: <?= htmlspecialchars($h['visitor_card_no']) ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <div><?= htmlspecialchars($h['phone']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($h['id_proof_type']) ?>: <?= htmlspecialchars($h['id_proof_number'] ?: 'N/A') ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($h['to_meet_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($h['department'] ?: '') ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted, #64748b);">
                                <?= htmlspecialchars($h['purpose']) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <div>In: <?= date('d M, h:i A', strtotime($h['in_time'])) ?></div>
                                <div style="color: var(--text-muted, #64748b);">
                                    Out: <?= $h['out_time'] ? date('d M, h:i A', strtotime($h['out_time'])) : '<span style="color:#10b981; font-weight:600;">Still Inside</span>' ?>
                                </div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($h['status'] === 'inside'): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Inside</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(100, 116, 139, 0.15); color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Completed</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <a href="<?= APP_URL ?>/visitors/pass?id=<?= $h['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="View Pass">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Check-In Modal -->
<div id="checkinModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 580px; max-height: 90vh; display: flex; flex-direction: column; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Check-In New Visitor</h3>
            <button class="btn-icon" onclick="closeModal('checkinModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/visitors/checkin">
            <?= Session::csrfField() ?>
            <div class="card-body" style="padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Visitor Full Name *</label>
                        <input type="text" name="visitor_name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">No. of Persons</label>
                        <input type="number" name="number_of_persons" class="form-control" value="1" min="1" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Email (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="Email address">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">ID Proof Type</label>
                        <select name="id_proof_type" class="form-control">
                            <option value="National ID / Aadhaar">National ID / Aadhaar</option>
                            <option value="Driving License">Driving License</option>
                            <option value="Passport">Passport</option>
                            <option value="Voter ID">Voter ID</option>
                            <option value="Other ID">Other ID</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">ID Proof Number</label>
                        <input type="text" name="id_proof_number" class="form-control" placeholder="ID number / Card number">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Purpose of Visit *</label>
                    <input type="text" name="purpose" class="form-control" placeholder="e.g. Parent-Teacher Meeting, Fee Payment, Official Inquiry" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Person to Meet (Staff Member)</label>
                        <select name="to_meet_user_id" class="form-control" onchange="document.getElementById('custom_meet_name').value = this.options[this.selectedIndex].text">
                            <option value="">-- Choose Staff or type below --</option>
                            <?php foreach ($staffList as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['full_name']) ?> (<?= htmlspecialchars($st['department_name'] ?? $st['role_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Or Name / Department *</label>
                        <input type="text" name="to_meet_name" id="custom_meet_name" class="form-control" placeholder="e.g. Principal, Admin Office" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Visitor Badge / Pass No</label>
                        <input type="text" name="visitor_card_no" class="form-control" value="PASS-<?= rand(100, 999) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Security notes (optional)">
                    </div>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('checkinModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check"></i> Check In Visitor</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCheckinModal() {
    document.getElementById('checkinModal').style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>