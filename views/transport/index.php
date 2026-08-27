<?php require_once VIEW_PATH . '/layouts/header.php'; ?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Transport Management</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Manage school bus fleet, routes, pickup/drop stops, and student passenger allocations</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openRouteModal()">
            <i class="bi bi-plus-circle"></i> Add Route
        </button>
        <button class="btn btn-secondary" onclick="openVehicleModal()">
            <i class="bi bi-bus-front"></i> Add Vehicle
        </button>
        <button class="btn btn-secondary" onclick="openAllocModal()">
            <i class="bi bi-person-plus"></i> Allocate Student
        </button>
    </div>
</div>

<!-- Quick Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-bus-front-fill"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Vehicles in Fleet</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['total_vehicles']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-signpost-2-fill"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Active Routes</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['total_routes']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Assigned Passengers</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['allocated']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #f59e0b; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 20px;">
            <i class="bi bi-person-badge"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Seat Capacity</div>
            <div style="font-size: 24px; font-weight: 700; color: #f59e0b;"><?= number_format($stats['total_capacity']) ?> Seats</div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/transport?tab=routes" class="btn" style="background: <?= $tab === 'routes' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'routes' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-signpost-split"></i> Routes & Stops (<?= count($routes) ?>)
    </a>
    <a href="<?= APP_URL ?>/transport?tab=vehicles" class="btn" style="background: <?= $tab === 'vehicles' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'vehicles' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-truck-front"></i> Vehicles Fleet (<?= count($vehicles) ?>)
    </a>
    <a href="<?= APP_URL ?>/transport?tab=allocations" class="btn" style="background: <?= $tab === 'allocations' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'allocations' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-person-check"></i> Passenger Allocations (<?= count($allocations) ?>)
    </a>
</div>

<?php if ($tab === 'routes'): ?>
<!-- Routes & Stops View -->
<div style="display: flex; flex-direction: column; gap: 16px;">
    <?php if (empty($routes)): ?>
        <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
            <i class="bi bi-signpost" style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
            No transport routes configured. Click "Add Route" to create bus routes with stops and timings.
        </div>
    <?php else: ?>
        <?php foreach ($routes as $r): ?>
            <div class="card" style="padding: 20px; border-radius: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">
                                <?= htmlspecialchars($r['route_title']) ?>
                            </h3>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                <i class="bi bi-people"></i> <?= $r['student_count'] ?> Students Assigned
                            </span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted, #64748b); margin-top: 4px;">
                            <strong>Vehicle:</strong> <?= htmlspecialchars($r['vehicle_no'] ?: 'Not Assigned') ?> 
                            <?php if ($r['driver_name']): ?>
                                &bull; Driver: <?= htmlspecialchars($r['driver_name']) ?> (<?= htmlspecialchars($r['driver_phone'] ?: 'N/A') ?>)
                            <?php endif; ?>
                            <?php if (!empty($r['fare']) && $r['fare'] > 0): ?>
                                &bull; Default Fare: <?= number_format($r['fare'], 2) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-sm btn-primary" onclick="openAddStopModal(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['route_title'])) ?>')">
                            <i class="bi bi-plus-circle"></i> Add Stop
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick='editRoute(<?= json_encode($r) ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="<?= APP_URL ?>/transport/delete-route" style="display: inline;" onsubmit="return confirm('Delete this route and all its stops?')">
                            <?= Session::csrfField() ?>
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white; border: none; border-radius: 6px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Stops Table -->
                <div style="background: var(--bg-surface-secondary, #f8fafc); border-radius: 8px; padding: 12px; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--text-muted, #64748b);">Stops & Schedule (<?= count($r['stops']) ?> stops)</div>
                    <?php if (empty($r['stops'])): ?>
                        <div style="font-size: 13px; color: var(--text-muted, #94a3b8); font-style: italic;">No stops added yet. Click "+ Add Stop" to set up pickup points.</div>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">
                            <?php foreach ($r['stops'] as $s): ?>
                                <div style="background: #fff; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 13px; color: var(--text-primary, #1e293b);">
                                            <?= htmlspecialchars($s['stop_name']) ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted, #64748b); margin-top: 2px;">
                                            Pickup: <?= $s['pickup_time'] ? date('h:i A', strtotime($s['pickup_time'])) : '-' ?> 
                                            &bull; Drop: <?= $s['drop_time'] ? date('h:i A', strtotime($s['drop_time'])) : '-' ?>
                                        </div>
                                    </div>
                                    <form method="POST" action="<?= APP_URL ?>/transport/delete-stop" style="display: inline;" onsubmit="return confirm('Remove stop?')">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px;" title="Delete Stop">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'vehicles'): ?>
