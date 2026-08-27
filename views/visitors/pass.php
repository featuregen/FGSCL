<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visitor Pass - <?= htmlspecialchars($visitor['visitor_card_no']) ?></title>
    <style>
        @page { size: auto; margin: 10mm; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .pass-card {
            width: 360px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .pass-header {
            background: #0f766e;
            color: #fff;
            padding: 16px 20px;
            text-align: center;
        }
        .pass-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .pass-header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            opacity: 0.9;
        }
        .pass-badge-type {
            background: #f59e0b;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-top: 8px;
        }
        .pass-body {
            padding: 20px;
        }
        .visitor-photo-box {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            color: #94a3b8;
            font-size: 28px;
        }
        .pass-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13px;
        }
        .pass-label {
            color: #64748b;
            font-weight: 600;
        }
        .pass-value {
            font-weight: 700;
            color: #1e293b;
            text-align: right;
        }
        .pass-footer {
            background: #f8fafc;
            padding: 14px 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #64748b;
        }
        .print-btn {
            background: #0f766e;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
        }
        @media print {
            body { background: transparent; padding: 0; }
            .pass-card { box-shadow: none; border: 2px solid #000; width: 100%; max-width: 340px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="pass-card">
    <div class="pass-header">
        <h2><?= htmlspecialchars($visitor['school_name']) ?></h2>
        <p><?= htmlspecialchars($visitor['school_address'] ?: 'Campus Entry Gate') ?></p>
        <div class="pass-badge-type">VISITOR PASS</div>
    </div>

    <div class="pass-body">
        <div class="visitor-photo-box">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>

        <div style="text-align: center; margin-bottom: 16px;">
            <div style="font-size: 17px; font-weight: 800; color: #0f766e;"><?= htmlspecialchars($visitor['visitor_name']) ?></div>
            <div style="font-size: 12px; color: #64748b;">Pass No: <strong><?= htmlspecialchars($visitor['visitor_card_no']) ?></strong></div>
        </div>

        <div class="pass-row">
            <span class="pass-label">Phone:</span>
            <span class="pass-value"><?= htmlspecialchars($visitor['phone']) ?></span>
        </div>
        <div class="pass-row">
            <span class="pass-label">To Meet:</span>
            <span class="pass-value"><?= htmlspecialchars($visitor['to_meet_name']) ?></span>
        </div>
        <div class="pass-row">
            <span class="pass-label">Purpose:</span>
            <span class="pass-value"><?= htmlspecialchars($visitor['purpose']) ?></span>
        </div>
        <div class="pass-row">
            <span class="pass-label">Persons:</span>
            <span class="pass-value"><?= $visitor['number_of_persons'] ?></span>
        </div>
        <div class="pass-row">
            <span class="pass-label">Date & Time:</span>
            <span class="pass-value"><?= date('d M Y, h:i A', strtotime($visitor['in_time'])) ?></span>
        </div>
    </div>

    <div class="pass-footer">
        <div>Please return this pass at security gate upon exit.</div>
        <button class="print-btn" onclick="window.print()">Print Gate Pass</button>
    </div>
</div>

</body>
</html>
