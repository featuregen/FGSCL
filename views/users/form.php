<!-- Add/Edit User Form -->
<?php $isEdit = !empty($user); ?>

<form action="<?= APP_URL ?>/users/<?= $isEdit ? 'update/' . $user['id'] : 'store' ?>" method="POST">
    <?= Session::csrfField() ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left Column — User Details -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-person me-2"></i> User Information</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required placeholder="Enter full name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username <span class="required">*</span></label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required placeholder="Username">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 9876543210">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <select class="form-control" name="gender">
                                <option value="">Select</option>
                                <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= $isEdit ? 'New Password' : 'Password' ?> <?= $isEdit ? '' : '<span class="required">*</span>' ?></label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" placeholder="<?= $isEdit ? 'Leave empty to keep current' : 'Min 8 characters' ?>">
                                <button type="button" class="password-toggle">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column — Role & Settings -->
        <div>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-shield me-2"></i> Role & Access</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">User Role <span class="required">*</span></label>
                        <select class="form-control" name="user_type" required>
                            <option value="">Select Role</option>
                            <?php
                            $roleOptions = [
                                'school_admin' => 'School Admin',
                                'principal' => 'Principal',
                                'teacher' => 'Teacher',
                                'staff' => 'Staff',
                                'student' => 'Student',
                                'parent' => 'Parent',
                                'accountant' => 'Accountant',
                                'librarian' => 'Librarian',
                                'transport_manager' => 'Transport Manager',
                            ];
                            if (Session::userRole() === ROLE_SUPER_ADMIN) {
                                $roleOptions = ['super_admin' => 'Super Admin'] + $roleOptions;
                            }
                            foreach ($roleOptions as $val => $label):
                            ?>
                                <option value="<?= $val ?>" <?= ($user['user_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (Session::userRole() === ROLE_SUPER_ADMIN && !empty($schools)): ?>
                        <div class="form-group">
                            <label class="form-label">School</label>
                            <select class="form-control" name="school_id">
                                <option value="">Platform Level (No School)</option>
                                <?php foreach ($schools as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($user['school_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($isEdit): ?>
                        <div class="form-group">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="form-check-label fw-600">Account is Active</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Update User' : 'Create User' ?>
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
