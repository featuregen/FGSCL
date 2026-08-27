<?php require_once VIEW_PATH . '/layouts/header.php'; ?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Inventory & Asset Management</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Track physical assets, stationery, classroom equipment, stock levels, and staff item issues</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openItemModal()">
            <i class="bi bi-plus-circle"></i> Add Item
        </button>
        <button class="btn btn-secondary" onclick="openIssueModal()">
            <i class="bi bi-box-arrow-up-right"></i> Issue Item
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-box-seam"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Items</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['total_items']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-stack"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Stock Units</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['total_stock']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #f59e0b; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 20px;">
            <i class="bi bi-exclamation-octagon"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Low Stock Alerts</div>
            <div style="font-size: 24px; font-weight: 700; color: #f59e0b;"><?= number_format($stats['low_stock']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-handbag"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Currently Issued</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['issued_count']) ?></div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/inventory?tab=items" class="btn" style="background: <?= $tab === 'items' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'items' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-boxes"></i> Stock Catalog (<?= count($items) ?>)
    </a>
    <a href="<?= APP_URL ?>/inventory?tab=issues" class="btn" style="background: <?= $tab === 'issues' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'issues' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-arrow-up-right-circle"></i> Issued Items (<?= count($issues) ?>)
    </a>
    <a href="<?= APP_URL ?>/inventory?tab=settings" class="btn" style="background: <?= $tab === 'settings' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'settings' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-gear"></i> Categories & Suppliers
    </a>
</div>

<?php if ($tab === 'items'): ?>
<!-- Filter bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= APP_URL ?>/inventory" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="tab" value="items">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="Search item name or code..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="min-width: 200px;">
            <select name="category_id" class="form-control" onchange="this.form.submit()">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $catFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        <?php if (!empty($search) || $catFilter !== 'all'): ?>
            <a href="<?= APP_URL ?>/inventory?tab=items" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Items Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Item Name / Code</th>
                    <th style="padding: 14px 18px;">Category</th>
                    <th style="padding: 14px 18px;">Supplier</th>
                    <th style="padding: 14px 18px;">Available / Total</th>
                    <th style="padding: 14px 18px;">Unit Price</th>
                    <th style="padding: 14px 18px;">Stock Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No items found in stock catalog. Click "Add Item" to begin tracking.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $it): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; font-size: 14px; color: var(--text-primary, #1e293b);">
                                    <?= htmlspecialchars($it['name']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Code: <?= htmlspecialchars($it['code']) ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                    <?= htmlspecialchars($it['category_name'] ?: 'General') ?>
                                </span>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= htmlspecialchars($it['supplier_name'] ?: 'Direct Purchase') ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; font-size: 15px; color: <?= $it['available_quantity'] <= $it['min_quantity_alert'] ? '#ef4444' : '#10b981' ?>;">
                                    <?= $it['available_quantity'] ?> <?= htmlspecialchars($it['unit']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Total: <?= $it['quantity'] ?> <?= htmlspecialchars($it['unit']) ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; font-weight: 600;">
                                <?= number_format($it['unit_price'], 2) ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($it['available_quantity'] <= 0): ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Out of Stock</span>
                                <?php elseif ($it['available_quantity'] <= $it['min_quantity_alert']): ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #d97706; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="btn btn-sm btn-secondary" onclick='editItem(<?= json_encode($it) ?>)'><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?= APP_URL ?>/inventory/delete-item" style="display: inline;" onsubmit="return confirm('Delete this inventory item?')">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $it['id'] ?>">
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

<?php elseif ($tab === 'issues'): ?>
<!-- Issues Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Item Name</th>
                    <th style="padding: 14px 18px;">Issued To</th>
                    <th style="padding: 14px 18px;">Quantity</th>
                    <th style="padding: 14px 18px;">Issue Date</th>
                    <th style="padding: 14px 18px;">Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($issues)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            No items currently issued out.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($issues as $iss): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--text-primary, #1e293b);"><?= htmlspecialchars($iss['item_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">Code: <?= htmlspecialchars($iss['item_code']) ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($iss['issued_to_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b); text-transform: capitalize;">Type: <?= htmlspecialchars($iss['issued_to_type']) ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-weight: 600; color: #0f766e;">
                                <?= $iss['quantity'] ?> <?= htmlspecialchars($iss['unit']) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= date('d M Y', strtotime($iss['issue_date'])) ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($iss['status'] === 'issued'): ?>
                                    <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Issued / In Use</span>
                                <?php elseif ($iss['status'] === 'returned'): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Returned</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(100, 116, 139, 0.15); color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 600;">Consumed</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <?php if ($iss['status'] === 'issued'): ?>
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <form method="POST" action="<?= APP_URL ?>/inventory/return-item" style="display: inline;">
                                            <?= Session::csrfField() ?>
                                            <input type="hidden" name="issue_id" value="<?= $iss['id'] ?>">
                                            <input type="hidden" name="action" value="returned">
                                            <button type="submit" class="btn btn-sm btn-primary" title="Return to Stock">
                                                <i class="bi bi-box-arrow-in-down"></i> Return
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= APP_URL ?>/inventory/return-item" style="display: inline;">
                                            <?= Session::csrfField() ?>
                                            <input type="hidden" name="issue_id" value="<?= $iss['id'] ?>">
                                            <input type="hidden" name="action" value="consumed">
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Mark Consumed">
                                                <i class="bi bi-check-all"></i> Consumed
                                            </button>
                                        </form>
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

<?php elseif ($tab === 'settings'): ?>
<!-- Categories & Suppliers Settings -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Categories Column -->
    <div class="card" style="padding: 24px; border-radius: 12px;">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700;">Item Categories</h3>
        <form method="POST" action="<?= APP_URL ?>/inventory/save-category" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <?= Session::csrfField() ?>
            <input type="text" name="name" class="form-control" placeholder="New Category Name..." required>
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="bi bi-plus"></i> Add</button>
        </form>

        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 8px;">Category Name</th>
                    <th style="padding: 8px;">Items</th>
                    <th style="padding: 8px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                        <td style="padding: 10px 8px; font-weight: 600;"><?= htmlspecialchars($c['name']) ?></td>
                        <td style="padding: 10px 8px;"><?= $c['item_count'] ?> items</td>
                        <td style="padding: 10px 8px; text-align: right;">
                            <form method="POST" action="<?= APP_URL ?>/inventory/delete-category" style="display: inline;" onsubmit="return confirm('Delete category?')">
                                <?= Session::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Suppliers Column -->
    <div class="card" style="padding: 24px; border-radius: 12px;">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700;">Suppliers & Vendors</h3>
        <form method="POST" action="<?= APP_URL ?>/inventory/save-supplier" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; background: var(--bg-surface-secondary, #f8fafc); padding: 14px; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0);">
            <?= Session::csrfField() ?>
            <input type="text" name="name" class="form-control" placeholder="Supplier / Company Name" required>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <input type="text" name="contact_person" class="form-control" placeholder="Contact Person">
                <input type="text" name="phone" class="form-control" placeholder="Phone Number">
            </div>
            <div style="text-align: right;">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Add Supplier</button>
            </div>
        </form>

        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 8px;">Supplier</th>
                    <th style="padding: 8px;">Contact / Phone</th>
                    <th style="padding: 8px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="3" style="text-align: center; color: var(--text-muted, #94a3b8); padding: 16px;">No suppliers registered.</td></tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $sup): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 10px 8px; font-weight: 600;"><?= htmlspecialchars($sup['name']) ?></td>
                            <td style="padding: 10px 8px;"><?= htmlspecialchars($sup['contact_person'] ?: '-') ?> (<?= htmlspecialchars($sup['phone'] ?: '-') ?>)</td>
                            <td style="padding: 10px 8px; text-align: right;">
                                <form method="POST" action="<?= APP_URL ?>/inventory/delete-supplier" style="display: inline;" onsubmit="return confirm('Delete supplier?')">
                                    <?= Session::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $sup['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;"><i class="bi bi-trash"></i></button>
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

<!-- Item Modal -->
<div id="itemModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 560px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="itemModalTitle">Add Stock Item</h3>
            <button class="btn-icon" onclick="closeModal('itemModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/inventory/save-item">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="it_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Item Name *</label>
                        <input type="text" name="name" id="it_name" class="form-control" placeholder="e.g. A4 Paper Ream" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Item Code</label>
                        <input type="text" name="code" id="it_code" class="form-control" placeholder="Auto-generated if empty">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Category</label>
                        <select name="category_id" id="it_cat_id" class="form-control">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Supplier</label>
                        <select name="supplier_id" id="it_sup_id" class="form-control">
                            <option value="">-- Select Supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Total Quantity *</label>
                        <input type="number" name="quantity" id="it_qty" class="form-control" value="10" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Unit (pcs/box/set)</label>
                        <input type="text" name="unit" id="it_unit" class="form-control" value="pcs">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Unit Price</label>
                        <input type="number" step="0.01" name="unit_price" id="it_price" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Low Stock Alert Level</label>
                    <input type="number" name="min_quantity_alert" id="it_min_alert" class="form-control" value="5" min="1">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('itemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Issue Item Modal -->
<div id="issueModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Issue Item / Asset</h3>
            <button class="btn-icon" onclick="closeModal('issueModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/inventory/issue-item">
            <?= Session::csrfField() ?>
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Item from Stock *</label>
                    <select name="item_id" class="form-control" required>
                        <option value="">-- Select Available Item --</option>
                        <?php foreach ($items as $it): ?>
                            <?php if ($it['available_quantity'] > 0): ?>
                                <option value="<?= $it['id'] ?>">
                                    <?= htmlspecialchars($it['name']) ?> (<?= $it['available_quantity'] ?> <?= htmlspecialchars($it['unit']) ?> available)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Issue Target Type *</label>
                        <select name="issued_to_type" id="issue_to_type" class="form-control" onchange="toggleIssueTarget(this.value)">
                            <option value="staff">Staff Member / Teacher</option>
                            <option value="department">Department</option>
                            <option value="classroom">Classroom / Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Quantity *</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                </div>

                <div class="form-group" id="staffSelectGroup">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Staff Member *</label>
                    <select name="issued_to_id" class="form-control">
                        <option value="">-- Choose Staff --</option>
                        <?php foreach ($staffMembers as $sm): ?>
                            <option value="<?= $sm['id'] ?>"><?= htmlspecialchars($sm['full_name']) ?> (<?= htmlspecialchars($sm['role_name'] ?? 'Staff') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="customTargetGroup" style="display: none;">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Department / Classroom Name *</label>
                    <input type="text" name="issued_to_name" class="form-control" placeholder="e.g. Science Lab 2, Grade 5A">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Issue Date *</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Expected Return Date</label>
                        <input type="date" name="return_date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('issueModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Issue</button>
            </div>
        </form>
    </div>
</div>

<script>
function openItemModal() {
    document.getElementById('itemModalTitle').innerText = 'Add Stock Item';
    document.getElementById('it_id').value = '';
    document.getElementById('it_name').value = '';
    document.getElementById('it_code').value = '';
    document.getElementById('it_cat_id').value = '';
    document.getElementById('it_sup_id').value = '';
    document.getElementById('it_qty').value = '10';
    document.getElementById('it_unit').value = 'pcs';
    document.getElementById('it_price').value = '';
    document.getElementById('it_min_alert').value = '5';
    document.getElementById('itemModal').style.display = 'flex';
}

function editItem(it) {
    document.getElementById('itemModalTitle').innerText = 'Edit Stock Item';
    document.getElementById('it_id').value = it.id;
    document.getElementById('it_name').value = it.name;
    document.getElementById('it_code').value = it.code || '';
    document.getElementById('it_cat_id').value = it.category_id || '';
    document.getElementById('it_sup_id').value = it.supplier_id || '';
    document.getElementById('it_qty').value = it.quantity;
    document.getElementById('it_unit').value = it.unit || 'pcs';
    document.getElementById('it_price').value = it.unit_price || '';
    document.getElementById('it_min_alert').value = it.min_quantity_alert || '5';
    document.getElementById('itemModal').style.display = 'flex';
}

function openIssueModal() {
    document.getElementById('issueModal').style.display = 'flex';
}

function toggleIssueTarget(type) {
    if (type === 'staff') {
        document.getElementById('staffSelectGroup').style.display = 'block';
        document.getElementById('customTargetGroup').style.display = 'none';
    } else {
        document.getElementById('staffSelectGroup').style.display = 'none';
        document.getElementById('customTargetGroup').style.display = 'block';
    }
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>