<?php
/**
 * ============================================================================
 * PETTY CASH MANAGEMENT SYSTEM
 * ============================================================================
 * Description: Manage petty cash requests, approvals, payments, and reconciliation
 * Features: Request, Approve, Pay, Reconcile, Reports
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Petty Cash Management";
$FileName = 'petty_cash.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedStatus = $_GET['status'] ?? '';
$selectedCategory = $_GET['category'] ?? '';
$selectedFund = $_GET['fund'] ?? '';
$selectedDateFrom = $_GET['date_from'] ?? '';
$selectedDateTo = $_GET['date_to'] ?? '';

// ============================================================================
// HANDLE POST - CREATE PETTY CASH FUND
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_fund'])) {
    try {
        $fundName = trim($_POST['fund_name'] ?? '');
        $initialBalance = (float)($_POST['initial_balance'] ?? 0);
        $maximumAmount = (float)($_POST['maximum_amount'] ?? 0);
        $responsiblePerson = (int)($_POST['responsible_person'] ?? 0);
        
        if (empty($fundName)) {
            throw new Exception("Fund name is required");
        }
        
        $lastId = (int)db_get_val("SELECT MAX(id) FROM petty_cash_fund") + 1;
        $randomId = randomFix(15) . $lastId;
        
        $data = [
            'fund_name' => $fundName,
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'maximum_amount' => $maximumAmount,
            'responsible_person' => $responsiblePerson,
            'status' => 'active',
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId
        ];
        
        db_insert("petty_cash_fund", $data);
        
        $_SESSION['success'] = "Petty cash fund created successfully!";
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - SUBMIT PETTY CASH REQUEST
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    try {
        $fundId = (int)($_POST['fund_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $requestDate = $_POST['request_date'] ?? date('Y-m-d');
        
        if ($fundId <= 0 || $categoryId <= 0 || $amount <= 0 || empty($description)) {
            throw new Exception("All fields are required");
        }
        
        // Check if fund has enough balance
        $fund = db_get_row("SELECT current_balance FROM petty_cash_fund WHERE id = ?", [$fundId]);
        if ($fund && (float)$fund['current_balance'] < $amount) {
            throw new Exception("Insufficient fund balance. Available: ₦" . number_format($fund['current_balance'], 2));
        }
        
        $lastId = (int)db_get_val("SELECT MAX(id) FROM petty_cash_request") + 1;
        $randomId = randomFix(15) . $lastId;
        
        $data = [
            'fund_id' => $fundId,
            'request_date' => $requestDate,
            'requested_by' => $create_by_userid,
            'category_id' => $categoryId,
            'amount' => $amount,
            'description' => $description,
            'status' => 'pending',
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId
        ];
        
        db_insert("petty_cash_request", $data);
        
        $_SESSION['success'] = "Petty cash request submitted successfully!";
        redirect($FileName . '?tab=requests');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - APPROVE REQUEST
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request'])) {
    try {
        $requestId = (int)($_POST['request_id'] ?? 0);
        
        $data = [
            'status' => 'approved',
            'approved_by' => $create_by_userid,
            'approved_date' => date('Y-m-d')
        ];
        
        db_update("petty_cash_request", $data, "id = ?", [$requestId]);
        
        $_SESSION['success'] = "Request approved successfully!";
        redirect($FileName . '?tab=approvals');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - REJECT REQUEST
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    try {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? '');
        
        $data = [
            'status' => 'rejected',
            'rejection_reason' => $reason
        ];
        
        db_update("petty_cash_request", $data, "id = ?", [$requestId]);
        
        $_SESSION['success'] = "Request rejected successfully!";
        redirect($FileName . '?tab=approvals');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - PAY REQUEST
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_request'])) {
    try {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $referenceNo = trim($_POST['reference_no'] ?? '');
        
        // Get request details
        $request = db_get_row("SELECT * FROM petty_cash_request WHERE id = ?", [$requestId]);
        if (empty($request)) {
            throw new Exception("Request not found");
        }
        
        // Update fund balance
        $fund = db_get_row("SELECT current_balance FROM petty_cash_fund WHERE id = ?", [$request['fund_id']]);
        if ($fund) {
            $newBalance = (float)$fund['current_balance'] - (float)$request['amount'];
            db_update("petty_cash_fund", ['current_balance' => $newBalance], "id = ?", [$request['fund_id']]);
        }
        
        // Update request
        $data = [
            'status' => 'paid',
            'paid_by' => $create_by_userid,
            'paid_date' => date('Y-m-d'),
            'payment_method' => $paymentMethod,
            'reference_no' => $referenceNo
        ];
        
        db_update("petty_cash_request", $data, "id = ?", [$requestId]);
        
        // Create payment record
        $lastId = (int)db_get_val("SELECT MAX(id) FROM petty_cash_payments") + 1;
        $randomId = randomFix(15) . $lastId;
        
        $paymentData = [
            'request_id' => $requestId,
            'fund_id' => $request['fund_id'],
            'payment_date' => date('Y-m-d'),
            'amount' => $request['amount'],
            'paid_to' => $request['requested_by'],
            'payment_method' => $paymentMethod,
            'reference_no' => $referenceNo,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId
        ];
        
        db_insert("petty_cash_payments", $paymentData);
        
        $_SESSION['success'] = "Payment processed successfully!";
        redirect($FileName . '?tab=payments');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// GET DATA
// ============================================================================

// Get funds
$funds = db_get_rows(
    "SELECT * FROM petty_cash_fund 
     WHERE create_by_userid = ? 
     ORDER BY id DESC",
    [$create_by_userid]
);

// Get categories
$categories = db_get_rows(
    "SELECT * FROM petty_cash_categories 
     WHERE create_by_userid = ? AND is_active = 1
     ORDER BY name ASC",
    [$create_by_userid]
);

// Get staff for dropdown
$staffList = db_get_rows(
    "SELECT id, first_name, last_name, staff_id 
     FROM staff_manage 
     WHERE create_by_userid = ? 
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// Build requests query
$requestsQuery = "SELECT 
                    pcr.*,
                    sm.first_name as requester_first,
                    sm.last_name as requester_last,
                    sm.staff_id as requester_staff_id,
                    pcc.name as category_name,
                    pf.fund_name,
                    app.first_name as approver_first,
                    app.last_name as approver_last,
                    pay.first_name as payer_first,
                    pay.last_name as payer_last
                FROM petty_cash_request pcr
                LEFT JOIN staff_manage sm ON pcr.requested_by = sm.id
                LEFT JOIN petty_cash_categories pcc ON pcr.category_id = pcc.id
                LEFT JOIN petty_cash_fund pf ON pcr.fund_id = pf.id
                LEFT JOIN staff_manage app ON pcr.approved_by = app.id
                LEFT JOIN staff_manage pay ON pcr.paid_by = pay.id
                WHERE pcr.create_by_userid = ?";

$requestParams = [$create_by_userid];

if (!empty($selectedStatus)) {
    $requestsQuery .= " AND pcr.status = ?";
    $requestParams[] = $selectedStatus;
}
if (!empty($selectedCategory)) {
    $requestsQuery .= " AND pcr.category_id = ?";
    $requestParams[] = (int)$selectedCategory;
}
if (!empty($selectedFund)) {
    $requestsQuery .= " AND pcr.fund_id = ?";
    $requestParams[] = (int)$selectedFund;
}
if (!empty($selectedDateFrom)) {
    $requestsQuery .= " AND DATE(pcr.request_date) >= ?";
    $requestParams[] = $selectedDateFrom;
}
if (!empty($selectedDateTo)) {
    $requestsQuery .= " AND DATE(pcr.request_date) <= ?";
    $requestParams[] = $selectedDateTo;
}

$requestsQuery .= " ORDER BY pcr.created_at DESC";

$requests = db_get_rows($requestsQuery, $requestParams);

// Get summary
$summaryQuery = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_paid
                FROM petty_cash_request
                WHERE create_by_userid = ?";

$summary = db_get_row($summaryQuery, [$create_by_userid]);

// Get total fund balance
$totalFundBalance = 0;
foreach ($funds as $fund) {
    $totalFundBalance += (float)$fund['current_balance'];
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getRequestStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">⏳ Pending</span>',
        'approved' => '<span class="badge badge-info">✅ Approved</span>',
        'paid' => '<span class="badge badge-success">💰 Paid</span>',
        'rejected' => '<span class="badge badge-danger">❌ Rejected</span>',
        'cancelled' => '<span class="badge badge-secondary">Cancelled</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function getPaymentMethodLabel($method) {
    $methods = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'mobile' => 'Mobile Payment'
    ];
    return $methods[$method] ?? $method;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title><?= htmlspecialchars($PageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .summary-box { background: #fff; border-radius: 10px; padding: 15px 20px; text-align: center; border-left: 4px solid #1B3058; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .summary-box .number { font-size: 24px; font-weight: 700; color: #1B3058; }
        .summary-box .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-top: 4px; }
        
        .nav-tabs { display: flex; list-style: none; margin: 0; padding: 0; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; flex-wrap: wrap; }
        .nav-tabs li a { display: block; padding: 12px 24px; color: #555; text-decoration: none; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .nav-tabs li a:hover { color: #1B3058; background: #f0f0f0; }
        .nav-tabs li.active a { color: #1B3058; border-bottom-color: #1B3058; background: #fff; }
        
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        .form-control { width: 100%; padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { border-color: #1B3058; outline: none; }
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
        
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .nav-tabs { flex-direction: column; }
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
            <div class="container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-money"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Manage petty cash requests, approvals, payments, and reconciliation</p>
                </div>
                
                <?= msg($stat) ?>
                
                <!-- Tabs -->
                <div class="card">
                    <ul class="nav-tabs">
                        <li class="<?= (!isset($_GET['tab']) || $_GET['tab'] == 'dashboard') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'requests') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=requests"><i class="fa fa-plus"></i> Requests</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'approvals') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=approvals"><i class="fa fa-check"></i> Approvals</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'payments') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=payments"><i class="fa fa-credit-card"></i> Payments</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'funds') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=funds"><i class="fa fa-bank"></i> Funds</a>
                        </li>
                    </ul>
                    
                    <div class="card-body">
                        
                        <!-- ============================================================ -->
                        <!-- TAB: DASHBOARD -->
                        <!-- ============================================================ -->
                        <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'dashboard'): ?>
                        
                        <div class="summary-grid">
                            <div class="summary-box">
                                <div class="number"><?= number_format($summary['total_requests'] ?? 0) ?></div>
                                <div class="label">Total Requests</div>
                            </div>
                            <div class="summary-box" style="border-color: #ffc107;">
                                <div class="number" style="color: #856404;"><?= number_format($summary['pending'] ?? 0) ?></div>
                                <div class="label">Pending</div>
                            </div>
                            <div class="summary-box" style="border-color: #17a2b8;">
                                <div class="number" style="color: #17a2b8;"><?= number_format($summary['approved'] ?? 0) ?></div>
                                <div class="label">Approved</div>
                            </div>
                            <div class="summary-box" style="border-color: #28a745;">
                                <div class="number" style="color: #28a745;"><?= number_format($summary['paid'] ?? 0) ?></div>
                                <div class="label">Paid</div>
                            </div>
                            <div class="summary-box" style="border-color: #dc3545;">
                                <div class="number" style="color: #dc3545;">₦<?= number_format($summary['total_paid'] ?? 0, 2) ?></div>
                                <div class="label">Total Paid</div>
                            </div>
                            <div class="summary-box" style="border-color: #1B3058;">
                                <div class="number">₦<?= number_format($totalFundBalance, 2) ?></div>
                                <div class="label">Total Fund Balance</div>
                            </div>
                        </div>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Filter by Status</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?tab=dashboard&status='+this.value">
                                    <option value="">-- All --</option>
                                    <option value="pending" <?= ($selectedStatus == 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= ($selectedStatus == 'approved') ? 'selected' : '' ?>>Approved</option>
                                    <option value="paid" <?= ($selectedStatus == 'paid') ? 'selected' : '' ?>>Paid</option>
                                    <option value="rejected" <?= ($selectedStatus == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto;">
                                <a href="<?= $FileName ?>?tab=requests" class="btn btn-success">
                                    <i class="fa fa-plus"></i> New Request
                                </a>
                            </div>
                        </div>
                        
                        <!-- Recent Requests -->
                        <h4>Recent Requests</h4>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Requested By</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($requests)): ?>
                                        <?php $i = 0; foreach (array_slice($requests, 0, 10) as $req): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= date('d M Y', strtotime($req['request_date'])) ?></td>
                                            <td><?= htmlspecialchars(($req['requester_first'] ?? '') . ' ' . ($req['requester_last'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($req['category_name'] ?? 'N/A') ?></td>
                                            <td>₦<?= number_format((float)$req['amount'], 2) ?></td>
                                            <td><?= getRequestStatusBadge($req['status']) ?></td>
                                            <td>
                                                <a href="petty_cash_receipt.php?id=<?= $req['randomid'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted" style="padding:20px;">
                                                No requests found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: REQUESTS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'requests'): ?>
                        
                        <h4><i class="fa fa-plus"></i> New Petty Cash Request</h4>
                        
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Fund *</label>
                                    <select class="form-control" name="fund_id" required>
                                        <option value="">-- Select Fund --</option>
                                        <?php foreach ($funds as $fund): ?>
                                            <option value="<?= $fund['id'] ?>">
                                                <?= htmlspecialchars($fund['fund_name']) ?> (₦<?= number_format((float)$fund['current_balance'], 2) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Category *</label>
                                    <select class="form-control" name="category_id" required>
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Amount (₦) *</label>
                                    <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Request Date</label>
                                    <input type="date" class="form-control" name="request_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label>Description *</label>
                                    <textarea class="form-control" name="description" rows="3" required placeholder="Describe the expense..."></textarea>
                                </div>
                            </div>
                            <div style="margin-top: 15px;">
                                <button type="submit" name="submit_request" class="btn btn-success">
                                    <i class="fa fa-save"></i> Submit Request
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <h4>All Requests</h4>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Status</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?tab=requests&status='+this.value">
                                    <option value="">-- All --</option>
                                    <option value="pending" <?= ($selectedStatus == 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= ($selectedStatus == 'approved') ? 'selected' : '' ?>>Approved</option>
                                    <option value="paid" <?= ($selectedStatus == 'paid') ? 'selected' : '' ?>>Paid</option>
                                    <option value="rejected" <?= ($selectedStatus == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto;">
                                <a href="<?= $FileName ?>?tab=requests" class="btn btn-danger btn-sm">Reset</a>
                            </div>
                        </div>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Requested By</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($requests)): ?>
                                        <?php $i = 0; foreach ($requests as $req): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= date('d M Y', strtotime($req['request_date'])) ?></td>
                                            <td><?= htmlspecialchars(($req['requester_first'] ?? '') . ' ' . ($req['requester_last'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($req['category_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars(substr($req['description'], 0, 50)) ?>...</td>
                                            <td>₦<?= number_format((float)$req['amount'], 2) ?></td>
                                            <td><?= getRequestStatusBadge($req['status']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted" style="padding:20px;">
                                                No requests found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: APPROVALS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'approvals'): ?>
                        
                        <h4><i class="fa fa-check"></i> Pending Approvals</h4>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Requested By</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $pendingRequests = array_filter($requests, function($r) {
                                        return $r['status'] == 'pending';
                                    });
                                    ?>
                                    <?php if (!empty($pendingRequests)): ?>
                                        <?php $i = 0; foreach ($pendingRequests as $req): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= date('d M Y', strtotime($req['request_date'])) ?></td>
                                            <td><?= htmlspecialchars(($req['requester_first'] ?? '') . ' ' . ($req['requester_last'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($req['category_name'] ?? 'N/A') ?></td>
                                            <td>₦<?= number_format((float)$req['amount'], 2) ?></td>
                                            <td><?= htmlspecialchars(substr($req['description'], 0, 30)) ?>...</td>
                                            <td>
                                                <form method="POST" action="" style="display:inline;">
                                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                    <button type="submit" name="approve_request" class="btn btn-success btn-sm">
                                                        <i class="fa fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button class="btn btn-danger btn-sm" onclick="showRejectForm(<?= $req['id'] ?>)">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted" style="padding:20px;">
                                                No pending approvals
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Reject Modal -->
                        <div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                            <div style="background:#fff; padding:30px; border-radius:10px; max-width:400px; width:90%;">
                                <h4>Reject Request</h4>
                                <form method="POST" action="">
                                    <input type="hidden" name="request_id" id="rejectRequestId">
                                    <div class="form-group">
                                        <label>Reason for Rejection</label>
                                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                    </div>
                                    <div style="display:flex; gap:10px; margin-top:15px;">
                                        <button type="submit" name="reject_request" class="btn btn-danger">Reject</button>
                                        <button type="button" class="btn btn-secondary" onclick="closeRejectForm()">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                        function showRejectForm(id) {
                            document.getElementById('rejectRequestId').value = id;
                            document.getElementById('rejectModal').style.display = 'flex';
                        }
                        function closeRejectForm() {
                            document.getElementById('rejectModal').style.display = 'none';
                        }
                        </script>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: PAYMENTS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'payments'): ?>
                        
                        <h4><i class="fa fa-credit-card"></i> Process Payments</h4>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Requested By</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $approvedRequests = array_filter($requests, function($r) {
                                        return $r['status'] == 'approved';
                                    });
                                    ?>
                                    <?php if (!empty($approvedRequests)): ?>
                                        <?php $i = 0; foreach ($approvedRequests as $req): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= date('d M Y', strtotime($req['request_date'])) ?></td>
                                            <td><?= htmlspecialchars(($req['requester_first'] ?? '') . ' ' . ($req['requester_last'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($req['category_name'] ?? 'N/A') ?></td>
                                            <td>₦<?= number_format((float)$req['amount'], 2) ?></td>
                                            <td><?= htmlspecialchars(substr($req['description'], 0, 30)) ?>...</td>
                                            <td>
                                                <button class="btn btn-success btn-sm" onclick="showPaymentForm(<?= $req['id'] ?>, '<?= $req['amount'] ?>')">
                                                    <i class="fa fa-credit-card"></i> Pay
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted" style="padding:20px;">
                                                No approved requests ready for payment
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Payment Modal -->
                        <div id="paymentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                            <div style="background:#fff; padding:30px; border-radius:10px; max-width:400px; width:90%;">
                                <h4>Process Payment</h4>
                                <form method="POST" action="">
                                    <input type="hidden" name="request_id" id="paymentRequestId">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input type="text" class="form-control" id="paymentAmount" readonly style="font-weight:700; font-size:16px; color:#28a745;">
                                    </div>
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select class="form-control" name="payment_method" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="mobile">Mobile Payment</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Reference No. (Optional)</label>
                                        <input type="text" class="form-control" name="reference_no" placeholder="Transaction reference">
                                    </div>
                                    <div style="display:flex; gap:10px; margin-top:15px;">
                                        <button type="submit" name="pay_request" class="btn btn-success">Confirm Payment</button>
                                        <button type="button" class="btn btn-secondary" onclick="closePaymentForm()">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                        function showPaymentForm(id, amount) {
                            document.getElementById('paymentRequestId').value = id;
                            document.getElementById('paymentAmount').value = '₦' + parseFloat(amount).toFixed(2);
                            document.getElementById('paymentModal').style.display = 'flex';
                        }
                        function closePaymentForm() {
                            document.getElementById('paymentModal').style.display = 'none';
                        }
                        </script>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: FUNDS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'funds'): ?>
                        
                        <h4><i class="fa fa-bank"></i> Petty Cash Funds</h4>
                        
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Fund Name *</label>
                                    <input type="text" class="form-control" name="fund_name" required placeholder="e.g., Office Petty Cash">
                                </div>
                                <div class="form-group">
                                    <label>Initial Balance (₦)</label>
                                    <input type="number" class="form-control" name="initial_balance" value="0" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Maximum Amount (₦)</label>
                                    <input type="number" class="form-control" name="maximum_amount" value="0" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Responsible Person</label>
                                    <select class="form-control" name="responsible_person">
                                        <option value="0">-- Select --</option>
                                        <?php foreach ($staffList as $staff): ?>
                                            <option value="<?= $staff['id'] ?>">
                                                <?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top: 15px;">
                                <button type="submit" name="create_fund" class="btn btn-success">
                                    <i class="fa fa-plus"></i> Create Fund
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fund Name</th>
                                        <th>Initial Balance</th>
                                        <th>Current Balance</th>
                                        <th>Responsible Person</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($funds)): ?>
                                        <?php $i = 0; foreach ($funds as $fund): $i++; 
                                            $responsible = db_get_row("SELECT first_name, last_name FROM staff_manage WHERE id = ?", [$fund['responsible_person']]);
                                        ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><strong><?= htmlspecialchars($fund['fund_name']) ?></strong></td>
                                            <td>₦<?= number_format((float)$fund['initial_balance'], 2) ?></td>
                                            <td style="font-weight:700; color:<?= ((float)$fund['current_balance'] > 0) ? '#28a745' : '#dc3545' ?>;">
                                                ₦<?= number_format((float)$fund['current_balance'], 2) ?>
                                            </td>
                                            <td><?= htmlspecialchars(($responsible['first_name'] ?? '') . ' ' . ($responsible['last_name'] ?? '')) ?: 'N/A' ?></td>
                                            <td>
                                                <span class="badge <?= ($fund['status'] == 'active') ? 'badge-success' : 'badge-secondary' ?>">
                                                    <?= ucfirst($fund['status'] ?? 'Inactive') ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted" style="padding:20px;">
                                                No petty cash funds created yet
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
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
</body>
</html>