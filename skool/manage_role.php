<?php
/**
 * Manage Roles - Modern PHP 8.x
 * Manage roles, permissions, and assign roles to staff
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Manage Roles";
$FileName = 'manage_role.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$add = $_GET['add'] ?? '';
$role_action = $_GET['role'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// ROLE CRUD
// ============================================================================
if (isset($_POST['add_role'])) {
    $role = trim($_POST['role'] ?? '');
    
    if (empty($role)) {
        $stat['error'] = "Role name is required";
    } else {
        $lastId = db_get_val("SELECT id FROM roles ORDER BY id DESC") ?? 0;
        $newId = $lastId + 1;
        $randomId = randomFix(15) . '-' . $newId;

        db_insert("roles", [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'role' => $role,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId,
        ]);

        $_SESSION['success'] = "Role added successfully";
        redirect($FileName . '?action=manage_roles');
    }
}

if (isset($_POST['edit_role']) && !empty($randomid)) {
    $role = trim($_POST['role'] ?? '');
    if (!empty($role)) {
        db_update("roles", ['role' => $role], "randomid = ?", [$randomid]);
        $_SESSION['success'] = "Role updated successfully";
    }
    redirect($FileName . '?action=manage_roles');
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_roles' && !empty($randomid)) {
    $roleId = db_get_val("SELECT id FROM roles WHERE randomid = ?", [$randomid]);
    if ($roleId) {
        db_delete("roles", "randomid = ?", [$randomid]);
        db_delete("role_permission", "role_id = ?", [$roleId]);
    }
    $_SESSION['success'] = "Role deleted successfully";
    redirect($FileName . '?action=manage_roles');
}

// ============================================================================
// PERMISSIONS (ADD FILES)
// ============================================================================
if (isset($_POST['add_files']) && !empty($randomid)) {
    $roleId = db_get_val("SELECT id FROM roles WHERE randomid = ?", [$randomid]);
    
    if ($roleId) {
        db_delete("role_permission", "role_id = ?", [$roleId]);
        
        if (isset($_POST['file_name']) && is_array($_POST['file_name'])) {
            foreach ($_POST['file_name'] as $fileName) {
                $lastId = db_get_val("SELECT id FROM role_permission ORDER BY id DESC") ?? 0;
                $newId = $lastId + 1;
                $randomId = randomFix(15) . '-' . $newId;
            
                db_insert("role_permission", [
                    'usertype' => $_SESSION['usertype'] ?? '',
                    'userid' => $_SESSION['userid'] ?? 0,
                    'role_id' => $roleId,
                    'file_name' => $fileName,
                    'create_by_usertype' => $create_by_usertype,
                    'create_by_userid' => $create_by_userid,
                    'randomid' => $randomId,
                ]);
            }
        }
        $_SESSION['success'] = "Permissions saved successfully";
    }
    redirect($FileName . '?action=manage_roles');
}

// ============================================================================
// ASSIGN ROLE TO STAFF
// ============================================================================
if (isset($_POST['assign_role'])) {
    $staffId = (int)($_POST['staff_id'] ?? 0);
    $roleId = $_POST['role_id'] ?? '';
    $principal = $_POST['principal'] ?? '';
    
    if ($staffId <= 0 || empty($roleId)) {
        $stat['error'] = "Please select both staff and role";
    } else {
        $staffUserExists = db_get_val(
            "SELECT id FROM school_register WHERE id = ? AND create_by_userid = ? AND usertype = '1'",
            [$staffId, $create_by_userid]
        );

        if (empty($staffUserExists)) {
            $_SESSION['error'] = "Selected staff login account was not found. Ensure the staff user exists in Manage Users.";
            redirect($FileName . '?action=assign_role');
            exit;
        }

        $existing = db_get_val(
            "SELECT id FROM assign_role WHERE staff_id = ? AND create_by_userid = ?",
            [$staffId, $create_by_userid]
        );
        
        if (empty($existing)) {
            $lastId = db_get_val("SELECT id FROM assign_role ORDER BY id DESC") ?? 0;
            $newId = $lastId + 1;
            $randomId = randomFix(15) . '-' . $newId;
            
            db_insert("assign_role", [
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'staff_id' => $staffId,
                'principal' => $principal,
                'role_id' => $roleId,
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype,
                'randomid' => $randomId,
            ]);
            $_SESSION['success'] = "Role assigned successfully";
        } else {
            db_update("assign_role", ['role_id' => $roleId], "staff_id = ? AND create_by_userid = ?", [$staffId, $create_by_userid]);
            $_SESSION['success'] = "Role updated successfully";
        }
        redirect($FileName . '?action=assign_role');
    }
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$financePermissionFiles = [
    ['file_name' => 'financial_dashboard.php', 'title' => 'Financial Dashboard'],
    ['file_name' => 'takefeesturcture.php', 'title' => 'Fee Structure'],
    ['file_name' => 'takefee.php', 'title' => 'Take Fee'],
    ['file_name' => 'bulk_fee_assignment.php', 'title' => 'Bulk Fee Assignment'],
    ['file_name' => 'fee_status_report.php', 'title' => 'Fee Status Report'],
    ['file_name' => 'payroll.php', 'title' => 'Payroll'],
    ['file_name' => 'petty_cash.php', 'title' => 'Petty Cash'],
    ['file_name' => 'inventory.php', 'title' => 'Inventory'],
    ['file_name' => 'expenses.php', 'title' => 'Expenses'],
];

foreach ($financePermissionFiles as $financeFile) {
    $existingFile = db_get_row("SELECT id FROM school_filename WHERE file_name = ?", [$financeFile['file_name']]);
    if (empty($existingFile)) {
        db_insert("school_filename", [
            'file_name' => $financeFile['file_name'],
            'title' => $financeFile['title'],
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => randomFix(15) . '-' . time() . '-' . substr(md5($financeFile['file_name']), 0, 6),
        ]);
    }
}

$roles = db_get_rows("SELECT * FROM roles WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]) ?: [];
$staffMembers = db_get_rows("SELECT * FROM staff_manage WHERE create_by_userid = ? ORDER BY first_name ASC", [$create_by_userid]) ?: [];
$files = db_get_rows("SELECT * FROM school_filename ORDER BY title ASC") ?: [];
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .roles-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        /* Tabs */
        .nav-tabs-custom { display: flex; gap: 5px; border-bottom: 2px solid #e0e0e0; margin-bottom: 25px; flex-wrap: wrap; }
        .nav-tab { padding: 12px 28px; background: #f5f5f5; border-radius: 30px 30px 0 0; text-decoration: none; color: #333; font-weight: 600; transition: all 0.2s; }
        .nav-tab:hover { background: #e0e0e0; }
        .nav-tab.active { background: #1B3058; color: white; }
        
        /* Cards */
        .card { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
        .card-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 18px 25px; }
        .card-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
        .card-header-secondary { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); }
        .card-body { padding: 25px; }
        
        /* Form */
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 200px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #e0e0e0; border-radius: 12px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        
        /* Buttons */
        .btn { padding: 12px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-icon { background: transparent; border: none; cursor: pointer; font-size: 16px; transition: all 0.2s; }
        .btn-edit { color: #17a2b8; }
        .btn-edit:hover { color: #0f6674; transform: scale(1.1); }
        .btn-delete { color: #dc3545; }
        .btn-delete:hover { color: #a71d2a; transform: scale(1.1); }
        
        /* Tables */
        .table-wrapper { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .data-table tr:hover { background: #fafafa; }
        
        /* Search Bar */
        .search-bar { margin-bottom: 20px; display: flex; justify-content: flex-end; }
        .search-input { padding: 10px 15px; border: 1px solid #e0e0e0; border-radius: 30px; width: 250px; font-size: 14px; }
        .search-input:focus { outline: none; border-color: #1B3058; }
        
        /* Permission Checkboxes Grid */
        .permission-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .permission-item { display: flex; align-items: center; gap: 10px; padding: 10px; background: #f8f9fa; border-radius: 10px; cursor: pointer; transition: all 0.2s; }
        .permission-item:hover { background: #e8eef5; }
        .permission-item input { width: 18px; height: 18px; cursor: pointer; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 50px; color: #999; }
        .empty-state i { font-size: 48px; color: #ccc; margin-bottom: 15px; display: block; }
        
        /* Loading Spinner */
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-loading { opacity: 0.7; pointer-events: none; }
        
        /* Inline Edit */
        .inline-input { padding: 6px 10px; border: 1px solid #1B3058; border-radius: 8px; width: 100%; max-width: 200px; }
        .action-cell { white-space: nowrap; }
        
        @media (max-width: 768px) { 
            .form-row { flex-direction: column; } 
            .search-bar { justify-content: flex-start; }
            .search-input { width: 100%; }
            .permission-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="roles-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-shield"></i> <?= $PageTitle ?></h2>
                    <p>Manage user roles, permissions, and assign roles to staff members</p>
                </div>

                <?= showMessage($stat) ?>

                <!-- Tabs Navigation -->
                <div class="nav-tabs-custom">
                    <a href="?action=manage_roles" class="nav-tab <?= ($action == '' || $action == 'manage_roles') ? 'active' : '' ?>">
                        <i class="fa fa-list"></i> Manage Roles
                    </a>
                    <a href="?action=assign_role" class="nav-tab <?= ($action == 'assign_role') ? 'active' : '' ?>">
                        <i class="fa fa-user-plus"></i> Assign Role to Staff
                    </a>
                </div>

                <!-- ============================================================ -->
                <!-- MANAGE ROLES TAB -->
                <!-- ============================================================ -->
                <?php if ($action == '' || $action == 'manage_roles'): ?>
                    
                    <!-- Add Role Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-plus-circle"></i> Add New Role</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Role Name</label>
                                        <input type="text" name="role" class="form-control" placeholder="e.g., Administrator, Teacher, Accountant" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" name="add_role" class="btn btn-primary"><i class="fa fa-plus"></i> Add Role</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Roles List -->
                    <div class="card">
                        <div class="card-header card-header-secondary">
                            <h3><i class="fa fa-list"></i> Roles List</h3>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="roleSearch" class="search-input" placeholder="🔍 Search roles...">
                            </div>
                            <div class="table-wrapper">
                                <table class="data-table" id="rolesTable">
                                    <thead>
                                        <tr><th>#</th><th>Role Name</th><th>Permissions</th><th style="width: 180px;">Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($roles)): ?>
                                            <?php $i = 1; foreach ($roles as $role): ?>
                                                <tr data-search="<?= strtolower($role['role']) ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?php if ($randomid == $role['randomid']): ?>
                                                            <form method="post" style="display: inline-block; width: 100%;" onsubmit="showButtonLoading(this)">
                                                                <input type="hidden" name="randomid" value="<?= $role['randomid'] ?>">
                                                                <input type="text" name="role" value="<?= htmlspecialchars($role['role']) ?>" class="inline-input" required>
                                                                <button type="submit" name="edit_role" class="btn btn-success btn-sm" style="margin-top: 5px;"><i class="fa fa-check"></i> Save</button>
                                                                <a href="?action=manage_roles" class="btn btn-sm" style="background:#6c757d; color:white; padding:5px 10px; border-radius:6px; text-decoration:none;">Cancel</a>
                                                            </form>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($role['role']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?action=manage_roles&add=add_files&randomid=<?= $role['randomid'] ?>" class="btn btn-info btn-sm"><i class="fa fa-key"></i> Set Permissions</a>
                                                    </td>
                                                    <td class="action-cell">
                                                        <?php if ($randomid != $role['randomid']): ?>
                                                            <a href="?action=manage_roles&randomid=<?= $role['randomid'] ?>" class="btn-icon btn-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        <?php endif; ?>
                                                        <a href="javascript:deleteItem('<?= $FileName ?>?action=delete_roles&randomid=<?= $role['randomid'] ?>', 'role')" class="btn-icon btn-delete" title="Delete"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="empty-state">No roles found. Add your first role above.<?= $create_by_userid ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add Permissions Modal (shown when add=add_files) -->
                    <?php if ($add == 'add_files' && !empty($randomid)): 
                        $currentRole = db_get_row("SELECT * FROM roles WHERE randomid = ?", [$randomid]);
                        $existingPermissions = db_get_rows("SELECT file_name FROM role_permission WHERE role_id = ?", [$currentRole['id'] ?? 0]);
                        $existingFileNames = array_column($existingPermissions, 'file_name');
                    ?>
                    <div class="card" style="margin-top: 0;">
                        <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <h3><i class="fa fa-key"></i> Set Permissions for: <?= htmlspecialchars($currentRole['role'] ?? '') ?></h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <div class="permission-grid">
                                    <?php if (!empty($files)): ?>
                                        <?php foreach ($files as $file): ?>
                                            <label class="permission-item">
                                                <input type="checkbox" name="file_name[]" value="<?= htmlspecialchars($file['file_name']) ?>" 
                                                    <?= in_array($file['file_name'], $existingFileNames) ? 'checked' : '' ?>>
                                                <span><?= htmlspecialchars($file['title']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="empty-state" style="padding: 20px;">No permission files found. Please add files first.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-row" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <button type="submit" name="add_files" class="btn btn-success"><i class="fa fa-save"></i> Save Permissions</button>
                                        <a href="?action=manage_roles" class="btn btn-secondary" style="background:#6c757d; color:white; padding:12px 24px; border-radius:12px; text-decoration:none; margin-left:10px;">Back to Roles</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- ASSIGN ROLE TO STAFF TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'assign_role'): ?>
                    
                    <!-- Staff List -->
                    <div class="card">
                        <div class="card-header card-header-secondary">
                            <h3><i class="fa fa-users"></i> Staff Members</h3>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="staffSearch" class="search-input" placeholder="🔍 Search staff by name or ID...">
                            </div>
                            <div class="table-wrapper">
                                <table class="data-table" id="staffTable">
                                    <thead>
                                        <tr><th>#</th><th>Staff ID</th><th>First Name</th><th>Last Name</th><th>Current Role</th><th style="width: 120px;">Action</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($staffMembers)): ?>
                                            <?php $i = 1; foreach ($staffMembers as $staff): 
                                                $staffUserId = db_get_val(
                                                    "SELECT id FROM school_register
                                                     WHERE create_by_userid = ?
                                                       AND usertype = '1'
                                                       AND (username = ? OR email = ?)
                                                     ORDER BY id DESC
                                                     LIMIT 1",
                                                    [$create_by_userid, $staff['staff_id'], $staff['email'] ?? '']
                                                );

                                                $assignedRole = [];
                                                if (!empty($staffUserId)) {
                                                    $assignedRole = db_get_row(
                                                        "SELECT r.role
                                                         FROM assign_role ar
                                                         LEFT JOIN roles r ON ar.role_id = r.id
                                                         WHERE ar.staff_id = ? AND ar.create_by_userid = ?",
                                                        [$staffUserId, $create_by_userid]
                                                    );
                                                }
                                                $roleName = $assignedRole['role'] ?? 'Not Assigned';
                                            ?>
                                                <tr data-search="<?= strtolower($staff['first_name'] . ' ' . $staff['last_name'] . ' ' . $staff['staff_id']) ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars($staff['staff_id']) ?></td>
                                                    <td><?= htmlspecialchars($staff['first_name']) ?></td>
                                                    <td><?= htmlspecialchars($staff['last_name']) ?></td>
                                                    <td>
                                                        <?php if ($roleName != 'Not Assigned'): ?>
                                                            <span style="background:#e8f5e9; color:#2e7d32; padding:4px 10px; border-radius:20px; font-size:12px;"><?= htmlspecialchars($roleName) ?></span>
                                                        <?php else: ?>
                                                            <span style="color:#999;"><?= $roleName ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?action=assign_role&role=assign_role_staff&randomid=<?= $staff['randomid'] ?>" class="btn btn-primary btn-sm"><i class="fa fa-user-plus"></i> Assign Role</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="empty-state">No staff members found. Please add staff first.<?= $create_by_userid ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                <td>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Role Form (shown when role=assign_role_staff) -->
                    <?php if ($role_action == 'assign_role_staff' && !empty($randomid)): 
                        $selectedStaff = db_get_row("SELECT * FROM staff_manage WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]) ?: [];
                        $staffId = $selectedStaff['staff_id'] ?? '';
                        $staffEmail = $selectedStaff['email'] ?? '';
                        $userId = db_get_val(
                            "SELECT id FROM school_register
                             WHERE create_by_userid = ?
                               AND usertype = '1'
                               AND (username = ? OR email = ?)
                             ORDER BY id DESC
                             LIMIT 1",
                            [$create_by_userid, $staffId, $staffEmail]
                        );
                        $currentAssignment = !empty($userId)
                            ? (db_get_row("SELECT role_id FROM assign_role WHERE staff_id = ? AND create_by_userid = ?", [$userId, $create_by_userid]) ?: [])
                            : [];
                        $currentRoleId = is_array($currentAssignment) ? (string)($currentAssignment['role_id'] ?? '') : '';
                        $staffName = trim((string)($selectedStaff['first_name'] ?? '') . ' ' . (string)($selectedStaff['last_name'] ?? ''));
                    ?>
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <h3><i class="fa fa-user-plus"></i> Assign Role to: <?= htmlspecialchars($staffName) ?></h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <input type="hidden" name="staff_id" value="<?= (int)$userId ?>">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Staff ID</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string)($selectedStaff['staff_id'] ?? '')) ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Staff Name</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($staffName) ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Login User ID</label>
                                        <input type="text" class="form-control" value="<?= !empty($userId) ? ('#' . (int)$userId) : 'Not found in Manage Users' ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Select Role</label>
                                        <select name="role_id" class="form-control" required <?= empty($userId) ? 'disabled' : '' ?>>
                                            <option value="">-- Select Role --</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['id'] ?>" <?= ($currentRoleId == (string)$role['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($role['role']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <button type="submit" name="assign_role" class="btn btn-success" <?= empty($userId) ? 'disabled' : '' ?>><i class="fa fa-save"></i> Assign Role</button>
                                        <a href="?action=assign_role" class="btn btn-secondary" style="background:#6c757d; color:white; padding:12px 24px; border-radius:12px; text-decoration:none; margin-left:10px;">Back to Staff List</a>
                                        <?php if (empty($userId)): ?>
                                            <div style="margin-top:10px; color:#dc3545; font-size:13px;">Staff login account not found. Create or sync this teacher in Manage Users first.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script>
// Search functionality for Roles
const roleSearch = document.getElementById('roleSearch');
if (roleSearch) {
    roleSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#rolesTable tbody tr');
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search') || '';
            row.style.display = searchText.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Search functionality for Staff
const staffSearch = document.getElementById('staffSearch');
if (staffSearch) {
    staffSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#staffTable tbody tr');
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search') || '';
            row.style.display = searchText.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Delete confirmation
function deleteItem(url, itemName) {
    if (confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.\n\nAll permissions assigned to this role will also be removed.`)) {
        showButtonLoading(null);
        window.location.href = url;
    }
}

// Show loading state on button
function showButtonLoading(form) {
    if (form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.innerHTML = '<span class="spinner"></span> Saving...';
        }
    }
}

// Select All checkboxes for permissions (optional)
function toggleAllPermissions(source) {
    const checkboxes = document.querySelectorAll('.permission-item input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>
</body>
</html>