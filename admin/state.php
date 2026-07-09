<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "State";
$FileName = 'state.php';

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
// ADD STATE
// ============================================================================
if (isset($_POST['addnewrecord'])) {
    $title = trim($_POST['title'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    if (empty($title)) {
        $stat['error'] = "State title is required";
    } else {
        // Check for duplicate state
        $existing = db_get_val("SELECT id FROM state WHERE title = ?", [$title]);
        
        if (!empty($existing)) {
            $stat['error'] = "State already exists";
        } else {
            $data = [
                'title' => $title,
                'status' => $status,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("state", $data);
            $_SESSION['success'] = "Submitted Successfully";
            redirect(ADMIN_URL . $FileName);
        }
    }
}

// ============================================================================
// UPDATE STATE
// ============================================================================
elseif (isset($_POST['udpaterecord']) && !empty($id)) {
    $title = trim($_POST['title'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    if (empty($title)) {
        $stat['error'] = "State title is required";
    } else {
        // Check for duplicate (excluding current)
        $existing = db_get_val("SELECT id FROM state WHERE title = ? AND id != ?", [$title, $id]);
        
        if (!empty($existing)) {
            $stat['error'] = "State already exists";
        } else {
            $data = [
                'title' => $title,
                'status' => $status,
            ];
            
            db_update("state", $data, "id = ?", [$id]);
            $_SESSION['success'] = "Updated Successfully";
            redirect(ADMIN_URL . $FileName);
        }
    }
}

// ============================================================================
// DELETE STATE
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Check if state is being used in other tables
    $inUse = db_get_val("SELECT id FROM school_register WHERE state = ? LIMIT 1", [$id]);
    
    if (!empty($inUse)) {
        $_SESSION['error'] = "Cannot delete this state as it is being used by registered schools";
    } else {
        db_delete("state", "id = ?", [$id]);
        $_SESSION['success'] = 'Deleted Successfully';
    }
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET DATA FOR EDIT
// ============================================================================
$editRecord = null;
if ($action == 'edit' && !empty($id)) {
    $editRecord = db_get_row("SELECT * FROM state WHERE id = ?", [$id]);
    if (empty($editRecord)) {
        $_SESSION['error'] = "State not found";
        redirect(ADMIN_URL . $FileName);
    }
}

// ============================================================================
// GET ALL STATES FOR LISTING
// ============================================================================
$allStates = db_get_rows("SELECT * FROM state ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: normal;
        }
        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-inactive {
            background: #ffebee;
            color: #c62828;
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
                        <!-- ==================== ADD STATE FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title text-center">
                                            <i class="fa fa-plus-circle"></i> Add New State
                                        </h3>
                                    </div>
                                    <div class="panel-body">
                                        <form role="form" action="" method="post">
                                            <div class="form-group">
                                                <label for="title">State Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="title" id="title" 
                                                       value="<?= e($_POST['title'] ?? '') ?>" 
                                                       placeholder="Enter state name (e.g., Maharashtra, Uttar Pradesh)">
                                            </div>

                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select name="status" class="form-control" id="status">
                                                    <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                                    <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                                </select>
                                            </div>

                                            <div class="form-group text-center">
                                                <button type="submit" name="addnewrecord" class="btn btn-primary">
                                                    <i class="fa fa-save"></i> Save State
                                                </button>
                                                <a href="<?= ADMIN_URL . $FileName ?>" class="btn btn-default">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <!-- ==================== EDIT STATE FORM ==================== -->
                        <?php elseif ($action == 'edit' && $editRecord): ?>
                            <div class="card-box">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title text-center">
                                            <i class="fa fa-edit"></i> Edit State
                                        </h3>
                                    </div>
                                    <div class="panel-body">
                                        <form role="form" action="" method="post">
                                            <div class="form-group">
                                                <label for="title">State Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="title" id="title" 
                                                       value="<?= e($editRecord['title']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select name="status" class="form-control" id="status">
                                                    <option value="1" <?= selected($editRecord['status'], '1') ?>>Active</option>
                                                    <option value="0" <?= selected($editRecord['status'], '0') ?>>Inactive</option>
                                                </select>
                                            </div>

                                            <div class="form-group text-center">
                                                <button type="submit" name="udpaterecord" class="btn btn-primary">
                                                    <i class="fa fa-save"></i> Update State
                                                </button>
                                                <a href="<?= ADMIN_URL . $FileName ?>" class="btn btn-default">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <!-- ==================== LIST ALL STATES ==================== -->
                        <?php else: ?>
                            <div class="card-box">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">
                                            <i class="fa fa-list"></i> State List
                                            <a href="<?= ADMIN_URL . $FileName ?>?action=add" class="btn btn-sm btn-primary pull-right">
                                                <i class="fa fa-plus"></i> Add New
                                            </a>
                                        </h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table id="datatable" class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>State Name</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($allStates)): ?>
                                                        <?php $i = 0; foreach ($allStates as $state): $i++; ?>
                                                            <tr>
                                                                <td><?= $i ?></td>
                                                                <td><?= e($state['title']) ?></td>
                                                                <td>
                                                                    <?php if ($state['status'] == '1'): ?>
                                                                        <span class="status-badge status-active">Active</span>
                                                                    <?php else: ?>
                                                                        <span class="status-badge status-inactive">Inactive</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <a href="<?= ADMIN_URL . $FileName ?>?action=edit&id=<?= e($state['id']) ?>" 
                                                                       class="table-action-btn" title="Edit">
                                                                        <i class="fa fa-pencil"></i>
                                                                    </a>
                                                                    <a href="javascript:del('<?= ADMIN_URL . $FileName ?>?action=delete&id=<?= e($state['id']) ?>')" 
                                                                       class="table-action-btn" title="Delete">
                                                                        <i class="fa fa-times"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center">No states found. Please add a state.<?= e($create_by_userid ?? '') ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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