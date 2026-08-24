<!-- School Setup Dashboard -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px;">
    <!-- Academic Years -->
    <a href="<?= APP_URL ?>/school-setup/academic-years" class="stat-card" style="text-decoration: none; cursor: pointer; transition: all 0.2s;">
        <div class="stat-icon" style="background: #E0F2F1; color: #1f9e8b;">
            <i class="bi bi-calendar3"></i>
        </div>
        <div>
            <div class="stat-label">Academic Years</div>
            <div class="stat-value"><?= $stats['academic_years'] ?></div>
        </div>
    </a>

    <!-- Classes -->
    <a href="<?= APP_URL ?>/school-setup/classes" class="stat-card" style="text-decoration: none; cursor: pointer; transition: all 0.2s;">
        <div class="stat-icon" style="background: #E3F2FD; color: #1565C0;">
            <i class="bi bi-building"></i>
        </div>
        <div>
            <div class="stat-label">Classes</div>
            <div class="stat-value"><?= $stats['classes'] ?></div>
        </div>
    </a>

    <!-- Sections -->
    <a href="<?= APP_URL ?>/school-setup/classes" class="stat-card" style="text-decoration: none; cursor: pointer; transition: all 0.2s;">
        <div class="stat-icon" style="background: #FFF3E0; color: #E65100;">
            <i class="bi bi-grid-3x2-gap"></i>
        </div>
        <div>
            <div class="stat-label">Sections</div>
            <div class="stat-value"><?= $stats['sections'] ?></div>
        </div>
    </a>

    <!-- Subjects -->
    <a href="<?= APP_URL ?>/school-setup/subjects" class="stat-card" style="text-decoration: none; cursor: pointer; transition: all 0.2s;">
        <div class="stat-icon" style="background: #F3E5F5; color: #7B1FA2;">
            <i class="bi bi-book"></i>
        </div>
        <div>
            <div class="stat-label">Subjects</div>
            <div class="stat-value"><?= $stats['subjects'] ?></div>
        </div>
    </a>

    <!-- General Settings -->
    <a href="<?= APP_URL ?>/school-setup/general" class="stat-card" style="text-decoration: none; cursor: pointer; transition: all 0.2s;">
        <div class="stat-icon" style="background: #E8F5E9; color: #388E3C;">
            <i class="bi bi-sliders"></i>
        </div>
        <div>
            <div class="stat-label">General Settings</div>
            <div class="stat-value">Manage</div>
        </div>
    </a>
</div>

<?php if (!$currentYear): ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 60px 40px;">
            <i class="bi bi-calendar-plus" style="font-size: 48px; color: var(--gray-300); margin-bottom: 16px;"></i>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Get Started</h3>
            <p style="color: var(--gray-500); margin-bottom: 24px;">Create your first academic year to begin setting up classes and subjects.</p>
            <a href="<?= APP_URL ?>/school-setup/academic-years" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create Academic Year
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body" style="padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">
                <i class="bi bi-calendar-check" style="color: var(--primary);"></i>
                Current Academic Year: <?= htmlspecialchars($currentYear['name']) ?>
            </h3>
            <p style="font-size: 13px; color: var(--gray-500); margin: 0;">
                <?= date('d M Y', strtotime($currentYear['start_date'])) ?> — <?= date('d M Y', strtotime($currentYear['end_date'])) ?>
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 20px;">
        <a href="<?= APP_URL ?>/school-setup/classes" class="card" style="text-decoration: none;">
            <div class="card-body" style="padding: 20px; text-align: center;">
                <i class="bi bi-building" style="font-size: 28px; color: #1565C0; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px;">Manage Classes</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin: 0;">Add classes and sections</p>
            </div>
        </a>
        <a href="<?= APP_URL ?>/school-setup/subjects" class="card" style="text-decoration: none;">
            <div class="card-body" style="padding: 20px; text-align: center;">
                <i class="bi bi-book" style="font-size: 28px; color: #7B1FA2; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px;">Manage Subjects</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin: 0;">Add subjects and assign to classes</p>
            </div>
        </a>
        <a href="<?= APP_URL ?>/school-setup/academic-years" class="card" style="text-decoration: none;">
            <div class="card-body" style="padding: 20px; text-align: center;">
                <i class="bi bi-calendar3" style="font-size: 28px; color: #1f9e8b; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px;">Academic Years</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin: 0;">Manage year periods</p>
            </div>
        </a>
        <a href="<?= APP_URL ?>/school-setup/form-settings" class="card" style="text-decoration: none;">
            <div class="card-body" style="padding: 20px; text-align: center;">
                <i class="bi bi-sliders" style="font-size: 28px; color: #E65100; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px;">Form Settings</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin: 0;">Admission prefix & custom fields</p>
            </div>
        </a>
        <a href="<?= APP_URL ?>/school-setup/departments" class="card" style="text-decoration: none;">
            <div class="card-body" style="padding: 20px; text-align: center;">
                <i class="bi bi-diagram-3" style="font-size: 28px; color: #00695C; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px;">Departments</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin: 0;">Configure staff departments</p>
            </div>
        </a>
        <a href="<?= APP_URL ?>/school-setup/designations" class="card" style="text-decoration: none;">
            <div class="card-body" style="padding: 20px; text-align: center;">
                <i class="bi bi-person-badge" style="font-size: 28px; color: #AD1457; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px;">Designations</h4>
                <p style="font-size: 12px; color: var(--gray-400); margin: 0;">Teaching & non-teaching roles</p>
            </div>
        </a>
    </div>
<?php endif; ?>
