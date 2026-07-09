<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "Roles";
$FileName = 'roles.php';

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
// ADD ROLE
// ============================================================================
if (isset($_POST['submit'])) {
    $role = trim($_POST['role'] ?? '');
    $usertype = trim($_POST['usertype'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    $errors = [];
    if (empty($role)) $errors[] = "Role name is required";
    if (empty($usertype)) $errors[] = "Usertype is required";
    
    if (empty($errors)) {
        // Check for duplicate role
        $existing = db_get_val("SELECT id FROM roles WHERE role = ?", [$role]);
        
        if (empty($existing)) {
            $data = [
                'role' => $role,
                'usertype' => $usertype,
                'status' => $status,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("roles", $data);
            $_SESSION['success'] = "Submitted Successfully";
            redirect(ADMIN_URL . $FileName);
        } else {
            $stat['error'] = "Role already exists";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// UPDATE ROLE
// ============================================================================
elseif (isset($_POST['update']) && !empty($id)) {
    $role = trim($_POST['role'] ?? '');
    $usertype = trim($_POST['usertype'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    $errors = [];
    if (empty($role)) $errors[] = "Role name is required";
    if (empty($usertype)) $errors[] = "Usertype is required";
    
    if (empty($errors)) {
        // Check for duplicate role (excluding current)
        $existing = db_get_val("SELECT id FROM roles WHERE role = ? AND id != ?", [$role, $id]);
        
        if (empty($existing)) {
            $data = [
                'role' => $role,
                'usertype' => $usertype,
                'status' => $status,
            ];
            
            db_update("roles", $data, "id = ?", [$id]);
            $_SESSION['success'] = "Updated Successfully";
            redirect(ADMIN_URL . $FileName);
        } else {
            $stat['error'] = "Role already exists";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// DELETE ROLE
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Check if role is being used by any admin user
    $inUse = db_get_val("SELECT id FROM admin_login WHERE usertype = (SELECT usertype FROM roles WHERE id = ?) LIMIT 1", [$id]);
    
    if (!empty($inUse)) {
        $stat['error'] = "Cannot delete this role as it is currently assigned to users";
    } else {
        db_delete("roles", "id = ?", [$id]);
        $_SESSION['success'] = 'Deleted Successfully';
    }
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET DATA FOR EDIT/VIEW
// ============================================================================
$roleData = null;
if (($action == 'edit' || $action == 'view') && !empty($id)) {
    $roleData = db_get_row("SELECT * FROM roles WHERE id = ?", [$id]);
    if (empty($roleData)) {
        $_SESSION['error'] = "Role not found";
        redirect(ADMIN_URL . $FileName);
    }
}

// ============================================================================
// GET ALL ROLES FOR LISTING
// ============================================================================
$allRoles = db_get_rows("SELECT * FROM roles ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .usertype-badge {
            display: inline-block;
            background: #e8e8e8;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-family: monospace;
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
                    <div class="col-md-12">
                        <div class="card-box aplhanewclass">
                            <div class="row">
                                <div class="col-md-9"></div>
                                <div class="col-md-3">
                                    <a href="<?= e($FileName) ?>?action=add" class="btn btn-default" style="float:right">
                                        <i class="fa fa-plus"></i> Add New Record
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ADD FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Role Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="role" 
                                                       value="<?= e($_POST['role'] ?? '') ?>" 
                                                       placeholder="Enter role name (e.g., Administrator, Teacher)">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Usertype <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control required" name="usertype" 
                                                       value="<?= e($_POST['usertype'] ?? '') ?>" 
                                                       placeholder="Enter usertype number">
                                                <small class="text-muted">Usertype numbers: 1=Staff, 2=Parent, etc.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="required form-control" name="status">
                                            <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                            <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Submit
                                        </button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== EDIT FORM ==================== -->
                        <?php elseif ($action == 'edit' && $roleData): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Role Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="role" 
                                                       value="<?= e($roleData['role']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Usertype <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control required" name="usertype" 
                                                       value="<?= e($roleData['usertype']) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="required form-control" name="status">
                                            <option value="1" <?= selected($roleData['status'], '1') ?>>Active</option>
                                            <option value="0" <?= selected($roleData['status'], '0') ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" name="update" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update
                                        </button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== VIEW DETAILS ==================== -->
                        <?php elseif ($action == 'view' && $roleData): ?>
                            <div class="card-box">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Role Name</th>
                                        <td><?= e($roleData['role']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Usertype</th>
                                        <td><span class="usertype-badge"><?= e($roleData['usertype']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <?php if ($roleData['status'] == '1'): ?>
                                                <span class="label label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created By</th>
                                        <td><?= e($roleData['create_by_userid'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                                <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                            </div>

                        <!-- ==================== LIST ALL ROLES ==================== -->
                        <?php else: ?>
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Role Name</th>
                                            <th>Usertype</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($allRoles)): ?>
                                            <?php $i = 0; foreach ($allRoles as $role): $i++; ?>
                                                <tr>
                                                    <td><?= $i ?></td>
                                                    <td><?= e($role['role']) ?></td>
                                                    <td><span class="usertype-badge"><?= e($role['usertype']) ?></span></td>
                                                    <td>
                                                        <?php if ($role['status'] == '1'): ?>
                                                            <span class="label label-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="label label-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= e($FileName) ?>?action=view&id=<?= e($role['id']) ?>" class="table-action-btn" title="View">
                                                            <i class="fa fa-search"></i>
                                                        </a>
                                                        <a href="<?= e($FileName) ?>?action=edit&id=<?= e($role['id']) ?>" class="table-action-btn" title="Edit">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                        <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($role['id']) ?>')" class="table-action-btn" title="Delete">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No roles found. Please add a role.<?= e($create_by_userid ?? '') ?></td>
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