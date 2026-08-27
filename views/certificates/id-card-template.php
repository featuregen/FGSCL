<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Cards - <?= htmlspecialchars($school['name'] ?? 'School') ?></title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .id-card {
            width: 340px;
            height: 215px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #cbd5e1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            page-break-inside: avoid;
        }
        .id-header {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            color: #fff;
            padding: 10px 14px;
            text-align: center;
        }
        .id-header .school-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .id-header .tagline {
            font-size: 9px;
            opacity: 0.9;
            margin-top: 2px;
        }
        .id-body {
            display: flex;
            padding: 10px 14px;
            gap: 12px;
            align-items: center;
            flex: 1;
        }
        .student-photo {
            width: 68px;
            height: 78px;
            border-radius: 6px;
            background: #f8fafc;
            border: 2px solid #0f766e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 28px;
            flex-shrink: 0;
            overflow: hidden;
        }
        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .student-info {
            flex: 1;
            font-size: 11px;
            line-height: 1.4;
        }
        .student-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f766e;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-row {
            display: flex;
            margin-bottom: 2px;
        }
        .info-label {
            color: #64748b;
            width: 65px;
            font-weight: 600;
            flex-shrink: 0;
        }
        .info-val {
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .id-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 6px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            color: #64748b;
        }
        .blood-badge {
            background: #ef4444;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 9px;
        }
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0f766e;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 100;
        }
        @media print {
            body { background: transparent; padding: 0; }
            .print-btn { display: none; }
            .id-card { box-shadow: none; border: 1px solid #94a3b8; }
        }
    </style>
</head>
<body>

<div class="grid-container">
    <?php if (empty($students)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
            No students found matching the selected class filter.
        </div>
    <?php else: ?>
        <?php foreach ($students as $st): ?>
            <div class="id-card">
                <div class="id-header">
                    <div class="school-title"><?= htmlspecialchars($school['name'] ?? 'ClassoraGen Academy') ?></div>
                    <div class="tagline">STUDENT IDENTITY CARD</div>
                </div>

                <div class="id-body">
                    <div class="student-photo">
                        <?php if (!empty($st['avatar'])): ?>
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($st['avatar']) ?>" alt="Photo">
                        <?php else: ?>
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <?php endif; ?>
                    </div>

                    <div class="student-info">
                        <div class="student-name"><?= htmlspecialchars($st['full_name']) ?></div>
                        <div class="info-row">
                            <span class="info-label">Adm No:</span>
                            <span class="info-val"><?= htmlspecialchars($st['admission_no'] ?: 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Class:</span>
                            <span class="info-val">Class <?= htmlspecialchars($st['class_name'] ?? '-') ?> - <?= htmlspecialchars($st['section_name'] ?? '') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Roll No:</span>
                            <span class="info-val"><?= htmlspecialchars($st['roll_number'] ?: '-') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Guardian:</span>
                            <span class="info-val"><?= htmlspecialchars($st['father_name'] ?: 'Parent') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Emergency:</span>
                            <span class="info-val"><?= htmlspecialchars($st['emergency_contact'] ?: $st['phone'] ?: 'N/A') ?></span>
                        </div>
                    </div>
                </div>

                <div class="id-footer">
                    <div>
                        <?php if (!empty($st['blood_group'])): ?>
                            <span class="blood-badge"><?= htmlspecialchars($st['blood_group']) ?></span>
                        <?php else: ?>
                            <span>Valid for Current Session</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-weight: 700; color: #0f766e;">Principal Signature</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<button class="print-btn" onclick="window.print()">Print ID Cards</button>

</body>
</html>
