<?php
/**
 * Next Term Begins - Modern PHP 8.x
 * Set the date when the next term starts using a calendar picker
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Next Term Begins";
$FileName = 'principal_set_termdate.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// ADD NEXT TERM DATE
// ============================================================================
if (isset($_POST['add_nextTerm'])) {
    $sessionId = $_POST['session'] ?? 0;
    $termId = $_POST['term_id'] ?? 0;
    $nextTermDate = $_POST['nex_term'] ?? '';
    
    if (empty($sessionId) || empty($termId) || empty($nextTermDate)) {
        $stat['error'] = "Please fill all required fields";
    } else {
        // Check if record already exists
        $existing = db_get_val(
            "SELECT id FROM principal_set_nextTerm WHERE session_id = ? AND term_id = ? AND create_by_userid = ?",
            [$sessionId, $termId, $create_by_userid]
        );
        
        if ($existing) {
            $stat['error'] = "Record already exists for this session and term";
        } else {
            $lastId = db_get_val("SELECT id FROM principal_set_nextTerm ORDER BY id DESC") ?? 0;
            $newId = $lastId + 1;
            $randomId = randomFix(15) . '-' . $newId;
            
            db_insert("principal_set_nextTerm", [
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'session_id' => $sessionId,
                'term_id' => $termId,
                'nextTerm' => $nextTermDate,
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype,
                'randomid' => $randomId,
            ]);
            
            $_SESSION['success'] = "Next term date saved successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// EDIT NEXT TERM DATE
// ============================================================================
if (isset($_POST['edit_next_term']) && !empty($randomid)) {
    $nextTermDate = $_POST['nex_term'] ?? '';
    
    if (!empty($nextTermDate)) {
        db_update("principal_set_nextTerm", ['nextTerm' => $nextTermDate], "randomid = ?", [$randomid]);
        $_SESSION['success'] = "Next term date updated successfully";
    }
    redirect($FileName);
}

// ============================================================================
// DELETE NEXT TERM DATE
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_mas' && !empty($randomid)) {
    db_delete("principal_set_nextTerm", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Record deleted successfully";
    redirect($FileName);
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$records = db_get_rows("SELECT * FROM principal_set_nextTerm WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        * { box-sizing: border-box; }
        .term-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .term-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 30px; }
        .term-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 25px 30px; }
        .term-header h2 { margin: 0; font-size: 24px; font-weight: 600; }
        .term-header p { margin: 8px 0 0; opacity: 0.8; }
        .term-body { padding: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, .form-select { width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 12px; font-size: 14px; transition: all 0.2s; background: #fff; }
        .form-control:focus, .form-select:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        .date-input { background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="%23666" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>') no-repeat right 15px center; background-size: 18px; cursor: pointer; }
        .btn { padding: 12px 28px; border: none; border-radius: 40px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(242,17,81,0.3); }
        .btn-save { background: #28a745; color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; }
        .btn-save:hover { background: #218838; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; }
        .data-table tr:hover { background: #fafafa; }
        .action-icons a { margin: 0 5px; color: #666; text-decoration: none; font-size: 16px; }
        .action-icons a:hover { color: #f21151; }
        .badge-date { background: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; display: inline-block; }
        .alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .empty-state { text-align: center; padding: 60px; color: #999; }
        .calendar-icon { margin-right: 10px; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .data-table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="term-container">
                <!-- Header -->
                <div style="margin-bottom: 20px;">
                    <h2 style="margin:0; color:#1B3058;">📅 <?= $PageTitle ?></h2>
                    <p style="color:#666; margin-top:5px;">Set the calendar date when the next academic term begins</p>
                </div>

                <?= showMessage($stat) ?>

                <!-- Add New Term Card -->
                <div class="term-card">
                    <div class="term-header">
                        <h2><i class="fa fa-calendar-plus-o"></i> Set New Term Date</h2>
                        <p>Select the session, ending term, and the date when the next term will begin</p>
                    </div>
                    <div class="term-body">
                        <form method="post">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Academic Session</label>
                                    <select name="session" class="form-select" required>
                                        <option value="">Select Session</option>
                                        <?php foreach ($sessions as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['session']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-flag-checkered"></i> Ending Term</label>
                                    <select name="term_id" class="form-select" required>
                                        <option value="">Select Term</option>
                                        <?php foreach ($terms as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['term']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-calendar-check-o"></i> Next Term Begins</label>
                                    <input type="text" name="nex_term" id="datepicker" class="form-control date-input" placeholder="Select a date" autocomplete="off" required>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <button type="submit" name="add_nextTerm" class="btn btn-primary"><i class="fa fa-save"></i> Save Date</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Records List Card -->
                <div class="term-card">
                    <div class="term-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                        <h2><i class="fa fa-list"></i> Saved Term Dates</h2>
                        <p>List of all configured next term start dates</p>
                    </div>
                    <div class="term-body">
                        <?php if (!empty($records)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Session</th>
                                        <th>Ending Term</th>
                                        <th>Next Term Begins</th>
                                        <th style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars(db_get_val("SELECT session FROM school_session WHERE id = ?", [$record['session_id']]) ?: 'N/A') ?></td>
                                            <td><?= htmlspecialchars(db_get_val("SELECT term FROM school_term WHERE id = ?", [$record['term_id']]) ?: 'N/A') ?></td>
                                            <td><span class="badge-date"><i class="fa fa-calendar"></i> <?= date('F j, Y', strtotime($record['nextTerm'])) ?></span></td>
                                            <td>
                                                <?php if ($randomid == $record['randomid']): ?>
                                                    <form method="post" style="display: inline-block;">
                                                        <input type="hidden" name="randomid" value="<?= $record['randomid'] ?>">
                                                        <input type="text" name="nex_term" value="<?= $record['nextTerm'] ?>" class="form-control" style="width: 130px; display: inline-block; padding: 6px;">
                                                        <button type="submit" name="edit_next_term" class="btn-save"><i class="fa fa-check"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <div class="action-icons">
                                                        <a href="?randomid=<?= $record['randomid'] ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        <a href="javascript:del('?action=delete_mas&randomid=<?= $record['randomid'] ?>')" title="Delete"><i class="fa fa-trash"></i></a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa fa-calendar" style="font-size: 48px; color: #ccc; margin-bottom: 15px; display: block;"></i>
                                <h3>No Records Found</h3>
                                <p>Use the form above to set the next term start date.</p>
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
    // Initialize flatpickr calendar
    flatpickr("#datepicker", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        allowInput: true,
        disableMobile: true,
        placeholder: "Select a date"
    });
    
    // If editing mode, also initialize datepicker for edit form
    <?php if ($randomid): ?>
    flatpickr(".edit-datepicker", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        allowInput: true
    });
    <?php endif; ?>
    
    function del(url) {
        if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
            window.location.href = url;
        }
    }
</script>
</body>
</html>