<!-- Plan Management (Super Admin) -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p style="color: var(--gray-500); margin: 0;">Configure subscription plans and pricing for schools</p>
    </div>
    <a href="<?= APP_URL ?>/plans/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create Plan
    </a>
</div>

<!-- Plans Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
    <?php foreach ($plans as $plan): ?>
        <?php 
        $isActive = $plan['is_active'];
        $isPremium = in_array($plan['slug'], ['premium', 'enterprise']);
        $isFree = $plan['price_monthly'] == 0 && $plan['price_per_student_monthly'] == 0;
        $features = $plan['features_list'] ?? [];
        ?>
        <div class="card" style="opacity: <?= $isActive ? '1' : '0.6' ?>; <?= !$isActive ? 'border-style: dashed;' : '' ?>">
            <!-- Plan Header -->
            <div class="card-header" style="background: <?= $isPremium ? 'linear-gradient(135deg, var(--primary-50), #F5F3FF)' : 'var(--gray-50)' ?>; padding: 20px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--gray-900);">
                            <?= htmlspecialchars($plan['name']) ?>
                        </h3>
                        <?php if (!$isActive): ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                        <?php if ($isFree): ?>
                            <span class="badge badge-success">Free</span>
                        <?php endif; ?>
                    </div>
                    <code style="font-size: 11px; background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px; color: var(--gray-500);"><?= htmlspecialchars($plan['slug']) ?></code>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-<?= $plan['pricing_type'] === 'per_student' ? 'info' : 'primary' ?>">
                        <i class="bi bi-<?= $plan['pricing_type'] === 'per_student' ? 'people' : 'tag' ?>"></i>
                        <?= $plan['pricing_type'] === 'per_student' ? 'Per Student' : 'Fixed' ?>
                    </span>
                </div>
            </div>

            <div class="card-body" style="padding: 20px;">
                <!-- Pricing -->
                <div style="margin-bottom: 16px;">
                    <?php if ($plan['pricing_type'] === 'per_student'): ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div style="background: var(--primary-50); padding: 10px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: var(--primary);">₹<?= number_format($plan['price_per_student_monthly'], 0) ?></div>
                                <div style="font-size: 10px; color: var(--gray-500); text-transform: uppercase;">/ student / mo</div>
                            </div>
                            <div style="background: var(--gray-50); padding: 10px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: var(--gray-700);">₹<?= number_format($plan['price_per_student_yearly'], 0) ?></div>
                                <div style="font-size: 10px; color: var(--gray-500); text-transform: uppercase;">/ student / yr</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 12px; margin-top: 8px; font-size: 11px; color: var(--gray-500);">
                            <span><i class="bi bi-arrow-down-short"></i> Min: <?= $plan['min_students'] ?> students</span>
                            <span><i class="bi bi-arrow-up-short"></i> Max: <?= $plan['max_students_limit'] ?: '∞' ?></span>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <div style="background: var(--primary-50); padding: 8px 6px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 16px; font-weight: 800; color: var(--primary);">₹<?= number_format($plan['price_monthly'], 0) ?></div>
                                <div style="font-size: 9px; color: var(--gray-500); text-transform: uppercase;">Monthly</div>
                            </div>
                            <div style="background: var(--gray-50); padding: 8px 6px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 16px; font-weight: 800; color: var(--gray-700);">₹<?= number_format($plan['price_quarterly'], 0) ?></div>
                                <div style="font-size: 9px; color: var(--gray-500); text-transform: uppercase;">Quarterly</div>
                            </div>
                            <div style="background: var(--gray-50); padding: 8px 6px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 16px; font-weight: 800; color: var(--gray-700);">₹<?= number_format($plan['price_half_yearly'], 0) ?></div>
                                <div style="font-size: 9px; color: var(--gray-500); text-transform: uppercase;">Half-Yr</div>
                            </div>
                            <div style="background: var(--gray-50); padding: 8px 6px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 16px; font-weight: 800; color: var(--gray-700);">₹<?= number_format($plan['price_yearly'], 0) ?></div>
                                <div style="font-size: 9px; color: var(--gray-500); text-transform: uppercase;">Yearly</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($plan['description'])): ?>
                    <p style="font-size: 12px; color: var(--gray-500); margin-bottom: 12px; line-height: 1.5;">
                        <?= htmlspecialchars($plan['description']) ?>
                    </p>
                <?php endif; ?>

                <!-- Limits -->
                <div style="display: flex; gap: 16px; margin-bottom: 12px; font-size: 12px; color: var(--gray-600);">
                    <span title="Max Students"><i class="bi bi-mortarboard" style="color: var(--primary);"></i> <?= $plan['max_students'] ?: '∞' ?></span>
                    <span title="Max Staff"><i class="bi bi-person-badge" style="color: var(--success);"></i> <?= $plan['max_staff'] ?: '∞' ?></span>
                    <span title="Max Branches"><i class="bi bi-building" style="color: var(--warning);"></i> <?= $plan['max_branches'] ?: '∞' ?></span>
                    <span title="Subscribers" style="margin-left: auto; font-weight: 600; color: var(--primary);">
                        <i class="bi bi-people-fill"></i> <?= $plan['subscriber_count'] ?> active
                    </span>
                </div>

                <!-- Features -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 6px;">
                        <?= count($features) ?> Modules Included
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                        <?php foreach (array_slice($features, 0, 8) as $feat): ?>
                            <span style="font-size: 10px; background: var(--gray-100); color: var(--gray-600); padding: 2px 8px; border-radius: 10px;">
                                <?= htmlspecialchars($feat) ?>
                            </span>
                        <?php endforeach; ?>
                        <?php if (count($features) > 8): ?>
                            <span style="font-size: 10px; background: var(--primary-50); color: var(--primary); padding: 2px 8px; border-radius: 10px; font-weight: 600;">
                                +<?= count($features) - 8 ?> more
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 8px; border-top: 1px solid var(--gray-100); padding-top: 16px;">
                    <a href="<?= APP_URL ?>/plans/edit/<?= $plan['id'] ?>" class="btn btn-sm btn-secondary" style="flex: 1;">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="<?= APP_URL ?>/plans/delete/<?= $plan['id'] ?>" class="btn btn-sm btn-secondary"
                       data-confirm-delete data-name="<?= htmlspecialchars($plan['name']) ?> plan" title="Deactivate">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>

            <!-- Sort Order Badge -->
            <div style="position: absolute; top: 12px; right: 12px; width: 24px; height: 24px; border-radius: 50%; background: var(--gray-100); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--gray-500);">
                <?= $plan['sort_order'] ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($plans)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-credit-card"></i></div>
            <div class="empty-title">No plans configured</div>
            <div class="empty-text">Create your first subscription plan to start onboarding schools</div>
            <a href="<?= APP_URL ?>/plans/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create Plan
            </a>
        </div>
    </div>
<?php endif; ?>
