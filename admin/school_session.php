<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "Session";
$FileName = 'school_session.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// ADD SESSION
// ============================================================================
if (isset($_POST['submit'])) {
    $session = trim($_POST['session'] ?? '');
    
    if (empty($session)) {
        $stat['error'] = "Session name is required";
    } else {
        // Check for duplicate session
        $existing = db_get_val("SELECT id FROM school_session WHERE session = ?", [$session]);
        
        if (!empty($existing)) {
            $stat['error'] = "Session already exists";
        } else {
            $data = [
                'session' => $session,
                'sid' => 0,
                'userid' => 0,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("school_session", $data);
            $_SESSION['success'] = "Submitted Successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// UPDATE SESSION
// ============================================================================
elseif (isset($_POST['update'])) {
    $editSession = trim($_POST['edit_session'] ?? '');
    $sessionId = $_POST['id'] ?? 0;
    
    if (empty($editSession)) {
        $stat['error'] = "Session name is required";
    } elseif (empty($sessionId)) {
        $stat['error'] = "Invalid session ID";
    } else {
        // Check for duplicate (excluding current)
        $existing = db_get_val("SELECT id FROM school_session WHERE session = ? AND id != ?", [$editSession, $sessionId]);
        
        if (!empty($existing)) {
            $stat['error'] = "Session already exists";
        } else {
            $data = ['session' => $editSession];
            db_update("school_session", $data, "id = ?", [$sessionId]);
            $_SESSION['success'] = "Updated Successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// DELETE SESSION
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Check if session is being used in other tables
    $inUse = db_get_val("SELECT id FROM school_class WHERE session_id = ? LIMIT 1", [$id]);
    if (empty($inUse)) {
        $inUse = db_get_val("SELECT id FROM school_subject WHERE session_id = ? LIMIT 1", [$id]);
    }
    if (empty($inUse)) {
        $inUse = db_get_val("SELECT id FROM score_entry_time_frame WHERE session = ? LIMIT 1", [$id]);
    }
    
    if (!empty($inUse)) {
        $_SESSION['error'] = "Cannot delete this session as it is being used by classes or subjects";
    } else {
        db_delete("school_session", "id = ?", [$id]);
        $_SESSION['success'] = 'Deleted Successfully';
    }
    redirect($FileName);
}

// ============================================================================
// GET ALL SESSIONS FOR LISTING
// ============================================================================
$allSessions = db_get_rows("SELECT * FROM school_session ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .inline-edit {
            border: none;
            padding: 5px;
            width: 100%;
            background: transparent;
        }
        .inline-edit:focus {
            outline: none;
            background: #f9f9f9;
        }
        .update-btn {
            background: none;
            border: none;
            color: #1B3058;
            cursor: pointer;
        }
        .update-btn:hover {
            color: #f21151;
        }
        .action-buttons {
            white-space: nowrap;
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
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="page-title"><?= e($PageTitle) ?></h4>
                        <ol class="breadcrumb">
                            <li><a href="<?= ADMIN_URL ?>">Home</a></li>
                            <li class="active"><?= e($PageTitle) ?></li>
                        </ol>
                        <?= showMessage($stat) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <!-- ==================== ADD SESSION FORM ==================== -->
                        <div class="card-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title text-center">
                                        <i class="fa fa-calendar-plus-o"></i> Add New Session
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <form role="form" action="" method="post">
                                        <div class="form-group">
                                            <label for="session">Session Name</label>
                                            <input type="text" class="form-control" id="session" name="session" 
                                                   placeholder="e.g., 2024-25" value="<?= e($_POST['session'] ?? '') ?>" 
                                                   autocomplete="off">
                                            <small class="text-muted">Format: YYYY-YY (e.g., 2024-25)</small>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-save"></i> Save Session
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== SESSIONS LIST WITH INLINE EDIT ==================== -->
                        <div class="card-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">
                                        <i class="fa fa-list"></i> Session List
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Session Name</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($allSessions)): ?>
                                                    <?php $i = 0; foreach ($allSessions as $session): $i++; ?>
                                                        <tr>
                                                            <form action="" method="post">
                                                                <td><?= $i ?></td>
                                                                <td>
                                                                    <input type="text" name="edit_session" 
                                                                           class="inline-edit" 
                                                                           value="<?= e($session['session']) ?>">
                                                                    <input type="hidden" name="id" value="<?= e($session['id']) ?>">
                                                                </td>
                                                                <td class="action-buttons">
                                                                    <button type="submit" name="update" class="update-btn" title="Update">
                                                                        <i class="fa fa-save"></i>
                                                                    </button>
                                                                    <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($session['id']) ?>')" 
                                                                       class="table-action-btn" title="Delete">
                                                                        <i class="fa fa-times"></i>
                                                                    </a>
                                                                </td>
                                                            </form>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No sessions found. Please add a session.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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