<!-- Academic Hub -->
<?php
    $totalClasses = count($classes);
    $totalSections = array_sum(array_column($classes, 'section_count'));
    $totalStudentsAll = array_sum(array_column($classes, 'student_count'));
?>

<!-- Stats Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="bi bi-calendar-range"></i></div>
        <div class="stat-value"><?= htmlspecialchars($currentYear['name'] ?? 'Not Set') ?></div>
        <div class="stat-label">Current Academic Year</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #E3F2FD; color: #1565C0;"><i class="bi bi-building"></i></div>
        <div class="stat-value"><?= $totalClasses ?></div>
        <div class="stat-label">Classes</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #FFF3E0; color: #E65100;"><i class="bi bi-grid-3x2"></i></div>
        <div class="stat-value"><?= $totalSections ?></div>
        <div class="stat-label">Sections</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #F3E5F5; color: #7B1FA2;"><i class="bi bi-book"></i></div>
        <div class="stat-value"><?= $totalSubjects ?></div>
        <div class="stat-label">Subjects</div>
    </div>
</div>

<!-- Quick Actions -->
<div style="display: flex; gap: 10px; margin-bottom: 24px;">
    <a href="<?= APP_URL ?>/academic/subjects" class="btn btn-primary">
        <i class="bi bi-book"></i> Manage Subjects
    </a>
    <a href="<?= APP_URL ?>/school-setup" class="btn btn-secondary">
        <i class="bi bi-gear"></i> School Setup
    </a>
</div>

<!-- Classes Overview -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-building" style="color: #1565C0;"></i> Classes Overview
            <?php if ($currentYear): ?>
                <span style="font-size: 12px; font-weight: 400; color: var(--gray-400); margin-left: 8px;"><?= htmlspecialchars($currentYear['name']) ?></span>
            <?php endif; ?>
        </h3>
        <span style="font-size: 12px; color: var(--gray-400);"><?= $totalStudentsAll ?> total students</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($classes)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Sections</th>
                            <th>Students</th>
                            <th>Subjects</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $cls): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($cls['name']) ?></td>
                                <td>
                                    <span class="badge" style="background: #E3F2FD; color: #1565C0;"><?= $cls['section_count'] ?> section<?= $cls['section_count'] != 1 ? 's' : '' ?></span>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #1f9e8b;"><?= $cls['student_count'] ?></span>
                                    <span style="font-size: 11px; color: var(--gray-400);"> students</span>
                                </td>
                                <td>
                                    <?php if ($cls['subject_count'] > 0): ?>
                                        <span class="badge" style="background: #F3E5F5; color: #7B1FA2;"><?= $cls['subject_count'] ?> subject<?= $cls['subject_count'] != 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--gray-400);">No subjects assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/academic/class-subjects/<?= $cls['id'] ?>" 
                                       class="btn btn-sm" style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 10px; border-radius: 6px; font-size: 12px;">
                                        <i class="bi bi-pencil"></i> Subjects
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 40px;">
                <i class="bi bi-building" style="font-size: 40px; color: var(--gray-300); margin-bottom: 8px;"></i>
                <h3>No classes set up</h3>
                <p>Go to School Setup to create classes for this academic year</p>
                <a href="<?= APP_URL ?>/school-setup" class="btn btn-primary">
                    <i class="bi bi-gear"></i> Go to School Setup
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
