<?php
/**
 * Profile Controller
 * View and update user profile
 */

class ProfileController
{
    public function index()
    {
        $user = Session::user();
        if (!$user) {
            Response::redirect('auth/login');
            return;
        }

        // Get full user data from DB
        $userData = Database::fetch(
            "SELECT * FROM users WHERE id = ?",
            [$user['id']]
        );

        Response::view('profile/index', [
            'pageTitle' => 'My Profile',
            'profile' => $userData,
            'breadcrumbs' => [
                ['label' => 'Profile'],
            ],
        ]);
    }

    public function update()
    {
        $user = Session::user();
        if (!$user) {
            Response::redirect('auth/login');
            return;
        }

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
        ];

        // Validate
        if (empty($data['full_name'])) {
            Session::flash('error', 'Full name is required.');
            Response::back();
            return;
        }

        // Check email uniqueness
        if (!empty($data['email'])) {
            $existing = Database::fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$data['email'], $user['id']]
            );
            if ($existing) {
                Session::flash('error', 'Email is already taken.');
                Response::back();
                return;
            }
        }

        // Handle avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $file = $_FILES['avatar'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                Session::flash('error', 'Invalid image format. Use JPG, PNG, GIF or WebP.');
                Response::back();
                return;
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                Session::flash('error', 'Image must be under 2MB.');
                Response::back();
                return;
            }

            $fileName = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
            $uploadDir = PUBLIC_PATH . '/uploads/photos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $data['avatar'] = $fileName;
            }
        }

        Database::update('users', $data, 'id = ?', [$user['id']]);

        // Update session data
        $updatedUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$user['id']]);
        if ($updatedUser) {
            Session::setUser($updatedUser, Session::permissions());
        }

        Session::flash('success', 'Profile updated successfully.');
        Response::redirect('profile');
    }

    public function changePassword()
    {
        $user = Session::user();
        if (!$user) {
            Response::redirect('auth/login');
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Get current hash
        $userData = Database::fetch("SELECT password FROM users WHERE id = ?", [$user['id']]);

        if (!$userData || !password_verify($currentPassword, $userData['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            Response::back();
            return;
        }

        if (strlen($newPassword) < 6) {
            Session::flash('error', 'New password must be at least 6 characters.');
            Response::back();
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New passwords do not match.');
            Response::back();
            return;
        }

        Database::update('users', [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ], 'id = ?', [$user['id']]);

        Session::flash('success', 'Password changed successfully.');
        Response::redirect('profile');
    }
}
