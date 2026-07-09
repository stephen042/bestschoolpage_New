<?php
/**
 * ============================================================================
 * EXPENSES REPORT PDF - Financial Report Export
 * ============================================================================
 * Description: Generate professional PDF financial reports with income statement,
 *              expense breakdown, and budget comparison
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

// ============================================================================
// GET SCHOOL DETAILS
// ============================================================================
$school = db_get_row(
    "SELECT * FROM school_register WHERE id = ?",
    [$create_by_userid]
);

// ============================================================================
// GET SESSION AND TERM NAMES
// ============================================================================
$sessionName = $selectedSession ? db_get_val(
    "SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?",
    [$selectedSession, $create_by_userid]
) : 'All Sessions';

$termName = $selectedTerm ? db_get_val(
    "SELECT term FROM school_term WHERE id = ? AND create_by_userid = ?",
    [$selectedTerm, $create_by_userid]
) : 'All Terms';

// ============================================================================
// GET TRANSACTION DATA
// ============================================================================
$query = "SELECT * FROM school_expenses WHERE create_by_userid = ? AND approval_status = 'approved'";
$params = [$create_by_userid];

if (!empty($selectedSession)) {
    $query .= " AND session_id = ?";
    $params[] = (int)$selectedSession;
}
if (!empty($selectedTerm)) {
    $query .= " AND term_id = ?";
    $params[] = (int)$selectedTerm;
}

$query .= " ORDER BY create_at ASC";
$transactions = db_get_rows($query, $params);

// ============================================================================
// CALCULATE TOTALS
// ============================================================================
$totalIncome = 0;
$totalExpenses = 0;
$expenseCategories = [];
$incomeSources = [];

foreach ($transactions as $trans) {
    $amount = (float)$trans['amount'];
    $category = $trans['category'] ?? 'Uncategorized';
    
    if ((int)$trans['credit_type'] == 1) {
        $totalIncome += $amount;
        $incomeSources[$category] = ($incomeSources[$category] ?? 0) + $amount;
    } else {
        $totalExpenses += $amount;
        $expenseCategories[$category] = ($expenseCategories[$category] ?? 0) + $amount;
    }
}

$netSurplus = $totalIncome - $totalExpenses;
$totalTransactions = count($transactions);

// Sort categories by amount (highest first)
arsort($expenseCategories);
arsort($incomeSources);

// ============================================================================
// GENERATE HTML
// ============================================================================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Financial Report - <?= date('Y-m-d') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
            color: #333;
            background: #fff;
        }
        
        .report-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* ===== HEADER ===== */
        .header {
            text-align: center;
            border-bottom: 2px solid #1B3058;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .school-name {
            font-size: 22px;
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
            font-size: 18px;
            font-weight: 700;
            color: #1B3058;
            margin: 10px 0 4px;
        }
        
        .report-subtitle {
            font-size: 11px;
            color: #666;
        }
        
        /* ===== SUMMARY CARDS ===== */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            padding: 12px 16px;
            border-radius: 6px;
            text-align: center;
        }
        
        .summary-card .number {
            font-size: 18px;
            font-weight: 700;
        }
        
        .summary-card .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        .summary-card.income {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .summary-card.income .number { color: #155724; }
        
        .summary-card.expense {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .summary-card.expense .number { color: #721c24; }
        
        .summary-card.surplus {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
        }
        .summary-card.surplus .number { color: #0c5460; }
        
        .summary-card.transactions {
            background: #e8f0fe;
            border-left: 4px solid #1B3058;
        }
        .summary-card.transactions .number { color: #1B3058; }
        
        /* ===== SECTIONS ===== */
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1B3058;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        
        /* ===== TABLES ===== */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        
        .report-table th {
            background: #1B3058;
            color: #fff;
            padding: 6px 10px;
            text-align: left;
            font-weight: 600;
        }
        
        .report-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .report-table .text-right {
            text-align: right;
        }
        
        .report-table .total-row td {
            font-weight: 700;
            border-top: 2px solid #1B3058;
            padding-top: 8px;
        }
        
        .report-table .total-row td:last-child {
            font-size: 11px;
            color: #1B3058;
        }
        
        .report-table .category-row td:first-child {
            padding-left: 20px;
        }
        
        .report-table .category-row td:last-child {
            font-weight: 600;
        }
        
        /* ===== CATEGORY BREAKDOWN ===== */
        .category-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .category-box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
        }
        
        .category-box h5 {
            font-size: 11px;
            font-weight: 700;
            color: #1B3058;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 9px;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-item .cat-name {
            font-weight: 500;
        }
        
        .category-item .cat-amount {
            font-weight: 600;
        }
        
        .category-item .cat-percent {
            font-size: 8px;
            color: #888;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 8px;
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
        
        /* ===== WATERMARK ===== */
        .watermark {
            position: fixed;
            bottom: 50px;
            right: 50px;
            opacity: 0.04;
            font-size: 50px;
            font-weight: 700;
            color: #1B3058;
            transform: rotate(-20deg);
            pointer-events: none;
            z-index: 0;
        }
        
        /* ===== PRINT ===== */
        @media print {
            body { padding: 10px; }
            .report-table th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .mt-20 { margin-top: 20px; }
        .mb-10 { margin-bottom: 10px; }
        .badge { font-size: 8px; padding: 2px 8px; border-radius: 10px; background: #e8f0fe; color: #1B3058; }
    </style>
</head>
<body>

<div class="watermark">FINANCIAL REPORT</div>

<div class="report-wrapper">

    <!-- ===== HEADER ===== -->
    <div class="header">
        <div class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></div>
        <div class="school-moto"><?= htmlspecialchars($school['moto'] ?? 'Excellence in Education') ?></div>
        <div class="school-address">
            <?= htmlspecialchars($school['location'] ?? '') ?>
            <?php if (!empty($school['phone'])): ?> | Tel: <?= htmlspecialchars($school['phone']) ?><?php endif; ?>
            <?php if (!empty($school['email'])): ?> | Email: <?= htmlspecialchars($school['email']) ?><?php endif; ?>
        </div>
        <div class="report-title">FINANCIAL REPORT</div>
        <div class="report-subtitle">
            Session: <?= htmlspecialchars($sessionName) ?> | Term: <?= htmlspecialchars($termName) ?>
            <br>
            <span style="font-size:9px; color:#999;">Generated: <?= date('d M Y H:i:s') ?></span>
        </div>
    </div>

    <!-- ===== SUMMARY CARDS ===== -->
    <div class="summary-grid">
        <div class="summary-card income">
            <div class="number">₦<?= number_format($totalIncome, 2) ?></div>
            <div class="label">Total Income</div>
        </div>
        <div class="summary-card expense">
            <div class="number">₦<?= number_format($totalExpenses, 2) ?></div>
            <div class="label">Total Expenses</div>
        </div>
        <div class="summary-card surplus">
            <div class="number" style="color:<?= ($netSurplus >= 0) ? '#155724' : '#721c24' ?>;">
                ₦<?= number_format($netSurplus, 2) ?>
            </div>
            <div class="label">Net Surplus / (Deficit)</div>
        </div>
        <div class="summary-card transactions">
            <div class="number"><?= $totalTransactions ?></div>
            <div class="label">Total Transactions</div>
        </div>
    </div>

    <!-- ===== INCOME STATEMENT ===== -->
    <div class="section">
        <div class="section-title">📊 Income Statement</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:60%;">Description</th>
                    <th style="width:40%;" class="text-right">Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Income Section -->
                <tr>
                    <td><strong>INCOME</strong></td>
                    <td class="text-right"></td>
                </tr>
                <?php if (!empty($incomeSources)): ?>
                    <?php foreach ($incomeSources as $source => $amount): ?>
                    <tr class="category-row">
                        <td style="padding-left:20px;"><?= htmlspecialchars($source) ?></td>
                        <td class="text-right"><?= number_format($amount, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="category-row">
                        <td style="padding-left:20px; color:#999;">No income recorded</td>
                        <td class="text-right">0.00</td>
                    </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td><strong>Total Income</strong></td>
                    <td class="text-right"><strong><?= number_format($totalIncome, 2) ?></strong></td>
                </tr>
                
                <!-- Expense Section -->
                <tr>
                    <td style="padding-top:10px;"><strong>EXPENSES</strong></td>
                    <td class="text-right"></td>
                </tr>
                <?php if (!empty($expenseCategories)): ?>
                    <?php foreach ($expenseCategories as $category => $amount): ?>
                    <tr class="category-row">
                        <td style="padding-left:20px;"><?= htmlspecialchars($category) ?></td>
                        <td class="text-right"><?= number_format($amount, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="category-row">
                        <td style="padding-left:20px; color:#999;">No expenses recorded</td>
                        <td class="text-right">0.00</td>
                    </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td><strong>Total Expenses</strong></td>
                    <td class="text-right"><strong><?= number_format($totalExpenses, 2) ?></strong></td>
                </tr>
                
                <!-- Net Surplus -->
                <tr class="total-row" style="background: <?= ($netSurplus >= 0) ? '#d4edda' : '#f8d7da' ?>;">
                    <td style="font-size:12px; color:<?= ($netSurplus >= 0) ? '#155724' : '#721c24' ?>;">
                        <strong>NET SURPLUS / (DEFICIT)</strong>
                    </td>
                    <td class="text-right" style="font-size:14px; color:<?= ($netSurplus >= 0) ? '#155724' : '#721c24' ?>;">
                        <strong>₦<?= number_format($netSurplus, 2) ?></strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ===== CATEGORY BREAKDOWN ===== -->
    <div class="section">
        <div class="section-title">📂 Expense Breakdown by Category</div>
        <div class="category-grid">
            <!-- Income Sources -->
            <div class="category-box">
                <h5>💰 Income Sources</h5>
                <?php if (!empty($incomeSources)): ?>
                    <?php foreach ($incomeSources as $source => $amount): 
                        $percent = ($totalIncome > 0) ? ($amount / $totalIncome) * 100 : 0;
                    ?>
                    <div class="category-item">
                        <span class="cat-name"><?= htmlspecialchars($source) ?></span>
                        <span class="cat-amount">
                            ₦<?= number_format($amount, 2) ?>
                            <span class="cat-percent">(<?= round($percent, 1) ?>%)</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <div class="category-item" style="border-top:2px solid #1B3058; padding-top:6px; font-weight:700;">
                        <span>TOTAL INCOME</span>
                        <span>₦<?= number_format($totalIncome, 2) ?></span>
                    </div>
                <?php else: ?>
                    <div class="text-muted" style="text-align:center; padding:10px;">No income data</div>
                <?php endif; ?>
            </div>
            
            <!-- Expense Categories -->
            <div class="category-box">
                <h5>💳 Expense Categories</h5>
                <?php if (!empty($expenseCategories)): ?>
                    <?php foreach ($expenseCategories as $category => $amount): 
                        $percent = ($totalExpenses > 0) ? ($amount / $totalExpenses) * 100 : 0;
                    ?>
                    <div class="category-item">
                        <span class="cat-name"><?= htmlspecialchars($category) ?></span>
                        <span class="cat-amount">
                            ₦<?= number_format($amount, 2) ?>
                            <span class="cat-percent">(<?= round($percent, 1) ?>%)</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <div class="category-item" style="border-top:2px solid #1B3058; padding-top:6px; font-weight:700;">
                        <span>TOTAL EXPENSES</span>
                        <span>₦<?= number_format($totalExpenses, 2) ?></span>
                    </div>
                <?php else: ?>
                    <div class="text-muted" style="text-align:center; padding:10px;">No expense data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== TRANSACTION DETAILS ===== -->
    <div class="section">
        <div class="section-title">📋 Transaction Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="text-right">Income (₦)</th>
                    <th class="text-right">Expense (₦)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php $i = 0; foreach ($transactions as $trans): $i++; ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= date('d M Y', strtotime($trans['create_at'])) ?></td>
                        <td><?= htmlspecialchars($trans['item_description']) ?></td>
                        <td><?= htmlspecialchars($trans['category'] ?? 'N/A') ?></td>
                        <td class="text-right" style="color:#28a745;">
                            <?= ($trans['credit_type'] == 1) ? number_format((float)$trans['amount'], 2) : '-' ?>
                        </td>
                        <td class="text-right" style="color:#dc3545;">
                            <?= ($trans['credit_type'] == 2) ? number_format((float)$trans['amount'], 2) : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding:15px;">
                            No transactions found for the selected period
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong><?= number_format($totalIncome, 2) ?></strong></td>
                    <td class="text-right"><strong><?= number_format($totalExpenses, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- ===== FINANCIAL SUMMARY ===== -->
    <div class="section">
        <div class="section-title">📈 Financial Summary</div>
        <table class="report-table" style="width:60%; margin:0 auto;">
            <tr>
                <td style="width:50%;"><strong>Total Income</strong></td>
                <td style="width:50%; text-align:right; color:#28a745; font-weight:700;">
                    ₦<?= number_format($totalIncome, 2) ?>
                </td>
            </tr>
            <tr>
                <td><strong>Total Expenses</strong></td>
                <td style="text-align:right; color:#dc3545; font-weight:700;">
                    ₦<?= number_format($totalExpenses, 2) ?>
                </td>
            </tr>
            <tr>
                <td><strong>Net Surplus / (Deficit)</strong></td>
                <td style="text-align:right; font-weight:700; font-size:16px; color:<?= ($netSurplus >= 0) ? '#28a745' : '#dc3545' ?>;">
                    ₦<?= number_format($netSurplus, 2) ?>
                </td>
            </tr>
            <tr>
                <td><strong>Total Transactions</strong></td>
                <td style="text-align:right; font-weight:700;"><?= $totalTransactions ?></td>
            </tr>
        </table>
    </div>

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        <div>
            <div><strong>Generated on:</strong> <?= date('d M Y H:i:s') ?></div>
            <div style="margin-top:3px; font-size:7px; color:#aaa;">
                This report is system-generated and requires verification.
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:10px; font-weight:600; color:#1B3058;">
                <?= htmlspecialchars($school['name'] ?? '') ?>
            </div>
            <div class="signature-line" style="margin-top:10px;">
                Authorized Signature<br>
                <span style="font-size:7px;">_______________________________</span>
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

$filename = 'Financial_Report_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
?>