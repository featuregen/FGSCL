<?php
/**
 * Auth Controller
 * Handles login, logout, OTP, forgot password
 */

require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Services/EmailService.php';

class AuthController
{
    /**
     * Show login page
     */
    public function login(): void
    {
        // Already logged in? Redirect to dashboard
        if (Session::isLoggedIn()) {
            Response::redirect('dashboard');
        }
        
        Response::view('auth.login', [
            'pageTitle' => 'Login',
        ], 'auth');
    }

    /**
     * Process login
     */
    public function doLogin(): void
    {
        $login = trim(Validator::input('login'));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate input
        if (empty($login) || empty($password)) {
            Session::flash('error', 'Email/Username and Password are required.');
            Response::redirect('auth/login');
        }

        // Find user by email or username
        $user = User::findByLogin($login);

        if (!$user) {
            Session::flash('error', 'Invalid credentials.');
            Response::redirect('auth/login');
        }

        // Check if account is active
        if (!$user['is_active']) {
            Session::flash('error', 'Your account has been deactivated. Contact administrator.');
            Response::redirect('auth/login');
        }

        // Check if account is locked
        if (User::isLocked($user)) {
            $lockedUntil = date('h:i A', strtotime($user['locked_until']));
            Session::flash('error', "Account locked due to too many failed attempts. Try again after {$lockedUntil}.");
            Response::redirect('auth/login');
        }

        // Verify password
        if (!User::verifyPassword($password, $user['password'])) {
            User::recordLogin($user['id'], false);
            Session::flash('error', 'Invalid credentials.');
            Response::redirect('auth/login');
        }

        // Check school subscription (for non-super-admin)
        if ($user['user_type'] !== 'super_admin' && $user['school_id']) {
            $school = Database::fetch("SELECT * FROM schools WHERE id = ? AND is_active = 1", [$user['school_id']]);
            if (!$school) {
                Session::flash('error', 'Your school account has been deactivated. Contact support.');
                Response::redirect('auth/login');
            }

            // Check subscription
            $subscription = Database::fetch(
                "SELECT * FROM subscriptions WHERE school_id = ? AND status = 'active' AND end_date >= CURDATE() ORDER BY end_date DESC LIMIT 1",
                [$user['school_id']]
            );
            if (!$subscription) {
                Session::flash('warning', 'Your school subscription has expired. Some features may be limited.');
            }
        }

        // Successful login
        $permissions = User::getPermissions($user['id']);
        
        // Get full user data with school info
        $userData = User::findWithSchool($user['id']);
        
        Session::setUser($userData, $permissions);
        User::recordLogin($user['id'], true);

        // Log activity
        require_once APP_PATH . '/Middleware/ActivityLogger.php';
        ActivityLogger::log('auth', 'login', $userData['full_name'] . ' logged in');

        // Force password change if required
        if ($user['force_password_change']) {
            Session::flash('warning', 'Please change your password.');
            Response::redirect('profile/change-password');
        }

        // Redirect based on role
        $dashboardRoutes = [
            'super_admin'       => 'dashboard',
            'school_admin'      => 'dashboard',
            'principal'         => 'dashboard',
            'teacher'           => 'dashboard',
            'student'           => 'dashboard',
            'parent_user'       => 'dashboard',
            'accountant'        => 'fees',
            'librarian'         => 'library',
            'transport_manager' => 'transport',
        ];

        $role = $userData['role_slug'] ?? $userData['user_type'];
        $redirect = $dashboardRoutes[$role] ?? 'dashboard';
        
        Session::flash('success', 'Welcome back, ' . $userData['full_name'] . '!');
        Response::redirect($redirect);
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if (Session::isLoggedIn()) {
            require_once APP_PATH . '/Middleware/ActivityLogger.php';
            ActivityLogger::log('auth', 'logout', 'User logged out');
        }
        
        Session::destroy();
        Session::start();
        Session::flash('success', 'You have been logged out successfully.');
        Response::redirect('auth/login');
    }

    /**
     * Show forgot password page
     */
    public function forgotPassword(): void
    {
        Response::view('auth.forgot-password', [
            'pageTitle' => 'Forgot Password',
        ], 'auth');
    }

    /**
     * Send OTP for password reset / OTP login
     */
    public function sendOtp(): void
    {
        $email = Validator::input('email');
        $type = Validator::input('type', 'reset'); // 'reset' or 'login'

        $validator = Validator::make($_POST)
            ->required('email', 'Email')
            ->email('email');

        if ($validator->fails()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                Response::error($validator->allErrors()[0]);
            }
            Session::flash('error', $validator->allErrors()[0]);
            Response::back();
        }

