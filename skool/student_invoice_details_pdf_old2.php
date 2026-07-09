<?php
/**
 * ============================================================================
 * STUDENT INVOICE HTML TEMPLATE - COMPLETE FIX
 * ============================================================================
 * Description: Dynamic invoice template with school name and logo from database
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

// ============================================================================
// SAFETY CHECK - Data must be passed from parent file
// ============================================================================
if (!isset($iStudentFeeDetailsPdf) || empty($iStudentFeeDetailsPdf)) {
    die("Error: Invoice data not found. Please generate invoice from the fee management page.");
}

// ============================================================================
// SAFE VARIABLE ACCESS - All data from database
// ============================================================================
$invoiceNo = $iStudentFeeDetailsPdf['invoiceno'] ?? 'N/A';
$studentName = (($iStudentNamePdf['first_name'] ?? '') . ' ' . ($iStudentNamePdf['last_name'] ?? ''));
$studentId = $iStudentFeeDetailsPdf['rollno'] ?? 'N/A';
$studentClass = $studentClassPdf ?? 'N/A';
$studentSession = $studentSessionPdf ?? 'N/A';
$studentTerm = $studentTermPdf ?? 'N/A';
$paymentType = ($iStudentFeeDetailsPdf['PType'] ?? 0) == 2 ? 'Fixed' : 'Flexible';
$studentStatus = $iStudentFeeDetailsPdf['student_status'] ?? 1;
$statusLabels = [1 => 'Returning', 2 => 'New', 3 => 'Scholarship'];
$statusLabel = $statusLabels[(int)$studentStatus] ?? 'N/A';

// School details from database
$schoolName = $schoolDetails['name'] ?? 'School Name';
$schoolMoto = $schoolDetails['moto'] ?? 'Excellence in Education';
$schoolLocation = $schoolDetails['location'] ?? '';
$schoolPhone = $schoolDetails['phone'] ?? '';
$schoolEmail = $schoolDetails['email'] ?? '';
$schoolLogo = $schoolDetails['logo'] ?? '';

// Totals from database
$subTotal = $subTotal ?? 0;
$totalDiscount = $totalDiscount ?? 0;
$totalPaid = $totalPaid ?? 0;
$totalOutstanding = $totalOutstanding ?? 0;
$statusClass = $statusClass ?? 'status-pending';
$statusText = $statusText ?? '⚠️ OUTSTANDING';

// Fee items from database
$feeItems = $feeItems ?? [];

// Transactions from database
$transactions = $transactions ?? [];

// ============================================================================
// FIND SCHOOL LOGO - Try multiple paths
// ============================================================================
$logoPath = '';
if (!empty($schoolLogo)) {
    // Try relative paths from skool directory
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
    
    // If still not found, try with absolute path
    if (empty($logoPath) && !empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $absolutePaths = [
            $docRoot . '/bestschoolpage/uploads/' . $schoolLogo,
            $docRoot . '/uploads/' . $schoolLogo,
            $docRoot . '/bestschoolpage/images/' . $schoolLogo,
        ];
        foreach ($absolutePaths as $path) {
            if (file_exists($path) && !is_dir($path)) {
                $logoPath = $path;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - <?= htmlspecialchars($invoiceNo) ?></title>
    <style>
        /* ===== RESET & BASE ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif; 
            background: #fff; 
            color: #2d2d2d;
            font-size: 10.5px;
            line-height: 1.5;
            padding: 20px;
        }
        
        /* ===== INVOICE CONTAINER ===== */
        .invoice-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 0;
            box-shadow: 0 0 40px rgba(0,0,0,0.05);
        }
        
        /* ===== HEADER ===== */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 35px 40px 25px 40px;
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
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        
        .school-info {
            display: flex;
            flex-direction: column;
        }
        
        .school-name {
            font-size: 26px;
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
            padding: 8px 24px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .invoice-meta {
            font-size: 10px;
            color: #6c757d;
            line-height: 1.6;
        }
        
        .invoice-meta strong {
            color: #1B3058;
        }
        
        /* ===== STATUS BANNER ===== */
        .status-banner {
            padding: 10px 40px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
            border-bottom: 2px solid #28a745;
        }
        
        .status-partial {
            background: #fff3cd;
            color: #856404;
            border-bottom: 2px solid #ffc107;
        }
        
        .status-pending {
            background: #f8d7da;
            color: #721c24;
            border-bottom: 2px solid #dc3545;
        }
        
        .status-scholarship {
            background: #d1ecf1;
            color: #0c5460;
            border-bottom: 2px solid #17a2b8;
        }
        
        /* ===== STUDENT INFO ===== */
        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
            gap: 12px;
            padding: 20px 40px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        .info-value {
            font-size: 12px;
            font-weight: 600;
            color: #1B3058;
        }
        
        /* ===== TABLE ===== */
        .table-container {
            padding: 20px 40px 10px 40px;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        
        .invoice-table thead th {
            background: #1B3058;
            color: #ffffff;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .invoice-table thead th:last-child {
            text-align: right;
        }
        
        .invoice-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        
        .invoice-table tbody td:last-child {
            text-align: right;
        }
        
        .invoice-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .invoice-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .fee-title-cell {
            font-weight: 600;
            color: #1B3058;
        }
        
        .amount-cell {
            font-weight: 600;
        }
        
        .paid-cell {
            color: #28a745;
            font-weight: 700;
        }
        
        .outstanding-cell {
            color: #dc3545;
            font-weight: 700;
        }
        
        .discount-cell {
            color: #6c757d;
        }
        
        /* ===== TOTALS ===== */
        .totals-section {
            padding: 15px 40px 25px 40px;
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
        
        /* ===== PAYMENT HISTORY ===== */
        .payment-history {
            padding: 0 40px 20px 40px;
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
        
        /* ===== FOOTER ===== */
        .invoice-footer {
            padding: 20px 40px 30px 40px;
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
        
        .footer-left strong {
            color: #1B3058;
        }
        
        .footer-right {
            text-align: right;
        }
        
        .signature-line {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #1B3058;
            font-size: 9px;
            color: #6c757d;
            min-width: 150px;
        }
        
        /* ===== WATERMARK ===== */
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
        
        /* ===== PRINT ===== */
        @media print {
            body { padding: 0; background: #fff; }
            .invoice-wrapper { box-shadow: none; border-radius: 0; }
            .invoice-header { background: #fff; }
            .status-banner { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-table thead th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .payment-history table th { background: #e9ecef !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        
        /* ===== RESPONSIVE ===== */
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

<!-- ===== WATERMARK ===== -->
<div class="watermark">
    <?= ($statusClass == 'status-paid' || $statusClass == 'status-scholarship') ? 'PAID' : 'PENDING' ?>
</div>

<!-- ===== INVOICE ===== -->
<div class="invoice-wrapper">

    <!-- ===== HEADER WITH LOGO ===== -->
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
                <div class="school-name"><?= htmlspecialchars($schoolName) ?></div>
                <div class="school-moto"><?= htmlspecialchars($schoolMoto) ?></div>
                <div class="school-address">
                    <?= htmlspecialchars($schoolLocation) ?><br>
                    <?php if (!empty($schoolPhone)): ?>Tel: <?= htmlspecialchars($schoolPhone) ?><?php endif; ?>
                    <?php if (!empty($schoolPhone) && !empty($schoolEmail)): ?> | <?php endif; ?>
                    <?php if (!empty($schoolEmail)): ?>Email: <?= htmlspecialchars($schoolEmail) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-badge">INVOICE</div>
            <div class="invoice-meta">
                <strong>Invoice #:</strong> <?= htmlspecialchars($invoiceNo) ?><br>
                <strong>Date:</strong> <?= date('d M Y', strtotime($iStudentFeeDetailsPdf['create_at'] ?? 'now')) ?><br>
                <strong>Due Date:</strong> <?= date('d M Y', strtotime('+30 days', strtotime($iStudentFeeDetailsPdf['create_at'] ?? 'now'))) ?>
            </div>
        </div>
    </div>

    <!-- ===== STATUS BANNER ===== -->
    <div class="status-banner <?= $statusClass ?>">
        <?= $statusText ?> — Outstanding Balance: ₦<?= number_format($totalOutstanding ?? 0, 2) ?>
    </div>

    <!-- ===== STUDENT INFO ===== -->
    <div class="student-info">
        <div class="info-item">
            <span class="info-label">Student Name</span>
            <span class="info-value"><?= htmlspecialchars($studentName ?: 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Student ID</span>
            <span class="info-value"><?= htmlspecialchars($studentId) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Class</span>
            <span class="info-value"><?= htmlspecialchars($studentClass) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Session</span>
            <span class="info-value"><?= htmlspecialchars($studentSession) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Term</span>
            <span class="info-value"><?= htmlspecialchars($studentTerm) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value"><?= $statusLabel ?></span>
        </div>
    </div>

    <!-- ===== FEE ITEMS TABLE ===== -->
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
                        $amount = (float)($item['fee'] ?? 0);
                        $discount = (float)($item['fees_disccount'] ?? 0);
                        $paid = (float)($item['fees_amount'] ?? 0);
                        $outstanding = (float)($item['fees_outstanding'] ?? 0);
                ?>
                <tr>
                    <td><?= $counter ?></td>
                    <td class="fee-title-cell"><?= htmlspecialchars($item['fee_title'] ?? 'Unknown Fee') ?></td>
                    <td class="amount-cell"><?= number_format($amount, 2) ?></td>
                    <td class="discount-cell"><?= number_format($discount, 2) ?></td>
                    <td class="paid-cell"><?= number_format($paid, 2) ?></td>
                    <td class="outstanding-cell"><?= number_format($outstanding, 2) ?></td>
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

    <!-- ===== TOTALS ===== -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Sub Total</td>
                <td class="value">₦<?= number_format($subTotal ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="value" style="color:#6c757d;">- ₦<?= number_format($totalDiscount ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="label">Total Paid</td>
                <td class="value" style="color:#28a745;">₦<?= number_format($totalPaid ?? 0, 2) ?></td>
            </tr>
            <tr class="grand-total">
                <td class="label">Outstanding Balance</td>
                <td class="value" style="color:<?= ($totalOutstanding ?? 0) > 0 ? '#dc3545' : '#28a745' ?>;">
                    ₦<?= number_format($totalOutstanding ?? 0, 2) ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- ===== PAYMENT HISTORY ===== -->
    <?php if (!empty($transactions)): ?>
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
    <?php endif; ?>

    <!-- ===== FOOTER ===== -->
    <div class="invoice-footer">
        <div class="footer-left">
            <strong>Terms & Conditions</strong><br>
            1. Payment is due within 30 days of invoice date.<br>
            2. Late payment may incur additional charges.<br>
            3. For inquiries, please contact the accounts department.
        </div>
        <div class="footer-right">
            <div style="font-size:10px; font-weight:600; color:#1B3058; margin-bottom:5px;">
                <?= htmlspecialchars($schoolName) ?>
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