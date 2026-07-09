<?php
/**
 * ============================================================================
 * BULK FEE ASSIGNMENT - PHP 8+ UPGRADED
 * ============================================================================
 * Description: Assign fees to all students in a class at once
 * Features: Select class, session, term, fees, and assign to all students
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Bulk Fee Assignment";
$FileName = 'bulk_fee_assignment.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$currentUserId = (int)($_SESSION['userid'] ?? 0);
$currentUserType = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// HANDLE POST REQUEST - PROCESS BULK ASSIGNMENT
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_fees'])) {
    try {
        $sessionId = (int)($_POST['session'] ?? 0);
        $termId = (int)($_POST['term_id'] ?? 0);
        $classId = (int)($_POST['class'] ?? 0);
        $selectedFees = $_POST['fee_structure_ids'] ?? [];
        $studentStatus = (int)($_POST['student_status'] ?? 1);
        $paymentType = (int)($_POST['payment_type'] ?? 1);
        
        // Validate inputs
        if ($sessionId <= 0) {
            throw new Exception("Please select a session");
        }
        if ($termId <= 0) {
            throw new Exception("Please select a term");
        }
        if ($classId <= 0) {
            throw new Exception("Please select a class");
        }
        if (empty($selectedFees) || !is_array($selectedFees)) {
            throw new Exception("Please select at least one fee to assign");
        }
        
        // Get all students in the class for this session and term
        $students = $db->getRows(
            "SELECT id, student_id, first_name, last_name 
             FROM manage_student 
             WHERE class = '" . $classId . "' 
             AND session = '" . $sessionId . "' 
             AND term_id = '" . $termId . "' 
             AND create_by_userid = '" . $create_by_userid . "' 
             ORDER BY first_name ASC"
        );
        
        if (empty($students)) {
            throw new Exception("No students found in this class for the selected session and term");
        }
        
        // Get fee structures
        $feeStructures = $db->getRows(
            "SELECT * FROM fee_sturcture 
             WHERE id IN (" . implode(',', array_map('intval', $selectedFees)) . ") 
             AND create_by_userid = '" . $create_by_userid . "' 
             AND status != 2"
        );
        
        if (empty($feeStructures)) {
            throw new Exception("Selected fee structures not found");
        }
        
        $assignedCount = 0;
        $skippedCount = 0;
        $errors = [];
        
        foreach ($students as $student) {
            // Check if student already has fees for this session/term/class
            $existingFee = $db->getRow(
                "SELECT id, randomid FROM student_fee 
                 WHERE student_id = '" . $student['id'] . "' 
                 AND session = '" . $sessionId . "' 
                 AND term_id = '" . $termId . "' 
                 AND class = '" . $classId . "' 
                 AND create_by_userid = '" . $create_by_userid . "'"
            );
            
            // If student already has fees, skip (to avoid duplicates)
            if (!empty($existingFee)) {
                $skippedCount++;
                continue;
            }
            
            // Generate invoice number
            $lastId = (int)$db->getVal("SELECT MAX(id) FROM student_fee") + 1;
            $invoiceNo = randomFix(7) . $lastId;
            $randomId = randomFix(15) . $lastId;
            
            // Calculate totals
            $totalAmount = 0;
            $totalDiscount = 0;
            $totalPaid = 0;
            
            foreach ($feeStructures as $fee) {
                $totalAmount += (float)$fee['amount'];
            }
            
            $remainAmount = $totalAmount - $totalDiscount - $totalPaid;
            
            // Insert main fee record
            $feeData = array(
                'student_id' => $student['id'],
                'session' => $sessionId,
                'class' => $classId,
                'term_id' => $termId,
                'rollno' => $student['student_id'],
                'student_status' => $studentStatus,
                'PType' => $paymentType,
                'total_amount_to_pay' => $totalAmount,
                'currently_paying_amount' => $totalPaid,
                'remain_amount' => $remainAmount,
                'discount_amount' => $totalDiscount,
                'invoiceno' => $invoiceNo,
                'userid' => $currentUserId,
                'usertype' => $currentUserType,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => $randomId,
                'create_at' => date("Y-m-d H:i:s"),
                'update_at' => date("Y-m-d H:i:s")
            );
            
            $studentFeeId = $db->insertAry("student_fee", $feeData);
            
            if (!$studentFeeId) {
                $errors[] = "Failed to save fee for student: " . $student['first_name'] . ' ' . $student['last_name'];
                continue;
            }
            
            // Insert individual fee items
            foreach ($feeStructures as $fee) {
                $feeItemData = array(
                    'student_fee_id' => $studentFeeId,
                    'fee_sturcture_id' => $fee['id'],
                    'fee' => $fee['amount'],
                    'fees_disccount' => 0,
                    'fees_outstanding' => $fee['amount'],
                    'fees_date' => date('Y-m-d'),
                    'fees_amount' => 0,
                    'payment_mode' => 0
                );
                $db->insertAry("student_fee_sturcture", $feeItemData);
            }
            
            // Create transaction record
            $fullName = trim($student['first_name'] . ' ' . $student['last_name']);
            $transData = array(
                'student_fee_id' => $studentFeeId,
                'student_id' => $student['id'],
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
                'usertype' => $currentUserType,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'create_at' => date("Y-m-d H:i:s")
            );
            $db->insertAry("student_fee_transcation", $transData);
            
            $assignedCount++;
        }
        
        $message = "Bulk assignment completed!<br>";
        $message .= "- Students assigned: " . $assignedCount . "<br>";
        $message .= "- Students skipped (already have fees): " . $skippedCount . "<br>";
        if (!empty($errors)) {
            $message .= "- Errors: " . implode(", ", $errors);
        }
        
        $_SESSION['success'] = $message;
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// GET DATA FOR DROPDOWNS
// ============================================================================

// Get sessions
$sessions = $db->getRows(
    "SELECT * FROM school_session 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     ORDER BY id DESC"
);

// Get terms
$terms = $db->getRows(
    "SELECT * FROM school_term 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     ORDER BY id ASC"
);

// Get classes
$classes = $db->getRows(
    "SELECT * FROM school_class 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     ORDER BY name ASC"
);

// Get fee structures
$feeStructures = $db->getRows(
    "SELECT * FROM fee_sturcture 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     AND status != 2 
     ORDER BY category ASC, title ASC"
);

// ============================================================================
// GET STATISTICS - FIXED
// ============================================================================

// Initialize variables
$totalStudents = 0;
$existingFeeCount = 0;
$studentsWithoutFees = 0;
$classInfo = '';

// If filters are selected, show preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview'])) {
    $sessionId = (int)($_POST['session'] ?? 0);
    $termId = (int)($_POST['term_id'] ?? 0);
    $classId = (int)($_POST['class'] ?? 0);

    if ($sessionId > 0 && $termId > 0 && $classId > 0) {
        // Get class name
        $classInfo = $db->getRow(
            "SELECT name FROM school_class WHERE id = '" . $classId . "' AND create_by_userid = '" . $create_by_userid . "'"
        );

        // Get total students in this class for this session and term
        $totalStudents = (int)$db->getVal(
            "SELECT COUNT(*) 
             FROM manage_student 
             WHERE create_by_userid = '" . $create_by_userid . "' 
               AND class = '" . $classId . "' 
               AND session = '" . $sessionId . "' 
               AND term_id = '" . $termId . "'"
        );

        // Get students who already have fees for this session/term/class
        $existingFeeCount = (int)$db->getVal(
            "SELECT COUNT(DISTINCT sf.student_id) 
             FROM student_fee sf
             WHERE sf.create_by_userid = '" . $create_by_userid . "' 
               AND sf.class = '" . $classId . "' 
               AND sf.session = '" . $sessionId . "' 
               AND sf.term_id = '" . $termId . "'"
        );

        // Calculate students without fees
        $studentsWithoutFees = max($totalStudents - $existingFeeCount, 0);
    }
}

// Get current session and term from database
$currentSession = !empty($sessions) ? $sessions[0]['id'] : 0;
$currentTerm = !empty($terms) ? $terms[0]['id'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title><?= htmlspecialchars($PageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* ===== GENERAL STYLES ===== */
        * { box-sizing: border-box; }
        .page-title { font-size: 24px; font-weight: 600; color: #1B3058; margin-bottom: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        
        /* ===== CARDS ===== */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 18px 24px; background: linear-gradient(135deg, #1B3058, #2a4780); color: #fff; font-weight: 600; font-size: 16px; }
        .card-body { padding: 24px; }
        
        /* ===== FORMS ===== */
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; min-width: 180px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .form-control { 
            width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 8px; 
            font-size: 14px; transition: all 0.3s; background: #fff;
        }
        .form-control:focus { border-color: #1B3058; outline: none; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        
        /* ===== CHECKBOXES ===== */
        .checkbox-group { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin: 10px 0; }
        .checkbox-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e0e0e0; }
        .checkbox-item:hover { background: #e8f0fe; }
        .checkbox-item input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .checkbox-item label { cursor: pointer; margin: 0; font-weight: normal; }
        .checkbox-item .fee-amount { color: #1B3058; font-weight: 600; margin-left: auto; }
        
        /* ===== BUTTONS ===== */
        .btn { 
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; 
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600; 
            cursor: pointer; transition: all 0.3s; text-decoration: none;
        }
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
        
        /* ===== STATS ===== */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fa; border-radius: 8px; padding: 15px 20px; text-align: center; border-left: 4px solid #1B3058; }
        .stat-box .number { font-size: 28px; font-weight: 700; color: #1B3058; }
        .stat-box .label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-top: 4px; }
        .stat-box.warning { border-color: #ffc107; }
        .stat-box.success { border-color: #28a745; }
        .stat-box.danger { border-color: #dc3545; }
        
        /* ===== ALERTS ===== */
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
            .checkbox-group { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="fixed-left">
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="container">
                
                <!-- Page Header -->
                <div class="page-title">
                    <i class="fa fa-users"></i> <?= htmlspecialchars($PageTitle) ?>
                    <small style="font-size:14px; color:#888; font-weight:400; display:block; margin-top:4px;">
                        Assign fees to all students in a class at once
                    </small>
                </div>
                
                <!-- Messages -->
                <?= msg($stat) ?>
                
                <!-- Main Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-gear"></i> Bulk Fee Assignment
                        <span style="float:right; font-size:12px; font-weight:400;">
                            <i class="fa fa-info-circle"></i> Select class, term, session, and fees to assign
                        </span>
                    </div>
                    <div class="card-body">
                        
                        <!-- Selection Form -->
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Session *</label>
                                    <select class="form-control" name="session" required>
                                        <option value="">-- Select Session --</option>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?= $session['id'] ?>" 
                                                <?= (isset($_POST['session']) && $_POST['session'] == $session['id']) ? 'selected' : '' ?>
                                                <?= (!isset($_POST['session']) && $session['id'] == $currentSession) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($session['session']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Term *</label>
                                    <select class="form-control" name="term_id" required>
                                        <option value="">-- Select Term --</option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term['id'] ?>" 
                                                <?= (isset($_POST['term_id']) && $_POST['term_id'] == $term['id']) ? 'selected' : '' ?>
                                                <?= (!isset($_POST['term_id']) && $term['id'] == $currentTerm) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($term['term']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Class *</label>
                                    <select class="form-control" name="class" required>
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>" 
                                                <?= (isset($_POST['class']) && $_POST['class'] == $class['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Student Status</label>
                                    <select class="form-control" name="student_status">
                                        <option value="1">Returning</option>
                                        <option value="2">New</option>
                                        <option value="3">Scholarship</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Type</label>
                                    <select class="form-control" name="payment_type">
                                        <option value="1">Flexible</option>
                                        <option value="2">Fixed</option>
                                    </select>
                                </div>
                                <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end; gap: 10px;">
                                    <button type="submit" name="preview" class="btn btn-info">
                                        <i class="fa fa-eye"></i> Preview
                                    </button>
                                    <a href="<?= $FileName ?>" class="btn btn-danger">
                                        <i class="fa fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                        
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview'])): ?>
                        
                        <!-- Preview Stats -->
                        <hr>
                        <h5><i class="fa fa-bar-chart"></i> Preview</h5>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="number"><?= $totalStudents ?></div>
                                <div class="label">Total Students</div>
                            </div>
                            <div class="stat-box warning">
                                <div class="number"><?= $studentsWithoutFees ?></div>
                                <div class="label">Students Without Fees</div>
                            </div>
                            <div class="stat-box success">
                                <div class="number"><?= $existingFeeCount ?></div>
                                <div class="label">Already Have Fees</div>
                            </div>
                            <div class="stat-box">
                                <div class="number"><?= count($feeStructures) ?></div>
                                <div class="label">Available Fees</div>
                            </div>
                        </div>
                        
                        <!-- Fee Selection Form -->
                        <form method="POST" action="" id="bulkForm">
                            <input type="hidden" name="session" value="<?= (int)($_POST['session'] ?? 0) ?>">
                            <input type="hidden" name="term_id" value="<?= (int)($_POST['term_id'] ?? 0) ?>">
                            <input type="hidden" name="class" value="<?= (int)($_POST['class'] ?? 0) ?>">
                            <input type="hidden" name="student_status" value="<?= (int)($_POST['student_status'] ?? 1) ?>">
                            <input type="hidden" name="payment_type" value="<?= (int)($_POST['payment_type'] ?? 1) ?>">
                            
                            <div style="margin-top: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                                    <strong><i class="fa fa-list"></i> Select Fees to Assign</strong>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-success" onclick="selectAll()">
                                            <i class="fa fa-check-square-o"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deselectAll()">
                                            <i class="fa fa-square-o"></i> Deselect All
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if (!empty($feeStructures)): ?>
                                <div class="checkbox-group">
                                    <?php foreach ($feeStructures as $fee): ?>
                                        <div class="checkbox-item">
                                            <input type="checkbox" name="fee_structure_ids[]" 
                                                   value="<?= $fee['id'] ?>" id="fee_<?= $fee['id'] ?>"
                                                   <?= (isset($_POST['fee_structure_ids']) && is_array($_POST['fee_structure_ids']) && in_array($fee['id'], $_POST['fee_structure_ids'])) ? 'checked' : '' ?>>
                                            <label for="fee_<?= $fee['id'] ?>">
                                                <?= htmlspecialchars($fee['title']) ?>
                                                <span style="font-size:11px; color:#888; display:block;">
                                                    <?= htmlspecialchars($fee['category'] ?? 'General') ?>
                                                </span>
                                            </label>
                                            <span class="fee-amount">₦<?= number_format((float)$fee['amount'], 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        No fee structures found. Please create fee structures first.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($feeStructures)): ?>
                            <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <button type="submit" name="assign_fees" class="btn btn-success" 
                                        onclick="return confirm('Are you sure you want to assign fees to <?= $studentsWithoutFees ?> students? This cannot be undone easily.')">
                                    <i class="fa fa-save"></i> Assign Fees to <?= $studentsWithoutFees ?> Students
                                </button>
                                <button type="button" class="btn btn-info" onclick="calculateTotal()">
                                    <i class="fa fa-calculator"></i> Calculate Total
                                </button>
                                <span id="totalAmount" style="font-size:16px; font-weight:700; color:#1B3058; align-self:center;"></span>
                            </div>
                            <?php endif; ?>
                        </form>
                        
                        <?php endif; ?>
                        
                        <!-- Instructions -->
                        <hr>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                            <h6><i class="fa fa-info-circle"></i> How It Works</h6>
                            <ol style="margin: 10px 0 0 20px; color: #555; line-height: 1.8;">
                                <li>Select <strong>Session, Term, and Class</strong> from the dropdowns above</li>
                                <li>Click <strong>"Preview"</strong> to see how many students will be affected</li>
                                <li>Select the <strong>fee structures</strong> you want to assign to all students</li>
                                <li>Click <strong>"Assign Fees"</strong> to process the bulk assignment</li>
                                <li>Students who already have fees for this session/term will be <strong>skipped</strong></li>
                            </ol>
                            <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 6px; color: #856404;">
                                <i class="fa fa-warning"></i> <strong>Note:</strong> This action will create fee records for all students in the selected class. Students who already have fees will be skipped to avoid duplicates.
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card" style="background: #f8f9fa; border: 1px dashed #ddd;">
                    <div class="card-body" style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                        <a href="takefee.php" class="btn btn-primary">
                            <i class="fa fa-money"></i> Manage Student Fees
                        </a>
                        <a href="fee_structure.php" class="btn btn-info">
                            <i class="fa fa-list"></i> Fee Structures
                        </a>
                        <a href="takefee.php?action=transaction" class="btn btn-success">
                            <i class="fa fa-history"></i> View Transactions
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script>
/**
 * Select all checkboxes
 */
function selectAll() {
    var checkboxes = document.querySelectorAll('input[name="fee_structure_ids[]"]');
    checkboxes.forEach(function(cb) {
        cb.checked = true;
    });
}

/**
 * Deselect all checkboxes
 */
function deselectAll() {
    var checkboxes = document.querySelectorAll('input[name="fee_structure_ids[]"]');
    checkboxes.forEach(function(cb) {
        cb.checked = false;
    });
}

/**
 * Calculate total amount of selected fees
 */
function calculateTotal() {
    var checkboxes = document.querySelectorAll('input[name="fee_structure_ids[]"]:checked');
    var total = 0;
    
    checkboxes.forEach(function(cb) {
        // Find the parent checkbox-item and get the fee amount
        var parent = cb.closest('.checkbox-item');
        if (parent) {
            var amountText = parent.querySelector('.fee-amount');
            if (amountText) {
                var amount = parseFloat(amountText.textContent.replace('₦', '').replace(/,/g, ''));
                if (!isNaN(amount)) {
                    total += amount;
                }
            }
        }
    });
    
    var totalSpan = document.getElementById('totalAmount');
    if (total > 0) {
        totalSpan.innerHTML = 'Total Selected: ₦' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    } else {
        totalSpan.innerHTML = '';
    }
}

/**
 * Auto-calculate when checkboxes change
 */
document.addEventListener('DOMContentLoaded', function() {
    var checkboxes = document.querySelectorAll('input[name="fee_structure_ids[]"]');
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', calculateTotal);
    });
    
    // Auto-hide alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }, 5000);
    });
});
</script>
</body>
</html>