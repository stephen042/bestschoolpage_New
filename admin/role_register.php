<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "Role Register";
$FileName = 'role_register.php';

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
// GET ROLES FOR DROPDOWN
// ============================================================================
$roles = db_get_rows("SELECT * FROM roles ORDER BY id DESC");

// ============================================================================
// ADD ADMIN USER
// ============================================================================
if (isset($_POST['submit'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['emailid'] ?? '');
    $password = $_POST['password'] ?? '';
    $usertype = $_POST['usertype'] ?? '';
    $status = $_POST['status'] ?? '1';
    
    // Validation
    $errors = [];
    if (empty($fullname)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if (empty($usertype)) $errors[] = "Usertype is required";
    
    if (empty($errors)) {
        // Check if email already exists
        $existing = db_get_val("SELECT id FROM admin_login WHERE emailid = ?", [$email]);
        
        if (empty($existing)) {
            $data = [
                'fullname' => $fullname,
                'emailid' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'usertype' => $usertype,
                'status' => $status,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("admin_login", $data);
            $_SESSION['success'] = "Submitted Successfully";
            redirect(ADMIN_URL . $FileName);
        } else {
            $stat['error'] = "Email already exists";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// UPDATE ADMIN USER
// ============================================================================
elseif (isset($_POST['update']) && !empty($id)) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['emailid'] ?? '');
    $password = $_POST['password'] ?? '';
    $usertype = $_POST['usertype'] ?? '';
    $status = $_POST['status'] ?? '1';
    
    $errors = [];
    if (empty($fullname)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($usertype)) $errors[] = "Usertype is required";
    
    if (empty($errors)) {
        // Check if email exists for another user
        $existing = db_get_val("SELECT id FROM admin_login WHERE emailid = ? AND id != ?", [$email, $id]);
        
        if (empty($existing)) {
            $data = [
                'fullname' => $fullname,
                'emailid' => $email,
                'usertype' => $usertype,
                'status' => $status,
            ];
            
            // Only update password if provided
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            db_update("admin_login", $data, "id = ?", [$id]);
            $_SESSION['success'] = "Updated Successfully";
            redirect(ADMIN_URL . $FileName);
        } else {
            $stat['error'] = "Email already exists for another user";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// DELETE ADMIN USER
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Prevent deleting yourself
    if ($id == ($_SESSION['userid'] ?? 0)) {
        $stat['error'] = "You cannot delete your own account";
    } else {
        db_delete("admin_login", "id = ?", [$id]);
        $_SESSION['success'] = 'Deleted Successfully';
    }
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET DATA FOR EDIT/VIEW
// ============================================================================
$userData = null;
if (($action == 'edit' || $action == 'view') && !empty($id)) {
    $userData = db_get_row("SELECT * FROM admin_login WHERE id = ?", [$id]);
    if (empty($userData)) {
        $_SESSION['error'] = "User not found";
        redirect(ADMIN_URL . $FileName);
    }
}

// ============================================================================
// GET ALL USERS FOR LISTING
// ============================================================================
$allUsers = db_get_rows("SELECT * FROM admin_login ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .password-field {
            font-family: monospace;
        }
        .table img {
            max-width: 50px;
            border-radius: 4px;
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
                                                <label>Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="fullname" 
                                                       value="<?= e($_POST['fullname'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Email ID <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control required" name="emailid" 
                                                       value="<?= e($_POST['emailid'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control required" name="password" 
                                                       value="" required>
                                                <small class="text-muted">Minimum 6 characters</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>User Type <span class="text-danger">*</span></label>
                                                <select class="required form-control" name="usertype" required>
                                                    <option value="">Select Role</option>
                                                    <?php foreach ($roles as $role): ?>
                                                        <option value="<?= e($role['usertype']) ?>" 
                                                            <?= selected($_POST['usertype'] ?? '', $role['usertype']) ?>>
                                                            <?= e($role['role']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="required form-control" name="status">
                                                    <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                                    <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
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
                        <?php elseif ($action == 'edit' && $userData): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="fullname" 
                                                       value="<?= e($userData['fullname']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Email ID <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control required" name="emailid" 
                                                       value="<?= e($userData['emailid']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Password (leave empty to keep current)</label>
                                                <input type="password" class="form-control" name="password" 
                                                       placeholder="Enter new password to change">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>User Type <span class="text-danger">*</span></label>
                                                <select class="required form-control" name="usertype" required>
                                                    <option value="">Select Role</option>
                                                    <?php foreach ($roles as $role): ?>
                                                        <option value="<?= e($role['usertype']) ?>" 
                                                            <?= selected($userData['usertype'], $role['usertype']) ?>>
                                                            <?= e($role['role']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="required form-control" name="status">
                                                    <option value="1" <?= selected($userData['status'], '1') ?>>Active</option>
                                                    <option value="0" <?= selected($userData['status'], '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
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
                        <?php elseif ($action == 'view' && $userData): ?>
                            <div class="card-box">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Full Name</th>
                                        <td><?= e($userData['fullname']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email ID</th>
                                        <td><?= e($userData['emailid']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>User Type</th>
                                        <td><?= e($userData['usertype']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <?php if ($userData['status'] == '1'): ?>
                                                <span class="label label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created By</th>
                                        <td><?= e($userData['create_by_userid'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                                <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                            </div>

                        <!-- ==================== LIST ALL USERS ==================== -->
                        <?php else: ?>
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fullname</th>
                                            <th>Email Id</th>
                                            <th>Usertype</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 0; foreach ($allUsers as $user): $i++; ?>
                                            <tr>
                                                <td><?= $i ?></td>
                                                <td><?= e($user['fullname']) ?></td>
                                                <td><?= e($user['emailid']) ?></td>
                                                <td><?= e($user['usertype']) ?></td>
                                                <td>
                                                    <?php if ($user['status'] == '1'): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= e($FileName) ?>?action=view&id=<?= e($user['id']) ?>" class="table-action-btn" title="View">
                                                        <i class="fa fa-search"></i>
                                                    </a>
                                                    <a href="<?= e($FileName) ?>?action=edit&id=<?= e($user['id']) ?>" class="table-action-btn" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <?php if ($user['id'] != ($_SESSION['userid'] ?? 0)): ?>
                                                        <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($user['id']) ?>')" class="table-action-btn" title="Delete">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted" title="Cannot delete your own account">
                                                            <i class="fa fa-ban"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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