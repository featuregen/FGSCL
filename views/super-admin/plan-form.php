<!-- Plan Create/Edit Form (Super Admin) -->
<?php
$plan = $plan ?? [];
$isEdit = $isEdit ?? false;
$features = $plan['features_list'] ?? [];
?>

<form action="<?= APP_URL ?>/plans/<?= $isEdit ? 'update/' . $plan['id'] : 'store' ?>" method="POST">
    <?= Session::csrfField() ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Left Column — Plan Details -->
        <div>
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-info-circle me-2"></i> Plan Information</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Plan Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="name" required 
                                   value="<?= htmlspecialchars($plan['name'] ?? '') ?>" placeholder="e.g. Starter, Growth, Premium">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug <span class="required">*</span></label>
                            <input type="text" class="form-control" name="slug" required 
                                   value="<?= htmlspecialchars($plan['slug'] ?? '') ?>" placeholder="e.g. starter" 
                                   pattern="[a-z0-9_-]+" title="Lowercase letters, numbers, hyphens, underscores only">
                            <div class="form-text">URL-safe identifier. Lowercase only.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" 
                                  placeholder="Brief description of what this plan includes"><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" 
                                   value="<?= (int)($plan['sort_order'] ?? 0) ?>" min="0">
                            <div class="form-text">Lower number = shows first</div>
                        </div>
                        <div class="form-group" style="display: flex; align-items: end; padding-bottom: 6px;">
                            <label class="form-check" style="gap: 10px;">
                                <input type="checkbox" class="form-check-input" name="is_active" <?= ($plan['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="form-check-label" style="font-weight: 600;">Active Plan</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-currency-rupee me-2"></i> Pricing</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Pricing Type <span class="required">*</span></label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 2px solid var(--gray-200); border-radius: 10px; cursor: pointer; transition: all 0.2s;" class="pricing-type-card" data-type="fixed">
                                <input type="radio" name="pricing_type" value="fixed" 
                                       <?= ($plan['pricing_type'] ?? 'fixed') === 'fixed' ? 'checked' : '' ?>
                                       style="accent-color: var(--primary); width: 18px; height: 18px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: var(--gray-800);">
                                        <i class="bi bi-tag-fill" style="color: var(--success);"></i> Fixed Price
                                    </div>
                                    <div style="font-size: 11px; color: var(--gray-500);">Same amount regardless of student count</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 2px solid var(--gray-200); border-radius: 10px; cursor: pointer; transition: all 0.2s;" class="pricing-type-card" data-type="per_student">
                                <input type="radio" name="pricing_type" value="per_student" 
                                       <?= ($plan['pricing_type'] ?? '') === 'per_student' ? 'checked' : '' ?>
                                       style="accent-color: var(--primary); width: 18px; height: 18px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: var(--gray-800);">
                                        <i class="bi bi-people-fill" style="color: var(--info);"></i> Per Student
                                    </div>
                                    <div style="font-size: 11px; color: var(--gray-500);">Billed based on active student count</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Fixed Pricing Fields -->
                    <div id="fixedPricing">
                        <div style="font-size: 12px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 10px; margin-top: 8px;">
                            <i class="bi bi-tag"></i> Fixed Prices (₹)
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Monthly</label>
                                <input type="number" class="form-control" name="price_monthly" step="0.01" min="0"
                                       value="<?= (float)($plan['price_monthly'] ?? 0) ?>" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quarterly</label>
                                <input type="number" class="form-control" name="price_quarterly" step="0.01" min="0"
                                       value="<?= (float)($plan['price_quarterly'] ?? 0) ?>" placeholder="0.00">
                                <div class="form-text" id="quarterlyHint"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Half-Yearly</label>
                                <input type="number" class="form-control" name="price_half_yearly" step="0.01" min="0"
                                       value="<?= (float)($plan['price_half_yearly'] ?? 0) ?>" placeholder="0.00">
                                <div class="form-text" id="halfYearlyHint"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Yearly</label>
                                <input type="number" class="form-control" name="price_yearly" step="0.01" min="0"
                                       value="<?= (float)($plan['price_yearly'] ?? 0) ?>" placeholder="0.00">
                                <div class="form-text" id="yearlyHint"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Per-Student Pricing Fields -->
                    <div id="perStudentPricing" style="display: none;">
                        <div style="font-size: 12px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 10px; margin-top: 8px;">
                            <i class="bi bi-people"></i> Per-Student Rate (₹)
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Per Student / Month</label>
                                <input type="number" class="form-control" name="price_per_student_monthly" step="0.01" min="0"
                                       value="<?= (float)($plan['price_per_student_monthly'] ?? 0) ?>" placeholder="e.g. 15">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Per Student / Year <span style="color: var(--gray-400); font-weight: 400;">(optional)</span></label>
                                <input type="number" class="form-control" name="price_per_student_yearly" step="0.01" min="0"
                                       value="<?= (float)($plan['price_per_student_yearly'] ?? 0) ?>" placeholder="e.g. 150">
                                <div class="form-text">Leave 0 to auto-calculate (monthly × 12)</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Minimum Students</label>
                                <input type="number" class="form-control" name="min_students" min="0"
                                       value="<?= (int)($plan['min_students'] ?? 0) ?>" placeholder="e.g. 50">
                                <div class="form-text">Min billable students (charged even if school has fewer)</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Max Students Limit</label>
                                <input type="number" class="form-control" name="max_students_limit" min="0"
                                       value="<?= (int)($plan['max_students_limit'] ?? 0) ?>" placeholder="0 = unlimited">
                                <div class="form-text">0 = no limit</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features / Modules -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-grid-3x3-gap me-2"></i> Default Modules</h3>
                </div>
                <div class="card-body">
                    <div class="form-text" style="margin-bottom: 12px;">
                        <i class="bi bi-info-circle"></i> Select modules that are enabled by default for schools on this plan. Super Admin can override per school.
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        <?php foreach ($modules ?? [] as $mod): ?>
                            <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--gray-200); border-radius: 8px; cursor: pointer; font-size: 13px; transition: all 0.2s;">
                                <input type="checkbox" name="features[]" value="<?= htmlspecialchars($mod['slug']) ?>" 
                                       <?= in_array($mod['slug'], $features) ? 'checked' : '' ?>
                                       style="accent-color: var(--primary); width: 16px; height: 16px;">
                                <span style="color: var(--gray-700);"><?= htmlspecialchars($mod['name']) ?></span>
                                <span style="font-size: 10px; color: var(--gray-400); margin-left: auto; text-transform: uppercase;"><?= htmlspecialchars($mod['category']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column — Limits & Preview -->
        <div>
            <!-- Limits -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-sliders me-2"></i> Limits</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Max Students</label>
                        <input type="number" class="form-control" name="max_students" min="0"
                               value="<?= (int)($plan['max_students'] ?? 0) ?>" placeholder="0 = unlimited">
                        <div class="form-text">Max students allowed in this plan (0 = unlimited)</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Staff</label>
                        <input type="number" class="form-control" name="max_staff" min="0"
                               value="<?= (int)($plan['max_staff'] ?? 0) ?>" placeholder="0 = unlimited">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Branches</label>
                        <input type="number" class="form-control" name="max_branches" min="1"
                               value="<?= (int)($plan['max_branches'] ?? 1) ?>" placeholder="1">
                    </div>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="card mb-4" style="position: sticky; top: 80px;">
                <div class="card-header" style="background: linear-gradient(135deg, var(--primary-50), #F5F3FF);">
                    <h3 class="card-title"><i class="bi bi-eye me-2"></i> Preview</h3>
                </div>
                <div class="card-body" id="planPreview">
                    <div style="text-align: center; padding: 16px 0;">
                        <div id="previewName" style="font-size: 20px; font-weight: 800; color: var(--gray-900);">Plan Name</div>
                        <div id="previewType" style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">Fixed Price</div>
                        <div style="margin: 16px 0;">
                            <span id="previewPrice" style="font-size: 32px; font-weight: 800; color: var(--primary);">₹0</span>
                            <span id="previewPeriod" style="font-size: 14px; color: var(--gray-400);">/month</span>
                        </div>
                        <div id="previewLimits" style="display: flex; justify-content: center; gap: 16px; font-size: 12px; color: var(--gray-500);">
                        </div>
                        <div id="previewModules" style="font-size: 12px; color: var(--gray-500); margin-top: 12px;">
                            0 modules selected
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Update Plan' : 'Create Plan' ?>
                    </button>
                    <a href="<?= APP_URL ?>/plans" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricingRadios = document.querySelectorAll('input[name="pricing_type"]');
    const fixedPricing = document.getElementById('fixedPricing');
    const perStudentPricing = document.getElementById('perStudentPricing');
    const monthlyInput = document.querySelector('input[name="price_monthly"]');

    function togglePricing() {
        const type = document.querySelector('input[name="pricing_type"]:checked')?.value || 'fixed';
        fixedPricing.style.display = type === 'fixed' ? 'block' : 'none';
        perStudentPricing.style.display = type === 'per_student' ? 'block' : 'none';

        // Highlight selected card
        document.querySelectorAll('.pricing-type-card').forEach(card => {
            card.style.borderColor = card.dataset.type === type ? 'var(--primary)' : 'var(--gray-200)';
            card.style.background = card.dataset.type === type ? 'var(--primary-50)' : 'transparent';
        });

        updatePreview();
    }

    // Price hints (auto-calculate suggestions)
    monthlyInput?.addEventListener('input', function() {
        const m = parseFloat(this.value) || 0;
        document.getElementById('quarterlyHint').textContent = m > 0 ? '3×monthly = ₹' + (m * 3).toLocaleString('en-IN') : '';
        document.getElementById('halfYearlyHint').textContent = m > 0 ? '6×monthly = ₹' + (m * 6).toLocaleString('en-IN') : '';
        document.getElementById('yearlyHint').textContent = m > 0 ? '12×monthly = ₹' + (m * 12).toLocaleString('en-IN') : '';
        updatePreview();
    });

    // Live preview
    function updatePreview() {
        const name = document.querySelector('input[name="name"]')?.value || 'Plan Name';
        const type = document.querySelector('input[name="pricing_type"]:checked')?.value || 'fixed';
        const maxStudents = document.querySelector('input[name="max_students"]')?.value || '∞';
        const maxStaff = document.querySelector('input[name="max_staff"]')?.value || '∞';
        const maxBranches = document.querySelector('input[name="max_branches"]')?.value || '1';
        const moduleCount = document.querySelectorAll('input[name="features[]"]:checked').length;

        document.getElementById('previewName').textContent = name;
        document.getElementById('previewType').textContent = type === 'per_student' ? 'Per Student' : 'Fixed Price';

        if (type === 'per_student') {
            const psm = document.querySelector('input[name="price_per_student_monthly"]')?.value || '0';
            document.getElementById('previewPrice').textContent = '₹' + parseFloat(psm).toLocaleString('en-IN');
            document.getElementById('previewPeriod').textContent = '/student/month';
        } else {
            const monthly = document.querySelector('input[name="price_monthly"]')?.value || '0';
            document.getElementById('previewPrice').textContent = '₹' + parseFloat(monthly).toLocaleString('en-IN');
            document.getElementById('previewPeriod').textContent = '/month';
        }

        document.getElementById('previewLimits').innerHTML = 
            '<span><i class="bi bi-mortarboard"></i> ' + (maxStudents == 0 ? '∞' : maxStudents) + ' students</span>' +
            '<span><i class="bi bi-person-badge"></i> ' + (maxStaff == 0 ? '∞' : maxStaff) + ' staff</span>' +
            '<span><i class="bi bi-building"></i> ' + maxBranches + ' branch</span>';

        document.getElementById('previewModules').textContent = moduleCount + ' modules selected';
    }

    pricingRadios.forEach(r => r.addEventListener('change', togglePricing));

    // Update preview on any input change
    document.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    // Auto-generate slug from name
    document.querySelector('input[name="name"]')?.addEventListener('input', function() {
        const slugInput = document.querySelector('input[name="slug"]');
        if (slugInput && !slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        }
    });
    document.querySelector('input[name="slug"]')?.addEventListener('input', function() {
        this.dataset.manual = '1';
    });

    togglePricing();
    if (monthlyInput?.value) monthlyInput.dispatchEvent(new Event('input'));
});
</script>
