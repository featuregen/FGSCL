<div class="auth-card">
    <div class="auth-header">
        <h2 class="auth-title">Forgot Password?</h2>
        <p class="auth-subtitle">Enter your email and we'll send you an OTP to reset your password</p>
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

    <form action="<?= APP_URL ?>/auth/send-otp" method="POST">
        <?= Session::csrfField() ?>
        <input type="hidden" name="type" value="reset">
        
        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <div class="input-icon-wrapper">
                <i class="bi bi-envelope input-icon"></i>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       placeholder="Enter your registered email"
                       required
                       autofocus>
            </div>
        </div>

        <button type="submit" class="btn-auth">
            <span>
                <i class="bi bi-send"></i>
                Send OTP
            </span>
        </button>
    </form>

    <div class="auth-divider">
        <span>or</span>
    </div>

    <a href="<?= APP_URL ?>/auth/login" class="btn-otp-login">
        <i class="bi bi-arrow-left"></i>
        Back to Login
    </a>
</div>
