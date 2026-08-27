<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress Report Card - <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .report-card {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 36px;
            border: 3px solid #0f766e;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
        }
        .school-header {
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .school-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 6px 0;
        }
        .school-subtitle {
            font-size: 13px;
            color: #475569;
            margin: 2px 0;
        }
        .exam-banner {
            background: #0f766e;
            color: #fff;
            text-align: center;
            padding: 8px 16px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .student-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            background: #f8fafc;
            padding: 14px 18px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
        }
        .info-val {
            font-weight: 700;
            color: #1e293b;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .marks-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
        }
        .marks-table td {
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
        }
        .marks-table tr.total-row {
            background: #f8fafc;
            font-weight: 800;
            font-size: 14px;
        }
        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .sum-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .sum-box .sum-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .sum-box .sum-val {
            font-size: 18px;
            font-weight: 800;
            color: #0f766e;
            margin-top: 4px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 50px;
            padding: 0 10px;
        }
        .sig-block {
            text-align: center;
            width: 160px;
            border-top: 1px solid #64748b;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
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
            .report-card { box-shadow: none; border: 2px solid #000; padding: 20px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="report-card">
    <div class="school-header">
        <h1 class="school-title"><?= htmlspecialchars($school['name'] ?? 'ClassoraGen Academy') ?></h1>
        <div class="school-subtitle"><?= htmlspecialchars($school['address'] ?? '') ?></div>
        <div class="school-subtitle">Affiliation / Code: <?= htmlspecialchars($school['code'] ?? 'SCH-01') ?> &bull; Phone: <?= htmlspecialchars($school['phone'] ?? '') ?></div>
    </div>

    <div class="exam-banner">
        <?= htmlspecialchars($exam['name']) ?> &mdash; STUDENT PROGRESS REPORT CARD
    </div>

    <div class="student-info-grid">
        <div class="info-item">
            <span class="info-label">Student Name:</span>
            <span class="info-val"><?= htmlspecialchars($student['full_name']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Class & Section:</span>
            <span class="info-val">Class <?= htmlspecialchars($student['class_name'] ?? '-') ?> - <?= htmlspecialchars($student['section_name'] ?? '') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Admission No:</span>
            <span class="info-val"><?= htmlspecialchars($student['admission_no'] ?: 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Roll Number:</span>
            <span class="info-val"><?= htmlspecialchars($student['roll_number'] ?: 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Father / Guardian:</span>
            <span class="info-val"><?= htmlspecialchars($student['father_name'] ?: 'Parent') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Date of Birth:</span>
            <span class="info-val"><?= $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A' ?></span>
        </div>
    </div>

    <!-- Marks Table -->
    <table class="marks-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Subject Name</th>
                <th style="text-align: center; width: 90px;">Max Marks</th>
                <th style="text-align: center; width: 90px;">Pass Marks</th>
                <th style="text-align: center; width: 110px;">Marks Scored</th>
                <th style="text-align: center; width: 80px;">Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalMax = 0;
            $totalScored = 0;
            $allPassed = true;
            ?>
            <?php if (empty($marks)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; color: #94a3b8;">
                        Marks have not been entered yet for this student in this examination.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($marks as $idx => $m): ?>
                    <?php 
                    $max = $m['max_marks'] ?: 100;
                    $pass = $m['pass_marks'] ?: 35;
                    $scored = $m['marks_obtained'] ?? 0;
                    $totalMax += $max;
                    $totalScored += $scored;
                    if ($scored < $pass) { $allPassed = false; }
                    ?>
                    <tr>
                        <td style="text-align: center; color: #64748b;"><?= $idx + 1 ?></td>
                        <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($m['subject_name']) ?></td>
                        <td style="text-align: center;"><?= $max ?></td>
                        <td style="text-align: center; color: #64748b;"><?= $pass ?></td>
                        <td style="text-align: center; font-weight: 800; color: <?= $scored >= $pass ? '#0f766e' : '#ef4444' ?>;">
                            <?= $scored ?>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            <?= htmlspecialchars($m['grade'] ?: ($scored >= 90 ? 'A+' : ($scored >= 75 ? 'A' : ($scored >= 60 ? 'B' : ($scored >= 35 ? 'C' : 'F'))))) ?>
                        </td>
                        <td style="color: #64748b; font-size: 12px;"><?= htmlspecialchars($m['remarks'] ?: 'Satisfactory') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php 
                $percentage = $totalMax > 0 ? round(($totalScored / $totalMax) * 100, 1) : 0;
                ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-right: 16px;">GRAND TOTAL:</td>
                    <td style="text-align: center;"><?= $totalMax ?></td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: center; color: #0f766e;"><?= $totalScored ?></td>
                    <td style="text-align: center;"><?= $percentage >= 75 ? 'A' : ($percentage >= 60 ? 'B' : ($percentage >= 35 ? 'C' : 'F')) ?></td>
                    <td style="font-weight: 700; color: <?= $allPassed ? '#059669' : '#dc2626' ?>;">
                        <?= $allPassed ? 'PASSED' : 'NEEDS IMPROVEMENT' ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary KPI Boxes -->
    <div class="summary-boxes">
        <div class="sum-box">
            <div class="sum-label">Percentage</div>
            <div class="sum-val"><?= $percentage ?? 0 ?>%</div>
        </div>
        <div class="sum-box">
            <div class="sum-label">Result Status</div>
            <div class="sum-val" style="font-size: 15px; color: <?= ($allPassed ?? false) ? '#059669' : '#dc2626' ?>;">
                <?= ($allPassed ?? false) ? 'PASSED' : 'FAIL / RETEST' ?>
            </div>
        </div>
        <div class="sum-box">
            <div class="sum-label">Attendance</div>
            <?php 
            $totDays = $attendance['total_days'] ?? 0;
            $presDays = $attendance['present_days'] ?? 0;
            $attPct = $totDays > 0 ? round(($presDays / $totDays) * 100) : 100;
            ?>
            <div class="sum-val"><?= $attPct ?>%</div>
        </div>
        <div class="sum-box">
            <div class="sum-label">Conduct</div>
            <div class="sum-val" style="font-size: 15px; color: #0f766e;">EXCELLENT</div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="sig-block">Class Teacher</div>
        <div class="sig-block">Parent / Guardian</div>
        <div class="sig-block">Principal Signature & Seal</div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">Print Marksheet</button>

</body>
</html>
