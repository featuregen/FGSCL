
<div class="content-header">
    <h2 class="content-title">Homework Submissions</h2>
    <a href="<?= APP_URL ?>/homework" class="btn btn-outline">
        <i class="bi-arrow-left"></i> Back to Homework
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3 class="mb-2"><?= htmlspecialchars($homework['title']) ?></h3>
        <p class="text-muted mb-3">
            <strong>Class:</strong> <?= htmlspecialchars($homework['class_name']) ?> <?= $homework['section_name'] ? ' - ' . htmlspecialchars($homework['section_name']) : '' ?> | 
            <strong>Subject:</strong> <?= htmlspecialchars($homework['subject_name']) ?> | 
            <strong>Due Date:</strong> <span class="<?= strtotime($homework['due_date']) < time() ? 'text-danger' : 'text-success' ?>"><?= date('d M, Y', strtotime($homework['due_date'])) ?></span>
        </p>
        <?php if ($homework['description']): ?>
            <div class="mb-3 p-3 bg-light rounded">
                <?= nl2br(htmlspecialchars($homework['description'])) ?>
            </div>
        <?php endif; ?>
        <?php if ($homework['attachment']): ?>
            <a href="<?= APP_URL ?>/public/uploads/homework/<?= $homework['attachment'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi-paperclip"></i> View Attachment
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">Student Submissions</h4>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 100px;">Roll No</th>
                    <th>Student Name</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>Submission</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): ?>
                <tr>
                    <td><?= htmlspecialchars($sub['roll_number']) ?></td>
                    <td><strong><?= htmlspecialchars($sub['full_name']) ?></strong></td>
                    <td><?= $sub['submitted_at'] ? date('d M, h:i A', strtotime($sub['submitted_at'])) : '-' ?></td>
                    <td>
                        <?php
                        $statusClass = [
                            'pending' => 'badge-warning',
                            'submitted' => 'badge-info',
                            'graded' => 'badge-success',
                            'late' => 'badge-danger'
                        ][$sub['status']] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?= $statusClass ?>"><?= ucfirst($sub['status']) ?></span>
                    </td>
                    <td>
                        <?php if ($sub['submission_text']): ?>
                            <button class="btn btn-sm btn-outline" onclick="alert('<?= htmlspecialchars(addslashes($sub['submission_text'])) ?>')">View Text</button>
                        <?php endif; ?>
                        <?php if ($sub['attachment']): ?>
                            <a href="<?= APP_URL ?>/public/uploads/homework/<?= $sub['attachment'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi-paperclip"></i> File
                            </a>
                        <?php endif; ?>
                        <?php if (!$sub['submission_text'] && !$sub['attachment']): ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $sub['marks'] !== null ? $sub['marks'] : '-' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($submissions)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No submissions found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
