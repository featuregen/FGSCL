<div class="auth-card">
    <div class="auth-header">
        <h2 class="auth-title">Verify OTP</h2>
        <p class="auth-subtitle">Enter the 6-digit code sent to <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
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

    <form action="<?= APP_URL ?>/auth/do-verify-otp" method="POST" id="otpForm">
        <?= Session::csrfField() ?>
        
        <!-- Hidden field to collect all OTP digits -->
        <input type="hidden" name="otp" id="otpHidden">

        <!-- OTP Input Boxes -->
        <div class="otp-input-group">
            <input type="text" class="otp-input" maxlength="1" data-index="0" autofocus inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]">
        </div>

        <button type="submit" class="btn-auth">
            <span>
                <i class="bi bi-check-circle"></i>
                Verify OTP
            </span>
        </button>
    </form>

    <div class="resend-timer">
        <p>Didn't receive the code? <a href="#" id="resendLink">Resend OTP</a> in <span id="timer">60</span>s</p>
    </div>

    <div class="auth-footer">
        <p><a href="<?= APP_URL ?>/auth/login"><i class="bi bi-arrow-left"></i> Back to Login</a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otpHidden');
    const form = document.getElementById('otpForm');

    // Auto-advance on input
    inputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            // Only allow digits
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            updateOtpValue();
        });

        input.addEventListener('keydown', function(e) {
            // Backspace
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                updateOtpValue();
            }
        });

        // Paste handling
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            
            for (let i = 0; i < Math.min(paste.length, inputs.length); i++) {
                inputs[i].value = paste[i];
            }
            
            const nextIndex = Math.min(paste.length, inputs.length - 1);
            inputs[nextIndex].focus();
            updateOtpValue();
        });
    });

    function updateOtpValue() {
        otpHidden.value = Array.from(inputs).map(i => i.value).join('');
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        updateOtpValue();
        if (otpHidden.value.length !== 6) {
            e.preventDefault();
            inputs[0].focus();
        }
    });

    // Resend timer
    let seconds = 60;
    const timerEl = document.getElementById('timer');
    const resendLink = document.getElementById('resendLink');
    
    const countdown = setInterval(() => {
        seconds--;
        timerEl.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(countdown);
            resendLink.classList.add('active');
            resendLink.closest('.resend-timer').innerHTML = '<p>Didn\'t receive the code? <a href="<?= APP_URL ?>/auth/forgot-password" class="active">Resend OTP</a></p>';
        }
    }, 1000);
});
</script>
