<?php
/**
 * ============================================================================
 * INCOME & EXPENDITURE - MODERN FINANCIAL MANAGEMENT
 * ============================================================================
 * Description: Complete financial management with auto income from fees,
 *              expense approval workflow, budget tracking, and reports
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Income & Expenditure";
$FileName = 'expenses.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$currentUserId = (int)($_SESSION['userid'] ?? 0);

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedCategory = $_GET['category'] ?? '';
$selectedType = $_GET['type'] ?? '';
$selectedStatus = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// ============================================================================
// HANDLE POST - ADD EXPENSE
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    try {
        $itemDescription = trim($_POST['item_description'] ?? '');
        $accountType = trim($_POST['account_type'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $creditType = (int)($_POST['credit_type'] ?? 0);
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $termId = (int)($_POST['term_id'] ?? 0);
        
        if (empty($itemDescription)) {
            throw new Exception("Item description is required");
        }
        if ($amount <= 0) {
            throw new Exception("Valid amount is required");
        }
        if ($creditType == 0) {
            throw new Exception("Please select Income or Expense");
        }
        
        // Handle receipt upload
        $receiptPath = '';
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
            $uploadDir = '../uploads/receipts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['receipt']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetPath)) {
                $receiptPath = 'uploads/receipts/' . $fileName;
            }
        }
        
        $lastId = (int)$db->getVal("SELECT MAX(id) FROM school_expenses") + 1;
        $randomId = randomFix(15) . $lastId;
        
        $aryData = array(
            'item_description' => $itemDescription,
            'account_type' => $accountType,
            'category' => $category,
            'amount' => $amount,
            'credit_type' => $creditType,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'receipt_path' => $receiptPath,
            'approval_status' => 'pending',
            'userid' => $currentUserId,
            'usertype' => $create_by_usertype,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => $randomId,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s")
        );
        
        $flgIn = $db->insertAry("school_expenses", $aryData);
        
        if (!$flgIn) {
            throw new Exception("Failed to save record");
        }
        
        $_SESSION['success'] = "Expense recorded successfully!";
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - APPROVE EXPENSE
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_expense'])) {
    try {
        $expenseId = (int)($_POST['expense_id'] ?? 0);
        
        $updateData = array(
            'approval_status' => 'approved',
            'approved_by' => $currentUserId,
            'approved_date' => date("Y-m-d H:i:s")
        );
        
        $flgIn = $db->updateAry("school_expenses", $updateData, "where id = '" . $expenseId . "'");
        
        if ($flgIn) {
            $_SESSION['success'] = "Expense approved successfully!";
        } else {
            throw new Exception("Failed to approve expense");
        }
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - REJECT EXPENSE
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_expense'])) {
    try {
        $expenseId = (int)($_POST['expense_id'] ?? 0);
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        
        $updateData = array(
            'approval_status' => 'rejected',
            'rejection_reason' => $rejectionReason
        );
        
        $flgIn = $db->updateAry("school_expenses", $updateData, "where id = '" . $expenseId . "'");
        
        if ($flgIn) {
            $_SESSION['success'] = "Expense rejected!";
        } else {
            throw new Exception("Failed to reject expense");
        }
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// GET DATA FOR DROPDOWNS
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

// Get expense categories
$expenseCategories = db_get_rows(
    "SELECT DISTINCT category FROM school_expenses 
     WHERE create_by_userid = ? AND category != '' 
     ORDER BY category ASC",
    [$create_by_userid]
);

// ============================================================================
// BUILD TRANSACTIONS QUERY
// ============================================================================
$transactions = [];
$totalIncome = 0;
$totalExpenses = 0;
$netSurplus = 0;

$query = "SELECT * FROM school_expenses WHERE create_by_userid = ?";
$params = [$create_by_userid];

if (!empty($selectedSession)) {
    $query .= " AND session_id = ?";
    $params[] = (int)$selectedSession;
}
if (!empty($selectedTerm)) {
    $query .= " AND term_id = ?";
    $params[] = (int)$selectedTerm;
}
if (!empty($selectedCategory)) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
}
if (!empty($selectedType)) {
    $query .= " AND credit_type = ?";
    $params[] = (int)$selectedType;
}
if (!empty($selectedStatus)) {
    $query .= " AND approval_status = ?";
    $params[] = $selectedStatus;
}
if (!empty($dateFrom)) {
    $query .= " AND DATE(create_at) >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $query .= " AND DATE(create_at) <= ?";
    $params[] = $dateTo;
}

$query .= " ORDER BY create_at DESC";
$transactions = db_get_rows($query, $params);

// Calculate totals
foreach ($transactions as $trans) {
    if ((int)$trans['credit_type'] == 1) {
        $totalIncome += (float)$trans['amount'];
    } else {
        $totalExpenses += (float)$trans['amount'];
    }
}
$netSurplus = $totalIncome - $totalExpenses;

// ============================================================================
// GET SUMMARY FOR CARDS
// ============================================================================
$summaryQuery = "SELECT 
                    SUM(CASE WHEN credit_type = 1 THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN credit_type = 2 THEN amount ELSE 0 END) as total_expenses,
                    COUNT(CASE WHEN approval_status = 'pending' THEN 1 END) as pending_count,
                    COUNT(*) as total_transactions
                FROM school_expenses
                WHERE create_by_userid = ?";

$summary = db_get_row($summaryQuery, [$create_by_userid]);

// ============================================================================
// GET MONTHLY DATA FOR CHARTS
// ============================================================================
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months"));
    
    $income = db_get_val(
        "SELECT COALESCE(SUM(amount), 0) FROM school_expenses 
         WHERE create_by_userid = ? 
         AND credit_type = 1 
         AND DATE_FORMAT(create_at, '%Y-%m') = ?",
        [$create_by_userid, $month]
    );
    
    $expense = db_get_val(
        "SELECT COALESCE(SUM(amount), 0) FROM school_expenses 
         WHERE create_by_userid = ? 
         AND credit_type = 2 
         AND DATE_FORMAT(create_at, '%Y-%m') = ?",
        [$create_by_userid, $month]
    );
    
    $monthlyData[] = [
        'month' => $monthLabel,
        'income' => (float)$income,
        'expense' => (float)$expense
    ];
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getTypeBadge($type) {
    if ($type == 1) {
        return '<span class="badge badge-success">💰 Income</span>';
    } else {
        return '<span class="badge badge-danger">💳 Expense</span>';
    }
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">⏳ Pending</span>',
        'approved' => '<span class="badge badge-success">✅ Approved</span>',
        'rejected' => '<span class="badge badge-danger">❌ Rejected</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}
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
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; font-size: 28px; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 16px 24px; background: linear-gradient(135deg, #1B3058, #2a4780); color: #fff; font-weight: 600; font-size: 16px; }
        .card-body { padding: 24px; }
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-bottom: 25px; }
        .summary-card { background: #fff; border-radius: 14px; padding: 20px 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-left: 4px solid #1B3058; transition: transform 0.2s; }
        .summary-card:hover { transform: translateY(-3px); }
        .summary-card .icon { font-size: 24px; margin-bottom: 6px; display: block; }
        .summary-card .label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; }
        .summary-card .value { font-size: 24px; font-weight: 700; color: #1B3058; margin-top: 4px; }
        .summary-card.income { border-color: #28a745; }
        .summary-card.income .value { color: #28a745; }
        .summary-card.expense { border-color: #dc3545; }
        .summary-card.expense .value { color: #dc3545; }
        .summary-card.surplus { border-color: #17a2b8; }
        .summary-card.surplus .value { color: #17a2b8; }
        .summary-card.pending { border-color: #ffc107; }
        .summary-card.pending .value { color: #856404; }
        
        .filter-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; margin-bottom: 20px; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .filter-select, .filter-input { width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; transition: all 0.2s; }
        .filter-select:focus, .filter-input:focus { border-color: #1B3058; outline: none; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; }
        .btn-primary { background: #1B3058; color: #fff; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: #fff; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        
        .table-wrapper { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table th { background: #f8f9fa; color: #1B3058; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .table td { padding: 10px 16px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .table tr:hover { background: #f8f9ff; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .chart-card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .chart-card h4 { font-size: 15px; font-weight: 600; color: #1B3058; margin-bottom: 15px; }
        .chart-card .chart-container { position: relative; height: 250px; }
        
        .action-bar { display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0 10px; justify-content: flex-end; }
        
        .nav-tabs { display: flex; list-style: none; margin: 0; padding: 0; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; flex-wrap: wrap; }
        .nav-tabs li a { display: block; padding: 12px 24px; color: #555; text-decoration: none; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .nav-tabs li a:hover { color: #1B3058; background: #f0f0f0; }
        .nav-tabs li.active a { color: #1B3058; border-bottom-color: #1B3058; background: #fff; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
        .form-control { width: 100%; padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { border-color: #1B3058; outline: none; }
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; }
        .form-group-inline { flex: 1; min-width: 150px; }
        
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .mt-20 { margin-top: 20px; }
        
        .radio-group { display: flex; gap: 30px; padding: 10px 0; }
        .radio-group label { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .radio-group input[type="radio"] { width: 20px; height: 20px; cursor: pointer; }
        .radio-income { color: #28a745; }
        .radio-expense { color: #dc3545; }
        
        .receipt-preview { max-width: 100px; max-height: 60px; border-radius: 4px; cursor: pointer; }
        
        @media (max-width: 1024px) {
            .chart-grid { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .form-row { flex-direction: column; }
            .form-group-inline { min-width: 100%; }
            .radio-group { flex-direction: column; gap: 10px; }
        }
        @media (max-width: 480px) {
            .summary-grid { grid-template-columns: 1fr; }
            .nav-tabs { flex-direction: column; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-money"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Complete financial management with income tracking, expense approval, and budget monitoring</p>
                </div>
                
                <?= msg($stat) ?>
                
                <!-- Tabs -->
                <div class="card">
                    <ul class="nav-tabs">
                        <li class="<?= (!isset($_GET['action']) || $_GET['action'] == '') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>"><i class="fa fa-dashboard"></i> Dashboard</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'add') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=add"><i class="fa fa-plus"></i> Add Transaction</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'approvals') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=approvals"><i class="fa fa-check"></i> Approvals</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'reports') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=reports"><i class="fa fa-file-text-o"></i> Reports</a>
                        </li>
                    </ul>
                    
                    <div class="card-body">
                        
                        <!-- ============================================================ -->
                        <!-- TAB: DASHBOARD -->
                        <!-- ============================================================ -->
                        <?php if (!isset($_GET['action']) || $_GET['action'] == ''): ?>
                        
                        <!-- Summary Cards -->
                        <div class="summary-grid">
                            <div class="summary-card income">
                                <span class="icon"><i class="fa fa-arrow-down"></i></span>
                                <div class="label">Total Income</div>
                                <div class="value">₦<?= number_format($summary['total_income'] ?? 0, 2) ?></div>
                            </div>
                            <div class="summary-card expense">
                                <span class="icon"><i class="fa fa-arrow-up"></i></span>
                                <div class="label">Total Expenses</div>
                                <div class="value">₦<?= number_format($summary['total_expenses'] ?? 0, 2) ?></div>
                            </div>
                            <div class="summary-card surplus">
                                <span class="icon"><i class="fa fa-balance-scale"></i></span>
                                <div class="label">Net Surplus</div>
                                <div class="value" style="color:<?= ($netSurplus >= 0) ? '#28a745' : '#dc3545' ?>;">
                                    ₦<?= number_format($netSurplus, 2) ?>
                                </div>
                            </div>
                            <div class="summary-card pending">
                                <span class="icon"><i class="fa fa-clock-o"></i></span>
                                <div class="label">Pending Approvals</div>
                                <div class="value"><?= $summary['pending_count'] ?? 0 ?></div>
                            </div>
                            <div class="summary-card">
                                <span class="icon"><i class="fa fa-exchange"></i></span>
                                <div class="label">Transactions</div>
                                <div class="value"><?= $summary['total_transactions'] ?? 0 ?></div>
                            </div>
                        </div>
                        
                        <!-- Charts -->
                        <div class="chart-grid">
                            <div class="chart-card">
                                <h4><i class="fa fa-line-chart"></i> Monthly Income vs Expenses</h4>
                                <div class="chart-container">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                            <div class="chart-card">
                                <h4><i class="fa fa-pie-chart"></i> Income vs Expense Breakdown</h4>
                                <div class="chart-container">
                                    <canvas id="pieChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filters -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Session</label>
                                <select class="filter-select" name="filter_session" onchange="applyFilters()">
                                    <option value="">-- All --</option>
                                    <?php foreach ($sessions as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['session']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Term</label>
                                <select class="filter-select" name="filter_term" onchange="applyFilters()">
                                    <option value="">-- All --</option>
                                    <?php foreach ($terms as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['term']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Type</label>
                                <select class="filter-select" name="filter_type" onchange="applyFilters()">
                                    <option value="">-- All --</option>
                                    <option value="1" <?= ($selectedType == '1') ? 'selected' : '' ?>>Income</option>
                                    <option value="2" <?= ($selectedType == '2') ? 'selected' : '' ?>>Expense</option>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px; align-items: flex-end;">
                                <button class="btn btn-primary" onclick="applyFilters()"><i class="fa fa-filter"></i> Filter</button>
                                <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                            </div>
                        </div>
                        
                        <!-- Transaction Table -->
                        <h4>Recent Transactions</h4>
                        <div class="table-wrapper">
                            <table class="table" id="transactionTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Account</th>
                                        <th>Income (₦)</th>
                                        <th>Expense (₦)</th>
                                        <th>Balance (₦)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transactions)): ?>
                                        <?php $i = 0; $runningBalance = 0; foreach ($transactions as $trans): $i++; 
                                            $amount = (float)$trans['amount'];
                                            if ($trans['credit_type'] == 1) {
                                                $runningBalance += $amount;
                                                $incomeDisplay = number_format($amount, 2);
                                                $expenseDisplay = '-';
                                            } else {
                                                $runningBalance -= $amount;
                                                $incomeDisplay = '-';
                                                $expenseDisplay = number_format($amount, 2);
                                            }
                                        ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= date('d M Y', strtotime($trans['create_at'])) ?></td>
                                            <td><?= htmlspecialchars($trans['item_description']) ?></td>
                                            <td><?= htmlspecialchars($trans['category'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($trans['account_type'] ?? 'N/A') ?></td>
                                            <td style="color:#28a745; font-weight:600;"><?= $incomeDisplay ?></td>
                                            <td style="color:#dc3545; font-weight:600;"><?= $expenseDisplay ?></td>
                                            <td style="font-weight:700;">₦<?= number_format($runningBalance, 2) ?></td>
                                            <td><?= getStatusBadge($trans['approval_status'] ?? 'pending') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted" style="padding:30px;">
                                                No transactions found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <script>
                        function applyFilters() {
                            const session = document.querySelector('select[name="filter_session"]').value;
                            const term = document.querySelector('select[name="filter_term"]').value;
                            const type = document.querySelector('select[name="filter_type"]').value;
                            let url = '<?= $FileName ?>';
                            let params = [];
                            if (session) params.push('session=' + session);
                            if (term) params.push('term_id=' + term);
                            if (type) params.push('type=' + type);
                            if (params.length) url += '?' + params.join('&');
                            window.location.href = url;
                        }
                        </script>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: ADD TRANSACTION -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'add'): ?>
                        
                        <h4><i class="fa fa-plus"></i> Add Transaction</h4>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Description *</label>
                                    <input type="text" class="form-control" name="item_description" required placeholder="Item description">
                                </div>
                                <div class="form-group-inline">
                                    <label>Account Type</label>
                                    <input type="text" class="form-control" name="account_type" placeholder="Cash, Bank, etc.">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Category *</label>
                                    <input type="text" class="form-control" name="category" required placeholder="e.g., Salaries, Utilities">
                                </div>
                                <div class="form-group-inline">
                                    <label>Amount (₦) *</label>
                                    <input type="number" class="form-control" name="amount" required step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Session</label>
                                    <select class="form-control" name="session_id">
                                        <option value="0">-- Select --</option>
                                        <?php foreach ($sessions as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['session']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-inline">
                                    <label>Term</label>
                                    <select class="form-control" name="term_id">
                                        <option value="0">-- Select --</option>
                                        <?php foreach ($terms as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['term']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Transaction Type *</label>
                                <div class="radio-group">
                                    <label class="radio-income">
                                        <input type="radio" name="credit_type" value="1" required> 💰 Income (Money In)
                                    </label>
                                    <label class="radio-expense">
                                        <input type="radio" name="credit_type" value="2" required> 💳 Expense (Money Out)
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Attach Receipt (Optional)</label>
                                <input type="file" class="form-control" name="receipt" accept="image/*,.pdf">
                                <small style="color:#888;">Upload receipt image or PDF (max 5MB)</small>
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" name="add_expense" class="btn btn-success">
                                    <i class="fa fa-save"></i> Save Transaction
                                </button>
                                <a href="<?= $FileName ?>" class="btn btn-danger">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: APPROVALS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'approvals'): ?>
                        
                        <h4><i class="fa fa-check"></i> Pending Approvals</h4>
                        
                        <?php
                        $pendingTransactions = db_get_rows(
                            "SELECT * FROM school_expenses 
                             WHERE create_by_userid = ? 
                             AND approval_status = 'pending'
                             ORDER BY create_at ASC",
                            [$create_by_userid]
                        );
                        ?>
                        
                        <?php if (!empty($pendingTransactions)): ?>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 0; foreach ($pendingTransactions as $trans): $i++; ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= date('d M Y', strtotime($trans['create_at'])) ?></td>
                                        <td><?= htmlspecialchars($trans['item_description']) ?></td>
                                        <td><?= htmlspecialchars($trans['category'] ?? 'N/A') ?></td>
                                        <td><strong>₦<?= number_format((float)$trans['amount'], 2) ?></strong></td>
                                        <td><?= getTypeBadge($trans['credit_type']) ?></td>
                                        <td>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="expense_id" value="<?= $trans['id'] ?>">
                                                <button type="submit" name="approve_expense" class="btn btn-success btn-sm">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <button class="btn btn-danger btn-sm" onclick="showRejectForm(<?= $trans['id'] ?>)">
                                                <i class="fa fa-times"></i> Reject
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted" style="padding:40px;">
                            <i class="fa fa-check-circle" style="font-size:48px; color:#28a745; display:block; margin-bottom:10px;"></i>
                            <h4>No pending approvals</h4>
                            <p>All transactions have been reviewed.</p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Reject Modal -->
                        <div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                            <div style="background:#fff; padding:30px; border-radius:10px; max-width:400px; width:90%;">
                                <h4>Reject Transaction</h4>
                                <form method="POST" action="">
                                    <input type="hidden" name="expense_id" id="rejectExpenseId">
                                    <div class="form-group">
                                        <label>Reason for Rejection</label>
                                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                    </div>
                                    <div style="display:flex; gap:10px; margin-top:15px;">
                                        <button type="submit" name="reject_expense" class="btn btn-danger">Reject</button>
                                        <button type="button" class="btn btn-secondary" onclick="closeRejectForm()">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                        function showRejectForm(id) {
                            document.getElementById('rejectExpenseId').value = id;
                            document.getElementById('rejectModal').style.display = 'flex';
                        }
                        function closeRejectForm() {
                            document.getElementById('rejectModal').style.display = 'none';
                        }
                        </script>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: REPORTS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'reports'): ?>
                        
                        <h4><i class="fa fa-file-text-o"></i> Financial Reports</h4>
                        
                        <div class="summary-grid" style="margin-top:15px;">
                            <div class="summary-card income">
                                <div class="label">Total Income</div>
                                <div class="value">₦<?= number_format($totalIncome, 2) ?></div>
                            </div>
                            <div class="summary-card expense">
                                <div class="label">Total Expenses</div>
                                <div class="value">₦<?= number_format($totalExpenses, 2) ?></div>
                            </div>
                            <div class="summary-card surplus">
                                <div class="label">Net Surplus</div>
                                <div class="value" style="color:<?= ($netSurplus >= 0) ? '#28a745' : '#dc3545' ?>;">
                                    ₦<?= number_format($netSurplus, 2) ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Export Buttons -->
                        <div style="display:flex; gap:10px; margin:15px 0; flex-wrap:wrap;">
                            <a href="expenses_report_pdf.php?session=<?= $selectedSession ?>&term_id=<?= $selectedTerm ?>" 
                               target="_blank" class="btn btn-danger">
                                <i class="fa fa-file-pdf-o"></i> Download PDF Report
                            </a>
                            <a href="expenses_report_csv.php?session=<?= $selectedSession ?>&term_id=<?= $selectedTerm ?>" 
                               class="btn btn-success">
                                <i class="fa fa-file-excel-o"></i> Export Excel
                            </a>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fa fa-print"></i> Print Report
                            </button>
                        </div>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Income (₦)</th>
                                        <th>Expense (₦)</th>
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
                                            <td style="color:#28a745;">
                                                <?= ($trans['credit_type'] == 1) ? number_format((float)$trans['amount'], 2) : '-' ?>
                                            </td>
                                            <td style="color:#dc3545;">
                                                <?= ($trans['credit_type'] == 2) ? number_format((float)$trans['amount'], 2) : '-' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted" style="padding:30px;">
                                                No transactions found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight:700; background:#f8f9fa;">
                                        <td colspan="4" style="text-align:right;">TOTAL</td>
                                        <td style="color:#28a745;">₦<?= number_format($totalIncome, 2) ?></td>
                                        <td style="color:#dc3545;">₦<?= number_format($totalExpenses, 2) ?></td>
                                    </tr>
                                    <tr style="font-weight:700; background:#e8f0fe;">
                                        <td colspan="4" style="text-align:right;">NET SURPLUS</td>
                                        <td colspan="2" style="color:<?= ($netSurplus >= 0) ? '#28a745' : '#dc3545' ?>; font-size:16px;">
                                            ₦<?= number_format($netSurplus, 2) ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php endif; ?>
                        
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
// CHARTS
// ============================================================================

// 1. Monthly Income vs Expenses Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyData = <?= json_encode($monthlyData) ?>;

new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [
            {
                label: 'Income',
                data: monthlyData.map(d => d.income),
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: '#28a745',
                borderWidth: 2,
                borderRadius: 4
            },
            {
                label: 'Expenses',
                data: monthlyData.map(d => d.expense),
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: '#dc3545',
                borderWidth: 2,
                borderRadius: 4
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

// 2. Income vs Expense Pie Chart
const pieCtx = document.getElementById('pieChart').getContext('2d');

new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: ['Income', 'Expenses'],
        datasets: [{
            data: [
                <?= $summary['total_income'] ?? 0 ?>,
                <?= $summary['total_expenses'] ?? 0 ?>
            ],
            backgroundColor: ['#28a745', '#dc3545'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
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
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                        return context.label + ': ₦' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '60%'
    }
});
</script>
</body>
</html>