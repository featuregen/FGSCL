<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($cert['cert_type']) ?> Certificate - <?= htmlspecialchars($cert['cert_no']) ?></title>
    <style>
        @page { size: A4 portrait; margin: 20mm; }
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .cert-frame {
            max-width: 780px;
            margin: 0 auto;
            background: #fff;
            padding: 50px 40px;
            border: 4px double #0f766e;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
        }
        .header-section {
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 16px;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 28px;
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
        .cert-title {
            text-align: center;
            margin: 30px 0;
        }
        .cert-title span {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 4px;
            color: #0f766e;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            font-size: 15px;
            font-weight: bold;
        }
        .cert-body {
            font-size: 18px;
            line-height: 2.2;
            text-align: justify;
            margin-bottom: 60px;
            color: #1e293b;
        }
        .highlight {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 80px;
            padding: 0 20px;
        }
        .sig-box {
            text-align: center;
            width: 180px;
            border-top: 1px solid #475569;
            padding-top: 8px;
            font-size: 14px;
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
            .cert-frame { box-shadow: none; border: 3px double #000; padding: 40px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="cert-frame">
    <div class="header-section">
        <h1 class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></h1>
        <div class="school-meta"><?= htmlspecialchars($school['address'] ?? '') ?></div>
        <div class="school-meta">Phone: <?= htmlspecialchars($school['phone'] ?? '') ?> &bull; Email: <?= htmlspecialchars($school['email'] ?? '') ?></div>
    </div>

    <div class="cert-title">
        <span><?= strtoupper(htmlspecialchars($cert['cert_type'])) ?> CERTIFICATE</span>
    </div>

    <div class="meta-row">
        <div>Ref No: <span style="color: #0f766e;"><?= htmlspecialchars($cert['cert_no']) ?></span></div>
        <div>Date: <?= date('d F, Y', strtotime($cert['issue_date'])) ?></div>
    </div>

    <div class="cert-body">
        This is to certify that <span class="highlight"><?= htmlspecialchars($cert['student_name']) ?></span>, 
        Son / Daughter of <span class="highlight"><?= htmlspecialchars($cert['father_name'] ?: 'Guardian') ?></span>, 
        bearing Admission No. <span class="highlight"><?= htmlspecialchars($cert['admission_no'] ?: 'N/A') ?></span>, 
        is a bonafide student of this institution studying in <span class="highlight">Class <?= htmlspecialchars($cert['class_name']) ?> (Section <?= htmlspecialchars($cert['section_name']) ?>)</span> 
        during the academic session <span class="highlight"><?= htmlspecialchars($cert['academic_year']) ?></span>.
        <br><br>
        To the best of our knowledge, he/she bears a <span class="highlight">Good moral character</span> and conducts himself/herself with discipline and dedication.
        <br><br>
        This certificate is issued on request for the purpose of: <span class="highlight"><?= htmlspecialchars($cert['purpose']) ?></span>.
    </div>

    <div class="signature-section">
        <div class="sig-box">Dealing Assistant</div>
        <div class="sig-box">School Seal / Stamp</div>
        <div class="sig-box">Principal Signature</div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">Print Certificate</button>

</body>
</html>
