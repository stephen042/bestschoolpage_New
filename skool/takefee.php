<?php
/**
 * ============================================================================
 * STUDENT FEE MANAGEMENT - IMPROVED WORKFLOW
 * ============================================================================
 * Description: Clear workflow for fee assignment, payment, and tracking
 * Version: 4.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Student Fee";
$FileName = 'takefee.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$currentUserId = (int)($_SESSION['userid'] ?? 0);

// // ============================================================================
// HANDLE POST - ASSIGN NEW FEES
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_fees'])) {
    try {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $sessionId = (int)($_POST['session'] ?? 0);
        $classId = (int)($_POST['class'] ?? 0);
        $termId = (int)($_POST['term_id'] ?? 0);
        $feeStructures = $_POST['fee_sturcture_id'] ?? [];
        $discounts = $_POST['discount'] ?? [];
        $amountsPaid = $_POST['amount_paid'] ?? [];
        $paymentModes = $_POST['payment_mode'] ?? [];
        $paymentDates = $_POST['payment_date'] ?? [];
        $studentStatus = (int)($_POST['student_status'] ?? 1);
        $paymentType = (int)($_POST['payment_type'] ?? 1);
        
        // Validate
        if ($studentId <= 0) throw new Exception("Invalid student");
        if (empty($feeStructures)) throw new Exception("No fees selected");
        
        // Check if student already has fees for this session/term
        $existingFee = $db->getRow(
            "SELECT id, randomid FROM student_fee 
             WHERE student_id = '" . $studentId . "' 
             AND session = '" . $sessionId . "' 
             AND term_id = '" . $termId . "' 
             AND class = '" . $classId . "' 
             AND create_by_userid = '" . $create_by_userid . "'"
        );
        
        if (!empty($existingFee)) {
            throw new Exception("Student already has fees assigned. Please use 'Pay Outstanding' option.");
        }
        
        // Calculate totals
        $totalAmount = 0;
        $totalDiscount = 0;
        $totalPaid = 0;
        
        foreach ($feeStructures as $key => $feeId) {
            $feeAmount = (float)($_POST['fee_amount'][$key] ?? 0);
            $discount = (float)($discounts[$key] ?? 0);
            $paid = (float)($amountsPaid[$key] ?? 0);
            
            $totalAmount += $feeAmount;
            $totalDiscount += $discount;
            $totalPaid += $paid;
        }
        
        $remainAmount = $totalAmount - $totalDiscount - $totalPaid;
        
        // Generate invoice
        $lastId = (int)$db->getVal("SELECT MAX(id) FROM student_fee") + 1;
        $invoiceNo = randomFix(7) . $lastId;
        $randomId = randomFix(15) . $lastId;
        
        // Insert main fee record
        $feeData = array(
            'student_id' => $studentId,
            'session' => $sessionId,
            'class' => $classId,
            'term_id' => $termId,
            'rollno' => $_POST['rollno'] ?? '',
            'student_status' => $studentStatus,
            'PType' => $paymentType,
            'total_amount_to_pay' => $totalAmount,
            'currently_paying_amount' => $totalPaid,
            'remain_amount' => $remainAmount,
            'discount_amount' => $totalDiscount,
            'payment_status' => ($remainAmount == 0) ? 'paid' : (($totalPaid > 0) ? 'partial' : 'pending'),
            'invoiceno' => $invoiceNo,
            'last_payment_date' => !empty($paymentDates) ? max($paymentDates) : date('Y-m-d'),
            'userid' => $currentUserId,
            'usertype' => $create_by_usertype,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => $randomId,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s")
        );
        
        $studentFeeId = $db->insertAry("student_fee", $feeData);
        
        if (!$studentFeeId) {
            throw new Exception("Failed to save fee record");
        }
        
        // Insert individual fee items
        foreach ($feeStructures as $key => $feeId) {
            $feeAmount = (float)($_POST['fee_amount'][$key] ?? 0);
            $discount = (float)($discounts[$key] ?? 0);
            $paid = (float)($amountsPaid[$key] ?? 0);
            $outstanding = $feeAmount - $discount - $paid;
            $discountReason = $_POST['discount_reason'][$key] ?? '';
            
            $feeItemData = array(
                'student_fee_id' => $studentFeeId,
                'fee_sturcture_id' => $feeId,
                'fee' => $feeAmount,
                'fees_disccount' => $discount,
                'discount_reason' => $discountReason,
                'fees_outstanding' => $outstanding,
                'fees_date' => $paymentDates[$key] ?? date('Y-m-d'),
                'fees_amount' => $paid,
                'payment_mode' => (int)($paymentModes[$key] ?? 0),
                'last_payment_date' => $paymentDates[$key] ?? date('Y-m-d')
            );
            $db->insertAry("student_fee_sturcture", $feeItemData);
        }
        
        // Create transaction record
        $student = $db->getRow("SELECT first_name, last_name FROM manage_student WHERE id = '" . $studentId . "'");
        $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        
        $transData = array(
            'student_fee_id' => $studentFeeId,
            'student_id' => $studentId,
            'session' => $sessionId,
            'class' => $classId,
            'term_id' => $termId,
            'fullname' => $fullName,
            'invoiceno' => $invoiceNo,
            'total_amount_to_pay' => $totalAmount,
            'currently_paying_amount' => $totalPaid,
            'remain_amount' => $remainAmount,
            'discount_amount' => $totalDiscount,
            'userid' => $currentUserId,
            'usertype' => $create_by_usertype,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'create_at' => date("Y-m-d H:i:s")
        );
        $db->insertAry("student_fee_transcation", $transData);
        
        $_SESSION['success'] = "Fees assigned successfully! Invoice: " . $invoiceNo;
        redirect($FileName . '?action=view&token=' . $randomId);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}
// // ============================================================================
// HANDLE POST - PAY OUTSTANDING
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_outstanding'])) {
    try {
        $studentFeeId = (int)($_POST['student_fee_id'] ?? 0);
        $feeItemIds = $_POST['fee_item_ids'] ?? [];
        $amountsPaid = $_POST['amount_paid'] ?? [];
        $discounts = $_POST['discount'] ?? [];
        $discountReasons = $_POST['discount_reason'] ?? [];
        $paymentModes = $_POST['payment_mode'] ?? [];
        $paymentDates = $_POST['payment_date'] ?? [];
        
        // Get current fee record
        $feeRecord = $db->getRow(
            "SELECT * FROM student_fee WHERE id = '" . $studentFeeId . "' 
             AND create_by_userid = '" . $create_by_userid . "'"
        );
        
        if (empty($feeRecord)) {
            throw new Exception("Fee record not found");
        }
        
        $totalNewPaid = 0;
        $totalNewDiscount = 0;
        $updatedItems = [];
        
        // Update each fee item
        foreach ($feeItemIds as $key => $feeItemId) {
            $paid = (float)($amountsPaid[$key] ?? 0);
            $discount = (float)($discounts[$key] ?? 0);
            $discountReason = $discountReasons[$key] ?? '';
            $paymentMode = (int)($paymentModes[$key] ?? 0);
            $paymentDate = $paymentDates[$key] ?? date('Y-m-d');
            
            if ($paid > 0 || $discount > 0) {
                // Get current fee item
                $feeItem = $db->getRow(
                    "SELECT * FROM student_fee_sturcture 
                     WHERE id = '" . $feeItemId . "' 
                     AND student_fee_id = '" . $studentFeeId . "'"
                );
                
                if (!empty($feeItem)) {
                    $currentOutstanding = (float)$feeItem['fees_outstanding'];
                    $currentDiscount = (float)$feeItem['fees_disccount'];
                    $currentPaid = (float)$feeItem['fees_amount'];
                    
                    $newPaid = $currentPaid + $paid;
                    $newDiscount = $currentDiscount + $discount;
                    $newOutstanding = $currentOutstanding - $paid - $discount;
                    
                    if ($newOutstanding < 0) $newOutstanding = 0;
                    
                    // Update fee item
                    $updateData = array(
                        'fees_amount' => $newPaid,
                        'fees_disccount' => $newDiscount,
                        'discount_reason' => $discountReason,
                        'fees_outstanding' => $newOutstanding,
                        'fees_date' => $paymentDate,
                        'payment_mode' => $paymentMode,
                        'last_payment_date' => $paymentDate
                    );
                    $db->updateAry("student_fee_sturcture", $updateData, "where id = '" . $feeItemId . "'");
                    
                    $totalNewPaid += $paid;
                    $totalNewDiscount += $discount;
                    $updatedItems[] = $feeItemId;
                }
            }
        }
        
        if (empty($updatedItems)) {
            throw new Exception("No payment or discount applied");
        }
        
        // Update main fee record
        $newTotalPaid = (float)$feeRecord['currently_paying_amount'] + $totalNewPaid;
        $newTotalDiscount = (float)$feeRecord['discount_amount'] + $totalNewDiscount;
        $newRemainAmount = (float)$feeRecord['total_amount_to_pay'] - $newTotalDiscount - $newTotalPaid;
        
        if ($newRemainAmount < 0) $newRemainAmount = 0;
        
        $paymentStatus = ($newRemainAmount == 0) ? 'paid' : 'partial';
        
        $updateFee = array(
            'currently_paying_amount' => $newTotalPaid,
            'remain_amount' => $newRemainAmount,
            'discount_amount' => $newTotalDiscount,
            'payment_status' => $paymentStatus,
            'last_payment_date' => date('Y-m-d'),
            'update_at' => date("Y-m-d H:i:s")
        );
        $db->updateAry("student_fee", $updateFee, "where id = '" . $studentFeeId . "'");
        
        // Create transaction record
        $student = $db->getRow("SELECT first_name, last_name FROM manage_student WHERE id = '" . $feeRecord['student_id'] . "'");
        $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        
        $transData = array(
            'student_fee_id' => $studentFeeId,
            'student_id' => $feeRecord['student_id'],
            'session' => $feeRecord['session'],
            'class' => $feeRecord['class'],
            'term_id' => $feeRecord['term_id'],
            'fullname' => $fullName,
            'invoiceno' => $feeRecord['invoiceno'],
            'total_amount_to_pay' => $feeRecord['total_amount_to_pay'],
            'currently_paying_amount' => $newTotalPaid,
            'remain_amount' => $newRemainAmount,
            'discount_amount' => $newTotalDiscount,
            'userid' => $currentUserId,
            'usertype' => $create_by_usertype,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'create_at' => date("Y-m-d H:i:s")
        );
        $db->insertAry("student_fee_transcation", $transData);
        
        $_SESSION['success'] = "Payment processed successfully! Outstanding: ₦" . number_format($newRemainAmount, 2);
        redirect($FileName . '?action=view&token=' . $feeRecord['randomid']);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}
// ============================================================================
// GET DATA
// ============================================================================

// Get students with fee status
$students = [];
$searchCondition = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_students'])) {
    $conditions = [];
    if (!empty($_POST['session'])) {
        $conditions[] = "m.session = '" . (int)$_POST['session'] . "'";
    }
    if (!empty($_POST['class'])) {
        $conditions[] = "m.class = '" . (int)$_POST['class'] . "'";
    }
    if (!empty($_POST['term_id'])) {
        $conditions[] = "m.term_id = '" . (int)$_POST['term_id'] . "'";
    }
    if (!empty($_POST['rollno'])) {
        $conditions[] = "m.student_id LIKE '%" . $_POST['rollno'] . "%'";
    }
    if (!empty($conditions)) {
        $searchCondition = " AND " . implode(" AND ", $conditions);
    }
}

$students = $db->getRows(
    "SELECT m.*, 
            sc.name as class_name,
            sf.id as fee_id,
            sf.randomid as fee_randomid,
            sf.invoiceno,
            sf.total_amount_to_pay,
            sf.currently_paying_amount,
            sf.remain_amount,
            sf.payment_status,
            sf.PType
     FROM manage_student m
     LEFT JOIN school_class sc ON m.class = sc.id
     LEFT JOIN student_fee sf ON m.id = sf.student_id 
         AND m.session = sf.session 
         AND m.term_id = sf.term_id 
         AND sf.create_by_userid = '" . $create_by_userid . "'
     WHERE m.create_by_userid = '" . $create_by_userid . "' 
     " . $searchCondition . "
     ORDER BY m.first_name ASC"
);

// Get sessions, terms, classes
$sessions = $db->getRows("SELECT * FROM school_session WHERE create_by_userid = '" . $create_by_userid . "' ORDER BY id DESC");
$terms = $db->getRows("SELECT * FROM school_term WHERE create_by_userid = '" . $create_by_userid . "' ORDER BY id ASC");
$classes = $db->getRows("SELECT * FROM school_class WHERE create_by_userid = '" . $create_by_userid . "' ORDER BY name ASC");
$feeStructures = $db->getRows("SELECT * FROM fee_sturcture WHERE create_by_userid = '" . $create_by_userid . "' AND status != 2 ORDER BY id DESC");

$defaultFeeToken = '';
$defaultFeeRecord = $db->getRow(
    "SELECT randomid FROM student_fee WHERE create_by_userid = '" . $create_by_userid . "' ORDER BY id DESC LIMIT 1"
);
if (!empty($defaultFeeRecord)) {
    $defaultFeeToken = $defaultFeeRecord['randomid'];
}

// Get fee record for view/edit
$feeRecord = [];
$feeItems = [];
if (isset($_GET['action']) && $_GET['action'] == 'view' && !empty($_GET['token'])) {
    $feeRecord = $db->getRow(
        "SELECT * FROM student_fee 
         WHERE randomid = '" . $_GET['token'] . "' 
         AND create_by_userid = '" . $create_by_userid . "'"
    );
    
    if (!empty($feeRecord)) {
        $feeItems = $db->getRows(
            "SELECT sfs.*, fs.title as fee_title 
             FROM student_fee_sturcture sfs
             LEFT JOIN fee_sturcture fs ON sfs.fee_sturcture_id = fs.id
             WHERE sfs.student_fee_id = '" . $feeRecord['id'] . "'"
        );
    }
}

// Get transactions
$transactions = [];
if (isset($_GET['action']) && $_GET['action'] == 'transactions') {
    $transCondition = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_transactions'])) {
        $conditions = [];
        if (!empty($_POST['session'])) $conditions[] = "session = '" . (int)$_POST['session'] . "'";
        if (!empty($_POST['class'])) $conditions[] = "class = '" . (int)$_POST['class'] . "'";
        if (!empty($_POST['term_id'])) $conditions[] = "term_id = '" . (int)$_POST['term_id'] . "'";
        if (!empty($_POST['invoiceno'])) $conditions[] = "invoiceno LIKE '%" . $_POST['invoiceno'] . "%'";
        if (!empty($conditions)) {
            $transCondition = " AND " . implode(" AND ", $conditions);
        }
    }
    
    $transactions = $db->getRows(
        "SELECT * FROM student_fee_transcation 
         WHERE create_by_userid = '" . $create_by_userid . "' 
         " . $transCondition . " 
         ORDER BY create_at DESC"
    );
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getStatusBadge($status, $remainAmount = null) {
    if ($status == 'paid') {
        return '<span class="badge badge-success">✅ Paid</span>';
    } elseif ($status == 'partial') {
        return '<span class="badge badge-warning">⏳ Partial</span>';
    } elseif ($status == 'scholarship') {
        return '<span class="badge badge-info">🎓 Scholarship</span>';
    } else {
        return '<span class="badge badge-danger">❌ Pending</span>';
    }
}

// function getPaymentTypeLabel($type) {
//     return ($type == 2) ? 'Fixed' : 'Flexible';
// }

function getPaymentModeLabel($mode) {
    $modes = [
        0 => 'Not Set',
        1 => 'Bank',
        2 => 'Cash',
        3 => 'POS',
        4 => 'Bank Transfer',
        5 => 'Scholarship'
    ];
    return $modes[(int)$mode] ?? 'Unknown';
}
function getPaymentTypeLabel($type) {
    return ($type == 2) ? 'Fixed' : 'Flexible';
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
        body { background: #f0f2f5; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 18px 24px; background: linear-gradient(135deg, #1B3058, #2a4780); color: #fff; font-weight: 600; font-size: 16px; }
        .card-body { padding: 24px; }
        
        .nav-tabs { display: flex; list-style: none; margin: 0; padding: 0; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; flex-wrap: wrap; }
        .nav-tabs li a { display: block; padding: 12px 24px; color: #555; text-decoration: none; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .nav-tabs li a:hover { color: #1B3058; background: #f0f0f0; }
        .nav-tabs li.active a { color: #1B3058; border-bottom-color: #1B3058; background: #fff; }
        
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; min-width: 180px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; }
        .form-control:focus { border-color: #1B3058; outline: none; }
        .form-control-sm { padding: 6px 10px; font-size: 13px; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; }
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
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table th { background: #f8f9fa; color: #1B3058; font-weight: 700; padding: 12px 16px; text-align: left; border-bottom: 2px solid #e0e0e0; }
        .table td { padding: 12px 16px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .table tr:hover { background: #f8f9ff; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        
        .fee-item { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #e0e0e0; }
        .fee-item .form-group { min-width: 120px; }
        .fee-item .form-group input, .fee-item .form-group select { padding: 6px 10px; font-size: 13px; }
        
        .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin: 10px 0; }
        
        .status-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .status-box { padding: 15px; border-radius: 8px; text-align: center; }
        .status-box .number { font-size: 28px; font-weight: 700; }
        .status-box .label { font-size: 12px; color: #666; }
        
        @media (max-width: 768px) { .form-row { flex-direction: column; } .form-group { min-width: 100%; } .nav-tabs { flex-direction: column; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="container">
                
                <h2><i class="fa fa-money"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                <?= msg($stat) ?>
                
                <!-- Tabs -->
                <div class="card">
                    <ul class="nav-tabs">
                        <li class="<?= (!isset($_GET['action']) || $_GET['action'] == '') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>"><i class="fa fa-users"></i> Students</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'view') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=view<?= !empty($defaultFeeToken) ? '&token=' . urlencode($defaultFeeToken) : '' ?>"><i class="fa fa-eye"></i> Fee Details</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'transactions') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=transactions"><i class="fa fa-history"></i> Transactions</a>
                        </li>
                    </ul>
                    
                    <div class="card-body">
                        
                        <!-- ============================================================ -->
                        <!-- STUDENT LIST (DEFAULT) -->
                        <!-- ============================================================ -->
                        <?php if (!isset($_GET['action']) || $_GET['action'] == ''): ?>
                        
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Session</label>
                                    <select class="form-control" name="session">
                                        <option value="">-- All --</option>
                                        <?php foreach ($sessions as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= (isset($_POST['session']) && $_POST['session'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Class</label>
                                    <select class="form-control" name="class">
                                        <option value="">-- All --</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= (isset($_POST['class']) && $_POST['class'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Term</label>
                                    <select class="form-control" name="term_id">
                                        <option value="">-- All --</option>
                                        <?php foreach ($terms as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= (isset($_POST['term_id']) && $_POST['term_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Roll No</label>
                                    <input type="text" class="form-control" name="rollno" value="<?= $_POST['rollno'] ?? '' ?>" placeholder="Search">
                                </div>
                                <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end; gap: 10px;">
                                    <button type="submit" name="search_students" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                                    <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Roll No</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Invoice</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($students)): ?>
                                        <?php $i = 0; foreach ($students as $student): $i++; 
                                            $hasFees = !empty($student['fee_id']);
                                            $balance = (float)($student['remain_amount'] ?? 0);
                                            $status = $student['payment_status'] ?? 'pending';
                                        ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                                            <td><strong><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></strong></td>
                                            <td><?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($student['invoiceno'] ?? '-') ?></td>
                                            <td><?= $hasFees ? '₦' . number_format((float)$student['total_amount_to_pay'], 2) : '-' ?></td>
                                            <td><?= $hasFees ? '₦' . number_format((float)$student['currently_paying_amount'], 2) : '-' ?></td>
                                            <td><strong><?= $hasFees ? '₦' . number_format($balance, 2) : '-' ?></strong></td>
                                            <td><?= $hasFees ? getStatusBadge($status, $balance) : '<span class="badge badge-info">No Fees</span>' ?></td>
                                            <td>
                                                <?php if (!$hasFees): ?>
                                                    <a href="<?= $FileName ?>?action=assign&student_id=<?= $student['id'] ?>&session=<?= $student['session'] ?>&class=<?= $student['class'] ?>&term_id=<?= $student['term_id'] ?>" 
                                                       class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Assign Fees</a>
                                                <?php elseif ($balance > 0): ?>
                                                    <a href="<?= $FileName ?>?action=pay&token=<?= $student['fee_randomid'] ?>" 
                                                       class="btn btn-warning btn-sm"><i class="fa fa-money"></i> Pay Outstanding</a>
                                                    <a href="<?= $FileName ?>?action=view&token=<?= $student['fee_randomid'] ?>" 
                                                       class="btn btn-info btn-sm"><i class="fa fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    <a href="<?= $FileName ?>?action=view&token=<?= $student['fee_randomid'] ?>" 
                                                       class="btn btn-info btn-sm"><i class="fa fa-eye"></i> View</a>
                                                    <a href="student_invoice_print_details_pdf.php?token=<?= $student['fee_randomid'] ?>" 
                                                       target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-file-pdf-o"></i> Receipt</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="10" style="text-align:center; padding:40px;">No students found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ============================================================ -->
                        <!-- ASSIGN FEES -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'assign'): ?>
                        
                        <h4><i class="fa fa-plus"></i> Assign Fees to Student</h4>
                        
                        <?php 
                        $studentId = (int)($_GET['student_id'] ?? 0);
                        $studentInfo = $db->getRow("SELECT * FROM manage_student WHERE id = '" . $studentId . "'");
                        if (empty($studentInfo)) {
                            echo '<div class="alert alert-danger">Student not found</div>';
                        } else {
                        ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="student_id" value="<?= $studentInfo['id'] ?>">
                            <input type="hidden" name="session" value="<?= $_GET['session'] ?? $studentInfo['session'] ?>">
                            <input type="hidden" name="class" value="<?= $_GET['class'] ?? $studentInfo['class'] ?>">
                            <input type="hidden" name="term_id" value="<?= $_GET['term_id'] ?? $studentInfo['term_id'] ?>">
                            <input type="hidden" name="rollno" value="<?= htmlspecialchars($studentInfo['student_id']) ?>">
                            
                            <div class="form-row" style="background: #f0f4ff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <div class="form-group"><label>Student</label><input type="text" class="form-control" value="<?= htmlspecialchars(($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? '')) ?>" readonly></div>
                                <div class="form-group"><label>Class</label><input type="text" class="form-control" value="<?= htmlspecialchars($db->getVal("SELECT name FROM school_class WHERE id = '" . $studentInfo['class'] . "'")) ?>" readonly></div>
                                <div class="form-group"><label>Roll No</label><input type="text" class="form-control" value="<?= htmlspecialchars($studentInfo['student_id']) ?>" readonly></div>
                                <div class="form-group"><label>Student Status</label>
                                    <select class="form-control" name="student_status">
                                        <option value="1">Returning</option>
                                        <option value="2">New</option>
                                        <option value="3">Scholarship</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group"><label>Payment Type</label>
                                    <select class="form-control" name="payment_type">
                                        <option value="1">Flexible</option>
                                        <option value="2">Fixed</option>
                                    </select>
                                </div>
                            </div>
                            
                            <h5>Select Fees</h5>
                            <?php if (!empty($feeStructures)): ?>
                                <?php foreach ($feeStructures as $key => $fee): ?>
                                    <div class="fee-item">
                                        <div class="form-row">
                                            <input type="hidden" name="fee_sturcture_id[]" value="<?= $fee['id'] ?>">
                                            <div class="form-group" style="flex: 0 0 150px;">
                                                <label><?= htmlspecialchars($fee['title']) ?></label>
                                                <input type="text" class="form-control" name="fee_amount[]" value="<?= $fee['amount'] ?>" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>Discount</label>
                                                <input type="number" class="form-control" name="discount[]" value="0" min="0" step="100" onchange="calculateTotals()">
                                            </div>
                                            <div class="form-group">
                                                <label>Amount Paid</label>
                                                <input type="number" class="form-control" name="amount_paid[]" value="0" min="0" step="100" onchange="calculateTotals()">
                                            </div>
                                            <div class="form-group">
                                                <label>Payment Mode</label>
                                                <select class="form-control" name="payment_mode[]">
                                                    <option value="0">-- Select --</option>
                                                    <option value="1">Bank</option>
                                                    <option value="2">Cash</option>
                                                    <option value="3">POS</option>
                                                    <option value="4">Bank Transfer</option>
                                                    <option value="5">Scholarship</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Payment Date</label>
                                                <input type="date" class="form-control" name="payment_date[]" value="<?= date('Y-m-d') ?>">
                                            </div>
                                            <div class="form-group" style="flex: 0 0 200px;">
                                                <label>Discount Reason</label>
                                                <input type="text" class="form-control" name="discount_reason[]" placeholder="e.g., Sibling discount">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-danger">No fee structures found. Create fee structures first.</div>
                            <?php endif; ?>
                            
                            <div class="form-row" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px;">
                                <div class="form-group"><label>Total Amount</label><input type="text" class="form-control" id="total_amount" value="0" readonly></div>
                                <div class="form-group"><label>Total Discount</label><input type="text" class="form-control" id="total_discount" value="0" readonly></div>
                                <div class="form-group"><label>Total Paid</label><input type="text" class="form-control" id="total_paid" value="0" readonly></div>
                                <div class="form-group"><label>Balance</label><input type="text" class="form-control" id="total_balance" value="0" readonly></div>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="assign_fees" class="btn btn-success"><i class="fa fa-save"></i> Assign Fees</button>
                                <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                            </div>
                        </form>
                        
                        <?php } ?>
                        
                        <!-- ============================================================ -->
                        <!-- PAY OUTSTANDING -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'pay'): ?>
                        
                        <h4><i class="fa fa-money"></i> Pay Outstanding Fees</h4>
                        
                        <?php 
                        $token = $_GET['token'] ?? '';
                        $feeRecord = $db->getRow("SELECT * FROM student_fee WHERE randomid = '" . $token . "' AND create_by_userid = '" . $create_by_userid . "'");
                        if (empty($feeRecord)) {
                            echo '<div class="alert alert-danger">Fee record not found</div>';
                        } else {
                            $feeItems = $db->getRows("SELECT * FROM student_fee_sturcture WHERE student_fee_id = '" . $feeRecord['id'] . "'");
                            $student = $db->getRow("SELECT first_name, last_name, student_id FROM manage_student WHERE id = '" . $feeRecord['student_id'] . "'");
                        ?>
                        
                        <div class="form-row" style="background: #f0f4ff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <div class="form-group"><label>Student</label><input type="text" class="form-control" value="<?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>" readonly></div>
                            <div class="form-group"><label>Roll No</label><input type="text" class="form-control" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" readonly></div>
                            <div class="form-group"><label>Invoice</label><input type="text" class="form-control" value="<?= htmlspecialchars($feeRecord['invoiceno']) ?>" readonly></div>
                            <div class="form-group"><label>Outstanding Balance</label><input type="text" class="form-control" value="₦<?= number_format((float)$feeRecord['remain_amount'], 2) ?>" readonly style="font-weight:700; color:#dc3545;"></div>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="student_fee_id" value="<?= $feeRecord['id'] ?>">
                            
                            <?php foreach ($feeItems as $key => $item): 
                                $feeStructure = $db->getRow("SELECT title FROM fee_sturcture WHERE id = '" . $item['fee_sturcture_id'] . "'");
                                $outstanding = (float)$item['fees_outstanding'];
                                if ($outstanding <= 0) continue;
                            ?>
                                <div class="fee-item">
                                    <div class="form-row">
                                        <input type="hidden" name="fee_item_ids[]" value="<?= $item['id'] ?>">
                                        <div class="form-group" style="flex: 0 0 150px;">
                                            <label><?= htmlspecialchars($feeStructure['title'] ?? '') ?></label>
                                            <input type="text" class="form-control" value="₦<?= number_format($outstanding, 2) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Discount</label>
                                            <input type="number" class="form-control" name="discount[]" value="0" min="0" step="100">
                                        </div>
                                        <div class="form-group">
                                            <label>Amount to Pay</label>
                                            <input type="number" class="form-control" name="amount_paid[]" value="<?= $outstanding ?>" min="0" step="100" max="<?= $outstanding ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Payment Mode</label>
                                            <select class="form-control" name="payment_mode[]">
                                                <option value="0">-- Select --</option>
                                                <option value="1">Bank</option>
                                                <option value="2">Cash</option>
                                                <option value="3">POS</option>
                                                <option value="4">Bank Transfer</option>
                                                <option value="5">Scholarship</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Payment Date</label>
                                            <input type="date" class="form-control" name="payment_date[]" value="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="form-group" style="flex: 0 0 200px;">
                                            <label>Discount Reason</label>
                                            <input type="text" class="form-control" name="discount_reason[]" placeholder="e.g., Sibling discount">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($feeItems)): ?>
                                <div class="alert alert-info">All fees are fully paid.</div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="pay_outstanding" class="btn btn-success"><i class="fa fa-save"></i> Process Payment</button>
                                <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                            </div>
                        </form>
                        
                        <?php } ?>
                        
                        <!-- ============================================================ -->
                        <!-- VIEW FEE DETAILS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'view'): ?>
                        
                        <?php if (empty($_GET['token'])): ?>
                            <div class="alert alert-info">Select a student fee record to view details.</div>
                        <?php elseif (empty($feeRecord)): ?>
                            <div class="alert alert-danger">Fee record not found for the selected token.</div>
                        <?php else: ?>
                            <h4><i class="fa fa-eye"></i> Fee Details</h4>
                            
                            <?php 
                            $student = $db->getRow("SELECT first_name, last_name, student_id FROM manage_student WHERE id = '" . $feeRecord['student_id'] . "'");
                            ?>
                            
                            <div style="margin-bottom: 20px;">
                                <a href="student_invoice_print_details_pdf.php?token=<?= $feeRecord['randomid'] ?>" target="_blank" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> Download Invoice</a>
                                <?php if ((float)$feeRecord['remain_amount'] > 0): ?>
                                    <a href="<?= $FileName ?>?action=pay&token=<?= $feeRecord['randomid'] ?>" class="btn btn-warning"><i class="fa fa-money"></i> Pay Outstanding</a>
                                <?php endif; ?>
                                <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
                            </div>
                            
                            <div class="form-row" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <div class="form-group"><label>Student</label><input type="text" class="form-control" value="<?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>" readonly></div>
                                <div class="form-group"><label>Roll No</label><input type="text" class="form-control" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" readonly></div>
                                <div class="form-group"><label>Invoice</label><input type="text" class="form-control" value="<?= htmlspecialchars($feeRecord['invoiceno']) ?>" readonly></div>
                                <div class="form-group"><label>Status</label><input type="text" class="form-control" value="<?= ucfirst($feeRecord['payment_status'] ?? 'pending') ?>" readonly></div>
                            </div>
                            
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Fee Title</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Paid</th>
                                        <th>Outstanding</th>
                                        <th>Payment Date</th>
                                        <th>Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feeItems as $item): 
                                        $feeStructure = $db->getRow("SELECT title FROM fee_sturcture WHERE id = '" . $item['fee_sturcture_id'] . "'");
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($feeStructure['title'] ?? '') ?></td>
                                        <td>₦<?= number_format((float)$item['fee'], 2) ?></td>
                                        <td>₦<?= number_format((float)$item['fees_disccount'], 2) ?></td>
                                        <td><strong>₦<?= number_format((float)$item['fees_amount'], 2) ?></strong></td>
                                        <td><?= number_format((float)$item['fees_outstanding'], 2) ?></td>
                                        <td><?= $item['fees_date'] ?></td>
                                        <td><?= getPaymentModeLabel($item['payment_mode']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight:700; background:#f8f9fa;">
                                        <td>Total</td>
                                        <td>₦<?= number_format((float)$feeRecord['total_amount_to_pay'], 2) ?></td>
                                        <td>₦<?= number_format((float)$feeRecord['discount_amount'], 2) ?></td>
                                        <td>₦<?= number_format((float)$feeRecord['currently_paying_amount'], 2) ?></td>
                                        <td style="color:<?= ($feeRecord['remain_amount'] > 0) ? '#dc3545' : '#28a745' ?>;">₦<?= number_format((float)$feeRecord['remain_amount'], 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php endif; ?>
                        
                        <?php endif; ?>
                        
                        <!-- ============================================================ -->
                        <!-- TRANSACTIONS -->
                        <!-- ============================================================ -->
                        <?php if (isset($_GET['action']) && $_GET['action'] == 'transactions'): ?>
                        
                        <h4><i class="fa fa-history"></i> Transaction History</h4>
                        
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group"><label>Session</label>
                                    <select class="form-control" name="session">
                                        <option value="">-- All --</option>
                                        <?php foreach ($sessions as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= (isset($_POST['session']) && $_POST['session'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group"><label>Class</label>
                                    <select class="form-control" name="class">
                                        <option value="">-- All --</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= (isset($_POST['class']) && $_POST['class'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group"><label>Term</label>
                                    <select class="form-control" name="term_id">
                                        <option value="">-- All --</option>
                                        <?php foreach ($terms as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= (isset($_POST['term_id']) && $_POST['term_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group"><label>Invoice</label>
                                    <input type="text" class="form-control" name="invoiceno" value="<?= $_POST['invoiceno'] ?? '' ?>" placeholder="Search invoice">
                                </div>
                                <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end; gap: 10px;">
                                    <button type="submit" name="filter_transactions" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
                                    <a href="<?= $FileName ?>?action=transactions" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Invoice</th>
                                        <th>Amount Paid</th>
                                        <th>Discount</th>
                                        <th>Balance</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transactions)): ?>
                                        <?php $i = 0; foreach ($transactions as $trans): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= htmlspecialchars($trans['fullname']) ?></td>
                                            <td><?= htmlspecialchars($trans['invoiceno']) ?></td>
                                            <td><strong>₦<?= number_format((float)$trans['currently_paying_amount'], 2) ?></strong></td>
                                            <td>₦<?= number_format((float)$trans['discount_amount'], 2) ?></td>
                                            <td>₦<?= number_format((float)$trans['remain_amount'], 2) ?></td>
                                            <td><?= $trans['create_at'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" style="text-align:center; padding:40px;">No transactions found</td></tr>
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
<script>
function calculateTotals() {
    // Get all fee items
    var feeItems = document.querySelectorAll('.fee-item');
    var totalAmount = 0;
    var totalDiscount = 0;
    var totalPaid = 0;
    
    feeItems.forEach(function(item) {
        var discount = parseFloat(item.querySelector('input[name="discount[]"]')?.value) || 0;
        var paid = parseFloat(item.querySelector('input[name="amount_paid[]"]')?.value) || 0;
        var feeAmount = parseFloat(item.querySelector('input[name="fee_amount[]"]')?.value) || 0;
        
        totalDiscount += discount;
        totalPaid += paid;
        totalAmount += feeAmount;
    });
    
    var balance = totalAmount - totalDiscount - totalPaid;
    if (balance < 0) balance = 0;
    
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
    document.getElementById('total_discount').value = totalDiscount.toFixed(2);
    document.getElementById('total_paid').value = totalPaid.toFixed(2);
    document.getElementById('total_balance').value = balance.toFixed(2);
}

// Auto-calculate on input change
document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('input[name="discount[]"], input[name="amount_paid[]"]');
    inputs.forEach(function(input) {
        input.addEventListener('change', calculateTotals);
        input.addEventListener('keyup', calculateTotals);
    });
});
</script>
</body>
</html>