<!-- Staff Profile View -->
<div style="display: grid; grid-template-columns: 300px 1fr; gap: 24px;">
    <!-- Left: Profile Card -->
    <div>
        <div class="card" style="text-align: center;">
            <div class="card-body" style="padding: 32px 24px;">
                <div style="width: 80px; height: 80px; border-radius: 20px; margin: 0 auto 16px;
                            background: linear-gradient(135deg, <?= $staff['user_type'] === 'teacher' ? '#1f9e8b, #0d7377' : '#6366F1, #4338CA' ?>);
                            display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 28px;">
                    <?php
                        $parts = explode(' ', $staff['full_name']);
                        echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    ?>
                </div>
                <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 4px;"><?= htmlspecialchars($staff['full_name']) ?></h3>
                <p style="font-size: 13px; color: var(--gray-500); margin: 0 0 8px;">
                    <?= htmlspecialchars($staff['designation'] ?? ucfirst($staff['user_type'])) ?>
                </p>
                <?php if ($staff['employee_id']): ?>
                    <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= htmlspecialchars($staff['employee_id']) ?></span>
                <?php endif; ?>
                <div style="margin-top: 16px;">
                    <?php if ($staff['is_active']): ?>
                        <span class="badge" style="background: #E0F2F1; color: #1f9e8b; padding: 4px 12px;">Active</span>
                    <?php else: ?>
                        <span class="badge" style="background: #FFEBEE; color: #C62828; padding: 4px 12px;">Inactive</span>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 20px;">
                    <a href="<?= APP_URL ?>/staff/edit/<?= $staff['id'] ?>" class="btn btn-primary w-100" style="margin-bottom: 8px;">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card" style="margin-top: 16px;">
            <div class="card-body">
                <h4 style="font-size: 13px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; margin-bottom: 12px;">Contact</h4>
                <?php
                    $contacts = [
                        ['bi-telephone', $staff['phone'], 'Phone'],
                        ['bi-envelope', $staff['email'], 'Email'],
                        ['bi-geo-alt', trim(($staff['city'] ?? '') . ', ' . ($staff['state'] ?? ''), ', '), 'Location'],
                    ];
                ?>
                <?php foreach ($contacts as [$icon, $val, $lbl]): ?>
                    <?php if ($val): ?>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <i class="bi <?= $icon ?>" style="color: var(--gray-400); font-size: 14px; width: 20px; text-align: center;"></i>
                            <div>
                                <div style="font-size: 12px; color: var(--gray-400);"><?= $lbl ?></div>
                                <div style="font-size: 13px; font-weight: 500;"><?= htmlspecialchars($val) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right: Details -->
    <div>
        <!-- Professional Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-briefcase" style="color: #7B1FA2;"></i> Professional Details</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <?php
                        $details = [
                            ['Department', $staff['department']],
                            ['Qualification', $staff['qualification']],
                            ['Experience', ($staff['experience_years'] ?? 0) . ' years'],
                            ['Date of Joining', $staff['date_of_joining'] ? date('d M Y', strtotime($staff['date_of_joining'])) : null],
                            ['Blood Group', $staff['blood_group']],
                            ['Emergency Contact', $staff['emergency_contact']],
                        ];
                    ?>
                    <?php foreach ($details as [$lbl, $val]): ?>
                        <div>
                            <div style="font-size: 11px; color: var(--gray-400); text-transform: uppercase; margin-bottom: 4px;"><?= $lbl ?></div>
                            <div style="font-size: 14px; font-weight: 500; color: var(--gray-700);"><?= htmlspecialchars($val ?? '—') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Subject Assignments -->
        <?php if (!empty($assignments)): ?>
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-book" style="color: #1565C0;"></i> Subject Assignments</h3>
                <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= count($assignments) ?> assignment<?= count($assignments) > 1 ? 's' : '' ?></span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="data-table">
                    <thead><tr><th>Class</th><th>Subject</th><th>Periods/Week</th></tr></thead>
                    <tbody>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($a['class_name']) ?></td>
                                <td><?= htmlspecialchars($a['subject_name']) ?></td>
                                <td><span style="font-weight: 600;"><?= $a['periods_per_week'] ?></span>/week</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 32px;">
                <i class="bi bi-book" style="font-size: 32px; color: var(--gray-300);"></i>
                <p style="color: var(--gray-400); margin-top: 8px;">No subject assignments yet</p>
                <a href="<?= APP_URL ?>/academic" class="btn btn-sm" style="background: #E3F2FD; color: #1565C0; border: none;">Go to Academic</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Custom Fields -->
        <?php if (!empty($customFields) && !empty($customValues)): ?>
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 style="font-size: 15px; font-weight: 700; margin: 0;">
                    <i class="bi bi-person-lines-fill" style="color: #7B1FA2;"></i> Additional Information
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr);">
                    <?php foreach ($customFields as $cf): 
                        $val = $customValues[$cf['id']] ?? '';
                        if (empty($val)) continue;
                    ?>
                    <div style="padding: 12px 20px; border-bottom: 1px solid var(--gray-50);">
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-400); font-weight: 600; margin-bottom: 2px;"><?= htmlspecialchars($cf['field_label']) ?></div>
                        <div style="font-size: 14px; font-weight: 500;"><?= htmlspecialchars($val) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
