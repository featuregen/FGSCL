<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transfer Certificate - <?= htmlspecialchars($cert['tc_no']) ?></title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .tc-frame {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border: 4px double #0f766e;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
        }
        .header-section {
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .school-name {
            font-size: 26px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 6px 0;
        }
        .school-meta {
            font-size: 14px;
            color: #475569;
            margin: 2px 0;
        }
        .tc-title {
            text-align: center;
            margin: 20px 0;
        }
        .tc-title span {
            background: #0f766e;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 24px;
            border-radius: 4px;
            letter-spacing: 1px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .tc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .tc-table td {
            padding: 10px 8px;
            border-bottom: 1px dotted #cbd5e1;
            vertical-align: top;
        }
        .tc-table td.num {
            width: 30px;
            font-weight: bold;
        }
        .tc-table td.field-name {
            width: 320px;
            color: #334155;
        }
        .tc-table td.field-val {
            font-weight: bold;
            color: #0f172a;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 60px;
            padding: 0 20px;
        }
        .sig-box {
            text-align: center;
            width: 160px;
            border-top: 1px solid #475569;
            padding-top: 8px;
            font-size: 13px;
            font-weight: bold;
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
        }
        @media print {
            body { background: transparent; padding: 0; }
            .tc-frame { box-shadow: none; border: 3px double #000; padding: 30px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="tc-frame">
    <div class="header-section">
        <h1 class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></h1>
        <div class="school-meta"><?= htmlspecialchars($school['address'] ?? '') ?></div>
        <div class="school-meta">Affiliation / Code: <?= htmlspecialchars($school['code'] ?? 'SCH-01') ?> &bull; Phone: <?= htmlspecialchars($school['phone'] ?? '') ?></div>
    </div>

    <div class="tc-title">
        <span>TRANSFER CERTIFICATE</span>
    </div>

    <div class="meta-row">
        <div>TC No: <span style="color: #0f766e;"><?= htmlspecialchars($cert['tc_no']) ?></span></div>
        <div>Admission / Scholar No: <?= htmlspecialchars($cert['admission_no'] ?: 'N/A') ?></div>
        <div>Date: <?= date('d/m/Y', strtotime($cert['issue_date'])) ?></div>
    </div>

    <table class="tc-table">
        <tr>
            <td class="num">1.</td>
            <td class="field-name">Name of the Student:</td>
            <td class="field-val"><?= htmlspecialchars($cert['student_name']) ?></td>
        </tr>
        <tr>
            <td class="num">2.</td>
            <td class="field-name">Father's / Guardian's Name:</td>
            <td class="field-val"><?= htmlspecialchars($cert['father_name'] ?: 'N/A') ?></td>
        </tr>
        <tr>
            <td class="num">3.</td>
            <td class="field-name">Mother's Name:</td>
            <td class="field-val"><?= htmlspecialchars($cert['mother_name'] ?: 'N/A') ?></td>
        </tr>
        <tr>
            <td class="num">4.</td>
            <td class="field-name">Gender:</td>
            <td class="field-val" style="text-transform: capitalize;"><?= htmlspecialchars($cert['gender'] ?: 'N/A') ?></td>
        </tr>
        <tr>
            <td class="num">5.</td>
            <td class="field-name">Date of Birth (in figures):</td>
            <td class="field-val"><?= $cert['dob'] ? date('d-m-Y', strtotime($cert['dob'])) : 'N/A' ?></td>
        </tr>
        <tr>
            <td class="num">6.</td>
            <td class="field-name">Date of First Admission in School:</td>
            <td class="field-val"><?= $cert['admission_date'] ? date('d-m-Y', strtotime($cert['admission_date'])) : 'N/A' ?></td>
        </tr>
        <tr>
            <td class="num">7.</td>
            <td class="field-name">Class in which student last studied:</td>
            <td class="field-val">Class <?= htmlspecialchars($cert['class_name']) ?> (Section <?= htmlspecialchars($cert['section_name']) ?>)</td>
        </tr>
        <tr>
            <td class="num">8.</td>
            <td class="field-name">School / Board Examination last taken:</td>
            <td class="field-val">Passed / Course Completed</td>
        </tr>
        <tr>
            <td class="num">9.</td>
            <td class="field-name">Qualified for promotion to higher class:</td>
            <td class="field-val"><?= htmlspecialchars($cert['qualified_promotion'] ?: 'Yes') ?></td>
        </tr>
        <tr>
            <td class="num">10.</td>
            <td class="field-name">Month up to which school dues paid:</td>
            <td class="field-val">All Clear</td>
        </tr>
        <tr>
            <td class="num">11.</td>
            <td class="field-name">Date of student's name removal / leaving:</td>
            <td class="field-val"><?= date('d-m-Y', strtotime($cert['leaving_date'])) ?></td>
        </tr>
        <tr>
            <td class="num">12.</td>
            <td class="field-name">Reason for leaving the school:</td>
            <td class="field-val"><?= htmlspecialchars($cert['reason_leaving']) ?></td>
        </tr>
        <tr>
            <td class="num">13.</td>
            <td class="field-name">General Conduct & Character:</td>
            <td class="field-val"><?= htmlspecialchars($cert['conduct']) ?></td>
        </tr>
    </table>

    <div class="signature-section">
        <div class="sig-box">Prepared By (Clerk)</div>
        <div class="sig-box">Checked By</div>
        <div class="sig-box">Principal Signature & Seal</div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">Print Transfer Certificate</button>

</body>
</html>
