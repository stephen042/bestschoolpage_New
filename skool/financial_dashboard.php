<?php
/**
 * ============================================================================
 * FINANCIAL DASHBOARD - Complete School Financial Overview
 * ============================================================================
 * Description: Real-time financial dashboard with charts, summaries, and reports
 * Features: Income overview, fee collection, expense tracking, payment status
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Financial Dashboard";
$FileName = 'financial_dashboard.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedYear = $_GET['year'] ?? date('Y');

// ============================================================================
// GET DATA FOR FILTERS
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session 
     WHERE create_by_userid = ? 
     ORDER BY id DESC",
    [$create_by_userid]
);

$terms = db_get_rows(
    "SELECT * FROM school_term 
     WHERE create_by_userid = ? 
     ORDER BY id ASC",
    [$create_by_userid]
);

// Get school details
$school = db_get_row(
    "SELECT * FROM school_register WHERE id = ?",
    [$create_by_userid]
);

// Set default session/term before summary calculations so cards reflect the same default view as the main dashboard
if (empty($selectedSession) && !empty($sessions)) {
    $selectedSession = $sessions[0]['id'];
}
if (empty($selectedTerm) && !empty($terms)) {
    $selectedTerm = $terms[0]['id'];
}

// ============================================================================
// 1. FEE COLLECTION SUMMARY
// ============================================================================
$feeSummary = [
    'total_students' => 0,
    'total_fees' => 0,
    'total_collected' => 0,
    'total_outstanding' => 0,
    'total_discount' => 0,
    'paid_count' => 0,
    'partial_count' => 0,
    'defaulter_count' => 0,
    'scholarship_count' => 0,
    'no_fee_count' => 0,
    'collection_rate' => 0
];

// Only run fee query if session and term are selected
if (!empty($selectedSession) && !empty($selectedTerm)) {
    $studentFilterSql = "ms.create_by_userid = ?";
    $studentFilterParams = [$create_by_userid];

    if (!empty($selectedSession)) {
        $studentFilterSql .= " AND ms.session = ?";
        $studentFilterParams[] = $selectedSession;
    }

    if (!empty($selectedTerm)) {
        $studentFilterSql .= " AND ms.term_id = ?";
        $studentFilterParams[] = $selectedTerm;
    }

    $feeQuery = "SELECT 
                    COUNT(DISTINCT ms.id) as total_students,
                    COALESCE(SUM(fee.total_amount_to_pay), 0) as total_fees,
                    COALESCE(SUM(fee.currently_paying_amount), 0) as total_collected,
                    COALESCE(SUM(CASE WHEN (fee.total_amount_to_pay - fee.discount_amount - fee.currently_paying_amount) > 0 THEN (fee.total_amount_to_pay - fee.discount_amount - fee.currently_paying_amount) ELSE 0 END), 0) as total_outstanding,
                    COALESCE(SUM(fee.discount_amount), 0) as total_discount,
                    SUM(CASE WHEN fee.student_status = 3 THEN 1 ELSE 0 END) as scholarship_count,
                    SUM(CASE WHEN fee.remain_amount = 0 AND fee.student_status != 3 THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN fee.remain_amount > 0 AND fee.currently_paying_amount > 0 THEN 1 ELSE 0 END) as partial_count,
                    SUM(CASE WHEN fee.remain_amount > 0 AND fee.currently_paying_amount = 0 AND fee.student_status != 3 THEN 1 ELSE 0 END) as defaulter_count,
                    SUM(CASE WHEN fee.id IS NULL THEN 1 ELSE 0 END) as no_fee_count
                FROM manage_student ms
                LEFT JOIN (
                    SELECT sf.student_id, sf.total_amount_to_pay, sf.currently_paying_amount, sf.remain_amount, sf.discount_amount, sf.student_status, sf.id
                    FROM student_fee sf
                    INNER JOIN (
                        SELECT student_id, MAX(id) AS latest_fee_id
                        FROM student_fee
                        WHERE session = ?
                          AND term_id = ?
                          AND create_by_userid = ?
                        GROUP BY student_id
                    ) latest_fee ON latest_fee.latest_fee_id = sf.id
                ) fee ON fee.student_id = ms.id
                WHERE $studentFilterSql";

    $feeParams = [$selectedSession, $selectedTerm, $create_by_userid, ...$studentFilterParams];
    $feeSummaryResult = db_get_row($feeQuery, $feeParams);

    if ($feeSummaryResult) {
        $feeSummary = array_merge($feeSummary, $feeSummaryResult);
        $netFeeAmount = max((float)$feeSummary['total_fees'] - (float)$feeSummary['total_discount'], 0);
        $feeSummary['collection_rate'] = ($netFeeAmount > 0)
            ? round(((float)$feeSummary['total_collected'] / $netFeeAmount) * 100, 2)
            : 0;
    }
}

// ============================================================================
// 2. MONTHLY INCOME TREND (Last 12 months)
// ============================================================================
$monthlyIncome = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    
    $income = db_get_val(
        "SELECT COALESCE(SUM(currently_paying_amount), 0) 
         FROM student_fee_transcation 
         WHERE create_by_userid = ? 
         AND DATE_FORMAT(create_at, '%Y-%m') = ?",
        [$create_by_userid, $month]
    );
    
    $monthlyIncome[] = [
        'month' => $monthName,
        'income' => (float)$income,
        'month_key' => $month
    ];
}

// ============================================================================
// 3. TERM INCOME COMPARISON
// ============================================================================
$termIncome = [];
$termQuery = "SELECT 
                t.term,
                COALESCE(SUM(sft.currently_paying_amount), 0) as total_income,
                COALESCE(SUM(sft.remain_amount), 0) as total_outstanding
            FROM student_fee_transcation sft
            LEFT JOIN school_term t ON sft.term_id = t.id
            WHERE sft.create_by_userid = ?
            GROUP BY sft.term_id
            ORDER BY sft.term_id DESC
            LIMIT 5";

$termIncomeResult = db_get_rows($termQuery, [$create_by_userid]);
foreach ($termIncomeResult as $row) {
    $termIncome[] = [
        'term' => $row['term'] ?? 'N/A',
        'income' => (float)$row['total_income'],
        'outstanding' => (float)$row['total_outstanding']
    ];
}

// ============================================================================
// 4. PAYMENT METHOD BREAKDOWN
// ============================================================================
$paymentMethods = [];
$methodQuery = "SELECT 
                    sfs.payment_mode,
                    COUNT(*) as count,
                    SUM(COALESCE(sfs.fees_amount, 0)) as total_amount
                FROM student_fee_sturcture sfs
                INNER JOIN student_fee sf ON sf.id = sfs.student_fee_id
                WHERE sf.create_by_userid = ?
                AND sfs.payment_mode IS NOT NULL
                AND sfs.payment_mode != 0
                GROUP BY sfs.payment_mode
                ORDER BY total_amount DESC";

$methodResult = db_get_rows($methodQuery, [$create_by_userid]);
$methodLabels = [
    1 => 'Bank',
    2 => 'Cash',
    3 => 'POS',
    4 => 'Bank Transfer',
    5 => 'Scholarship'
];

foreach ($methodResult as $row) {
    $mode = (int)($row['payment_mode'] ?? 0);
    if ($mode > 0 && isset($methodLabels[$mode])) {
        $paymentMethods[] = [
            'method' => $methodLabels[$mode],
            'count' => (int)$row['count'],
            'amount' => (float)$row['total_amount']
        ];
    }
}

// ============================================================================
// 5. CLASS WISE FEE COLLECTION - FIXED
// ============================================================================
$classWise = [];
if (!empty($selectedSession) && !empty($selectedTerm)) {
    $classQuery = "SELECT 
                        sc.name as class_name,
                        COUNT(DISTINCT ms.id) as student_count,
                        COALESCE(SUM(fee.total_amount_to_pay), 0) as total_fee,
                        COALESCE(SUM(fee.currently_paying_amount), 0) as collected,
                        COALESCE(SUM(fee.remain_amount), 0) as outstanding
                    FROM manage_student ms
                    LEFT JOIN school_class sc ON ms.class = sc.id
                    LEFT JOIN (
                        SELECT sf.student_id, sf.total_amount_to_pay, sf.currently_paying_amount, sf.remain_amount
                        FROM student_fee sf
                        INNER JOIN (
                            SELECT student_id, MAX(id) AS latest_fee_id
                            FROM student_fee
                            WHERE session = ?
                              AND term_id = ?
                              AND create_by_userid = ?
                            GROUP BY student_id
                        ) latest_fee ON latest_fee.latest_fee_id = sf.id
                    ) fee ON fee.student_id = ms.id
                    WHERE $studentFilterSql
                    GROUP BY ms.class
                    ORDER BY collected DESC
                    LIMIT 10";

    $classResult = db_get_rows($classQuery, [$selectedSession, $selectedTerm, $create_by_userid, ...$studentFilterParams]);
    foreach ($classResult as $row) {
        $classWise[] = [
            'class_name' => $row['class_name'] ?? 'N/A',
            'student_count' => (int)$row['student_count'],
            'total_fee' => (float)$row['total_fee'],
            'collected' => (float)$row['collected'],
            'outstanding' => (float)$row['outstanding'],
            'collection_rate' => ($row['total_fee'] > 0) 
                ? round(($row['collected'] / $row['total_fee']) * 100, 2) 
                : 0
        ];
    }
}

// ============================================================================
// 6. RECENT TRANSACTIONS
// ============================================================================
$recentTransactions = db_get_rows(
    "SELECT 
        sft.*,
        ms.first_name,
        ms.last_name,
        ms.student_id as roll_no
    FROM student_fee_transcation sft
    LEFT JOIN manage_student ms ON sft.student_id = ms.id
    WHERE sft.create_by_userid = ?
    ORDER BY sft.create_at DESC
    LIMIT 10",
    [$create_by_userid]
);

// ============================================================================
// 7. DAILY INCOME (Last 7 days)
// ============================================================================
$dailyIncome = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dateLabel = date('D d', strtotime("-$i days"));
    
    $income = db_get_val(
        "SELECT COALESCE(SUM(currently_paying_amount), 0) 
         FROM student_fee_transcation 
         WHERE create_by_userid = ? 
         AND DATE(create_at) = ?",
        [$create_by_userid, $date]
    );
    
    $dailyIncome[] = [
        'date' => $dateLabel,
        'income' => (float)$income
    ];
}

// ============================================================================
// 8. TOTAL EXPENSES - Using correct column names
// ============================================================================
$totalExpenses = 0;
try {
    // Use the correct column: create_by_userid (exists in your table)
    $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total_expenses 
                     FROM school_expenses 
                     WHERE create_by_userid = ?";
    $expenseResult = db_get_row($expenseQuery, [$create_by_userid]);
    if ($expenseResult) {
        $totalExpenses = (float)$expenseResult['total_expenses'];
    }
} catch (Exception $e) {
    // If query fails, use 0
    $totalExpenses = 0;
}

// ============================================================================
// 9. SUMMARY CARDS DATA
// ============================================================================
$summaryCards = [
    [
        'label' => 'Total Students',
        'value' => number_format($feeSummary['total_students']),
        'icon' => 'fa-users',
        'color' => 'primary'
    ],
    [
        'label' => 'Total Fees',
        'value' => '₦' . number_format($feeSummary['total_fees'], 2),
        'icon' => 'fa-money',
        'color' => 'info'
    ],
    [
        'label' => 'Total Collected',
        'value' => '₦' . number_format($feeSummary['total_collected'], 2),
        'icon' => 'fa-check-circle',
        'color' => 'success'
    ],
    [
        'label' => 'Outstanding',
        'value' => '₦' . number_format($feeSummary['total_outstanding'], 2),
        'icon' => 'fa-exclamation-triangle',
        'color' => 'danger'
    ],
    [
        'label' => 'Collection Rate',
        'value' => $feeSummary['collection_rate'] . '%',
        'icon' => 'fa-percent',
        'color' => 'warning'
    ],
    [
        'label' => 'Expenses',
        'value' => '₦' . number_format($totalExpenses, 2),
        'icon' => 'fa-shopping-cart',
        'color' => 'secondary'
    ]
];

// ============================================================================
// 10. STATUS BREAKDOWN
// ============================================================================
$statusBreakdown = [
    ['label' => '✅ Paid', 'count' => $feeSummary['paid_count'], 'color' => '#28a745'],
    ['label' => '⏳ Partial', 'count' => $feeSummary['partial_count'], 'color' => '#ffc107'],
    ['label' => '❌ Defaulter', 'count' => $feeSummary['defaulter_count'], 'color' => '#dc3545'],
    ['label' => '🎓 Scholarship', 'count' => $feeSummary['scholarship_count'], 'color' => '#17a2b8'],
    ['label' => 'No Fee', 'count' => $feeSummary['no_fee_count'], 'color' => '#6c757d']
];

?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title><?= htmlspecialchars($PageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .dashboard-container { max-width: 1600px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; font-size: 28px; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .filter-card { background: #fff; border-radius: 16px; padding: 20px 25px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .filter-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 180px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .filter-select { width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; transition: all 0.2s; }
        .filter-select:focus { border-color: #1B3058; outline: none; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; }
        .btn-primary { background: #1B3058; color: #fff; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 25px; }
        .summary-card { background: #fff; border-radius: 14px; padding: 20px 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-left: 4px solid #1B3058; transition: transform 0.2s; }
        .summary-card:hover { transform: translateY(-3px); }
        .summary-card .icon { font-size: 28px; margin-bottom: 8px; display: block; }
        .summary-card .label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; }
        .summary-card .value { font-size: 24px; font-weight: 700; color: #1B3058; margin-top: 4px; }
        .summary-card.primary { border-color: #1B3058; }
        .summary-card.success { border-color: #28a745; }
        .summary-card.danger { border-color: #dc3545; }
        .summary-card.warning { border-color: #ffc107; }
        .summary-card.info { border-color: #17a2b8; }
        .summary-card.secondary { border-color: #6c757d; }
        
        .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .chart-card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .chart-card h4 { font-size: 15px; font-weight: 600; color: #1B3058; margin-bottom: 15px; }
        .chart-card .chart-container { position: relative; height: 250px; }
        .chart-card .chart-container.pie { height: 300px; }
        
        .recent-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .recent-table th { background: #f8f9fa; color: #1B3058; padding: 10px 14px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .recent-table td { padding: 10px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .recent-table tr:hover { background: #f8f9ff; }
        
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .status-donut { display: flex; justify-content: center; align-items: center; gap: 30px; flex-wrap: wrap; }
        .status-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .status-dot { width: 14px; height: 14px; border-radius: 50%; display: inline-block; }
        
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        
        @media (max-width: 1024px) {
            .chart-grid { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .chart-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="dashboard-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-dashboard"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Real-time financial overview and analytics for your school</p>
                </div>
                
                <?= msg($stat) ?>
                
                <!-- Filter Bar -->
                <div class="filter-card">
                    <form method="GET" action="" id="filterForm">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Session</label>
                                <select name="session" class="filter-select" onchange="this.form.submit()">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($sessions as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['session']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Term</label>
                                <select name="term_id" class="filter-select" onchange="this.form.submit()">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($terms as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['term']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px; align-items: flex-end;">
                                <a href="<?= $FileName ?>" class="btn btn-primary"><i class="fa fa-refresh"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Summary Cards -->
                <div class="summary-grid">
                    <?php foreach ($summaryCards as $card): ?>
                        <div class="summary-card <?= $card['color'] ?>">
                            <span class="icon"><i class="fa <?= $card['icon'] ?>"></i></span>
                            <div class="label"><?= $card['label'] ?></div>
                            <div class="value"><?= $card['value'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Charts -->
                <div class="chart-grid">
                    <!-- Chart 1: Monthly Income Trend -->
                    <div class="chart-card">
                        <h4><i class="fa fa-line-chart"></i> Monthly Income Trend</h4>
                        <div class="chart-container">
                            <canvas id="monthlyIncomeChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Chart 2: Payment Status Breakdown -->
                    <div class="chart-card">
                        <h4><i class="fa fa-pie-chart"></i> Payment Status Breakdown</h4>
                        <div class="chart-container pie">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="status-donut mt-20">
                            <?php foreach ($statusBreakdown as $status): ?>
                                <span class="status-item">
                                    <span class="status-dot" style="background:<?= $status['color'] ?>"></span>
                                    <?= $status['label'] ?> (<?= $status['count'] ?>)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="chart-grid">
                    <!-- Chart 3: Class Wise Collection -->
                    <div class="chart-card">
                        <h4><i class="fa fa-bar-chart"></i> Class Wise Collection</h4>
                        <div class="chart-container">
                            <canvas id="classChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Chart 4: Daily Income (Last 7 Days) -->
                    <div class="chart-card">
                        <h4><i class="fa fa-calendar"></i> Daily Income (Last 7 Days)</h4>
                        <div class="chart-container">
                            <canvas id="dailyIncomeChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="chart-card" style="margin-top: 20px;">
                    <h4><i class="fa fa-history"></i> Recent Transactions</h4>
                    <div style="overflow-x: auto;">
                        <table class="recent-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Invoice</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): ?>
                                    <?php $i = 0; foreach ($recentTransactions as $trans): $i++; ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars(($trans['first_name'] ?? '') . ' ' . ($trans['last_name'] ?? '')) ?>
                                            </strong>
                                            <br><small style="color:#888;"><?= htmlspecialchars($trans['roll_no'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($trans['invoiceno'] ?? 'N/A') ?></td>
                                        <td style="font-weight:700; color:#28a745;">
                                            ₦<?= number_format((float)($trans['currently_paying_amount'] ?? 0), 2) ?>
                                        </td>
                                        <td style="color:<?= ((float)($trans['remain_amount'] ?? 0) > 0) ? '#dc3545' : '#28a745' ?>;">
                                            ₦<?= number_format((float)($trans['remain_amount'] ?? 0), 2) ?>
                                        </td>
                                        <td><?= date('d M Y H:i', strtotime($trans['create_at'] ?? 'now')) ?></td>
                                        <td>
                                            <?php if ((float)($trans['remain_amount'] ?? 0) == 0): ?>
                                                <span class="badge badge-success">Paid</span>
                                            <?php elseif ((float)($trans['currently_paying_amount'] ?? 0) > 0): ?>
                                                <span class="badge badge-warning">Partial</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted" style="padding:30px;">
                                            No recent transactions found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script>
// ============================================================================
// CHART.JS CONFIGURATION
// ============================================================================

// Colors
const colors = {
    primary: '#1B3058',
    success: '#28a745',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#17a2b8',
    secondary: '#6c757d',
    light: '#e9ecef',
};

const chartColors = [
    '#1B3058', '#f21151', '#28a745', '#ffc107', '#17a2b8', 
    '#6c757d', '#dc3545', '#fd7e14', '#20c997', '#6610f2'
];

// ============================================================================
// 1. MONTHLY INCOME TREND
// ============================================================================
const monthlyCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlyIncome, 'month')) ?>,
        datasets: [{
            label: 'Income (₦)',
            data: <?= json_encode(array_column($monthlyIncome, 'income')) ?>,
            borderColor: colors.primary,
            backgroundColor: colors.primary + '20',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.primary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₦' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// ============================================================================
// 2. PAYMENT STATUS CHART (Pie)
// ============================================================================
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusLabels = <?= json_encode(array_column($statusBreakdown, 'label')) ?>;
const statusCounts = <?= json_encode(array_column($statusBreakdown, 'count')) ?>;
const statusColors = <?= json_encode(array_column($statusBreakdown, 'color')) ?>;

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: statusColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '65%'
    }
});

// ============================================================================
// 3. CLASS WISE COLLECTION CHART
// ============================================================================
const classCtx = document.getElementById('classChart').getContext('2d');
const classLabels = <?= json_encode(array_column($classWise, 'class_name')) ?>;
const classCollected = <?= json_encode(array_column($classWise, 'collected')) ?>;
const classOutstanding = <?= json_encode(array_column($classWise, 'outstanding')) ?>;

new Chart(classCtx, {
    type: 'bar',
    data: {
        labels: classLabels,
        datasets: [
            {
                label: 'Collected (₦)',
                data: classCollected,
                backgroundColor: colors.success + '80',
                borderColor: colors.success,
                borderWidth: 2
            },
            {
                label: 'Outstanding (₦)',
                data: classOutstanding,
                backgroundColor: colors.danger + '80',
                borderColor: colors.danger,
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    padding: 15,
                    font: { size: 11 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ₦' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// ============================================================================
// 4. DAILY INCOME CHART
// ============================================================================
const dailyCtx = document.getElementById('dailyIncomeChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($dailyIncome, 'date')) ?>,
        datasets: [{
            label: 'Daily Income (₦)',
            data: <?= json_encode(array_column($dailyIncome, 'income')) ?>,
            backgroundColor: [
                '#1B3058', '#2a4780', '#1B3058', '#2a4780', 
                '#1B3058', '#2a4780', '#1B3058'
            ],
            borderRadius: 4,
            borderColor: '#1B3058',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₦' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>