        $user = User::findByEmail($email);

        if (!$user) {
            // Don't reveal if email exists
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                Response::success('If this email is registered, an OTP has been sent.');
            }
            Session::flash('success', 'If this email is registered, an OTP has been sent.');
            Response::back();
        }

        // Generate OTP
        $otp = User::generateOtp($user['id']);

        // Send OTP via email
        $emailService = new EmailService();
        $subject = $type === 'login' ? 'Your Login OTP' : 'Password Reset OTP';
        $body = "
            <h2>{$subject}</h2>
            <p>Hello {$user['full_name']},</p>
            <p>Your One-Time Password (OTP) is:</p>
            <div style='text-align:center; margin: 20px 0;'>
                <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #4F46E5; 
                             background: #EEF2FF; padding: 15px 30px; border-radius: 10px; display: inline-block;'>{$otp}</span>
            </div>
            <p>This OTP is valid for <strong>10 minutes</strong>. Do not share it with anyone.</p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        try {
            $emailService->send($user['email'], $subject, $body);
        } catch (Exception $e) {
            if (APP_DEBUG) {
                // In development, show OTP directly
                Session::flash('info', "DEV MODE — OTP: {$otp}");
            }
        }

        // Store email in session for verification page
        Session::set('otp_email', $email);
        Session::set('otp_type', $type);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::success('OTP sent successfully to your email.');
        }

        Session::flash('success', 'OTP has been sent to your email address.');
        Response::redirect('auth/verify-otp');
    }

    /**
     * Show OTP verification page
     */
    public function verifyOtp(): void
    {
        $email = Session::get('otp_email');
        $type = Session::get('otp_type', 'login');

        if (!$email) {
            Response::redirect('auth/login');
        }

        Response::view('auth.otp-verify', [
            'pageTitle' => 'Verify OTP',
            'email'     => $email,
            'type'      => $type,
        ], 'auth');
    }

    /**
     * Process OTP verification
     */
    public function doVerifyOtp(): void
    {
        $email = Session::get('otp_email');
        $otp = Validator::input('otp');
        $type = Session::get('otp_type', 'login');

        if (!$email || !$otp) {
            Session::flash('error', 'Invalid request.');
            Response::redirect('auth/login');
        }

        $user = User::verifyOtp($email, $otp);

        if (!$user) {
            Session::flash('error', 'Invalid or expired OTP. Please try again.');
            Response::redirect('auth/verify-otp');
        }

        if ($type === 'login') {
            // OTP Login — set session
            $permissions = User::getPermissions($user['id']);
            $userData = User::findWithSchool($user['id']);
            Session::setUser($userData, $permissions);
            User::recordLogin($user['id'], true);
            
            Session::remove('otp_email');
            Session::remove('otp_type');
            
            Session::flash('success', 'Welcome back, ' . $userData['full_name'] . '!');
            Response::redirect('dashboard');
        } else {
            // Password reset — redirect to reset form
            $token = User::createPasswordResetToken($user['id']);
            Session::remove('otp_email');
            Session::remove('otp_type');
            
            Response::redirect('auth/reset-password?token=' . $token);
        }
    }

    /**
     * Show password reset form
     */
    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        
        if (!$token) {
            Session::flash('error', 'Invalid reset link.');
            Response::redirect('auth/forgot-password');
        }

        $reset = User::verifyResetToken($token);
        if (!$reset) {
            Session::flash('error', 'This reset link has expired or is invalid.');
            Response::redirect('auth/forgot-password');
        }

        Response::view('auth.reset-password', [
            'pageTitle' => 'Reset Password',
            'token'     => $token,
            'email'     => $reset['email'],
        ], 'auth');
    }

    /**
     * Process password reset
     */
    public function doResetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';

        $validator = Validator::make($_POST)
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password')
            ->matches('password', 'password_confirmation', 'Password', 'Confirm Password');

        if ($validator->fails()) {
            Session::flash('error', $validator->allErrors()[0]);
            Response::redirect('auth/reset-password?token=' . $token);
        }

        $reset = User::verifyResetToken($token);
        if (!$reset) {
            Session::flash('error', 'This reset link has expired.');
            Response::redirect('auth/forgot-password');
        }

        // Update password
        User::update($reset['user_id'], ['password' => $password]);
        
        // Mark token as used
        Database::update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$reset['id']]);

        Session::flash('success', 'Password reset successfully! Please login with your new password.');
        Response::redirect('auth/login');
    }

    /**
     * Show OTP login page
     */
    public function otpLogin(): void
    {
        Response::view('auth.otp-login', [
            'pageTitle' => 'OTP Login',
        ], 'auth');
    }
}
