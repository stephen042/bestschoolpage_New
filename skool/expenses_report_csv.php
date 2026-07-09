<?php
/**
 * ============================================================================
 * EXPENSES REPORT CSV - Excel Export
 * ============================================================================
 * Description: Export financial report to CSV/Excel format
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';

// ============================================================================
// GET DATA
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

$query .= " ORDER BY create_at DESC";
$transactions = db_get_rows($query, $params);

// ============================================================================
// CALCULATE TOTALS
// ============================================================================
$totalIncome = 0;
$totalExpenses = 0;
$runningBalance = 0;

foreach ($transactions as $trans) {
    if ((int)$trans['credit_type'] == 1) {
        $totalIncome += (float)$trans['amount'];
    } else {
        $totalExpenses += (float)$trans['amount'];
    }
}
$netSurplus = $totalIncome - $totalExpenses;

// ============================================================================
// HEADERS FOR DOWNLOAD
// ============================================================================
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Financial_Report_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

// ============================================================================
// SUMMARY SECTION
// ============================================================================
fputcsv($output, ['FINANCIAL REPORT SUMMARY']);
fputcsv($output, ['Generated:', date('d M Y H:i:s')]);
fputcsv($output, []);
fputcsv($output, ['Total Income', 'Total Expenses', 'Net Surplus', 'Total Transactions']);
fputcsv($output, [
    '₦' . number_format($totalIncome, 2),
    '₦' . number_format($totalExpenses, 2),
    '₦' . number_format($netSurplus, 2),
    count($transactions)
]);
fputcsv($output, []);

// ============================================================================
// TRANSACTION DETAILS
// ============================================================================
fputcsv($output, ['TRANSACTION DETAILS']);
fputcsv($output, [
    'Date', 'Description', 'Category', 'Account', 'Type', 'Amount (₦)', 'Status'
]);

foreach ($transactions as $trans) {
    $type = ((int)$trans['credit_type'] == 1) ? 'Income' : 'Expense';
    $status = ucfirst($trans['approval_status'] ?? 'pending');
    
    fputcsv($output, [
        $trans['create_at'],
        $trans['item_description'],
        $trans['category'] ?? 'N/A',
        $trans['account_type'] ?? 'N/A',
        $type,
        number_format((float)$trans['amount'], 2),
        $status
    ]);
}

// ============================================================================
// CATEGORY BREAKDOWN
// ============================================================================
fputcsv($output, []);
fputcsv($output, ['CATEGORY BREAKDOWN']);

// Income by Category
fputcsv($output, ['INCOME BY CATEGORY']);
$incomeByCategory = [];
foreach ($transactions as $trans) {
    if ((int)$trans['credit_type'] == 1) {
        $cat = $trans['category'] ?? 'Uncategorized';
        $incomeByCategory[$cat] = ($incomeByCategory[$cat] ?? 0) + (float)$trans['amount'];
    }
}
fputcsv($output, ['Category', 'Amount (₦)']);
foreach ($incomeByCategory as $cat => $amount) {
    fputcsv($output, [$cat, number_format($amount, 2)]);
}
if (empty($incomeByCategory)) {
    fputcsv($output, ['No income data', '0.00']);
}

// Expenses by Category
fputcsv($output, []);
fputcsv($output, ['EXPENSES BY CATEGORY']);
$expenseByCategory = [];
foreach ($transactions as $trans) {
    if ((int)$trans['credit_type'] == 2) {
        $cat = $trans['category'] ?? 'Uncategorized';
        $expenseByCategory[$cat] = ($expenseByCategory[$cat] ?? 0) + (float)$trans['amount'];
    }
}
fputcsv($output, ['Category', 'Amount (₦)']);
foreach ($expenseByCategory as $cat => $amount) {
    fputcsv($output, [$cat, number_format($amount, 2)]);
}
if (empty($expenseByCategory)) {
    fputcsv($output, ['No expense data', '0.00']);
}

fclose($output);
exit;
?>