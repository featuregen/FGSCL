<?php require_once VIEW_PATH . '/layouts/header.php'; ?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Hostel Management</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Manage boarding facilities, hostel buildings, rooms, bed capacity, and resident allotments</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openHostelModal()">
            <i class="bi bi-plus-circle"></i> Add Hostel
        </button>
        <button class="btn btn-secondary" onclick="openRoomModal()">
            <i class="bi bi-door-open"></i> Add Room
        </button>
        <button class="btn btn-secondary" onclick="openAllocModal()">
            <i class="bi bi-person-plus"></i> Allot Bed
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-buildings"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Hostel Buildings</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['total_hostels']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-door-closed"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Rooms</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['total_rooms']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #f59e0b; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 20px;">
            <i class="bi bi-person-workspace"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Occupied Beds</div>
            <div style="font-size: 24px; font-weight: 700; color: #f59e0b;"><?= number_format($stats['occupied_beds']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Available Beds</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['available_beds']) ?> / <?= number_format($stats['total_beds']) ?></div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/hostel?tab=hostels" class="btn" style="background: <?= $tab === 'hostels' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'hostels' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-buildings"></i> Hostels & Buildings (<?= count($hostels) ?>)
    </a>
    <a href="<?= APP_URL ?>/hostel?tab=rooms" class="btn" style="background: <?= $tab === 'rooms' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'rooms' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-door-open"></i> Rooms & Capacity (<?= count($rooms) ?>)
    </a>
    <a href="<?= APP_URL ?>/hostel?tab=allocations" class="btn" style="background: <?= $tab === 'allocations' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'allocations' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-people"></i> Resident Allotments (<?= count($allocations) ?>)
    </a>
</div>

