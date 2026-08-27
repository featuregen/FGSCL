<?php require_once VIEW_PATH . '/layouts/header.php'; ?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Leave Management</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Manage staff leave applications, quotas, and approvals</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openApplyModal()">
            <i class="bi bi-plus-circle"></i> Apply Leave
        </button>
        <?php if ($canManage): ?>
        <button class="btn btn-secondary" onclick="openTypeModal()">
            <i class="bi bi-gear"></i> Leave Types
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px;">
    <div class="card" style="padding: 20px; border-left: 4px solid #f59e0b; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 22px;">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Pending Approval</div>
            <div style="font-size: 26px; font-weight: 700; color: #f59e0b;"><?= number_format($stats['pending']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 20px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 22px;">
            <i class="bi bi-check-circle"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Approved (Month)</div>
            <div style="font-size: 26px; font-weight: 700; color: #10b981;"><?= number_format($stats['approved']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 20px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 22px;">
            <i class="bi bi-person-slash"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">On Leave Today</div>
            <div style="font-size: 26px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['on_leave']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 20px; border-left: 4px solid #ef4444; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); display: flex; align-items: center; justify-content: center; color: #ef4444; font-size: 22px;">
            <i class="bi bi-x-circle"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Rejected</div>
            <div style="font-size: 26px; font-weight: 700; color: #ef4444;"><?= number_format($stats['rejected']) ?></div>
        </div>
    </div>
</div>

<!-- Tabs & Filter Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="<?= APP_URL ?>/leave" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label class="form-label" style="margin: 0; font-weight: 600; font-size: 14px;">Status:</label>
            <select name="status" class="form-control" onchange="this.form.submit()" style="padding: 6px 12px; min-width: 140px;">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <label class="form-label" style="margin: 0; font-weight: 600; font-size: 14px;">Leave Type:</label>
            <select name="type_id" class="form-control" onchange="this.form.submit()" style="padding: 6px 12px; min-width: 160px;">
                <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>>All Types</option>
                <?php foreach ($leaveTypes as $lt): ?>
                    <option value="<?= $lt['id'] ?>" <?= $typeFilter == $lt['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lt['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-left: auto;">
            <a href="<?= APP_URL ?>/leave" class="btn btn-secondary" style="padding: 6px 14px; font-size: 13px;">Reset Filter</a>
        </div>
    </form>
</div>

<!-- Requests Table Card -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Leave Applications</h3>
        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
            <?= count($requests) ?> Applications
        </span>
    </div>
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px;">Staff / Applicant</th>
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px;">Leave Type</th>
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px;">Dates & Duration</th>
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px;">Reason</th>
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px;">Applied On</th>
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px;">Status</th>
                    <th style="padding: 14px 18px; font-weight: 600; font-size: 13px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            <i class="bi bi-calendar-x" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            No leave applications found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; color: var(--text-primary, #1e293b);"><?= htmlspecialchars($r['applicant_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">
                                    <?= htmlspecialchars($r['department_name'] ?? $r['role_name'] ?? 'Staff') ?>
                                </div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span class="badge" style="background: rgba(14, 165, 233, 0.12); color: #0ea5e9; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                    <?= htmlspecialchars($r['type_name']) ?>
                                </span>
                                <?php if ($r['is_paid']): ?>
                                    <span style="font-size: 11px; color: #10b981; font-weight: 600; margin-left: 4px;">(Paid)</span>
                                <?php else: ?>
                                    <span style="font-size: 11px; color: #ef4444; font-weight: 600; margin-left: 4px;">(LWP)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; font-size: 13px;">
                                    <?= date('d M Y', strtotime($r['from_date'])) ?> &rarr; <?= date('d M Y', strtotime($r['to_date'])) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">
                                    <?= (float)$r['total_days'] ?> day(s)
                                </div>
                            </td>
                            <td style="padding: 14px 18px; max-width: 250px;">
                                <div style="font-size: 13px; color: var(--text-primary, #334155); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($r['reason']) ?>">
                                    <?= htmlspecialchars($r['reason']) ?>
                                </div>
                                <?php if (!empty($r['action_reason'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted, #64748b); margin-top: 2px;">
                                        <strong>Remark:</strong> <?= htmlspecialchars($r['action_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted, #64748b);">
                                <?= date('d M Y', strtotime($r['created_at'])) ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($r['status'] === 'pending'): ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #b45309; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                <?php elseif ($r['status'] === 'approved'): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #047857; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                        <i class="bi bi-check-circle-fill"></i> Approved
                                    </span>
                                <?php elseif ($r['status'] === 'rejected'): ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #b91c1c; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                        <i class="bi bi-x-circle-fill"></i> Rejected
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <?php if ($r['status'] === 'pending' && $canManage): ?>
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <form method="POST" action="<?= APP_URL ?>/leave/approve" style="display: inline;">
                                            <?= Session::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background: #10b981; color: white; border: none; padding: 4px 10px; font-size: 12px; border-radius: 6px;" title="Approve">
                                                <i class="bi bi-check"></i> Approve
                                            </button>
                                        </form>
                                        <button class="btn btn-sm" onclick="openRejectModal(<?= $r['id'] ?>)" style="background: #ef4444; color: white; border: none; padding: 4px 10px; font-size: 12px; border-radius: 6px;" title="Reject">
                                            <i class="bi bi-x"></i> Reject
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--text-muted, #94a3b8);">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Apply Leave Modal -->
<div id="applyModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); animation: fadeIn 0.2s ease;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Apply for Leave</h3>
            <button class="btn-icon" onclick="closeModal('applyModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/leave/apply">
            <?= Session::csrfField() ?>
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                <?php if ($canManage && !empty($staffList)): ?>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Apply on behalf of Staff Member:</label>
                    <select name="user_id" class="form-control">
                        <option value="<?= $currentUserId ?>">Myself (<?= htmlspecialchars(Session::get('user_data')['full_name'] ?? '') ?>)</option>
                        <?php foreach ($staffList as $st): ?>
                            <?php if ($st['id'] != $currentUserId): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['full_name']) ?> (<?= htmlspecialchars($st['role_name']) ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Leave Type *</label>
                    <select name="leave_type_id" class="form-control" required>
                        <option value="">-- Select Leave Category --</option>
                        <?php foreach ($leaveTypes as $lt): ?>
                            <option value="<?= $lt['id'] ?>">
                                <?= htmlspecialchars($lt['name']) ?> (<?= $lt['days_per_year'] ?> days/yr - <?= $lt['is_paid'] ? 'Paid' : 'Unpaid' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">From Date *</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">To Date *</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Reason for Leave *</label>
                    <textarea name="reason" rows="3" class="form-control" placeholder="Describe the reason for your leave application..." required></textarea>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('applyModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit Application</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Reason Modal -->
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 440px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #ef4444;">Reject Leave Request</h3>
            <button class="btn-icon" onclick="closeModal('rejectModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/leave/reject">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="reject_leave_id">
            <div class="card-body" style="padding: 24px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Reason for Rejection:</label>
                    <textarea name="action_reason" rows="3" class="form-control" placeholder="Specify why this leave request is being rejected..." required></textarea>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
                <button type="submit" class="btn" style="background: #ef4444; color: white;">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<!-- Leave Types Modal -->
<div id="typeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Configure Leave Types</h3>
            <button class="btn-icon" onclick="closeModal('typeModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <div class="card-body" style="padding: 24px; overflow-y: auto;">
            <!-- Form to add new type -->
            <form method="POST" action="<?= APP_URL ?>/leave/save-type" style="background: var(--bg-surface-secondary, #f8fafc); padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border-color, #e2e8f0);">
                <?= Session::csrfField() ?>
                <div style="font-weight: 600; font-size: 14px; margin-bottom: 12px; color: var(--text-primary, #1e293b);">Add / Edit Leave Type</div>
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <input type="text" name="name" class="form-control" placeholder="Leave Type Name (e.g. Study Leave)" required>
                    </div>
                    <div>
                        <input type="text" name="code" class="form-control" placeholder="Code (e.g. STL)">
                    </div>
                    <div>
                        <input type="number" name="days_per_year" class="form-control" placeholder="Days/Yr" value="10" min="1" required>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="is_paid" value="1" checked> Paid Leave
                    </label>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Save Leave Type</button>
                </div>
            </form>

            <!-- Table of existing types -->
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color, #e2e8f0); text-align: left;">
                        <th style="padding: 8px 12px;">Name & Code</th>
                        <th style="padding: 8px 12px;">Allowed Quota</th>
                        <th style="padding: 8px 12px;">Pay Type</th>
                        <th style="padding: 8px 12px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaveTypes as $lt): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 10px 12px; font-weight: 600;"><?= htmlspecialchars($lt['name']) ?> (<?= htmlspecialchars($lt['code'] ?? '') ?>)</td>
                            <td style="padding: 10px 12px;"><?= $lt['days_per_year'] ?> days/year</td>
                            <td style="padding: 10px 12px;">
                                <?= $lt['is_paid'] ? '<span style="color:#10b981; font-weight:600;">Paid</span>' : '<span style="color:#ef4444; font-weight:600;">Unpaid</span>' ?>
                            </td>
                            <td style="padding: 10px 12px; text-align: right;">
                                <form method="POST" action="<?= APP_URL ?>/leave/delete-type" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this leave type?')">
                                    <?= Session::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $lt['id'] ?>">
                                    <button type="submit" class="btn btn-sm" style="background: none; border: none; color: #ef4444; cursor: pointer;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openApplyModal() {
    document.getElementById('applyModal').style.display = 'flex';
}
function openRejectModal(id) {
    document.getElementById('reject_leave_id').value = id;
    document.getElementById('rejectModal').style.display = 'flex';
}
function openTypeModal() {
    document.getElementById('typeModal').style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>