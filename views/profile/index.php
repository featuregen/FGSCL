<!-- Profile Page -->
<div style="max-width: 800px;">

    <!-- Profile Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-person-circle" style="color: var(--primary);"></i> Personal Information
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/profile/update" method="POST" enctype="multipart/form-data">
                <?= Session::csrfField() ?>

                <!-- Avatar -->
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 28px;">
                    <div id="avatarPreview" style="width: 80px; height: 80px; border-radius: 50%; 
                                background: var(--primary); display: flex; align-items: center; justify-content: center; 
                                color: white; font-weight: 700; font-size: 28px; flex-shrink: 0; overflow: hidden;">
                        <?php if (!empty($profile['avatar'])): ?>
                            <img src="<?= APP_URL ?>/uploads/photos/<?= htmlspecialchars($profile['avatar']) ?>" 
                                 alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <?php
                                $initials = '';
                                if (!empty($profile['full_name'])) {
                                    $parts = explode(' ', $profile['full_name']);
                                    $initials = strtoupper(substr($parts[0], 0, 1));
                                    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
                                }
                                echo $initials;
                            ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="avatarInput" class="btn btn-secondary" style="cursor: pointer; font-size: 13px;">
                            <i class="bi bi-camera"></i> Change Photo
                        </label>
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;">
                        <p style="font-size: 11px; color: var(--gray-400); margin-top: 6px;">JPG, PNG or WebP. Max 2MB.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" required>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control" id="username" 
                               value="<?= htmlspecialchars($profile['username'] ?? '') ?>" disabled
                               style="background: var(--gray-50); cursor: not-allowed;">
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Role (read-only) -->
                <div class="form-group" style="margin-top: 4px;">
                    <label class="form-label">Role</label>
                    <div style="padding: 10px 16px; background: var(--gray-50); border-radius: 8px; font-size: 14px; color: var(--gray-600);">
                        <i class="bi bi-shield-check" style="color: var(--primary); margin-right: 6px;"></i>
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $profile['user_type'] ?? 'User'))) ?>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-key" style="color: var(--warning);"></i> Change Password
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= APP_URL ?>/profile/change-password" method="POST">
                <?= Session::csrfField() ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password</label>
                        <div class="input-icon-wrapper" style="position: relative;">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button type="button" class="toggle-pw" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray-400); cursor: pointer;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <div class="input-icon-wrapper" style="position: relative;">
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <button type="button" class="toggle-pw" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray-400); cursor: pointer;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <div class="input-icon-wrapper" style="position: relative;">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="toggle-pw" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray-400); cursor: pointer;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-lock"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
// Avatar preview
document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('avatarPreview').innerHTML = 
                '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
        };
        reader.readAsDataURL(file);
    }
});

// Toggle password visibility
document.querySelectorAll('.toggle-pw').forEach(btn => {
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
</script>
