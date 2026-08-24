<div class="auth-card">
    <div class="auth-header">
        <h2 class="auth-title">Reset Password</h2>
        <p class="auth-subtitle">Create a new password for <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
    </div>

    <!-- Flash Messages -->
    <?php $flashMessages = Session::getFlash(); ?>
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <span><?= $flash['message'] ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endforeach; ?>

    <form action="<?= APP_URL ?>/auth/do-reset-password" method="POST">
        <?= Session::csrfField() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

        <div class="form-group">
            <label class="form-label" for="password">New Password</label>
            <div class="input-icon-wrapper">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Minimum 8 characters"
                       required 
                       minlength="8"
                       autofocus>
                <button type="button" class="toggle-password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="input-icon-wrapper">
                <i class="bi bi-lock-fill input-icon"></i>
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Re-enter your password"
                       required 
                       minlength="8">
                <button type="button" class="toggle-password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <!-- Password Strength Meter -->
        <div class="password-strength mb-4" style="margin-bottom: 20px;">
            <div style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s; border-radius: 2px;"></div>
            </div>
            <p id="strengthText" style="font-size: 11px; color: var(--gray-500); margin-top: 6px;"></p>
        </div>

        <button type="submit" class="btn-auth">
            <span>
                <i class="bi bi-check-lg"></i>
                Reset Password
            </span>
        </button>
    </form>
</div>

<script>
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let strength = 0;

    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    if (/[^A-Za-z0-9]/.test(password)) strength += 25;

    bar.style.width = strength + '%';
    
    if (strength <= 25) { bar.style.background = '#EF4444'; text.textContent = 'Weak password'; text.style.color = '#EF4444'; }
    else if (strength <= 50) { bar.style.background = '#F59E0B'; text.textContent = 'Fair password'; text.style.color = '#F59E0B'; }
    else if (strength <= 75) { bar.style.background = '#3B82F6'; text.textContent = 'Good password'; text.style.color = '#3B82F6'; }
    else { bar.style.background = '#10B981'; text.textContent = 'Strong password'; text.style.color = '#10B981'; }

    if (!password) { bar.style.width = '0%'; text.textContent = ''; }
});
</script>
