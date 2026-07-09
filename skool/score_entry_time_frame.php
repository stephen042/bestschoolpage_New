<?php
/**
 * Assessment & Grade Management - Redesigned (FIXED)
 * Removed: Score Entry Time Frame (percentages now in Manage Assessment)
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Assessment & Grade Management";
$FileName = 'score_entry_time_frame.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$validate = new Validation();
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// ADD ASSESSMENT (WITH PERCENTAGE)
// ============================================================================
if (isset($_POST['add_assessment'])) {
    $validate->addRule($_POST['class'] ?? '', 'Num', 'Class', true);
    $validate->addRule($_POST['assesment'] ?? '', '', 'Assessment', true);
    $validate->addRule($_POST['percentage'] ?? '', 'Num', 'Percentage', true);

    if ($validate->validate() && empty($stat['error'])) {
        $iLastId = (db_get_val("SELECT id FROM school_assessment ORDER BY id DESC") ?? 0) + 1;
        $iRandomId = randomFix(15) . '-' . $iLastId;

        $aryData = [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'class_id' => (int)$_POST['class'],
            'assesment' => trim($_POST['assesment']),
            'percentage' => (float)$_POST['percentage'],
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $iRandomId,
        ];
        
        $flgIn = db_insert("school_assessment", $aryData);
        
        $_SESSION['success'] = "Assessment added successfully.";
        redirect($FileName . '?action=manage_assessment');
    } else {
        $stat['error'] = $validate->errors();
    }
}

// ============================================================================
// EDIT ASSESSMENT
// ============================================================================
if (isset($_POST['edit_assesment']) && !empty($randomid)) {
    $aryData = [
        'assesment' => trim($_POST['assesment'] ?? ''),
        'percentage' => (float)($_POST['percentage'] ?? 0),
    ];
    db_update("school_assessment", $aryData, "randomid = ?", [$randomid]);
    
    $_SESSION['success'] = "Assessment updated successfully.";
    redirect($FileName . '?action=manage_assessment');
}

// ============================================================================
// DELETE ASSESSMENT
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_mas' && !empty($randomid)) {
    db_delete("school_assessment", "randomid = ?", [$randomid]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect($FileName . '?action=manage_assessment');
}

// ============================================================================
// ADD GRADE - FIXED
// ============================================================================
if (isset($_POST['add_grade'])) {
    $maximum_number = trim($_POST['maximum_number'] ?? '');
    $minimum_number = trim($_POST['minimum_number'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $order_type = trim($_POST['order_type'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    $errors = [];
    
    if ($maximum_number === '') {
        $errors[] = "Maximum Number is required";
    }
    if ($minimum_number === '') {
        $errors[] = "Minimum Number is required";
    }
    if (empty($grade)) {
        $errors[] = "Grade is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }

    if ($maximum_number !== '' && !is_numeric($maximum_number)) {
        $errors[] = "Maximum Number must be numeric";
    }
    if ($minimum_number !== '' && !is_numeric($minimum_number)) {
        $errors[] = "Minimum Number must be numeric";
    }

    if (empty($errors)) {
        $maxVal = (float)$maximum_number;
        $minVal = (float)$minimum_number;
        if ($minVal > $maxVal) {
            $errors[] = "Minimum Number cannot be greater than Maximum Number";
        }
    }
    
    if (empty($errors)) {
        if ($maxVal > 100) {
            $errors[] = "Maximum Number cannot be greater than 100";
        }
        if ($minVal < 0) {
            $errors[] = "Minimum Number cannot be less than 0";
        }
    }

    // Allow flexible combinations; only prevent exact duplicates.
    if (empty($errors)) {
        $existing = db_get_val(
            "SELECT id FROM school_grade
             WHERE minimum_number = ?
             AND maximum_number = ?
             AND grade = ?
             AND create_by_userid = ?",
            [$minVal, $maxVal, $grade, $create_by_userid]
        );

        if ($existing) {
            $errors[] = "This exact grade range already exists.";
        }
    }
    
    if (empty($errors)) {
        $aryData = [
            'maximum_number' => $maxVal,
            'minimum_number' => $minVal,
            'grade' => $grade,
            'description' => $description,
            'comment' => $comment,
            'order_type' => $order_type,
            'userid' => $_SESSION['userid'] ?? 0,
            'usertype' => $_SESSION['usertype'] ?? '',
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => randomFix(15) . '-' . time(),
        ];
        
        $flgIn2 = db_insert("school_grade", $aryData);
        
        $_SESSION['success'] = "Grade added successfully.";
        redirect($FileName . '?action=grade');
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// EDIT GRADE
// ============================================================================
if (isset($_POST['edit_grade']) && !empty($randomid)) {
    $aryData = [
        'maximum_number' => (float)($_POST['maximum_number'] ?? 0),
        'minimum_number' => (float)($_POST['minimum_number'] ?? 0),
        'grade' => trim($_POST['grade'] ?? ''),
        'comment' => trim($_POST['comment'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'order_type' => trim($_POST['order_type'] ?? ''),
    ];
    db_update("school_grade", $aryData, "randomid = ?", [$randomid]);
    
    $_SESSION['success'] = "Grade updated successfully.";
    redirect($FileName . '?action=grade');
}

// ============================================================================
// DELETE GRADE
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_grade' && !empty($randomid)) {
    db_delete("school_grade", "randomid = ?", [$randomid]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect($FileName . '?action=grade');
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$assessments = db_get_rows("SELECT * FROM school_assessment WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$grades = db_get_rows("SELECT * FROM school_grade WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$classes = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);

// Helper function to get class name
function getClassName($id) {
    return db_get_val("SELECT name FROM school_class WHERE id = ?", [$id]) ?: 'N/A';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        .assessment-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0 0 5px; font-size: 24px; }
        .page-header p { color: #666; margin: 0; }
        .nav-tabs-custom { display: flex; gap: 5px; border-bottom: 2px solid #e0e0e0; margin-bottom: 25px; flex-wrap: wrap; }
        .nav-tab { padding: 12px 25px; background: #f5f5f5; border-radius: 30px 30px 0 0; text-decoration: none; color: #333; font-weight: 500; transition: all 0.2s; }
        .nav-tab:hover { background: #e0e0e0; }
        .nav-tab.active { background: #1B3058; color: white; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
        .card-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 18px 25px; }
        .card-header h3 { margin: 0; font-size: 18px; }
        .card-body { padding: 25px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #333; font-size: 13px; }
        .form-control, .form-select { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        .btn { padding: 10px 24px; border: none; border-radius: 30px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; }
        .data-table tr:hover { background: #fafafa; }
        .action-icons a { margin: 0 5px; color: #666; text-decoration: none; }
        .action-icons a:hover { color: #f21151; }
        .percentage-badge { background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .inline-edit { display: inline-flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .inline-input { width: 120px; padding: 6px 10px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .data-table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="assessment-container">
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-gears"></i> <?= $PageTitle ?></h2>
                    <p>Manage assessments (with percentages) and grade scales</p>
                </div>

                <?= showMessage($stat) ?>

                <!-- Tabs Navigation -->
                <div class="nav-tabs-custom">
                    <a href="?action=manage_assessment" class="nav-tab <?= ($action == '' || $action == 'manage_assessment') ? 'active' : '' ?>">
                        <i class="fa fa-plus-circle"></i> Manage Assessment
                    </a>
                    <a href="?action=grade" class="nav-tab <?= ($action == 'grade') ? 'active' : '' ?>">
                        <i class="fa fa-star"></i> Manage Grade
                    </a>
                </div>

                <!-- ============================================================ -->
                <!-- MANAGE ASSESSMENT TAB -->
                <!-- ============================================================ -->
                <?php if ($action == '' || $action == 'manage_assessment'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-puzzle-piece"></i> Add New Assessment</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label><i class="fa fa-graduation-cap"></i> Select Class *</label>
                                        <select name="class" class="form-select" required>
                                            <option value="">-- Select Class --</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fa fa-tag"></i> Assessment Name *</label>
                                        <input type="text" name="assesment" class="form-control" placeholder="e.g., CA1, CA2, Exam, Project" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fa fa-percent"></i> Percentage (%) *</label>
                                        <input type="number" name="percentage" class="form-control" placeholder="e.g., 20, 30, 60" step="any" required>
                                    </div>
                                </div>
                                <button type="submit" name="add_assessment" class="btn btn-primary"><i class="fa fa-save"></i> Save Assessment</button>
                            </form>
                        </div>
                    </div>

                    <!-- Assessments List -->
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                            <h3><i class="fa fa-list"></i> Assessments List</h3>
                        </div>
                        <div class="card-body">
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Class</th>
                                            <th>Assessment Name</th>
                                            <th>Percentage</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($assessments)): ?>
                                            <?php $i = 1; foreach ($assessments as $item): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars(getClassName($item['class_id'])) ?></td>
                                                    <td>
                                                        <?php if ($randomid == $item['randomid']): ?>
                                                            <form method="POST" class="inline-edit">
                                                                <input type="hidden" name="randomid" value="<?= $item['randomid'] ?>">
                                                                <input type="text" name="assesment" value="<?= htmlspecialchars($item['assesment']) ?>" class="inline-input" required>
                                                                <input type="number" name="percentage" value="<?= $item['percentage'] ?>" class="inline-input" step="any" required>
                                                                <button type="submit" name="edit_assesment" class="btn-sm" style="background:#28a745; color:white; border:none; padding:5px 12px; border-radius:20px;"><i class="fa fa-check"></i> Save</button>
                                                                <a href="?action=manage_assessment" class="btn-sm" style="background:#6c757d; color:white; padding:5px 12px; border-radius:20px; text-decoration:none;">Cancel</a>
                                                            </form>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($item['assesment']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="percentage-badge"><?= $item['percentage'] ?? '0' ?>%</span>
                                                    </td>
                                                    <td class="action-icons">
                                                        <?php if ($randomid != $item['randomid']): ?>
                                                            <a href="?action=manage_assessment&randomid=<?= $item['randomid'] ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        <?php endif; ?>
                                                        <a href="javascript:del('?action=delete_mas&randomid=<?= $item['randomid'] ?>')" title="Delete" onclick="return confirm('Delete this assessment?')"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" style="text-align:center;">No assessments found. Add your first assessment above.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- MANAGE GRADE TAB - FIXED -->
                <!-- ============================================================ -->
                <?php if ($action == 'grade'): ?>
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);">
                            <h3><i class="fa fa-plus-circle"></i> Add Grade Scale</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Maximum Number *</label>
                                        <input type="number" name="maximum_number" class="form-control" placeholder="e.g., 100" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Minimum Number *</label>
                                        <input type="number" name="minimum_number" class="form-control" placeholder="e.g., 70" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Grade *</label>
                                        <input type="text" name="grade" class="form-control" placeholder="e.g., A, B, C" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Description *</label>
                                        <input type="text" name="description" class="form-control" placeholder="e.g., Excellent" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Comment</label>
                                        <input type="text" name="comment" class="form-control" placeholder="e.g., Outstanding performance">
                                    </div>
                                    <div class="form-group">
                                        <label>Order Type</label>
                                        <select name="order_type" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Fail">Fail</option>
                                            <option value="Poor">Poor</option>
                                            <option value="Pass">Pass</option>
                                            <option value="Credit">Credit</option>
                                            <option value="Distinction">Distinction</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" name="add_grade" class="btn btn-primary"><i class="fa fa-save"></i> Save Grade</button>
                            </form>
                        </div>
                    </div>

                    <!-- Grades List -->
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                            <h3><i class="fa fa-list"></i> Grade Scales</h3>
                        </div>
                        <div class="card-body">
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>#</th><th>Grade</th><th>Min</th><th>Max</th><th>Description</th><th>Comment</th><th>Order Type</th><th>Action</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($grades)): 
                                            $i=1; foreach($grades as $g): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td>
                                                    <?php if ($randomid == $g['randomid']): ?>
                                                        <form method="POST" class="inline-edit">
                                                            <input type="hidden" name="randomid" value="<?= $g['randomid'] ?>">
                                                            <input type="text" name="grade" value="<?= htmlspecialchars($g['grade']) ?>" class="inline-input">
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($g['grade']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($randomid == $g['randomid']): ?>
                                                            <input type="number" name="minimum_number" value="<?= $g['minimum_number'] ?>" class="inline-input">
                                                        <?php else: ?>
                                                            <?= $g['minimum_number'] ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($randomid == $g['randomid']): ?>
                                                            <input type="number" name="maximum_number" value="<?= $g['maximum_number'] ?>" class="inline-input">
                                                        <?php else: ?>
                                                            <?= $g['maximum_number'] ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($randomid == $g['randomid']): ?>
                                                            <input type="text" name="description" value="<?= htmlspecialchars($g['description']) ?>" class="inline-input">
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($g['description']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($randomid == $g['randomid']): ?>
                                                            <input type="text" name="comment" value="<?= htmlspecialchars($g['comment']) ?>" class="inline-input">
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($g['comment']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($randomid == $g['randomid']): ?>
                                                            <select name="order_type" class="inline-input">
                                                                <option value="Fail" <?= ($g['order_type']=='Fail')?'selected':'' ?>>Fail</option>
                                                                <option value="Poor" <?= ($g['order_type']=='Poor')?'selected':'' ?>>Poor</option>
                                                                <option value="Pass" <?= ($g['order_type']=='Pass')?'selected':'' ?>>Pass</option>
                                                                <option value="Credit" <?= ($g['order_type']=='Credit')?'selected':'' ?>>Credit</option>
                                                                <option value="Distinction" <?= ($g['order_type']=='Distinction')?'selected':'' ?>>Distinction</option>
                                                            </select>
                                                        <?php else: ?>
                                                            <?= $g['order_type'] ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-icons">
                                                        <?php if ($randomid == $g['randomid']): ?>
                                                            <button type="submit" name="edit_grade" class="btn-sm" style="background:#28a745; color:white; border:none; padding:5px 12px; border-radius:20px;"><i class="fa fa-check"></i> Save</button>
                                                            <a href="?action=grade" class="btn-sm" style="background:#6c757d; color:white; padding:5px 12px; border-radius:20px; text-decoration:none;">Cancel</a>
                                                            <?php echo '</form>'; ?>
                                                        <?php else: ?>
                                                            <a href="?action=grade&randomid=<?= $g['randomid'] ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                                                            <a href="javascript:del('?action=delete_grade&randomid=<?= $g['randomid'] ?>')" title="Delete"><i class="fa fa-trash"></i></a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; 
                                        else: ?>
                                            <tr><td colspan="8" style="text-align:center;">No grades found. Add your first grade above.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>
<script>
function del(url) {
    if (confirm('Are you sure you want to delete this item?')) {
        window.location.href = url;
    }
}
</script>
</body>
</html>