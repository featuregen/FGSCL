<div class="content-header">
    <h2 class="content-title">General Settings</h2>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="<?= APP_URL ?>/school-setup/save-general" method="POST">
            <?= Session::csrfField() ?>
            
            <div class="form-group mb-4">
                <label class="form-label" style="font-weight: 600;">Currency Symbol</label>
                <input type="text" class="form-control" name="currency" value="<?= htmlspecialchars($currency) ?>" placeholder="e.g. $, ₹, AED, €, £" required>
                <div class="text-xs text-muted mt-2">This currency symbol will be displayed across the Fees, Payroll, and Accounting modules.</div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: 24px 0;">

            <button type="submit" class="btn btn-primary">
                <i class="bi-check-lg"></i> Save Settings
            </button>
        </form>
    </div>
</div>
