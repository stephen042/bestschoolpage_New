<?php
/**
 * ============================================================================
 * FEE STATUS REPORT PDF
 * ============================================================================
 * Description: Generates PDF report for defaulters & paid students
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
// GET FILTERS
// ============================================================================
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedClass = $_GET['class_id'] ?? '';
$selectedStatus = $_GET['status'] ?? 'all';

// ============================================================================
// GET DATA
// ============================================================================
if (empty($selectedSession) || empty($selectedTerm) || empty($selectedClass)) {
    die("Please select Session, Term, and Class");
}

// Get session, term, class names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?", [$selectedSession, $create_by_userid]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ? AND create_by_userid = ?", [$selectedTerm, $create_by_userid]);
$className = db_get_val("SELECT name FROM school_class WHERE id = ? AND create_by_userid = ?", [$selectedClass, $create_by_userid]);

// Get school details
$school = db_get_row("SELECT * FROM school_register WHERE id = ?", [$create_by_userid]);

// Build query
$sql = "SELECT 
            ms.id,
            ms.student_id,
            ms.first_name,
            ms.last_name,
            ms.class,
            sc.name as class_name,
            sf.id as fee_id,
            sf.total_amount_to_pay,
            sf.discount_amount,
            sf.currently_paying_amount,
            sf.remain_amount,
            sf.student_status,
            sf.invoiceno,
            CASE 
                WHEN sf.student_status = 3 THEN 'Scholarship'
                WHEN sf.id IS NULL THEN 'No Fee Record'
                WHEN sf.remain_amount = 0 THEN 'Paid'
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount > 0 THEN 'Partial'
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount = 0 THEN 'Defaulter'
                ELSE 'Unknown'
            END as payment_status,
            CASE 
                WHEN sf.student_status = 3 THEN 'scholarship'
                WHEN sf.remain_amount = 0 THEN 'paid'
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount > 0 THEN 'partial'
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount = 0 THEN 'defaulter'
                ELSE 'no_fee'
            END as status_key
        FROM manage_student ms
        LEFT JOIN school_class sc ON ms.class = sc.id
        LEFT JOIN student_fee sf ON ms.id = sf.student_id 
            AND sf.session = ? 
            AND sf.term_id = ?
            AND sf.class = ?
            AND sf.create_by_userid = ?
        WHERE ms.create_by_userid = ?
            AND ms.class = ?
            AND ms.session = ?
            AND ms.term_id = ?
        ORDER BY 
            CASE 
                WHEN sf.student_status = 3 THEN 1
                WHEN sf.remain_amount = 0 THEN 2
                ELSE 3
            END,
            ms.first_name ASC";

$params = [
    $selectedSession, $selectedTerm, $selectedClass, $create_by_userid,
    $create_by_userid, $selectedClass, $selectedSession, $selectedTerm
];

$reportData = db_get_rows($sql, $params);

// Filter by status
if ($selectedStatus !== 'all' && !empty($reportData)) {
    $reportData = array_filter($reportData, function($row) use ($selectedStatus) {
        return ($row['status_key'] ?? '') == $selectedStatus;
    });
    $reportData = array_values($reportData);
}

// Calculate summary
$summary = [
    'total' => count($reportData),
    'paid' => 0,
    'defaulter' => 0,
    'partial' => 0,
    'scholarship' => 0,
    'no_fee' => 0,
    'total_outstanding' => 0,
    'total_collected' => 0
];

foreach ($reportData as $row) {
    $status = $row['status_key'] ?? 'no_fee';
    $summary[$status] = ($summary[$status] ?? 0) + 1;
    $summary['total_outstanding'] += (float)($row['remain_amount'] ?? 0);
    $summary['total_collected'] += (float)($row['currently_paying_amount'] ?? 0);
}

$statusLabels = [
    'paid' => '✅ Paid',
    'defaulter' => '❌ Defaulter',
    'partial' => '⏳ Partial',
    'scholarship' => '🎓 Scholarship',
    'no_fee' => 'No Fee'
];

$reportTitle = ($selectedStatus == 'all') ? 'FEE STATUS REPORT' : strtoupper($statusLabels[$selectedStatus] ?? 'REPORT');

// ============================================================================
// GENERATE HTML
// ============================================================================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= $reportTitle ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1B3058;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
        }
        .school-moto {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
        .school-address {
            font-size: 9px;
            color: #888;
        }
        .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #1B3058;
            margin: 10px 0 5px;
        }
        .report-subtitle {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        .summary-grid {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .summary-box {
            padding: 8px 16px;
            border-radius: 4px;
            text-align: center;
            min-width: 100px;
        }
        .summary-box .number { font-size: 16px; font-weight: 700; }
        .summary-box .label { font-size: 9px; color: #666; }
        .summary-box.paid { background: #d4edda; }
        .summary-box.paid .number { color: #155724; }
        .summary-box.defaulter { background: #f8d7da; }
        .summary-box.defaulter .number { color: #721c24; }
        .summary-box.partial { background: #fff3cd; }
        .summary-box.partial .number { color: #856404; }
        .summary-box.scholarship { background: #d1ecf1; }
        .summary-box.scholarship .number { color: #0c5460; }
        .summary-box.total { background: #e8f0fe; }
        .summary-box.total .number { color: #1B3058; }
        .summary-box.collected { background: #d4edda; }
        .summary-box.collected .number { color: #28a745; }
        .summary-box.outstanding { background: #f8d7da; }
        .summary-box.outstanding .number { color: #dc3545; }
        .table-wrapper { margin-top: 10px; }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .report-table th {
            background: #1B3058;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
        }
        .report-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
        }
        .report-table tr:hover { background: #f8f9ff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .status-paid { color: #28a745; font-weight: 700; }
        .status-defaulter { color: #dc3545; font-weight: 700; }
        .status-partial { color: #856404; font-weight: 700; }
        .status-scholarship { color: #17a2b8; font-weight: 700; }
        .status-no_fee { color: #6c757d; font-weight: 700; }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            margin-top: 20px;
            padding-top: 5px;
            border-top: 1px solid #333;
            width: 200px;
            text-align: center;
            font-size: 9px;
        }
        @media print {
            body { padding: 10px; }
            .report-table th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></div>
    <div class="school-moto"><?= htmlspecialchars($school['moto'] ?? 'Excellence in Education') ?></div>
    <div class="school-address">
        <?= htmlspecialchars($school['location'] ?? '') ?>
        <?php if (!empty($school['phone'])): ?> | Tel: <?= htmlspecialchars($school['phone']) ?><?php endif; ?>
        <?php if (!empty($school['email'])): ?> | Email: <?= htmlspecialchars($school['email']) ?><?php endif; ?>
    </div>
    <div class="report-title"><?= $reportTitle ?></div>
    <div class="report-subtitle">
        Session: <?= htmlspecialchars($sessionName ?? 'N/A') ?> | 
        Term: <?= htmlspecialchars($termName ?? 'N/A') ?> | 
        Class: <?= htmlspecialchars($className ?? 'N/A') ?>
        <?php if ($selectedStatus !== 'all'): ?>
            | Status: <?= $statusLabels[$selectedStatus] ?? '' ?>
        <?php endif; ?>
    </div>
</div>

<!-- SUMMARY -->
<div class="summary-grid">
    <div class="summary-box total">
        <div class="number"><?= $summary['total'] ?></div>
        <div class="label">Total Students</div>
    </div>
    <div class="summary-box collected">
        <div class="number">₦<?= number_format($summary['total_collected'], 0) ?></div>
        <div class="label">Total Collected</div>
    </div>
    <div class="summary-box outstanding">
        <div class="number">₦<?= number_format($summary['total_outstanding'], 0) ?></div>
        <div class="label">Total Outstanding</div>
    </div>
    <div class="summary-box paid">
        <div class="number"><?= ($summary['paid'] ?? 0) + ($summary['scholarship'] ?? 0) ?></div>
        <div class="label">Paid (incl. Scholarship)</div>
    </div>
    <div class="summary-box defaulter">
        <div class="number"><?= ($summary['defaulter'] ?? 0) + ($summary['partial'] ?? 0) ?></div>
        <div class="label">Defaulters</div>
    </div>
</div>

<!-- TABLE -->
<div class="table-wrapper">
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Total Fee (₦)</th>
                <th>Discount (₦)</th>
                <th>Amount Paid (₦)</th>
                <th>Outstanding (₦)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reportData)): ?>
                <?php $counter = 0; foreach ($reportData as $row): $counter++; ?>
                <tr>
                    <td><?= $counter ?></td>
                    <td><?= htmlspecialchars($row['student_id'] ?? '') ?></td>
                    <td><strong><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></strong></td>
                    <td class="text-right"><?= number_format((float)($row['total_amount_to_pay'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= number_format((float)($row['discount_amount'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= number_format((float)($row['currently_paying_amount'] ?? 0), 2) ?></td>
                    <td class="text-right" style="font-weight:700; color:<?= ((float)($row['remain_amount'] ?? 0) > 0) ? '#dc3545' : '#28a745' ?>;">
                        <?= number_format((float)($row['remain_amount'] ?? 0), 2) ?>
                    </td>
                    <td class="status-<?= $row['status_key'] ?? 'no_fee' ?>">
                        <?= $statusLabels[$row['status_key'] ?? 'no_fee'] ?? 'Unknown' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#999;">No records found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- FOOTER -->
<div class="footer">
    <div>
        <div><strong>Generated on:</strong> <?= date('d M Y H:i:s') ?></div>
        <div style="margin-top:5px; font-size:8px; color:#aaa;">This report is system-generated and requires verification.</div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:9px; font-weight:600; color:#1B3058;"><?= htmlspecialchars($school['name'] ?? '') ?></div>
        <div class="signature-line">
            Authorized Signature<br>
            <span style="font-size:8px;">_______________________________</span>
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

$filename = 'Fee_Status_Report_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
?>