<?php if ($tab === 'hostels'): ?>
<!-- Hostels Cards View -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <?php if (empty($hostels)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
            No hostels found. Click "Add Hostel" to configure boarding buildings.
        </div>
    <?php else: ?>
        <?php foreach ($hostels as $h): ?>
            <div class="card" style="padding: 22px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">
                                <?= htmlspecialchars($h['name']) ?>
                            </h3>
                            <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5; margin-top: 4px; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; font-size: 11px; font-weight: 700;">
                                <?= htmlspecialchars(str_replace('_', ' ', $h['type'])) ?>
                            </span>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <button class="btn btn-sm btn-secondary" onclick='editHostel(<?= json_encode($h) ?>)'><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="<?= APP_URL ?>/hostel/delete-hostel" style="display: inline;" onsubmit="return confirm('Delete this hostel?')">
                                <?= Session::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white; border: none; border-radius: 6px;"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <div style="font-size: 13px; color: var(--text-muted, #64748b); margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px;">
                        <div><i class="bi bi-person-badge"></i> <strong>Warden:</strong> <?= htmlspecialchars($h['warden_name'] ?: 'Not Assigned') ?> (<?= htmlspecialchars($h['warden_phone'] ?: 'N/A') ?>)</div>
                        <div><i class="bi bi-geo-alt"></i> <strong>Location:</strong> <?= htmlspecialchars($h['address'] ?: 'Campus premises') ?></div>
                    </div>
                </div>

                <!-- Bed Occupancy Bar -->
                <div style="background: var(--bg-surface-secondary, #f8fafc); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; margin-bottom: 6px;">
                        <span>Bed Occupancy:</span>
                        <span><?= $h['occupied_beds'] ?> / <?= $h['total_beds'] ?> Beds</span>
                    </div>
                    <?php 
                    $pct = $h['total_beds'] > 0 ? min(100, round(($h['occupied_beds'] / $h['total_beds']) * 100)) : 0; 
                    ?>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?= $pct ?>%; height: 100%; background: <?= $pct > 90 ? '#ef4444' : ($pct > 70 ? '#f59e0b' : '#10b981') ?>; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted, #64748b); margin-top: 4px; text-align: right;">
                        <?= $h['room_count'] ?> Rooms &bull; <?= $pct ?>% Full
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'rooms'): ?>
<!-- Rooms Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Room No / Hostel</th>
                    <th style="padding: 14px 18px;">Room Type</th>
                    <th style="padding: 14px 18px;">Bed Capacity</th>
                    <th style="padding: 14px 18px;">Cost Per Bed</th>
                    <th style="padding: 14px 18px;">Occupancy Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No rooms added yet. Click "Add Room" to create hostel rooms.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $rm): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; font-size: 14px; color: var(--text-primary, #1e293b);">
                                    Room <?= htmlspecialchars($rm['room_no']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($rm['hostel_name']) ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span class="badge" style="background: rgba(14, 165, 233, 0.12); color: #0284c7; padding: 4px 8px; border-radius: 6px; font-weight: 600; text-transform: uppercase;">
                                    <?= htmlspecialchars(str_replace('_', ' ', $rm['room_type'])) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 18px; font-weight: 600;">
                                <span style="color: <?= $rm['occupied_count'] >= $rm['number_of_beds'] ? '#ef4444' : '#10b981' ?>;">
                                    <?= $rm['occupied_count'] ?> / <?= $rm['number_of_beds'] ?> Beds
                                </span>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; font-weight: 600;">
                                <?= number_format($rm['cost_per_bed'], 2) ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($rm['occupied_count'] >= $rm['number_of_beds']): ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Full</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                        <?= $rm['number_of_beds'] - $rm['occupied_count'] ?> Beds Available
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="btn btn-sm btn-secondary" onclick='editRoom(<?= json_encode($rm) ?>)'><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?= APP_URL ?>/hostel/delete-room" style="display: inline;" onsubmit="return confirm('Delete this room?')">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $rm['id'] ?>">
                                        <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white; border: none; border-radius: 6px;"><i class="bi bi-trash"></i></button>
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

<?php elseif ($tab === 'allocations'): ?>
<!-- Resident Allotments Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Student Name</th>
                    <th style="padding: 14px 18px;">Class & Section</th>
                    <th style="padding: 14px 18px;">Hostel & Room</th>
                    <th style="padding: 14px 18px;">Bed Number</th>
                    <th style="padding: 14px 18px;">Check-In Date</th>
                    <th style="padding: 14px 18px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allocations)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No student residents allotted to hostel rooms. Click "Allot Bed" to assign.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allocations as $a): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px; font-weight: 600; color: var(--text-primary, #1e293b);">
                                <?= htmlspecialchars($a['student_name']) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                Class <?= htmlspecialchars($a['class_name'] ?? 'N/A') ?> - <?= htmlspecialchars($a['section_name'] ?? '') ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; color: #0f766e;"><?= htmlspecialchars($a['hostel_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Room: <?= htmlspecialchars($a['room_no']) ?> (<?= htmlspecialchars($a['room_type']) ?>)</div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; font-weight: 600;">
                                <?= htmlspecialchars($a['bed_number'] ?: 'Bed 1') ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= date('d M Y', strtotime($a['checkin_date'])) ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <form method="POST" action="<?= APP_URL ?>/hostel/vacate" style="display: inline;" onsubmit="return confirm('Vacate this student from the hostel room?')">
                                    <?= Session::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background: #ef4444; color: white; border: none; border-radius: 6px;">
                                        <i class="bi bi-box-arrow-right"></i> Vacate Bed
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Hostel Modal -->
<div id="hostelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="hostelModalTitle">Add Hostel</h3>
            <button class="btn-icon" onclick="closeModal('hostelModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/hostel/save-hostel">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="hostel_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Hostel Name *</label>
                    <input type="text" name="name" id="h_name" class="form-control" placeholder="e.g. Tagore Boys Hostel" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Hostel Type *</label>
                        <select name="type" id="h_type" class="form-control">
                            <option value="boys">Boys Hostel</option>
                            <option value="girls">Girls Hostel</option>
                            <option value="co_ed">Co-Educational</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Intake Capacity</label>
                        <input type="number" name="intake_capacity" id="h_capacity" class="form-control" value="100" min="1">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Warden Name</label>
                        <input type="text" name="warden_name" id="h_warden" class="form-control" placeholder="Warden full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Warden Phone</label>
                        <input type="text" name="warden_phone" id="h_phone" class="form-control" placeholder="Phone number">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Address / Location</label>
                    <input type="text" name="address" id="h_address" class="form-control" placeholder="Building address">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('hostelModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Hostel</button>
            </div>
        </form>
    </div>
</div>

<!-- Room Modal -->
<div id="roomModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="roomModalTitle">Add Room</h3>
            <button class="btn-icon" onclick="closeModal('roomModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/hostel/save-room">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="rm_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Hostel *</label>
                    <select name="hostel_id" id="rm_hostel_id" class="form-control" required>
                        <option value="">-- Choose Hostel --</option>
                        <?php foreach ($hostels as $h): ?>
                            <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Room Number *</label>
                        <input type="text" name="room_no" id="rm_no" class="form-control" placeholder="e.g. 101, 102" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Room Type</label>
                        <select name="room_type" id="rm_type" class="form-control">
                            <option value="non_ac">Non-AC</option>
                            <option value="ac">AC Room</option>
                            <option value="single">Single Occupancy</option>
                            <option value="double">Double Sharing</option>
                            <option value="dormitory">Dormitory</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Number of Beds *</label>
                        <input type="number" name="number_of_beds" id="rm_beds" class="form-control" value="4" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Monthly Cost / Bed</label>
                        <input type="number" step="0.01" name="cost_per_bed" id="rm_cost" class="form-control" placeholder="0.00">
                    </div>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('roomModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Room</button>
            </div>
        </form>
    </div>
</div>

<!-- Allot Bed Modal -->
<div id="allocModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Allot Hostel Bed</h3>
            <button class="btn-icon" onclick="closeModal('allocModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/hostel/allocate">
            <?= Session::csrfField() ?>
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Student *</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($unallocatedStudents as $st): ?>
                            <option value="<?= $st['id'] ?>">
                                <?= htmlspecialchars($st['full_name']) ?> (Class <?= htmlspecialchars($st['class_name'] ?? '') ?> - <?= htmlspecialchars($st['section_name'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Hostel *</label>
                    <select name="hostel_id" id="alloc_hostel_select" class="form-control" required onchange="loadHostelRooms(this.value)">
                        <option value="">-- Choose Hostel --</option>
                        <?php foreach ($hostels as $h): ?>
                            <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?> (<?= htmlspecialchars($h['type']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Room *</label>
                        <select name="room_id" id="alloc_room_select" class="form-control" required>
                            <option value="">-- Select Room --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Bed Number</label>
                        <input type="text" name="bed_number" class="form-control" placeholder="e.g. Bed-A" value="Bed-1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Check-In Date *</label>
                    <input type="date" name="checkin_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('allocModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Allotment</button>
            </div>
        </form>
    </div>
</div>

<script>
const allRooms = <?= json_encode($rooms) ?>;

function openHostelModal() {
    document.getElementById('hostelModalTitle').innerText = 'Add Hostel';
    document.getElementById('hostel_id').value = '';
    document.getElementById('h_name').value = '';
    document.getElementById('h_capacity').value = '100';
    document.getElementById('h_warden').value = '';
    document.getElementById('h_phone').value = '';
    document.getElementById('h_address').value = '';
    document.getElementById('hostelModal').style.display = 'flex';
}

function editHostel(h) {
    document.getElementById('hostelModalTitle').innerText = 'Edit Hostel';
    document.getElementById('hostel_id').value = h.id;
    document.getElementById('h_name').value = h.name;
    document.getElementById('h_type').value = h.type;
    document.getElementById('h_capacity').value = h.intake_capacity;
    document.getElementById('h_warden').value = h.warden_name || '';
    document.getElementById('h_phone').value = h.warden_phone || '';
    document.getElementById('h_address').value = h.address || '';
    document.getElementById('hostelModal').style.display = 'flex';
}

function openRoomModal() {
    document.getElementById('roomModalTitle').innerText = 'Add Room';
    document.getElementById('rm_id').value = '';
    document.getElementById('rm_no').value = '';
    document.getElementById('rm_beds').value = '4';
    document.getElementById('rm_cost').value = '';
    document.getElementById('roomModal').style.display = 'flex';
}

function editRoom(rm) {
    document.getElementById('roomModalTitle').innerText = 'Edit Room';
    document.getElementById('rm_id').value = rm.id;
    document.getElementById('rm_hostel_id').value = rm.hostel_id;
    document.getElementById('rm_no').value = rm.room_no;
    document.getElementById('rm_type').value = rm.room_type;
    document.getElementById('rm_beds').value = rm.number_of_beds;
    document.getElementById('rm_cost').value = rm.cost_per_bed || '';
    document.getElementById('roomModal').style.display = 'flex';
}

function openAllocModal() {
    document.getElementById('allocModal').style.display = 'flex';
}

function loadHostelRooms(hostelId) {
    const roomSelect = document.getElementById('alloc_room_select');
    roomSelect.innerHTML = '<option value="">-- Select Room --</option>';
    if (!hostelId) return;

    const filtered = allRooms.filter(r => r.hostel_id == hostelId);
    filtered.forEach(r => {
        const available = r.number_of_beds - r.occupied_count;
        const opt = document.createElement('option');
        opt.value = r.id;
        opt.textContent = `Room ${r.room_no} (${r.room_type} - ${available} beds available)`;
        if (available <= 0) {
            opt.disabled = true;
            opt.textContent += ' [FULL]';
        }
        roomSelect.appendChild(opt);
    });
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>