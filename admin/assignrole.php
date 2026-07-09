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
    $role = $_POST['role'] ?? '';
    $usertype = $_POST['usertype'] ?? '';
    $status = $_POST['status'] ?? '1';
    
    if (!empty($role) && !empty($usertype)) {
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
        $stat['error'] = "Please fill all required fields";
    }
}

// ============================================================================
// UPDATE ROLE
// ============================================================================
elseif (isset($_POST['update'])) {
    $role = $_POST['role'] ?? '';
    $usertype = $_POST['usertype'] ?? '';
    $status = $_POST['status'] ?? '1';
    
    if (!empty($role) && !empty($usertype) && !empty($id)) {
        $data = [
            'role' => $role,
            'usertype' => $usertype,
            'status' => $status,
        ];
        
        db_update("roles", $data, "id = ?", [$id]);
        $_SESSION['success'] = "Updated Successfully";
        redirect(ADMIN_URL . $FileName);
    } else {
        $stat['error'] = "Please fill all required fields";
    }
}

// ============================================================================
// DELETE ROLE
// ============================================================================
elseif (($action == 'delete') && !empty($id)) {
    db_delete("roles", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET DATA FOR EDIT/VIEW
// ============================================================================
$roleDetail = [];
if (($action == 'edit' || $action == 'view') && !empty($id)) {
    $roleDetail = db_get_row("SELECT * FROM roles WHERE id = ?", [$id]);
    if (empty($roleDetail)) {
        $stat['error'] = "Record not found";
        redirect(ADMIN_URL . $FileName);
    }
}

// Get all roles for listing
$allRoles = db_get_rows("SELECT * FROM roles ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?> 
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
                                            Add New Record
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== ADD FORM ==================== -->
                            <?php if ($action == 'add'): ?>
                                <div class="card-box">
                                    <form role="form" action="" method="post">
                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="role">Role</label>
                                            <div class="col-lg-10">
                                                <input type="text" class="form-control required" id="role" name="role" 
                                                       value="<?= e($_POST['role'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="usertype">Usertype</label>
                                            <div class="col-lg-10">
                                                <input type="number" class="form-control required" id="usertype" name="usertype" 
                                                       value="<?= e($_POST['usertype'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="status">Status</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="status">
                                                    <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                                    <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-default">Submit</button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </form>
                                </div>

                            <!-- ==================== EDIT FORM ==================== -->
                            <?php elseif ($action == 'edit' && !empty($roleDetail)): ?>
                                <div class="card-box">
                                    <form role="form" action="" method="post">
                                        <input type="hidden" name="id" value="<?= e($id) ?>">
                                        
                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="role">Role</label>
                                            <div class="col-lg-10">
                                                <input type="text" class="form-control required" id="role" name="role" 
                                                       value="<?= e($roleDetail['role']) ?>">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="usertype">Usertype</label>
                                            <div class="col-lg-10">
                                                <input type="number" class="form-control required" id="usertype" name="usertype" 
                                                       value="<?= e($roleDetail['usertype']) ?>">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="status">Status</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="status">
                                                    <option value="1" <?= selected($roleDetail['status'], '1') ?>>Active</option>
                                                    <option value="0" <?= selected($roleDetail['status'], '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>

                                        <button type="submit" name="update" class="btn btn-default">Update</button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </form>
                                </div>

                            <!-- ==================== VIEW DETAILS ==================== -->
                            <?php elseif ($action == 'view' && !empty($roleDetail)): ?>
                                <div class="card-box">
                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label">Role:</label>
                                        <div class="col-lg-10"><?= e($roleDetail['role']) ?></div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label">Usertype:</label>
                                        <div class="col-lg-10"><?= e($roleDetail['usertype']) ?></div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label">Status:</label>
                                        <div class="col-lg-10">
                                            <?= $roleDetail['status'] == '1' ? 'Active' : 'Inactive' ?>
                                        </div>
                                    </div>

                                    <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                </div>

                            <!-- ==================== LIST ALL ROLES ==================== -->
                            <?php else: ?>
                                <div class="card-box">
                                    <table id="datatable" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Role</th>
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
                                                        <td><?= e($role['usertype']) ?></td>
                                                        <td><?= $role['status'] == '1' ? 'Active' : 'Inactive' ?></td>
                                                        <td>
                                                            <a href="<?= e($FileName) ?>?action=view&id=<?= e($role['id']) ?>" class="table-action-btn">
                                                                <i class="fa fa-search"></i>
                                                            </a>
                                                            <a href="<?= e($FileName) ?>?action=edit&id=<?= e($role['id']) ?>" class="table-action-btn">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($role['id']) ?>')" class="table-action-btn">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No roles found. Please add a role.</td>
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