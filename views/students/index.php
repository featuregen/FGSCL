<!-- Student List -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p style="color: var(--gray-500); margin: 0;">
            <?= $totalStudents ?> student<?= $totalStudents !== 1 ? 's' : '' ?> enrolled
            <?php if ($currentYear): ?> · <?= htmlspecialchars($currentYear['name']) ?><?php endif; ?>
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= APP_URL ?>/students/import" class="btn btn-secondary" style="display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import from Excel
        </a>
        <a href="<?= APP_URL ?>/students/create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Student
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= APP_URL ?>/students" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" class="form-control" name="search" placeholder="Search name, admission no, phone..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div style="width: 160px;">
                <select class="form-control" name="class_id" id="filterClass" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= $cls['id'] ?>" <?= ($classId ?? '') == $cls['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cls['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($sections)): ?>
                <div style="width: 140px;">
                    <select class="form-control" name="section_id">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= ($sectionId ?? '') == $sec['id'] ? 'selected' : '' ?>>Section <?= htmlspecialchars($sec['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div style="width: 140px;">
                <select class="form-control" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="graduated" <?= ($status ?? '') === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                    <option value="transferred" <?= ($status ?? '') === 'transferred' ? 'selected' : '' ?>>Transferred</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= APP_URL ?>/students" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Clear</a>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($students)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Roll</th>
                            <th>Father</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $stu): ?>
                            <tr>
                                <td><?= (($page - 1) * $perPage) + $i + 1 ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; 
                                                     background: <?= ($stu['gender'] ?? '') === 'female' ? '#F3E5F5' : '#E3F2FD' ?>; 
                                                     display: flex; align-items: center; justify-content: center; 
                                                     color: <?= ($stu['gender'] ?? '') === 'female' ? '#7B1FA2' : '#1565C0' ?>; 
                                                     font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                            <?= strtoupper(substr($stu['full_name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($stu['full_name']) ?></div>
                                            <div style="font-size: 11px; color: var(--gray-400);">
                                                <?= $stu['gender'] ? ucfirst($stu['gender']) : '' ?>
                                                <?= $stu['date_of_birth'] ? ' · ' . date('d M Y', strtotime($stu['date_of_birth'])) : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; font-size: 12px; color: var(--primary);"><?= htmlspecialchars($stu['admission_no']) ?></span>
                                </td>
                                <td>
                                    <span style="font-size: 13px;"><?= htmlspecialchars($stu['class_name'] ?? '—') ?></span>
                                    <?php if ($stu['section_name']): ?>
                                        <span class="badge" style="background: #E3F2FD; color: #1565C0; font-size: 10px;"><?= $stu['section_name'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($stu['roll_number'] ?? '—') ?></td>
                                <td>
                                    <div style="font-size: 13px;"><?= htmlspecialchars($stu['father_name'] ?? '—') ?></div>
                                    <?php if ($stu['father_phone']): ?>
                                        <div style="font-size: 11px; color: var(--gray-400);"><?= htmlspecialchars($stu['father_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 13px;"><?= htmlspecialchars($stu['phone'] ?? '—') ?></td>
                                <td>
                                    <?php
                                        $sColors = [
                                            'active' => ['bg' => '#E0F2F1', 'color' => '#1f9e8b'],
                                            'inactive' => ['bg' => '#F5F5F5', 'color' => '#999'],
                                            'graduated' => ['bg' => '#E3F2FD', 'color' => '#1565C0'],
                                            'transferred' => ['bg' => '#FFF3E0', 'color' => '#E65100'],
                                            'dropped' => ['bg' => '#FFEBEE', 'color' => '#C62828'],
                                        ];
                                        $sc = $sColors[$stu['student_status']] ?? $sColors['active'];
                                    ?>
                                    <span class="badge" style="background: <?= $sc['bg'] ?>; color: <?= $sc['color'] ?>;">
                                        <?= ucfirst($stu['student_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="<?= APP_URL ?>/users/view/<?= $stu['user_id'] ?? $stu['id'] ?>" class="btn btn-sm" 
                                           style="background: #E3F2FD; color: #1565C0; border: none; padding: 4px 8px; border-radius: 6px;" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/students/edit/<?= $stu['id'] ?>" class="btn btn-sm" 
                                           style="background: #E0F2F1; color: #1f9e8b; border: none; padding: 4px 8px; border-radius: 6px;" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="<?= APP_URL ?>/students/delete/<?= $stu['id'] ?>" style="display:inline;" 
                                              onsubmit="return confirm('Delete this student?')">
                                            <button type="submit" class="btn btn-sm" style="background: var(--danger-light); color: var(--danger); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-mortarboard" style="font-size: 48px; color: var(--gray-300); margin-bottom: 12px;"></i>
                <h3>No students found</h3>
                <p>Add your first student to get started</p>
                <a href="<?= APP_URL ?>/students/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Student
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<?php
    // Build base URL with existing filters
    $queryParams = array_filter([
        'search' => $search ?? '',
        'class_id' => $classId ?? '',
        'section_id' => $sectionId ?? '',
        'status' => $status ?? '',
    ]);
    $baseUrl = APP_URL . '/students?' . http_build_query($queryParams);
    $sep = empty($queryParams) ? '?' : '&';
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
    <div style="font-size: 13px; color: var(--gray-500);">
        Showing <?= (($page - 1) * $perPage) + 1 ?>–<?= min($page * $perPage, $totalStudents) ?> of <?= $totalStudents ?> students
    </div>
    <div style="display: flex; gap: 4px; align-items: center;">
        <?php if ($page > 1): ?>
            <a href="<?= $baseUrl . $sep ?>page=<?= $page - 1 ?>" 
               style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--gray-200); color: var(--gray-600); font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                <i class="bi bi-chevron-left" style="font-size: 11px;"></i> Prev
            </a>
        <?php endif; ?>

        <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if ($start > 1) echo '<span style="padding: 6px 8px; font-size: 13px; color: var(--gray-400);">…</span>';
        ?>

        <?php for ($p = $start; $p <= $end; $p++): ?>
            <?php if ($p == $page): ?>
                <span style="padding: 6px 12px; border-radius: 6px; background: #1f9e8b; color: white; font-size: 13px; font-weight: 600;">
                    <?= $p ?>
                </span>
            <?php else: ?>
                <a href="<?= $baseUrl . $sep ?>page=<?= $p ?>" 
                   style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--gray-200); color: var(--gray-600); font-size: 13px; text-decoration: none;">
                    <?= $p ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end < $totalPages) echo '<span style="padding: 6px 8px; font-size: 13px; color: var(--gray-400);">…</span>'; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?= $baseUrl . $sep ?>page=<?= $page + 1 ?>" 
               style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--gray-200); color: var(--gray-600); font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                Next <i class="bi bi-chevron-right" style="font-size: 11px;"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
