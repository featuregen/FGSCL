

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Master Data Hub</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Manage standardized dropdown values, student houses, document types, and categorization lists</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start;">
    <!-- Category Sidebar List -->
    <div class="card" style="padding: 12px; border-radius: 12px;">
        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted, #64748b); text-transform: uppercase; padding: 8px 12px; margin-bottom: 4px;">
            Master Categories
        </div>
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <?php foreach ($categories as $catKey => $catInfo): ?>
                <a href="<?= APP_URL ?>/masters?cat=<?= $catKey ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s ease; background: <?= $activeCategory === $catKey ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $activeCategory === $catKey ? '#fff' : 'var(--text-primary, #334155)' ?>;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi <?= $catInfo['icon'] ?>"></i>
                        <?= htmlspecialchars($catInfo['label']) ?>
                    </span>
                    <span class="badge" style="background: <?= $activeCategory === $catKey ? 'rgba(255,255,255,0.25)' : 'rgba(0,0,0,0.06)' ?>; color: inherit; padding: 2px 8px; border-radius: 12px; font-size: 11px;">
                        <?= $counts[$catKey] ?? 0 ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Active Category Items & Form -->
    <div class="card" style="padding: 24px; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); padding-bottom: 16px;">
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">
                    <?= htmlspecialchars($categories[$activeCategory]['label']) ?>
                </h3>
                <div style="font-size: 13px; color: var(--text-muted, #64748b); margin-top: 2px;">
                    Standard values used across student admission, profiles, and operations
                </div>
            </div>
        </div>

        <!-- Add Item Form -->
        <form method="POST" action="<?= APP_URL ?>/masters/save" style="background: var(--bg-surface-secondary, #f8fafc); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0); margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <?= Session::csrfField() ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
            
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="name" class="form-control" placeholder="Item Name (e.g. Phoenix House, A+)" required>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <input type="text" name="code" class="form-control" placeholder="Short Code (Optional)">
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>
        </form>

        <!-- Items Table -->
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 2px solid var(--border-color, #e2e8f0); text-align: left;">
                        <th style="padding: 12px 16px; width: 60px;">#</th>
                        <th style="padding: 12px 16px;">Item Name</th>
                        <th style="padding: 12px 16px;">Code / Key</th>
                        <th style="padding: 12px 16px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-muted, #94a3b8);">
                                No items found in this category. Use the form above to add values.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                                <td style="padding: 12px 16px; color: var(--text-muted, #94a3b8); font-weight: 600;"><?= $idx + 1 ?></td>
                                <td style="padding: 12px 16px; font-weight: 700; color: var(--text-primary, #1e293b);"><?= htmlspecialchars($it['name']) ?></td>
                                <td style="padding: 12px 16px; color: var(--text-muted, #64748b);">
                                    <code style="background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($it['code'] ?: 'N/A') ?></code>
                                </td>
                                <td style="padding: 12px 16px; text-align: right;">
                                    <form method="POST" action="<?= APP_URL ?>/masters/delete" style="display: inline;" onsubmit="return confirm('Delete this master item?')">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $it['id'] ?>">
                                        <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px;" title="Delete Item">
                                            <i class="bi bi-trash"></i>
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
</div>
