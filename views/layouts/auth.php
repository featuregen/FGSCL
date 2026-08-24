<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'ClassoraGen - Complete School Management System' ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Login') ?> — <?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/images/favicon.png">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/images/favicon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/auth.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <!-- Floating Background Shapes -->
        <div class="floating-shapes">
            <div class="floating-shape"></div>
            <div class="floating-shape"></div>
            <div class="floating-shape"></div>
        </div>
        
        <!-- Left Branding Panel -->
        <div class="auth-branding">
            <div class="brand-content">
                <div class="brand-logo-lg">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1 class="brand-title"><?= APP_NAME ?></h1>
                <p class="brand-subtitle">
                    Complete school management platform. Streamline admissions, 
                    attendance, fees, exams, and more — all in one place.
                </p>
                <ul class="feature-list">
                    <li class="feature-item">
                        <span class="feature-icon"><i class="bi bi-people-fill"></i></span>
                        Multi-role access for Admin, Teachers, Students & Parents
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="bi bi-graph-up-arrow"></i></span>
                        Real-time analytics & comprehensive dashboards
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="bi bi-phone-fill"></i></span>
                        Mobile app for on-the-go management
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon"><i class="bi bi-shield-check"></i></span>
                        Secure, reliable, and always available
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Right Form Panel -->
        <div class="auth-form-panel">
            <?= $content ?>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
