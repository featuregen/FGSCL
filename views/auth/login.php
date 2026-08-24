<div class="auth-card">
    <div class="auth-header">
        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your account to continue</p>
    </div>

    <!-- Flash Messages -->
    <?php $flashMessages = Session::getFlash(); ?>
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
            <span><?= $flash['message'] ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endforeach; ?>

    <form action="<?= APP_URL ?>/auth/do-login" method="POST" id="loginForm">
        <?= Session::csrfField() ?>
        
        <!-- Email or Username -->
        <div class="form-group">
            <label class="form-label" for="login">Email or Username</label>
            <div class="input-icon-wrapper">
                <i class="bi bi-person input-icon"></i>
                <input type="text" 
                       class="form-control" 
                       id="login" 
                       name="login" 
                       placeholder="Enter email or username"
                       value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                       required
                       autocomplete="username"
                       autofocus>
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-icon-wrapper">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Enter your password"
                       required
                       autocomplete="current-password">
                <button type="button" class="toggle-password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <!-- Remember & Forgot -->
        <div class="auth-options">
            <label class="form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <span class="form-check-label">Remember me</span>
            </label>
            <a href="<?= APP_URL ?>/auth/forgot-password" class="forgot-link">Forgot Password?</a>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-auth" id="loginBtn">
            <span>
                <i class="bi bi-box-arrow-in-right"></i>
                Sign In
            </span>
        </button>
    </form>

    <!-- Divider -->
    <div class="auth-divider">
        <span>or</span>
    </div>

    <!-- OTP Login -->
    <a href="<?= APP_URL ?>/auth/forgot-password" class="btn-otp-login">
        <i class="bi bi-phone"></i>
        Login with Email OTP
    </a>

    <div class="auth-footer">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
    </div>
</div>
