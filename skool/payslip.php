<?php
/**
 * ============================================================================
 * PAYSLIP PDF GENERATOR
 * ============================================================================
 * Description: Generate professional payslip PDF for staff
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

// ============================================================================
// DOMPDF AUTOLOAD
// ============================================================================
$dompdfPath = dirname(__DIR__) . '/dompdf_New/autoload.inc.php';

if (!file_exists($dompdfPath)) {
    die("Dompdf not found at: " . $dompdfPath);
}

require_once($dompdfPath);

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================================================
// CONFIGURATION & SESSION
// ============================================================================
include('../config.php');
include('inc.session-create.php');

// ============================================================================
// GET PAYROLL DATA
// ============================================================================
$payrollId = $_GET['id'] ?? '';

if (empty($payrollId)) {
    die("Invalid payslip ID");
}

// Get payroll record
$payroll = db_get_row(
    "SELECT p.*, sm.first_name, sm.last_name, sm.staff_id, sm.email, sm.phone
     FROM payroll p
     LEFT JOIN staff_manage sm ON p.staff_id = sm.id
     WHERE p.randomid = ? AND p.create_by_userid = ?",
    [$payrollId, $create_by_userid]
);

if (empty($payroll)) {
    die("Payslip not found");
}

// Get school details
$school = db_get_row(
    "SELECT * FROM school_register WHERE id = ?",
    [$create_by_userid]
);

// ============================================================================
// GENERATE HTML
// ============================================================================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payslip - <?= htmlspecialchars($payroll['staff_id'] ?? '') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
            color: #333;
            background: #fff;
        }
        
        .payslip-wrapper {
            max-width: 700px;
            margin: 0 auto;
            border: 2px solid #1B3058;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #1B3058;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
        }
        
        .school-moto {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        
        .school-address {
            font-size: 8px;
            color: #888;
        }
        
        .payslip-title {
            font-size: 16px;
            font-weight: 700;
            color: #1B3058;
            margin: 8px 0 4px;
        }
        
        .payslip-period {
            font-size: 11px;
            color: #666;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .info-item {
            display: flex;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 80px;
            font-size: 9px;
        }
        
        .info-value {
            font-weight: 500;
            color: #333;
            font-size: 10px;
        }
        
        .table-wrapper {
            margin: 15px 0;
        }
        
        .payroll-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        .payroll-table th {
            background: #1B3058;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
        }
        
        .payroll-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
        }
        
        .payroll-table .text-right {
            text-align: right;
        }
        
        .payroll-table .total-row td {
            font-weight: 700;
            border-top: 2px solid #1B3058;
        }
        
        .payroll-table .total-row td:last-child {
            font-size: 11px;
            color: #1B3058;
        }
        
        .signature-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        
        .signature-line {
            margin-top: 20px;
            padding-top: 5px;
            border-top: 1px solid #333;
            width: 180px;
            text-align: center;
        }
        
        .net-pay-box {
            background: #28a745;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            text-align: center;
            margin: 10px 0;
            font-size: 14px;
            font-weight: 700;
        }
        
        .footer {
            text-align: center;
            font-size: 7px;
            color: #999;
            margin-top: 15px;
        }
        
        @media print {
            body { padding: 0; }
            .payslip-wrapper { border: none; }
            .payroll-table th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .net-pay-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="payslip-wrapper">

    <!-- HEADER -->
    <div class="header">
        <div class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></div>
        <div class="school-moto"><?= htmlspecialchars($school['moto'] ?? 'Excellence in Education') ?></div>
        <div class="school-address">
            <?= htmlspecialchars($school['location'] ?? '') ?>
            <?php if (!empty($school['phone'])): ?> | Tel: <?= htmlspecialchars($school['phone']) ?><?php endif; ?>
        </div>
        <div class="payslip-title">PAYSLIP</div>
        <div class="payslip-period">
            <?= date('F Y', mktime(0, 0, 0, (int)$payroll['month'], 1, (int)$payroll['year'])) ?>
        </div>
    </div>

    <!-- EMPLOYEE INFO -->
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Employee Name:</span>
            <span class="info-value"><?= htmlspecialchars(($payroll['first_name'] ?? '') . ' ' . ($payroll['last_name'] ?? '')) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Staff ID:</span>
            <span class="info-value"><?= htmlspecialchars($payroll['staff_id'] ?? '') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Email:</span>
            <span class="info-value"><?= htmlspecialchars($payroll['email'] ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Payment Date:</span>
            <span class="info-value">
                <?= $payroll['payment_date'] ? date('d M Y', strtotime($payroll['payment_date'])) : 'Not Paid Yet' ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="info-value">
                <?= ucfirst($payroll['payment_status'] ?? 'Pending') ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Generated:</span>
            <span class="info-value"><?= date('d M Y H:i') ?></span>
        </div>
    </div>

    <!-- NET PAY -->
    <div class="net-pay-box">
        Net Pay: ₦<?= number_format((float)$payroll['net_pay'], 2) ?>
    </div>

    <!-- EARNINGS & DEDUCTIONS -->
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th style="width:60%;">Description</th>
                    <th style="width:40%;" class="text-right">Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Earnings -->
                <tr>
                    <td><strong>EARNINGS</strong></td>
                    <td class="text-right"></td>
                </tr>
                <tr>
                    <td>Basic Salary</td>
                    <td class="text-right"><?= number_format((float)$payroll['basic_salary'], 2) ?></td>
                </tr>
                <?php if ((float)$payroll['housing_allowance'] > 0): ?>
                <tr>
                    <td>Housing Allowance</td>
                    <td class="text-right"><?= number_format((float)$payroll['housing_allowance'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['transport_allowance'] > 0): ?>
                <tr>
                    <td>Transport Allowance</td>
                    <td class="text-right"><?= number_format((float)$payroll['transport_allowance'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['medical_allowance'] > 0): ?>
                <tr>
                    <td>Medical Allowance</td>
                    <td class="text-right"><?= number_format((float)$payroll['medical_allowance'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['feeding_allowance'] > 0): ?>
                <tr>
                    <td>Feeding Allowance</td>
                    <td class="text-right"><?= number_format((float)$payroll['feeding_allowance'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['other_allowance'] > 0): ?>
                <tr>
                    <td>Other Allowance</td>
                    <td class="text-right"><?= number_format((float)$payroll['other_allowance'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td><strong>Gross Pay</strong></td>
                    <td class="text-right"><strong><?= number_format((float)$payroll['gross_pay'], 2) ?></strong></td>
                </tr>
                
                <!-- Deductions -->
                <tr>
                    <td><strong>DEDUCTIONS</strong></td>
                    <td class="text-right"></td>
                </tr>
                <?php if ((float)$payroll['paye_tax'] > 0): ?>
                <tr>
                    <td>PAYE Tax</td>
                    <td class="text-right"><?= number_format((float)$payroll['paye_tax'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['pension'] > 0): ?>
                <tr>
                    <td>Pension</td>
                    <td class="text-right"><?= number_format((float)$payroll['pension'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['nhf'] > 0): ?>
                <tr>
                    <td>NHF</td>
                    <td class="text-right"><?= number_format((float)$payroll['nhf'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['union_dues'] > 0): ?>
                <tr>
                    <td>Union Dues</td>
                    <td class="text-right"><?= number_format((float)$payroll['union_dues'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['loan_deduction'] > 0): ?>
                <tr>
                    <td>Loan Deduction</td>
                    <td class="text-right"><?= number_format((float)$payroll['loan_deduction'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$payroll['other_deductions'] > 0): ?>
                <tr>
                    <td>Other Deductions</td>
                    <td class="text-right"><?= number_format((float)$payroll['other_deductions'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td><strong>Total Deductions</strong></td>
                    <td class="text-right"><strong><?= number_format((float)$payroll['total_deductions'], 2) ?></strong></td>
                </tr>
                
                <!-- NET PAY -->
                <tr class="total-row" style="background: #e8f5e9;">
                    <td style="font-size: 12px; color: #28a745;"><strong>NET PAY</strong></td>
                    <td class="text-right" style="font-size: 12px; color: #28a745;">
                        <strong>₦<?= number_format((float)$payroll['net_pay'], 2) ?></strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- SIGNATURE -->
    <div class="signature-section">
        <div>
            <div style="font-weight:600; margin-bottom:5px;">Prepared By:</div>
            <div class="signature-line" style="width:160px; margin-top:0;">
                ____________________
            </div>
            <div style="font-size:8px; color:#888;">(Authorized Signature)</div>
        </div>
        <div style="text-align:right;">
            <div style="font-weight:600; margin-bottom:5px;">Payment Status:</div>
            <div>
                <?php if ($payroll['payment_status'] == 'paid'): ?>
                    <span style="color:#28a745; font-weight:700;">✅ PAID</span>
                    <div style="font-size:8px; color:#888;">Paid on: <?= date('d M Y', strtotime($payroll['payment_date'])) ?></div>
                <?php else: ?>
                    <span style="color:#ffc107; font-weight:700;">⏳ PENDING</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        This is a computer-generated payslip. No signature is required.<br>
        Generated on: <?= date('d M Y H:i:s') ?>
    </div>

</div>

</body>
</html>
<?php
$html = ob_get_clean();

// ============================================================================
// GENERATE PDF
// ============================================================================
$dompdf = new Dompdf();
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isJavascriptEnabled', true);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Payslip_' . ($payroll['staff_id'] ?? '') . '_' . $payroll['month'] . '_' . $payroll['year'] . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
?>