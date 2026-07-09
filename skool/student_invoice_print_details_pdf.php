<?php
/**
 * ============================================================================
 * STUDENT INVOICE PDF - SELF-CONTAINED
 * ============================================================================
 * Description: Generates PDF invoice with dynamic school name and logo
 * ALL code in ONE file - no external template dependencies
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
// GET INVOICE DATA
// ============================================================================
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Invalid invoice token. Please go back and try again.");
}

// ============================================================================
// FETCH ALL DATA - Using PDO functions
// ============================================================================

// 1. Get fee details
$feeRecord = db_get_row(
    "SELECT * FROM student_fee 
     WHERE create_by_userid = ? 
     AND randomid = ?",
    [$create_by_userid, $token]
);

if (empty($feeRecord)) {
    die("Invoice not found. Please check the invoice number and try again.");
}

// 2. Get student details
$student = db_get_row(
    "SELECT id, first_name, last_name, student_id FROM manage_student 
     WHERE create_by_userid = ? 
     AND id = ?",
    [$create_by_userid, $feeRecord['student_id']]
);

// 3. Get session, class, term
$sessionName = db_get_val(
    "SELECT session FROM school_session 
     WHERE create_by_userid = ? 
     AND id = ?",
    [$create_by_userid, $feeRecord['session']]
);

$className = db_get_val(
    "SELECT name FROM school_class 
     WHERE create_by_userid = ? 
     AND id = ?",
    [$create_by_userid, $feeRecord['class']]
);

$termName = db_get_val(
    "SELECT term FROM school_term 
     WHERE create_by_userid = ? 
     AND id = ?",
    [$create_by_userid, $feeRecord['term_id']]
);

// 4. Get school details
$school = db_get_row(
    "SELECT * FROM school_register 
     WHERE id = ?",
    [$create_by_userid]
);

// 5. Get fee items
$feeItems = db_get_rows(
    "SELECT sfs.*, fs.title as fee_title 
     FROM student_fee_sturcture sfs
     LEFT JOIN fee_sturcture fs ON sfs.fee_sturcture_id = fs.id
     WHERE sfs.student_fee_id = ?",
    [$feeRecord['id']]
);

// 6. Get transactions
$transactions = db_get_rows(
    "SELECT * FROM student_fee_transcation 
     WHERE student_fee_id = ? 
     AND create_by_userid = ? 
     ORDER BY create_at DESC",
    [$feeRecord['id'], $create_by_userid]
);

$paymentHistoryCount = is_array($transactions) ? count($transactions) : 0;
$showPaymentHistory = !empty($transactions) && $paymentHistoryCount <= 6;

// ============================================================================
// CALCULATE TOTALS
// ============================================================================
$subTotal = 0;
$totalDiscount = 0;
$totalPaid = 0;
$totalOutstanding = 0;

if (!empty($feeItems)) {
    foreach ($feeItems as $item) {
        $subTotal += (float)($item['fee'] ?? 0);
        $totalDiscount += (float)($item['fees_disccount'] ?? 0);
        $totalPaid += (float)($item['fees_amount'] ?? 0);
        $totalOutstanding += (float)($item['fees_outstanding'] ?? 0);
    }
}

// ============================================================================
// DETERMINE STATUS
// ============================================================================
$remainAmount = (float)($feeRecord['remain_amount'] ?? 0);
$studentStatus = (int)($feeRecord['student_status'] ?? 1);

if ($studentStatus == 3 && $remainAmount == 0) {
    $statusClass = 'status-scholarship';
    $statusText = '🎓 SCHOLARSHIP - FULLY COVERED';
} elseif ($remainAmount == 0) {
    $statusClass = 'status-paid';
    $statusText = '✅ FULLY PAID';
} elseif ($remainAmount > 0 && (float)($feeRecord['currently_paying_amount'] ?? 0) > 0) {
    $statusClass = 'status-partial';
    $statusText = '⏳ PARTIALLY PAID';
} else {
    $statusClass = 'status-pending';
    $statusText = '⚠️ OUTSTANDING';
}

// ============================================================================
// FIND SCHOOL LOGO
// ============================================================================
$logoPath = '';
$schoolLogo = $school['logo'] ?? '';

if (!empty($schoolLogo)) {
    $possiblePaths = [
        '../uploads/' . $schoolLogo,
        '../images/' . $schoolLogo,
        '../assets/images/' . $schoolLogo,
        '../' . $schoolLogo,
        $schoolLogo
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && !is_dir($path)) {
            $logoPath = $path;
            break;
        }
    }
}

// ============================================================================
// GENERATE HTML
// ============================================================================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - <?= htmlspecialchars($feeRecord['invoiceno'] ?? 'N/A') ?></title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif; 
            background: #fff; 
            color: #2d2d2d;
            font-size: 9.5px;
            line-height: 1.35;
            padding: 0;
            margin: 0;
        }
        .invoice-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 0 40px rgba(0,0,0,0.05);
            page-break-inside: avoid;
            page-break-after: avoid;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 18px 24px 14px 24px;
            border-bottom: 3px solid #1B3058;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        .header-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .school-logo {
            max-width: 64px;
            max-height: 64px;
            object-fit: contain;
        }
        .school-info {
            display: flex;
            flex-direction: column;
        }
        .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .school-moto {
            font-size: 11px;
            color: #6c757d;
            font-style: italic;
            margin-bottom: 4px;
        }
        .school-address {
            font-size: 10px;
            color: #6c757d;
            line-height: 1.4;
        }
        .header-right {
            text-align: right;
            min-width: 220px;
        }
        .invoice-badge {
            background: #1B3058;
            color: #fff;
            padding: 6px 16px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1.5px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
        }
        .invoice-meta {
            font-size: 10px;
            color: #6c757d;
            line-height: 1.6;
        }
        .invoice-meta strong {
            color: #1B3058;
        }
        .status-banner {
            padding: 8px 24px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .status-paid { background: #d4edda; color: #155724; border-bottom: 2px solid #28a745; }
        .status-partial { background: #fff3cd; color: #856404; border-bottom: 2px solid #ffc107; }
        .status-pending { background: #f8d7da; color: #721c24; border-bottom: 2px solid #dc3545; }
        .status-scholarship { background: #d1ecf1; color: #0c5460; border-bottom: 2px solid #17a2b8; }
        .student-info {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 8px;
            padding: 10px 24px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item { display: flex; flex-direction: column; }
        .info-label {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .info-value {
            font-size: 10px;
            font-weight: 600;
            color: #1B3058;
        }
        .table-container {
            padding: 10px 24px 6px 24px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .invoice-table thead th {
            background: #1B3058;
            color: #ffffff;
            padding: 7px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 8.2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .invoice-table thead th:last-child {
            text-align: right;
        }
        .invoice-table tbody td {
            padding: 7px 8px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .invoice-table tbody td:last-child {
            text-align: right;
        }
        .fee-title-cell { font-weight: 600; color: #1B3058; }
        .amount-cell { font-weight: 600; }
        .paid-cell { color: #28a745; font-weight: 700; }
        .outstanding-cell { color: #dc3545; font-weight: 700; }
        .discount-cell { color: #6c757d; }
        .totals-section {
            padding: 8px 24px 12px 24px;
            border-top: 2px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 45%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .totals-table td {
            padding: 5px 8px;
            border: none;
        }
        .totals-table .label {
            color: #6c757d;
            text-align: right;
            padding-right: 15px;
            font-weight: 500;
        }
        .totals-table .value {
            font-weight: 600;
            text-align: right;
            min-width: 100px;
        }
        .totals-table .grand-total td {
            font-size: 14px;
            font-weight: 700;
            color: #1B3058;
            border-top: 2px solid #1B3058;
            padding-top: 10px;
        }
        .totals-table .grand-total .value {
            color: #1B3058;
            font-size: 15px;
        }
        .payment-history {
            padding: 0 24px 10px 24px;
        }
        .payment-history h4 {
            font-size: 11px;
            font-weight: 700;
            color: #1B3058;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payment-history table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .payment-history table th {
            background: #e9ecef;
            color: #495057;
            padding: 6px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
        }
        .payment-history table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .invoice-footer {
            padding: 10px 24px 16px 24px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            background: #f8f9fa;
        }
        .footer-left {
            font-size: 9px;
            color: #6c757d;
            line-height: 1.6;
        }
        .footer-left strong { color: #1B3058; }
        .footer-right { text-align: right; }
        .signature-line {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #1B3058;
            font-size: 8.5px;
            color: #6c757d;
            min-width: 150px;
        }
        .watermark {
            position: fixed;
            bottom: 50px;
            right: 50px;
            opacity: 0.06;
            font-size: 60px;
            font-weight: 700;
            color: #1B3058;
            transform: rotate(-20deg);
            pointer-events: none;
            z-index: 0;
        }
        @media print {
            body { padding: 0; background: #fff; }
            .invoice-wrapper { box-shadow: none; border-radius: 0; }
            .invoice-header { background: #fff; }
            .status-banner { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-table thead th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .payment-history table th { background: #e9ecef !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 768px) {
            .invoice-header { flex-direction: column; align-items: center; text-align: center; padding: 20px; }
            .header-left { flex-direction: column; align-items: center; }
            .header-right { text-align: center; margin-top: 15px; }
            .student-info { grid-template-columns: 1fr 1fr 1fr; gap: 8px; padding: 15px 20px; }
            .totals-table { width: 100%; }
            .invoice-footer { flex-direction: column; align-items: center; text-align: center; }
            .footer-right { margin-top: 15px; }
        }
        @media (max-width: 480px) {
            .student-info { grid-template-columns: 1fr 1fr; }
            .invoice-table thead th, .invoice-table tbody td { padding: 6px 8px; font-size: 9px; }
            .school-name { font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="watermark">
    <?= ($statusClass == 'status-paid' || $statusClass == 'status-scholarship') ? 'PAID' : 'PENDING' ?>
</div>

<div class="invoice-wrapper">

    <!-- HEADER -->
    <div class="invoice-header">
        <div class="header-left">
            <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
                <img src="<?= $logoPath ?>" class="school-logo" alt="School Logo">
            <?php else: ?>
                <div style="width:80px; height:80px; background:#f0f0f0; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#999; font-size:12px; text-align:center;">
                    Logo<br>Not Found
                </div>
            <?php endif; ?>
            <div class="school-info">
                <div class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></div>
                <div class="school-moto"><?= htmlspecialchars($school['moto'] ?? 'Excellence in Education') ?></div>
                <div class="school-address">
                    <?= htmlspecialchars($school['location'] ?? '') ?><br>
                    <?php if (!empty($school['phone'])): ?>Tel: <?= htmlspecialchars($school['phone']) ?><?php endif; ?>
                    <?php if (!empty($school['phone']) && !empty($school['email'])): ?> | <?php endif; ?>
                    <?php if (!empty($school['email'])): ?>Email: <?= htmlspecialchars($school['email']) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-badge">INVOICE</div>
            <div class="invoice-meta">
                <strong>Invoice #:</strong> <?= htmlspecialchars($feeRecord['invoiceno'] ?? 'N/A') ?><br>
                <strong>Date:</strong> <?= date('d M Y', strtotime($feeRecord['create_at'] ?? 'now')) ?><br>
                <strong>Due Date:</strong> <?= date('d M Y', strtotime('+30 days', strtotime($feeRecord['create_at'] ?? 'now'))) ?>
            </div>
        </div>
    </div>

    <!-- STATUS BANNER -->
    <div class="status-banner <?= $statusClass ?>">
        <?= $statusText ?> — Outstanding Balance: ₦<?= number_format($totalOutstanding, 2) ?>
    </div>

    <!-- STUDENT INFO -->
    <div class="student-info">
        <div class="info-item">
            <span class="info-label">Student Name</span>
            <span class="info-value"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Student ID</span>
            <span class="info-value"><?= htmlspecialchars($feeRecord['rollno'] ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Class</span>
            <span class="info-value"><?= htmlspecialchars($className ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Session</span>
            <span class="info-value"><?= htmlspecialchars($sessionName ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Term</span>
            <span class="info-value"><?= htmlspecialchars($termName ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value">
                <?php 
                $statuses = [1 => 'Returning', 2 => 'New', 3 => 'Scholarship'];
                echo $statuses[$studentStatus] ?? 'N/A';
                ?>
            </span>
        </div>
    </div>

    <!-- FEE ITEMS TABLE -->
    <div class="table-container">
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:30%;">Fee Description</th>
                    <th style="width:15%;">Amount (₦)</th>
                    <th style="width:15%;">Discount (₦)</th>
                    <th style="width:17%;">Amount Paid (₦)</th>
                    <th style="width:18%;">Outstanding (₦)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 0;
                if (!empty($feeItems)):
                    foreach ($feeItems as $item): 
                        $counter++;
                ?>
                <tr>
                    <td><?= $counter ?></td>
                    <td class="fee-title-cell"><?= htmlspecialchars($item['fee_title'] ?? 'Unknown Fee') ?></td>
                    <td class="amount-cell"><?= number_format((float)($item['fee'] ?? 0), 2) ?></td>
                    <td class="discount-cell"><?= number_format((float)($item['fees_disccount'] ?? 0), 2) ?></td>
                    <td class="paid-cell"><?= number_format((float)($item['fees_amount'] ?? 0), 2) ?></td>
                    <td class="outstanding-cell"><?= number_format((float)($item['fees_outstanding'] ?? 0), 2) ?></td>
                </tr>
                <?php 
                    endforeach; 
                else: 
                ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#999;">
                        No fee items found for this invoice.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TOTALS -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Sub Total</td>
                <td class="value">₦<?= number_format($subTotal, 2) ?></td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="value" style="color:#6c757d;">- ₦<?= number_format($totalDiscount, 2) ?></td>
            </tr>
            <tr>
                <td class="label">Total Paid</td>
                <td class="value" style="color:#28a745;">₦<?= number_format($totalPaid, 2) ?></td>
            </tr>
            <tr class="grand-total">
                <td class="label">Outstanding Balance</td>
                <td class="value" style="color:<?= $totalOutstanding > 0 ? '#dc3545' : '#28a745' ?>;">
                    ₦<?= number_format($totalOutstanding, 2) ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- PAYMENT HISTORY -->
    <?php if ($showPaymentHistory): ?>
    <div class="payment-history">
        <h4>📋 Payment History</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount Paid (₦)</th>
                    <th>Discount (₦)</th>
                    <th>Balance (₦)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $trans): ?>
                <tr>
                    <td><?= date('d M Y H:i', strtotime($trans['create_at'] ?? 'now')) ?></td>
                    <td style="font-weight:600; color:#28a745;">₦<?= number_format((float)($trans['currently_paying_amount'] ?? 0), 2) ?></td>
                    <td>₦<?= number_format((float)($trans['discount_amount'] ?? 0), 2) ?></td>
                    <td>₦<?= number_format((float)($trans['remain_amount'] ?? 0), 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php elseif (!empty($transactions)): ?>
    <div class="payment-history">
        <h4>📋 Payment History</h4>
        <div class="payment-note">Additional payment history is available in the school portal and has been omitted here to keep this invoice compact and printable on one page.</div>
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="invoice-footer">
        <div class="footer-left">
            <strong>Terms & Conditions</strong><br>
            1. Payment is due within 30 days of invoice date.<br>
            2. Late payment may incur additional charges.<br>
            3. For inquiries, please contact the accounts department.
        </div>
        <div class="footer-right">
            <div style="font-size:10px; font-weight:600; color:#1B3058; margin-bottom:5px;">
                <?= htmlspecialchars($school['name'] ?? '') ?>
            </div>
            <div style="font-size:9px; color:#6c757d; margin-bottom:15px;">
                Authorized Signature
            </div>
            <div class="signature-line">
                _______________________________
            </div>
            <div style="font-size:9px; color:#6c757d; margin-top:4px;">
                Generated on: <?= date('d M Y H:i:s') ?>
            </div>
        </div>
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

// Download the PDF
$filename = 'Invoice_' . ($feeRecord['invoiceno'] ?? '') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
?>