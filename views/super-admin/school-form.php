<!-- Add/Edit School Form -->
<?php $isEdit = !empty($school); ?>

<form action="<?= APP_URL ?>/schools/<?= $isEdit ? 'update/' . $school['id'] : 'store' ?>" method="POST" enctype="multipart/form-data">
    <?= Session::csrfField() ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left Column — School Details -->
        <div>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-building me-2"></i> School Information</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">School Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($school['name'] ?? '') ?>" required placeholder="e.g. Delhi Public School">
                        </div>
                        <div class="form-group">
                            <label class="form-label">School Code <span class="required">*</span></label>
                            <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($school['code'] ?? '') ?>" required placeholder="e.g. DPS001" <?= $isEdit ? 'readonly' : '' ?> style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Board</label>
                            <select class="form-control" name="board">
                                <option value="">Select Board</option>
                                <?php foreach (['CBSE', 'ICSE', 'State Board', 'IB', 'IGCSE', 'Other'] as $board): ?>
                                    <option value="<?= $board ?>" <?= ($school['board'] ?? '') === $board ? 'selected' : '' ?>><?= $board ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">School Type</label>
                            <select class="form-control" name="school_type">
                                <?php foreach (['primary' => 'Primary', 'secondary' => 'Secondary', 'higher_secondary' => 'Higher Secondary', 'k12' => 'K-12', 'college' => 'College'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= ($school['school_type'] ?? 'k12') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($school['email'] ?? '') ?>" required placeholder="school@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone <span class="required">*</span></label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($school['phone'] ?? '') ?>" required placeholder="+91 9876543210">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" value="<?= htmlspecialchars($school['website'] ?? '') ?>" placeholder="https://school.com">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Full address"><?= htmlspecialchars($school['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($school['city'] ?? '') ?>" placeholder="City">
                        </div>
                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($school['state'] ?? '') ?>" placeholder="State">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?= htmlspecialchars($school['pincode'] ?? '') ?>" placeholder="560001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Principal Name</label>
                            <input type="text" class="form-control" name="principal_name" value="<?= htmlspecialchars($school['principal_name'] ?? '') ?>" placeholder="Principal name">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Tagline</label>
                            <input type="text" class="form-control" name="tagline" value="<?= htmlspecialchars($school['tagline'] ?? '') ?>" placeholder="Education for a better tomorrow">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Account (only for new schools) -->
            <?php if (!$isEdit): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-person-gear me-2"></i> School Admin Account</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Admin Full Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="admin_name" required placeholder="Full name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username <span class="required">*</span></label>
                            <input type="text" class="form-control" name="admin_username" required placeholder="e.g. admin_dps" pattern="[a-zA-Z0-9_.]+" title="Letters, numbers, underscore and dot only">
                            <div class="form-text">Used for login. Letters, numbers, underscore, dot only.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Email <span class="required">*</span></label>
                            <input type="email" class="form-control" name="admin_email" required placeholder="admin@school.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Phone</label>
                            <input type="text" class="form-control" name="admin_phone" placeholder="Phone number">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" name="admin_password" required minlength="8" placeholder="Min 8 characters">
                                <button type="button" class="password-toggle">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-text"><i class="bi bi-info-circle"></i> Admin can login with either email or username. A welcome email with credentials will be sent.</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Feature Modules -->
            <div class="card mb-4">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title"><i class="bi bi-grid-3x3-gap me-2"></i> Feature Modules</h3>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllModules(true)">Enable All</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllModules(false)">Disable All</button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="form-text" style="margin-bottom: 16px;">
                        <i class="bi bi-info-circle"></i> Select which modules this school can access. Core modules cannot be disabled.
                    </p>

                    <?php
                    // Get all modules grouped by category
                    require_once APP_PATH . '/Services/ModuleService.php';
                    $modulesByCategory = ModuleService::getModulesByCategory();
                    $categoryLabels = ModuleService::getCategoryLabels();
                    
                    // Get currently enabled modules for edit mode
                    $enabledModuleSlugs = [];
                    if ($isEdit) {
                        $schoolModules = ModuleService::getSchoolModuleStatus($school['id']);
                        foreach ($schoolModules as $m) {
                            if ($m['is_enabled']) $enabledModuleSlugs[] = $m['slug'];
                        }
                    }
                    // For new school, get plan default features
                    $planDefaultFeatures = [];
                    if (!empty($plans)) {
                        foreach ($plans as $p) {
                            $planDefaultFeatures[$p['id']] = json_decode($p['features'] ?? '[]', true);
                        }
                    }
                    ?>

                    <?php foreach ($modulesByCategory as $category => $modules): ?>
                        <?php $catInfo = $categoryLabels[$category] ?? ['label' => ucfirst($category), 'icon' => 'bi-box', 'color' => '#6B7280']; ?>
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--gray-100);">
                                <i class="bi <?= $catInfo['icon'] ?>" style="color: <?= $catInfo['color'] ?>; font-size: 16px;"></i>
                                <span style="font-weight: 700; font-size: 13px; color: var(--gray-700); text-transform: uppercase; letter-spacing: 0.5px;">
                                    <?= $catInfo['label'] ?>
                                </span>
                                <?php if ($category === 'core'): ?>
                                    <span class="badge badge-info" style="font-size: 10px;">Always On</span>
                                <?php elseif ($category === 'premium'): ?>
                                    <span class="badge badge-warning" style="font-size: 10px;">Premium</span>
                                <?php endif; ?>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <?php foreach ($modules as $mod): ?>
                                    <?php 
                                    $isCore = (bool)$mod['is_core'];
                                    $isChecked = $isCore; // core always checked
                                    if ($isEdit) {
                                        $isChecked = in_array($mod['slug'], $enabledModuleSlugs);
                                    }
                                    ?>
                                    <label class="module-toggle <?= $isCore ? 'core' : '' ?>" 
                                           style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; cursor: <?= $isCore ? 'default' : 'pointer' ?>; 
                                                  background: <?= $isChecked ? 'var(--primary-50)' : 'var(--gray-50)' ?>; border: 1px solid <?= $isChecked ? 'var(--primary-100)' : 'var(--gray-100)' ?>;
                                                  transition: all 0.2s;">
                                        <input type="checkbox" 
                                               name="modules[]" 
                                               value="<?= $mod['slug'] ?>" 
                                               class="module-checkbox"
                                               data-category="<?= $category ?>"
                                               data-slug="<?= $mod['slug'] ?>"
                                               <?= $isChecked ? 'checked' : '' ?> 
                                               <?= $isCore ? 'checked disabled' : '' ?>
                                               style="width: 16px; height: 16px; accent-color: var(--primary); flex-shrink: 0;">
                                        <?php if ($isCore): ?>
                                            <input type="hidden" name="modules[]" value="<?= $mod['slug'] ?>">
                                        <?php endif; ?>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-weight: 600; font-size: 13px; color: var(--gray-800); display: flex; align-items: center; gap: 6px;">
                                                <i class="bi <?= htmlspecialchars($mod['icon']) ?>" style="color: <?= $catInfo['color'] ?>; font-size: 14px;"></i>
                                                <?= htmlspecialchars($mod['name']) ?>
                                            </div>
                                            <div style="font-size: 11px; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($mod['description']) ?>
                                            </div>
                                        </div>
                                        <?php if ($isCore): ?>
                                            <i class="bi bi-lock" style="color: var(--gray-400); font-size: 12px;" title="Core — cannot be disabled"></i>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="margin-top: 12px; padding: 10px 14px; background: var(--primary-50); border-radius: 8px; font-size: 12px; color: var(--gray-600);">
                        <i class="bi bi-lightbulb" style="color: var(--warning);"></i>
                        <strong id="moduleCount">0</strong> modules selected. 
                        <span style="color: var(--gray-400);">Modules can be changed anytime without affecting existing data.</span>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.module-checkbox:not(:disabled)');
                const countEl = document.getElementById('moduleCount');
                
                function updateModuleCount() {
                    const checked = document.querySelectorAll('.module-checkbox:checked').length;
                    countEl.textContent = checked;
                }
                
                function updateToggleStyle(checkbox) {
                    const label = checkbox.closest('.module-toggle');
                    if (checkbox.checked) {
                        label.style.background = 'var(--primary-50)';
                        label.style.borderColor = 'var(--primary-100)';
                    } else {
                        label.style.background = 'var(--gray-50)';
                        label.style.borderColor = 'var(--gray-100)';
                    }
                }
                
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        updateModuleCount();
                        updateToggleStyle(this);
                    });
                });
                
                updateModuleCount();

                // Plan selection auto-selects default modules (new school only)
                <?php if (!$isEdit): ?>
                const planDefaults = <?= json_encode($planDefaultFeatures) ?>;
                const planSelect = document.getElementById('planSelect');
                if (planSelect) {
                    planSelect.addEventListener('change', function() {
                        const planId = this.value;
                        if (planId && planDefaults[planId]) {
                            // Reset non-core checkboxes
                            checkboxes.forEach(cb => {
                                cb.checked = planDefaults[planId].includes(cb.dataset.slug);
                                updateToggleStyle(cb);
                            });
                            updateModuleCount();
                        }
                    });
                }
                <?php endif; ?>
            });
            
            function toggleAllModules(enable) {
                document.querySelectorAll('.module-checkbox:not(:disabled)').forEach(cb => {
                    cb.checked = enable;
                    const label = cb.closest('.module-toggle');
                    label.style.background = enable ? 'var(--primary-50)' : 'var(--gray-50)';
                    label.style.borderColor = enable ? 'var(--primary-100)' : 'var(--gray-100)';
                });
                document.getElementById('moduleCount').textContent = 
                    document.querySelectorAll('.module-checkbox:checked').length;
            }
            </script>
        </div>

        <!-- Right Column — Branding & Plan -->
        <div>
            <!-- Branding -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-palette me-2"></i> Branding</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">School Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <?php if (!empty($school['logo'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="<?= APP_URL ?>/uploads/logos/<?= htmlspecialchars($school['logo']) ?>" alt="Logo" style="max-height: 60px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Primary Color</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" name="primary_color" value="<?= htmlspecialchars($school['primary_color'] ?? '#4F46E5') ?>" style="width: 50px; height: 36px; border: none; cursor: pointer; border-radius: 6px;">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($school['primary_color'] ?? '#4F46E5') ?>" style="flex: 1;" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Secondary Color</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" name="secondary_color" value="<?= htmlspecialchars($school['secondary_color'] ?? '#7C3AED') ?>" style="width: 50px; height: 36px; border: none; cursor: pointer; border-radius: 6px;">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($school['secondary_color'] ?? '#7C3AED') ?>" style="flex: 1;" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Plan -->

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-credit-card me-2"></i> Subscription Plan</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Select Plan</label>
                            <select class="form-control" name="plan_id" id="planSelect">
                                <option value="">No Plan (Free)</option>
                                <?php foreach ($plans ?? [] as $plan): ?>
                                    <option value="<?= $plan['id'] ?>" 
                                        <?= (($subscription['plan_id'] ?? '') == $plan['id']) ? 'selected' : '' ?>
                                        data-type="<?= $plan['pricing_type'] ?>"
                                        data-desc="<?= htmlspecialchars($plan['description'] ?? '') ?>">
                                        <?= htmlspecialchars($plan['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text" id="planDescription"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Billing Cycle</label>
                            <select class="form-control" name="billing_cycle" id="billingCycle">
                                <option value="monthly" <?= (($subscription['billing_cycle'] ?? '') === 'monthly') ? 'selected' : '' ?>>Monthly</option>
                                <option value="quarterly" <?= (($subscription['billing_cycle'] ?? '') === 'quarterly') ? 'selected' : '' ?>>Quarterly (3 months)</option>
                                <option value="half_yearly" <?= (($subscription['billing_cycle'] ?? '') === 'half_yearly') ? 'selected' : '' ?>>Half-Yearly (6 months)</option>
                                <option value="yearly" <?= (($subscription['billing_cycle'] ?? '') === 'yearly') ? 'selected' : '' ?>>Yearly (12 months)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Pricing Type</label>
                            <select class="form-control" name="pricing_type" id="pricingType">
                                <option value="fixed" <?= (($subscription['pricing_type'] ?? '') === 'fixed') ? 'selected' : '' ?>>Fixed Amount</option>
                                <option value="per_student" <?= (($subscription['pricing_type'] ?? '') === 'per_student') ? 'selected' : '' ?>>Per Student (auto-count active students)</option>
                            </select>
                            <div class="form-text" id="pricingHint">Same amount every billing cycle</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" id="amountLabel">Subscription Amount (₹) <span class="required">*</span></label>
                            <input type="number" class="form-control" name="subscription_amount" id="subscriptionAmount"
                                   step="0.01" min="0" value="<?= htmlspecialchars($subscription['amount'] ?? '0') ?>" placeholder="Enter amount">
                            <div class="form-text" id="amountHint">Enter the amount to charge per billing cycle</div>
                        </div>
                    </div>

                    <!-- Amount Summary -->
                    <div id="amountSummary" style="display: none; background: var(--primary-50); border-radius: 10px; padding: 14px 16px; margin-top: 4px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 11px; font-weight: 600; color: var(--gray-500); text-transform: uppercase;">Subscription</div>
                                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 4px;">
                                    <span style="font-size: 24px; font-weight: 800; color: var(--primary);" id="summaryAmount">₹0</span>
                                    <span style="font-size: 13px; color: var(--gray-500);" id="summaryPeriod">/month</span>
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 12px; color: var(--gray-500);" id="summaryDetail"></div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const planSelect = document.getElementById('planSelect');
                const billingCycle = document.getElementById('billingCycle');
                const pricingType = document.getElementById('pricingType');
                const amountInput = document.getElementById('subscriptionAmount');
                const amountLabel = document.getElementById('amountLabel');
                const amountHint = document.getElementById('amountHint');
                const pricingHint = document.getElementById('pricingHint');
                const planDesc = document.getElementById('planDescription');
                const summary = document.getElementById('amountSummary');

                const cyclePeriods = {
                    monthly: '/month', quarterly: '/quarter', 
                    half_yearly: '/half-year', yearly: '/year'
                };

                function updateUI() {
                    const opt = planSelect.options[planSelect.selectedIndex];
                    const type = pricingType.value;
                    const cycle = billingCycle.value;
                    const amount = parseFloat(amountInput.value) || 0;

                    // Show plan description
                    planDesc.textContent = opt?.dataset.desc || '';

                    // Update labels based on pricing type
                    if (type === 'per_student') {
                        amountLabel.innerHTML = 'Amount Per Student (₹) <span class="required">*</span>';
                        amountHint.textContent = 'This amount × active students = total bill each cycle';
                        pricingHint.textContent = 'Billed based on active student count at billing time';
                    } else {
                        amountLabel.innerHTML = 'Subscription Amount (₹) <span class="required">*</span>';
                        amountHint.textContent = 'This exact amount will be charged per billing cycle';
                        pricingHint.textContent = 'Same amount every billing cycle';
                    }

                    // Auto-set pricing type from plan
                    if (opt?.dataset.type && planSelect.value) {
                        pricingType.value = opt.dataset.type;
                    }

                    // Summary
                    if (amount > 0) {
                        summary.style.display = 'block';
                        document.getElementById('summaryAmount').textContent = '₹' + amount.toLocaleString('en-IN');
                        
                        if (type === 'per_student') {
                            document.getElementById('summaryPeriod').textContent = '/student' + (cyclePeriods[cycle] || '/month');
                            document.getElementById('summaryDetail').innerHTML = 
                                '<i class="bi bi-people"></i> Per active student<br>Billed ' + cycle.replace('_', '-');
                        } else {
                            document.getElementById('summaryPeriod').textContent = cyclePeriods[cycle] || '/month';
                            document.getElementById('summaryDetail').innerHTML = 
                                '<i class="bi bi-tag"></i> Fixed amount<br>Billed ' + cycle.replace('_', '-');
                        }
                    } else {
                        summary.style.display = 'none';
                    }
                }

                planSelect.addEventListener('change', function() {
                    updateUI();
                    // Auto-set pricing type from plan template
                    const opt = this.options[this.selectedIndex];
                    if (opt?.dataset.type && this.value) {
                        pricingType.value = opt.dataset.type;
                        updateUI();
                    }
                });
                billingCycle.addEventListener('change', updateUI);
                pricingType.addEventListener('change', updateUI);
                amountInput.addEventListener('input', updateUI);
            });
            </script>

            <!-- Status (edit only) -->
            <?php if ($isEdit): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" <?= ($school['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <span class="form-check-label fw-600">School is Active</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Update School' : 'Create School' ?>
                </button>
                <a href="<?= APP_URL ?>/schools" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
