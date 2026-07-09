<?php
/**
 * ============================================================================
 * FEE STRUCTURE MANAGEMENT - PHP 8+ UPGRADED
 * ============================================================================
 * Description: Manage fee structures with categories, class assignment,
 *              due dates, and late fees
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Fee Structure";
$FileName = 'takefeesturcture.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// HANDLE POST REQUESTS
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Add New Fee Structure
    if (isset($_POST['addnewrecord'])) {
        try {
            // Validate input
            $title = trim($_POST['title'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            
            if (empty($title)) {
                throw new Exception("Fee title is required");
            }
            if ($amount <= 0) {
                throw new Exception("Valid fee amount is required");
            }
            
            // Generate random ID
            $lastId = (int)$db->getVal("SELECT MAX(id) FROM fee_sturcture") + 1;
            $randomId = randomFix(15) . $lastId;
            
            // Insert data using your existing insertAry method
            $aryData = array(
                'title' => $title,
                'amount' => $amount,
                'category' => trim($_POST['category'] ?? 'General'),
                'class_id' => !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null,
                'term_id' => !empty($_POST['term_id']) ? (int)$_POST['term_id'] : null,
                'session_id' => !empty($_POST['session_id']) ? (int)$_POST['session_id'] : null,
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'late_fee_amount' => (float)($_POST['late_fee_amount'] ?? 0),
                'late_fee_type' => $_POST['late_fee_type'] ?? 'fixed',
                'is_optional' => (int)($_POST['is_optional'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype,
                'randomid' => $randomId,
                'status' => 1
            );
            
            $flgIn = $db->insertAry("fee_sturcture", $aryData);
            
            $_SESSION['success'] = "Fee structure created successfully!";
            redirect($FileName);
            
        } catch (Exception $e) {
            $stat['error'] = $e->getMessage();
        }
    }
    
    // Update Fee Structure
    if (isset($_POST['updaterecord'])) {
        try {
            $randomId = trim($_POST['randomid'] ?? '');
            if (empty($randomId)) {
                throw new Exception("Invalid fee structure");
            }
            
            $title = trim($_POST['title'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            
            if (empty($title)) {
                throw new Exception("Fee title is required");
            }
            if ($amount <= 0) {
                throw new Exception("Valid fee amount is required");
            }
            
            $aryData = array(
                'title' => $title,
                'amount' => $amount,
                'category' => trim($_POST['category'] ?? 'General'),
                'class_id' => !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null,
                'term_id' => !empty($_POST['term_id']) ? (int)$_POST['term_id'] : null,
                'session_id' => !empty($_POST['session_id']) ? (int)$_POST['session_id'] : null,
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'late_fee_amount' => (float)($_POST['late_fee_amount'] ?? 0),
                'late_fee_type' => $_POST['late_fee_type'] ?? 'fixed',
                'is_optional' => (int)($_POST['is_optional'] ?? 0),
                'description' => trim($_POST['description'] ?? '')
            );
            
            $flgIn2 = $db->updateAry("fee_sturcture", $aryData, "where randomid='" . $randomId . "'");
            
            $_SESSION['success'] = "Fee structure updated successfully!";
            redirect($FileName);
            
        } catch (Exception $e) {
            $stat['error'] = $e->getMessage();
        }
    }
}

// ============================================================================
// HANDLE GET REQUESTS
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    try {
        $randomId = trim($_GET['randomid'] ?? '');
        if (empty($randomId)) {
            throw new Exception("Invalid fee structure");
        }
        
        $aryData = array(
            'status' => 2
        );
        $flgIn2 = $db->updateAry("fee_sturcture", $aryData, "where randomid='" . $randomId . "'");
        $_SESSION['success'] = "Fee structure deleted successfully!";
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
    redirect($FileName);
}

// ============================================================================
// GET DATA
// ============================================================================
// Get all fee structures
$feeStructures = $db->getRows(
    "SELECT * FROM fee_sturcture 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     AND status != 2 
     ORDER BY id DESC"
);

// Get classes for dropdown
$classes = $db->getRows(
    "SELECT id, name FROM school_class 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     ORDER BY name ASC"
);

// Get sessions for dropdown
$sessions = $db->getRows(
    "SELECT id, session FROM school_session 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     ORDER BY id DESC"
);

// Get terms for dropdown
$terms = $db->getRows(
    "SELECT id, term FROM school_term 
     WHERE create_by_userid = '" . $create_by_userid . "' 
     ORDER BY id ASC"
);

// Get fee structure for editing
$editFee = [];
if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['randomid'])) {
    $editFee = $db->getRow(
        "SELECT * FROM fee_sturcture 
         WHERE randomid = '" . $_GET['randomid'] . "' 
         AND create_by_userid = '" . $create_by_userid . "'"
    );
}
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
        .container { max-width: 1400px; margin: 0 auto; padding: 0 15px; }
        
        /* ===== CARDS ===== */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 18px 24px; background: linear-gradient(135deg, #1B3058, #2a4780); color: #fff; font-weight: 600; font-size: 16px; }
        .card-body { padding: 24px; }
        
        /* ===== FORMS ===== */
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; min-width: 200px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .form-control { 
            width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 8px; 
            font-size: 14px; transition: all 0.3s; background: #fff;
        }
        .form-control:focus { border-color: #1B3058; outline: none; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        .form-control-sm { padding: 6px 10px; font-size: 13px; }
        textarea.form-control { resize: vertical; min-height: 60px; }
        
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
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        .btn-block { width: 100%; justify-content: center; }
        
        /* ===== TABLE ===== */
        .table-wrapper { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table th { background: #f8f9fa; color: #1B3058; font-weight: 700; padding: 12px 16px; text-align: left; border-bottom: 2px solid #e0e0e0; }
        .table td { padding: 12px 16px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .table tr:hover { background: #f8f9ff; }
        .table .text-center { text-align: center; }
        .table .text-muted { color: #999; }
        
        /* ===== BADGES ===== */
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        /* ===== ALERTS ===== */
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert i { font-size: 18px; }
        
        /* ===== STATS ===== */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-box { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #1B3058; }
        .stat-box .label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; }
        .stat-box .value { font-size: 28px; font-weight: 700; color: #1B3058; margin-top: 4px; }
        .stat-box .sub { font-size: 12px; color: #666; margin-top: 4px; }
        
        /* ===== SEARCH ===== */
        .search-box { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .search-box .form-control { flex: 1; min-width: 200px; }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .table th, .table td { padding: 8px 10px; font-size: 12px; }
            .btn { padding: 8px 16px; font-size: 13px; }
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
                    <i class="fa fa-money"></i> <?= htmlspecialchars($PageTitle) ?>
                    <small style="font-size:14px; color:#888; font-weight:400; display:block; margin-top:4px;">
                        Manage fee structures, categories, and assignments
                    </small>
                </div>
                
                <!-- Messages -->
                <?= msg($stat) ?>
                
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="label">Total Fees</div>
                        <div class="value"><?= count($feeStructures) ?></div>
                        <div class="sub">Active fee structures</div>
                    </div>
                    <div class="stat-box" style="border-color: #28a745;">
                        <div class="label">Total Amount</div>
                        <div class="value">₦<?= number_format(array_sum(array_column($feeStructures, 'amount')), 2) ?></div>
                        <div class="sub">Sum of all fees</div>
                    </div>
                    <div class="stat-box" style="border-color: #ffc107;">
                        <div class="label">Categories</div>
                        <div class="value">
                            <?php 
                            $categories = array_unique(array_column($feeStructures, 'category'));
                            echo count(array_filter($categories));
                            ?>
                        </div>
                        <div class="sub">Unique fee categories</div>
                    </div>
                </div>
                
                <!-- Add/Edit Fee Form -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa <?= empty($editFee) ? 'fa-plus' : 'fa-edit' ?>"></i> 
                        <?= empty($editFee) ? 'Add New Fee Structure' : 'Edit Fee Structure' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="feeForm">
                            <?php if (!empty($editFee)): ?>
                                <input type="hidden" name="randomid" value="<?= htmlspecialchars($editFee['randomid']) ?>">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Fee Title *</label>
                                    <input type="text" class="form-control" name="title" 
                                           value="<?= htmlspecialchars($editFee['title'] ?? '') ?>" 
                                           placeholder="e.g., Tuition Fee" required>
                                </div>
                                <div class="form-group">
                                    <label>Amount (₦) *</label>
                                    <input type="number" class="form-control" name="amount" 
                                           value="<?= htmlspecialchars($editFee['amount'] ?? '') ?>" 
                                           placeholder="0.00" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="category">
                                        <option value="General" <?= (($editFee['category'] ?? '') == 'General') ? 'selected' : '' ?>>General</option>
                                        <option value="Tuition" <?= (($editFee['category'] ?? '') == 'Tuition') ? 'selected' : '' ?>>Tuition</option>
                                        <option value="Transport" <?= (($editFee['category'] ?? '') == 'Transport') ? 'selected' : '' ?>>Transport</option>
                                        <option value="Meals" <?= (($editFee['category'] ?? '') == 'Meals') ? 'selected' : '' ?>>Meals</option>
                                        <option value="Sports" <?= (($editFee['category'] ?? '') == 'Sports') ? 'selected' : '' ?>>Sports</option>
                                        <option value="Library" <?= (($editFee['category'] ?? '') == 'Library') ? 'selected' : '' ?>>Library</option>
                                        <option value="Laboratory" <?= (($editFee['category'] ?? '') == 'Laboratory') ? 'selected' : '' ?>>Laboratory</option>
                                        <option value="Other" <?= (($editFee['category'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Class (Optional)</label>
                                    <select class="form-control" name="class_id">
                                        <option value="">-- All Classes --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>" 
                                                <?= ((int)($editFee['class_id'] ?? 0) == (int)$class['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Session (Optional)</label>
                                    <select class="form-control" name="session_id">
                                        <option value="">-- Any Session --</option>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?= $session['id'] ?>" 
                                                <?= ((int)($editFee['session_id'] ?? 0) == (int)$session['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($session['session']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Term (Optional)</label>
                                    <select class="form-control" name="term_id">
                                        <option value="">-- Any Term --</option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term['id'] ?>" 
                                                <?= ((int)($editFee['term_id'] ?? 0) == (int)$term['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($term['term']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Due Date (Optional)</label>
                                    <input type="date" class="form-control" name="due_date" 
                                           value="<?= htmlspecialchars($editFee['due_date'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Late Fee Amount</label>
                                    <input type="number" class="form-control" name="late_fee_amount" 
                                           value="<?= htmlspecialchars($editFee['late_fee_amount'] ?? '0') ?>" 
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Late Fee Type</label>
                                    <select class="form-control" name="late_fee_type">
                                        <option value="fixed" <?= (($editFee['late_fee_type'] ?? '') == 'fixed') ? 'selected' : '' ?>>Fixed Amount</option>
                                        <option value="percentage" <?= (($editFee['late_fee_type'] ?? '') == 'percentage') ? 'selected' : '' ?>>Percentage</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" rows="2" 
                                              placeholder="Additional details about this fee"><?= htmlspecialchars($editFee['description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group" style="flex: 0 0 150px;">
                                    <label>Optional?</label>
                                    <select class="form-control" name="is_optional">
                                        <option value="0" <?= ((int)($editFee['is_optional'] ?? 0) == 0) ? 'selected' : '' ?>>No</option>
                                        <option value="1" <?= ((int)($editFee['is_optional'] ?? 0) == 1) ? 'selected' : '' ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <button type="submit" name="<?= empty($editFee) ? 'addnewrecord' : 'updaterecord' ?>" 
                                        class="btn <?= empty($editFee) ? 'btn-success' : 'btn-primary' ?>">
                                    <i class="fa <?= empty($editFee) ? 'fa-save' : 'fa-check' ?>"></i>
                                    <?= empty($editFee) ? 'Save Fee' : 'Update Fee' ?>
                                </button>
                                <?php if (!empty($editFee)): ?>
                                    <a href="<?= $FileName ?>" class="btn btn-danger">
                                        <i class="fa fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Fee List -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-list"></i> Fee Structures
                        <span class="badge badge-info" style="float:right; background:#fff; color:#1B3058;">
                            <?= count($feeStructures) ?> records
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="search-box">
                            <input type="text" id="searchFee" class="form-control" placeholder="🔍 Search fees by title, category...">
                            <button class="btn btn-primary" onclick="window.location.reload()">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                        
                        <!-- Table -->
                        <div class="table-wrapper">
                            <table class="table" id="feeTable">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Fee Title</th>
                                        <th>Amount (₦)</th>
                                        <th>Category</th>
                                        <th>Class</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th style="width:120px; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($feeStructures)): ?>
                                        <?php $i = 0; foreach ($feeStructures as $fee): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($fee['title']) ?></strong>
                                                <?php if (!empty($fee['description'])): ?>
                                                    <br><small style="color:#888;"><?= htmlspecialchars(substr($fee['description'], 0, 50)) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong>₦<?= number_format((float)$fee['amount'], 2) ?></strong></td>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($fee['category'] ?? 'General') ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($fee['class_id'])) {
                                                    $className = $db->getVal("SELECT name FROM school_class WHERE id = '" . $fee['class_id'] . "'");
                                                    echo htmlspecialchars($className ?: 'N/A');
                                                } else {
                                                    echo '<span style="color:#888;">All Classes</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($fee['due_date'])) {
                                                    echo date('d M Y', strtotime($fee['due_date']));
                                                } else {
                                                    echo '<span style="color:#888;">Not set</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                            <td style="text-align:center;">
                                                <a href="<?= $FileName ?>?action=edit&randomid=<?= $fee['randomid'] ?>" 
                                                   class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" 
                                                   onclick="confirmDelete('<?= $fee['randomid'] ?>', '<?= htmlspecialchars($fee['title']) ?>')" 
                                                   class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" style="text-align:center; padding:40px; color:#999;">
                                                <i class="fa fa-inbox" style="font-size:40px; display:block; margin-bottom:10px;"></i>
                                                No fee structures found.<br>
                                                <small>Click "Save Fee" above to create your first fee structure.</small>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card" style="background: #f8f9fa; border: 1px dashed #ddd;">
                    <div class="card-body" style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                        <a href="takefee.php?action=add" class="btn btn-success">
                            <i class="fa fa-user-plus"></i> Assign Fees to Student
                        </a>
                        <a href="takefee.php?action=transaction" class="btn btn-primary">
                            <i class="fa fa-history"></i> View Transactions
                        </a>
                        <a href="bulk_fee_assignment.php" class="btn btn-warning">
                            <i class="fa fa-users"></i> Bulk Assign Fees
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
 * Confirm delete with SweetAlert style
 */
function confirmDelete(randomid, title) {
    if (confirm('Are you sure you want to delete "' + title + '"? This action cannot be undone.')) {
        window.location.href = '<?= $FileName ?>?action=delete&randomid=' + randomid;
    }
}

/**
 * Search functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchFee');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#feeTable tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                // Skip the "no records" row
                if (row.querySelector('td[colspan]')) {
                    return;
                }
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide empty message
            const emptyRow = document.querySelector('#feeTable tbody tr td[colspan]');
            if (emptyRow) {
                if (visibleCount === 0 && searchTerm !== '') {
                    emptyRow.parentElement.style.display = '';
                    emptyRow.textContent = 'No matching fee structures found for "' + searchTerm + '"';
                } else if (visibleCount === 0) {
                    emptyRow.parentElement.style.display = '';
                    emptyRow.textContent = 'No fee structures found.';
                } else {
                    emptyRow.parentElement.style.display = 'none';
                }
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>
</body>
</html>