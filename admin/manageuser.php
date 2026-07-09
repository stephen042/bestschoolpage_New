<?php
/**
 * ============================================================================
 * MANAGE USER - MODERN UI WITH PARENT-CHILD RELATIONSHIPS
 * ============================================================================
 * - View all users (parents and staff)
 * - Block/Unblock users
 * - Reset passwords with copy to clipboard
 * - Show which children are linked to each parent
 * - Bulk status updates for parents and staff
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Manage Users";
$FileName = 'manage_user.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$userType = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';
$search = $_GET['search'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// PASSWORD GENERATION FUNCTION
// ============================================================================
function generateSecurePassword($length = 10) {
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $numbers = '0123456789';
    $symbols = '!@#$%&*?';
    $all = $uppercase . $numbers . $symbols;
    
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    return $password;
}

// ============================================================================
// TOGGLE USER STATUS
// ============================================================================
if (isset($_GET['toggle_status']) && !empty($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $currentStatus = db_get_val("SELECT status FROM school_register WHERE id = ? AND create_by_userid = ?", [$userId, $create_by_userid]);
    $newStatus = ($currentStatus == '1') ? '0' : '1';
    $message = ($newStatus == '1') ? "User activated successfully" : "User blocked successfully";
    
    db_update("school_register", ['status' => $newStatus], "id = ? AND create_by_userid = ?", [$userId, $create_by_userid]);
    $_SESSION['success'] = $message;
    redirect($FileName . '?action=' . $action . '&search=' . urlencode($search));
    exit;
}

// ============================================================================
// RESET USER PASSWORD
// ============================================================================
if (isset($_GET['reset_password']) && !empty($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $newPassword = generateSecurePassword(10);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $result = db_update("school_register", ['password' => $hashedPassword], "id = ? AND create_by_userid = ?", [$userId, $create_by_userid]);
    
    if ($result !== false) {
        $_SESSION['success'] = "Password reset successfully! New password: " . $newPassword;
        $_SESSION['temp_password'] = $newPassword;
    } else {
        $_SESSION['error'] = "Failed to reset password.";
    }
    
    redirect($FileName . '?action=' . $action . '&search=' . urlencode($search));
    exit;
}

// ============================================================================
// RESET PARENT PASSWORD
// ============================================================================
if (isset($_GET['reset_parent_password']) && !empty($_GET['id'])) {
    $parentId = (int)$_GET['id'];
    $newPassword = generateSecurePassword(10);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $result = db_update("student_guardian", ['password' => $hashedPassword], "id = ?", [$parentId]);
    
    if ($result !== false) {
        $_SESSION['success'] = "Parent password reset successfully! New password: " . $newPassword;
        $_SESSION['temp_password'] = $newPassword;
    } else {
        $_SESSION['error'] = "Failed to reset password.";
    }
    
    redirect($FileName . '?action=staff_account');
    exit;
}

// ============================================================================
// BULK UPDATE PARENT STATUS
// ============================================================================
if (isset($_POST['submit_parent'])) {
    if (isset($_POST['parent_id']) && is_array($_POST['parent_id'])) {
        foreach ($_POST['parent_id'] as $key => $val) {
            $parent_status = $_POST['parent_status'][$key] ?? '0';
            db_update("student_guardian", ['status' => $parent_status], "id = ?", [$val]);
        }
        $_SESSION['success'] = "Parent statuses updated successfully.";
    }
    redirect($FileName . '?action=staff_account');
    exit;
}

// ============================================================================
// BULK UPDATE STAFF STATUS
// ============================================================================
if (isset($_POST['submit_staff'])) {
    if (isset($_POST['staff_id']) && is_array($_POST['staff_id'])) {
        foreach ($_POST['staff_id'] as $key => $val) {
            $staff_status = $_POST['staff_status'][$key] ?? '0';
            db_update("school_register", ['status' => $staff_status], "id = ? AND create_by_userid = ?", [$val, $create_by_userid]);
        }
        $_SESSION['success'] = "Staff statuses updated successfully.";
    }
    redirect($FileName . '?action=view_staff');
    exit;
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$searchCondition = "";
if (!empty($search)) {
    $searchCondition = " AND (username LIKE '%$search%' OR name LIKE '%$search%' OR email LIKE '%$search%')";
}

// Get all users for All Users tab
$allUsers = db_get_rows("SELECT * FROM school_register WHERE create_by_userid = ? $searchCondition ORDER BY id DESC", [$create_by_userid]);

// Get parents for Parents tab (with their linked children)
$parents = db_get_rows("SELECT * FROM student_guardian WHERE create_by_userid = ? GROUP BY parent_id ORDER BY id ASC", [$create_by_userid]);

// Get staff for Staff tab
$staff = db_get_rows("SELECT * FROM school_register WHERE usertype = '1' AND create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// Get password from session if just generated
$tempPassword = $_SESSION['temp_password'] ?? '';
unset($_SESSION['temp_password']);

// Function to get children linked to a parent
function getChildrenByParentId($parentId) {
    $children = db_get_rows(
        "SELECT first_name, last_name, student_id, class 
         FROM manage_student 
         WHERE parent_id = ? ORDER BY first_name ASC",
        [$parentId]
    );
    return $children;
}

// Function to get class name
function getClassName($id) {
    return db_get_val("SELECT name FROM school_class WHERE id = ?", [$id]) ?: 'N/A';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .users-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .tabs-modern {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .tab-btn {
            padding: 12px 28px;
            background: transparent;
            border: none;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
        }
        .tab-btn:hover { background: #e8eef5; color: #1B3058; }
        .tab-btn.active {
            background: #1B3058;
            color: white;
            box-shadow: 0 4px 12px rgba(27,48,88,0.3);
        }
        
        .card-modern {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 25px;
        }
        .card-header {
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            padding: 18px 25px;
        }
        .card-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
        .card-body { padding: 25px; }
        
        .search-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .search-input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            width: 250px;
            font-size: 14px;
        }
        .search-input:focus { outline: none; border-color: #1B3058; }
        
        .table-wrapper { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #eee; }
        .data-table th { background: #f8f9fa; font-weight: 700; color: #1B3058; font-size: 13px; }
        .data-table tr:hover td { background: #fafafa; }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-blocked { background: #f8d7da; color: #721c24; }
        
        .action-icons { display: flex; gap: 8px; flex-wrap: wrap; }
        .action-icon {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 13px;
            padding: 5px 10px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .action-icon.activate { color: #28a745; }
        .action-icon.activate:hover { background: #d4edda; }
        .action-icon.block { color: #dc3545; }
        .action-icon.block:hover { background: #f8d7da; }
        .action-icon.reset { color: #ffc107; }
        .action-icon.reset:hover { background: #fff3cd; }
        .action-icon.view { color: #17a2b8; }
        .action-icon.view:hover { background: #d1ecf1; }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin-right: 10px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: #28a745; }
        input:checked + .slider:before { transform: translateX(26px); }
        .status-text { font-size: 12px; font-weight: 500; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .modal-password {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 16px;
            font-family: monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        .btn-copy {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .children-list {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }
        .child-tag {
            display: inline-block;
            background: #e8eef5;
            padding: 2px 8px;
            border-radius: 12px;
            margin-right: 5px;
            margin-top: 3px;
            font-size: 10px;
        }
        
        .empty-state { text-align: center; padding: 40px; color: #999; }
        .empty-state i { font-size: 48px; color: #ccc; margin-bottom: 15px; display: block; }
        
        .alert { padding: 15px 20px; border-radius: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        
        .btn-primary {
            background: #1B3058;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        
        @media (max-width: 768px) {
            .tabs-modern { flex-direction: column; align-items: stretch; }
            .tab-btn { text-align: center; }
            .data-table { display: block; overflow-x: auto; }
            .search-bar { justify-content: flex-start; }
            .search-input { width: 100%; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="users-container">
                
                <div class="page-header">
                    <h2><i class="fa fa-users"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>View and manage user accounts. Parent accounts show linked children.</p>
                </div>

                <?= showMessage($stat) ?>
                
                <!-- Password Modal -->
                <div id="passwordModal" class="modal">
                    <div class="modal-content">
                        <span class="modal-close" onclick="closeModal()">&times;</span>
                        <h3><i class="fa fa-key"></i> New Password Generated</h3>
                        <div id="modalPasswordDisplay" class="modal-password"></div>
                        <p>Please copy this password and share it securely with the user.</p>
                        <button class="btn-copy" onclick="copyModalPassword()"><i class="fa fa-copy"></i> Copy Password</button>
                    </div>
                </div>

                <!-- Modern Tabs -->
                <div class="tabs-modern">
                    <button class="tab-btn <?= ($action == '' || $action == 'all_users') ? 'active' : '' ?>" onclick="location.href='<?= $FileName ?>?action=all_users'">
                        <i class="fa fa-list"></i> All Users
                    </button>
                    <button class="tab-btn <?= ($action == 'staff_account') ? 'active' : '' ?>" onclick="location.href='<?= $FileName ?>?action=staff_account'">
                        <i class="fa fa-users"></i> Parents
                    </button>
                    <button class="tab-btn <?= ($action == 'view_staff') ? 'active' : '' ?>" onclick="location.href='<?= $FileName ?>?action=view_staff'">
                        <i class="fa fa-user-tie"></i> Staff
                    </button>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 1: ALL USERS -->
                <!-- ============================================================ -->
                <?php if ($action == '' || $action == 'all_users'): ?>
                <div class="card-modern">
                    <div class="card-header">
                        <h3><i class="fa fa-list"></i> All System Users</h3>
                    </div>
                    <div class="card-body">
                        <div class="search-bar">
                            <form method="GET" action="">
                                <input type="hidden" name="action" value="all_users">
                                <input type="text" name="search" class="search-input" placeholder="🔍 Search by name, username, email..." value="<?= htmlspecialchars($search) ?>">
                            </form>
                        </div>
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>User Type</th>
                                        <th>Linked Children</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($allUsers)): ?>
                                        <?php $i = 1; foreach ($allUsers as $user): 
                                            $isActive = ($user['status'] ?? '0') == '1';
                                            $userTypeLabel = ($user['usertype'] == '1') ? '👑 Staff' : '👨‍👩‍👧 Parent';
                                            $children = [];
                                            if ($user['usertype'] == '2') {
                                                $children = getChildrenByParentId($user['username']);
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                                <td><?= $userTypeLabel ?></td>
                                                <td>
                                                    <?php if (!empty($children)): ?>
                                                        <?php foreach ($children as $child): ?>
                                                            <span class="child-tag"><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></span>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <span style="color:#ccc;">No children linked</span>
                                                    <?php endif; ?>
                                                 </span>
                                                <td>
                                                    <span class="status-badge <?= $isActive ? 'status-active' : 'status-blocked' ?>">
                                                        <i class="fa <?= $isActive ? 'fa-check-circle' : 'fa-ban' ?>"></i>
                                                        <?= $isActive ? 'Active' : 'Blocked' ?>
                                                    </span>
                                                </td>
                                                <td class="action-icons">
                                                    <a href="<?= $FileName ?>?action=all_users&toggle_status=1&id=<?= $user['id'] ?>" 
                                                       class="action-icon <?= $isActive ? 'block' : 'activate' ?>" 
                                                       onclick="return confirm('Are you sure you want to <?= $isActive ? 'block' : 'activate' ?> this user?')">
                                                        <i class="fa <?= $isActive ? 'fa-ban' : 'fa-check-circle' ?>"></i>
                                                        <?= $isActive ? 'Block' : 'Activate' ?>
                                                    </a>
                                                    <a href="<?= $FileName ?>?action=all_users&reset_password=1&id=<?= $user['id'] ?>" 
                                                       class="action-icon reset" 
                                                       onclick="return confirm('Reset password for this user? A new password will be generated.')">
                                                        <i class="fa fa-refresh"></i> Reset Pwd
                                                    </a>
                                                  </span>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="empty-state">
                                                <i class="fa fa-users"></i>
                                                No users found.
                                             </span>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- TAB 2: PARENTS (with children display) -->
                <!-- ============================================================ -->
                <?php if ($action == 'staff_account'): ?>
                <div class="card-modern">
                    <div class="card-header">
                        <h3><i class="fa fa-users"></i> Parent Accounts</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Parent ID</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Linked Children</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($parents)): ?>
                                            <?php $i = 1; foreach ($parents as $parent): 
                                                $parentDetail = db_get_row("SELECT * FROM student_guardian WHERE parent_id = ? AND create_by_userid = ? ORDER BY id ASC", [$parent['parent_id'], $create_by_userid]);
                                                if (!empty($parentDetail)):
                                                    $isActive = ($parentDetail['status'] ?? '0') == '1';
                                                    $children = getChildrenByParentId($parent['parent_id']);
                                            ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars($parent['parent_id'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars(($parentDetail['first_name'] ?? '') . ' ' . ($parentDetail['last_name'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($parentDetail['email'] ?? '') ?></td>
                                                    <td>
                                                        <?php if (!empty($children)): ?>
                                                            <?php foreach ($children as $child): ?>
                                                                <span class="child-tag"><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span style="color:#ccc;">No children linked</span>
                                                        <?php endif; ?>
                                                     </span>
                                                    <td>
                                                        <input type="hidden" name="parent_id[]" value="<?= $parentDetail['id'] ?>">
                                                        <label class="switch">
                                                            <input type="checkbox" class="parent-toggle" data-id="<?= $parentDetail['id'] ?>" <?= $isActive ? 'checked' : '' ?>>
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <input type="hidden" name="parent_status[]" id="parent_status_<?= $parentDetail['id'] ?>" value="<?= $isActive ? '1' : '0' ?>">
                                                        <span class="status-text" id="status_text_<?= $parentDetail['id'] ?>">
                                                            <?= $isActive ? '🟢 Active' : '🔴 Blocked' ?>
                                                        </span>
                                                     </span>
                                                    <td>
                                                        <a href="<?= $FileName ?>?action=staff_account&reset_parent_password=1&id=<?= $parentDetail['id'] ?>" 
                                                           class="action-icon reset" 
                                                           onclick="return confirm('Reset password for this parent? A new password will be generated.')">
                                                            <i class="fa fa-refresh"></i> Reset Pwd
                                                        </a>
                                                     </span>
                                                </tr>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="empty-state">
                                                    <i class="fa fa-user-slash"></i>
                                                    No parents found.
                                                 </span>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button type="submit" name="submit_parent" class="btn-primary">
                                    <i class="fa fa-save"></i> Update Parent Statuses
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <script>
                    document.querySelectorAll('.parent-toggle').forEach(toggle => {
                        toggle.addEventListener('change', function() {
                            const id = this.dataset.id;
                            const hiddenInput = document.getElementById(`parent_status_${id}`);
                            const statusText = document.getElementById(`status_text_${id}`);
                            if (this.checked) {
                                hiddenInput.value = '1';
                                statusText.innerHTML = '🟢 Active';
                            } else {
                                hiddenInput.value = '0';
                                statusText.innerHTML = '🔴 Blocked';
                            }
                        });
                    });
                </script>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- TAB 3: STAFF -->
                <!-- ============================================================ -->
                <?php if ($action == 'view_staff'): ?>
                <div class="card-modern">
                    <div class="card-header">
                        <h3><i class="fa fa-user-tie"></i> Staff Accounts</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($staff)): ?>
                                            <?php $i = 1; foreach ($staff as $staffMember): 
                                                $role = db_get_val("SELECT role FROM roles WHERE id = (SELECT role_id FROM assign_role WHERE staff_id = ? AND create_by_userid = ?)", [$staffMember['id'], $create_by_userid]);
                                                $isActive = ($staffMember['status'] ?? '0') == '1';
                                            ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars($staffMember['username'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($role ?: 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($staffMember['name'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($staffMember['email'] ?? '') ?></td>
                                                    <td>
                                                        <input type="hidden" name="staff_id[]" value="<?= $staffMember['id'] ?>">
                                                        <label class="switch">
                                                            <input type="checkbox" class="staff-toggle" data-id="<?= $staffMember['id'] ?>" <?= $isActive ? 'checked' : '' ?>>
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <input type="hidden" name="staff_status[]" id="staff_status_<?= $staffMember['id'] ?>" value="<?= $isActive ? '1' : '0' ?>">
                                                        <span class="status-text" id="staff_status_text_<?= $staffMember['id'] ?>">
                                                            <?= $isActive ? '🟢 Active' : '🔴 Blocked' ?>
                                                        </span>
                                                     </span>
                                                    <td>
                                                        <a href="<?= $FileName ?>?action=view_staff&reset_password=1&id=<?= $staffMember['id'] ?>" 
                                                           class="action-icon reset" 
                                                           onclick="return confirm('Reset password for this staff member? A new password will be generated.')">
                                                            <i class="fa fa-refresh"></i> Reset Pwd
                                                        </a>
                                                     </span>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="empty-state">
                                                    <i class="fa fa-user-slash"></i>
                                                    No staff members found.
                                                 </span>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button type="submit" name="submit_staff" class="btn-primary">
                                    <i class="fa fa-save"></i> Update Staff Statuses
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <script>
                    document.querySelectorAll('.staff-toggle').forEach(toggle => {
                        toggle.addEventListener('change', function() {
                            const id = this.dataset.id;
                            const hiddenInput = document.getElementById(`staff_status_${id}`);
                            const statusText = document.getElementById(`staff_status_text_${id}`);
                            if (this.checked) {
                                hiddenInput.value = '1';
                                statusText.innerHTML = '🟢 Active';
                            } else {
                                hiddenInput.value = '0';
                                statusText.innerHTML = '🔴 Blocked';
                            }
                        });
                    });
                </script>
                <?php endif; ?>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>

<script>
// Show modal if password was just generated
<?php if (!empty($tempPassword)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalPasswordDisplay').innerHTML = '<?= addslashes($tempPassword) ?>';
        document.getElementById('passwordModal').style.display = 'flex';
    });
<?php endif; ?>

function closeModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

function copyModalPassword() {
    const password = document.getElementById('modalPasswordDisplay').innerText;
    navigator.clipboard.writeText(password);
    alert('Password copied to clipboard!');
}
</script>

</body>
</html>