<!-- Vehicles Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Vehicle No / Model</th>
                    <th style="padding: 14px 18px;">Driver Details</th>
                    <th style="padding: 14px 18px;">Seating Capacity</th>
                    <th style="padding: 14px 18px;">Insurance Expiry</th>
                    <th style="padding: 14px 18px;">Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No vehicles added. Click "Add Vehicle" to register buses/vans in the fleet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vehicles as $v): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--text-primary, #1e293b); font-size: 14px;">
                                    <?= htmlspecialchars($v['vehicle_no']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($v['model'] ?: 'Standard Bus') ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($v['driver_name'] ?: 'Not Assigned') ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Phone: <?= htmlspecialchars($v['driver_phone'] ?: '-') ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; font-size: 14px; color: #6366f1;">
                                    <?= $v['capacity'] ?> Seats
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);"><?= $v['passenger_count'] ?> assigned</div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= $v['insurance_expiry'] ? date('d M Y', strtotime($v['insurance_expiry'])) : 'N/A' ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($v['status'] === 'active'): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Active</span>
                                <?php elseif ($v['status'] === 'maintenance'): ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.12); color: #d97706; padding: 4px 8px; border-radius: 6px; font-weight: 600;">In Maintenance</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="btn btn-sm btn-secondary" onclick='editVehicle(<?= json_encode($v) ?>)'><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?= APP_URL ?>/transport/delete-vehicle" style="display: inline;" onsubmit="return confirm('Delete this vehicle?')">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
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
<!-- Passenger Allocations Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Student Name</th>
                    <th style="padding: 14px 18px;">Class & Section</th>
                    <th style="padding: 14px 18px;">Route & Vehicle</th>
                    <th style="padding: 14px 18px;">Stop & Timings</th>
                    <th style="padding: 14px 18px;">Driver Phone</th>
                    <th style="padding: 14px 18px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allocations)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No students currently allocated to bus routes. Click "Allocate Student" to assign.
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
                                <div style="font-weight: 600; color: #0f766e;"><?= htmlspecialchars($a['route_title']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Bus: <?= htmlspecialchars($a['vehicle_no'] ?: '-') ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($a['stop_name'] ?: 'Direct Route') ?></div>
                                <?php if ($a['pickup_time']): ?>
                                    <div style="font-size: 11px; color: var(--text-muted, #64748b);">
                                        Pick: <?= date('h:i A', strtotime($a['pickup_time'])) ?> | Drop: <?= date('h:i A', strtotime($a['drop_time'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted, #64748b);">
                                <?= htmlspecialchars($a['driver_phone'] ?: '-') ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <form method="POST" action="<?= APP_URL ?>/transport/remove-allocation" style="display: inline;" onsubmit="return confirm('Cancel this student transport allocation?')">
                                    <?= Session::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background: #ef4444; color: white; border: none; border-radius: 6px;">
                                        <i class="bi bi-person-dash"></i> Deallocate
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

<!-- Route Modal -->
<div id="routeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="routeModalTitle">Add Route</h3>
            <button class="btn-icon" onclick="closeModal('routeModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/transport/save-route">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="route_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Route Title *</label>
                    <input type="text" name="route_title" id="route_title" class="form-control" placeholder="e.g. Route 1 - North City to Campus" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Assign Vehicle</label>
                    <select name="vehicle_id" id="route_vehicle_id" class="form-control">
                        <option value="">-- No Vehicle Assigned --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>">
                                <?= htmlspecialchars($v['vehicle_no']) ?> (<?= htmlspecialchars($v['model'] ?: 'Bus') ?> - <?= $v['capacity'] ?> seats)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Start Point</label>
                        <input type="text" name="start_point" id="route_start" class="form-control" placeholder="e.g. Central Station">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">End Point</label>
                        <input type="text" name="end_point" id="route_end" class="form-control" placeholder="e.g. School Gate 1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Monthly Transport Fare</label>
                    <input type="number" step="0.01" name="fare" id="route_fare" class="form-control" placeholder="0.00">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('routeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Route</button>
            </div>
        </form>
    </div>
</div>

<!-- Vehicle Modal -->
<div id="vehicleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="vehicleModalTitle">Add Vehicle</h3>
            <button class="btn-icon" onclick="closeModal('vehicleModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/transport/save-vehicle">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="veh_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Vehicle Registration No *</label>
                        <input type="text" name="vehicle_no" id="veh_no" class="form-control" placeholder="e.g. DL 01 AB 1234" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Capacity *</label>
                        <input type="number" name="capacity" id="veh_capacity" class="form-control" value="30" min="1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Model / Maker</label>
                    <input type="text" name="model" id="veh_model" class="form-control" placeholder="e.g. Tata Marcopolo 40-Seater">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Driver Name</label>
                        <input type="text" name="driver_name" id="veh_driver_name" class="form-control" placeholder="Driver full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Driver Phone</label>
                        <input type="text" name="driver_phone" id="veh_driver_phone" class="form-control" placeholder="Phone number">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Insurance Expiry</label>
                        <input type="date" name="insurance_expiry" id="veh_insurance" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Status</label>
                        <select name="status" id="veh_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('vehicleModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Vehicle</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Stop Modal -->
<div id="stopModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 460px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Add Stop to Route</h3>
            <button class="btn-icon" onclick="closeModal('stopModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/transport/save-stop">
            <?= Session::csrfField() ?>
            <input type="hidden" name="route_id" id="stop_route_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div style="font-size: 14px; font-weight: 600; color: #0f766e;" id="stop_route_title"></div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Stop Name / Location *</label>
                    <input type="text" name="stop_name" class="form-control" placeholder="e.g. Green Park Junction" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Pickup Time</label>
                        <input type="time" name="pickup_time" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Drop Time</label>
                        <input type="time" name="drop_time" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Stop Fare / Surcharge</label>
                    <input type="number" step="0.01" name="fare" class="form-control" placeholder="0.00">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('stopModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Stop</button>
            </div>
        </form>
    </div>
</div>

<!-- Allocate Student Modal -->
<div id="allocModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Assign Student to Route</h3>
            <button class="btn-icon" onclick="closeModal('allocModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/transport/allocate">
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
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Transport Route *</label>
                    <select name="route_id" id="alloc_route_select" class="form-control" required onchange="loadRouteStops(this.value)">
                        <option value="">-- Choose Route --</option>
                        <?php foreach ($routes as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['route_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Pickup Stop</label>
                    <select name="stop_id" id="alloc_stop_select" class="form-control">
                        <option value="">-- Direct Route / Default Stop --</option>
                    </select>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('allocModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Allocation</button>
            </div>
        </form>
    </div>
</div>

<script>
const allRoutes = <?= json_encode($routes) ?>;

function openRouteModal() {
    document.getElementById('routeModalTitle').innerText = 'Add Route';
    document.getElementById('route_id').value = '';
    document.getElementById('route_title').value = '';
    document.getElementById('route_vehicle_id').value = '';
    document.getElementById('route_start').value = '';
    document.getElementById('route_end').value = '';
    document.getElementById('route_fare').value = '';
    document.getElementById('routeModal').style.display = 'flex';
}

function editRoute(r) {
    document.getElementById('routeModalTitle').innerText = 'Edit Route';
    document.getElementById('route_id').value = r.id;
    document.getElementById('route_title').value = r.route_title;
    document.getElementById('route_vehicle_id').value = r.vehicle_id || '';
    document.getElementById('route_start').value = r.start_point || '';
    document.getElementById('route_end').value = r.end_point || '';
    document.getElementById('route_fare').value = r.fare || '';
    document.getElementById('routeModal').style.display = 'flex';
}

function openVehicleModal() {
    document.getElementById('vehicleModalTitle').innerText = 'Add Vehicle';
    document.getElementById('veh_id').value = '';
    document.getElementById('veh_no').value = '';
    document.getElementById('veh_capacity').value = '30';
    document.getElementById('veh_model').value = '';
    document.getElementById('veh_driver_name').value = '';
    document.getElementById('veh_driver_phone').value = '';
    document.getElementById('veh_insurance').value = '';
    document.getElementById('veh_status').value = 'active';
    document.getElementById('vehicleModal').style.display = 'flex';
}

function editVehicle(v) {
    document.getElementById('vehicleModalTitle').innerText = 'Edit Vehicle';
    document.getElementById('veh_id').value = v.id;
    document.getElementById('veh_no').value = v.vehicle_no;
    document.getElementById('veh_capacity').value = v.capacity;
    document.getElementById('veh_model').value = v.model || '';
    document.getElementById('veh_driver_name').value = v.driver_name || '';
    document.getElementById('veh_driver_phone').value = v.driver_phone || '';
    document.getElementById('veh_insurance').value = v.insurance_expiry || '';
    document.getElementById('veh_status').value = v.status || 'active';
    document.getElementById('vehicleModal').style.display = 'flex';
}

function openAddStopModal(routeId, routeTitle) {
    document.getElementById('stop_route_id').value = routeId;
    document.getElementById('stop_route_title').innerText = 'Route: ' + routeTitle;
    document.getElementById('stopModal').style.display = 'flex';
}

function openAllocModal() {
    document.getElementById('allocModal').style.display = 'flex';
}

function loadRouteStops(routeId) {
    const stopSelect = document.getElementById('alloc_stop_select');
    stopSelect.innerHTML = '<option value="">-- Direct Route / Default Stop --</option>';
    if (!routeId) return;

    const route = allRoutes.find(r => r.id == routeId);
    if (route && route.stops) {
        route.stops.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.stop_name + (s.pickup_time ? ' (' + s.pickup_time + ')' : '');
            stopSelect.appendChild(opt);
        });
    }
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>