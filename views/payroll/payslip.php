<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= htmlspecialchars($payslip['staff_name']) ?></title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background: #f8f9fa;
        }
        .payslip-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .school-info h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #1f9e8b;
        }
        .school-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .payslip-title {
            text-align: right;
        }
        .payslip-title h2 {
            margin: 0;
            font-size: 28px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .payslip-title p {
            margin: 5px 0 0 0;
            color: #666;
            font-weight: 600;
        }
        .employee-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
        }
        .detail-group {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
        }
        .detail-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .salary-table th {
            background: #f0f2f5;
            padding: 12px 15px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }
        .salary-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .salary-table .amount {
            text-align: right;
            font-weight: 500;
        }
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .totals-box {
            width: 300px;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .total-row.net {
            font-size: 18px;
            font-weight: 700;
            color: #1f9e8b;
            border-top: 2px solid #e0e0e0;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            color: #888;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto 40px;
            background: #1f9e8b;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .print-btn:hover {
            background: #198070;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .payslip-container {
                box-shadow: none;
                padding: 0;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Print Payslip</button>

<div class="payslip-container">
    <div class="header">
        <div class="school-info">
            <h1><?= htmlspecialchars($school['name'] ?? 'School ERP') ?></h1>
            <p><?= htmlspecialchars($school['address'] ?? '') ?></p>
            <p><?= htmlspecialchars($school['phone'] ?? '') ?> | <?= htmlspecialchars($school['email'] ?? '') ?></p>
        </div>
        <div class="payslip-title">
            <h2>PAYSLIP</h2>
            <p><?= date('F Y', mktime(0, 0, 0, $payslip['month'], 1, $payslip['year'])) ?></p>
        </div>
    </div>

    <div class="employee-details">
        <div>
            <div class="detail-group">
                <div class="detail-label">Employee Name</div>
                <div class="detail-value"><?= htmlspecialchars($payslip['staff_name']) ?></div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Department</div>
                <div class="detail-value"><?= htmlspecialchars($payslip['department_name'] ?? 'N/A') ?></div>
            </div>
        </div>
        <div>
            <div class="detail-group">
                <div class="detail-label">Designation</div>
                <div class="detail-value"><?= htmlspecialchars($payslip['designation_name'] ?? 'N/A') ?></div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Payment Status</div>
                <div class="detail-value" style="color: <?= $payslip['status'] === 'paid' ? '#1f9e8b' : '#f59e0b' ?>;">
                    <?= ucfirst($payslip['status']) ?>
                    <?php if ($payslip['status'] === 'paid'): ?>
                        on <?= date('d M Y', strtotime($payslip['payment_date'])) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
        $allowances = [];
        $deductions = [];
        $totalAllowances = 0;
        $totalDeductions = 0;

        if (!empty($payslip['allowances_json'])) {
            $allowances = json_decode($payslip['allowances_json'], true) ?: [];
            foreach ($allowances as $a) $totalAllowances += $a['amount'];
        }
        if (!empty($payslip['deductions_json'])) {
            $deductions = json_decode($payslip['deductions_json'], true) ?: [];
            foreach ($deductions as $d) $totalDeductions += $d['amount'];
        }
    ?>

    <table class="salary-table">
        <thead>
            <tr>
                <th>Earnings</th>
                <th class="amount">Amount</th>
                <th>Deductions</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="amount"><?= htmlspecialchars($currency) ?><?= number_format($payslip['basic_salary'], 2) ?></td>
                <td>
                    <?php if (isset($deductions[0])): ?>
                        <?= htmlspecialchars($deductions[0]['name']) ?>
                    <?php endif; ?>
                </td>
                <td class="amount">
                    <?php if (isset($deductions[0])): ?>
                        <?= htmlspecialchars($currency) ?><?= number_format($deductions[0]['amount'], 2) ?>
                    <?php endif; ?>
                </td>
            </tr>
            
            <?php 
                $maxRows = max(count($allowances), count($deductions));
                for ($i = 0; $i < $maxRows; $i++): 
            ?>
            <tr>
                <td>
                    <?php if (isset($allowances[$i])): ?>
                        <?= htmlspecialchars($allowances[$i]['name']) ?>
                    <?php endif; ?>
                </td>
                <td class="amount">
                    <?php if (isset($allowances[$i])): ?>
                        <?= htmlspecialchars($currency) ?><?= number_format($allowances[$i]['amount'], 2) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isset($deductions[$i + 1])): ?>
                        <?= htmlspecialchars($deductions[$i + 1]['name']) ?>
                    <?php endif; ?>
                </td>
                <td class="amount">
                    <?php if (isset($deductions[$i + 1])): ?>
                        <?= htmlspecialchars($currency) ?><?= number_format($deductions[$i + 1]['amount'], 2) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-box">
            <div class="total-row">
                <span>Basic Salary</span>
                <span><?= htmlspecialchars($currency) ?><?= number_format($payslip['basic_salary'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>Total Allowances</span>
                <span>+ <?= htmlspecialchars($currency) ?><?= number_format($totalAllowances, 2) ?></span>
            </div>
            <div class="total-row">
                <span>Total Deductions</span>
                <span>- <?= htmlspecialchars($currency) ?><?= number_format($totalDeductions, 2) ?></span>
            </div>
            <div class="total-row net">
                <span>Net Salary</span>
                <span><?= htmlspecialchars($currency) ?><?= number_format($payslip['net_salary'], 2) ?></span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</div>

</body>
</html>
