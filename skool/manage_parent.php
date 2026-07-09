<?php
/**
 * ============================================================================
 * MANAGE PARENTS - VIEW & EDIT ONLY (Parent ID comes from Students)
 * ============================================================================
 * - Parent ID = Student ID of first child in family
 * - Cannot create new parents here (only through student registration)
 * - Can view, edit details, reset password, block/unblock
 * - Shows all children linked to each parent
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Manage Parents";
$FileName = 'manage_parents.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$search = $_GET['search'] ?? '';
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';

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
// FUNCTION: htmlspecialchars_null_safe - Safe wrapper for htmlspecialchars
// ============================================================================
function hsc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ============================================================================
// GET LINKED CHILDREN FOR A PARENT
// ============================================================================
function getLinkedChildren($parentId) {
    return db_get_rows(
        "SELECT * FROM manage_student WHERE parent_id = ? ORDER BY first_name ASC",
        [$parentId]
    );
}

// ============================================================================
// UPDATE PARENT DETAILS
// ============================================================================
if (isset($_POST['update_parent']) && !empty($randomid)) {
    $title = $_POST['title'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $otherName = trim($_POST['other_name'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $homeAddress1 = trim($_POST['home_address1'] ?? '');
    $homeAddress2 = trim($_POST['home_address2'] ?? '');
    $homeCity = trim($_POST['home_city'] ?? '');
    $homeState = $_POST['home_state'] ?? '';
    $officeAddress1 = trim($_POST['office_address1'] ?? '');
    $officeAddress2 = trim($_POST['office_address2'] ?? '');
    $officeCity = trim($_POST['office_city'] ?? '');
    $officeState = $_POST['office_state'] ?? '';
    
    $updateData = [
        'title' => $title,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'other_name' => $otherName,
        'occupation' => $occupation,
        'phone' => $phone,
        'email' => $email,
        'home_address_1' => $homeAddress1,
        'home_address_2' => $homeAddress2,
        'home_city' => $homeCity,
        'home_state' => $homeState,
        'office_address_1' => $officeAddress1,
        'office_address_2' => $officeAddress2,
        'office_city' => $officeCity,
        'office_state' => $officeState,
    ];
    
    $result = db_update("student_guardian", $updateData, "randomid = ?", [$randomid]);
    
    if ($result !== false) {
        // Update school_register as well
        $parent = db_get_row("SELECT parent_id FROM student_guardian WHERE randomid = ?", [$randomid]);
        if ($parent) {
            db_update("school_register", [
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'contact_no' => $phone
            ], "username = ?", [$parent['parent_id']]);
        }
        $_SESSION['success'] = "Parent information updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update parent information.";
    }
    
    redirect($FileName . '?randomid=' . $randomid);
}

// ============================================================================
// RESET PARENT PASSWORD
// ============================================================================
if (isset($_GET['reset_password']) && !empty($_GET['parent_id'])) {
    $parentId = $_GET['parent_id'];
    $newPassword = generateSecurePassword(10);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $result = db_update("school_register", ['password' => $hashedPassword], "username = ?", [$parentId]);
    
    if ($result !== false) {
        $_SESSION['success'] = "Password reset successfully! New password: " . $newPassword;
        $_SESSION['temp_password'] = $newPassword;
    } else {
        $_SESSION['error'] = "Failed to reset password.";
    }
    
    redirect($FileName . '?randomid=' . $randomid);
}

// ============================================================================
// TOGGLE PARENT STATUS (ACTIVE/BLOCKED)
// ============================================================================
if (isset($_GET['toggle_status']) && !empty($_GET['parent_id'])) {
    $parentId = $_GET['parent_id'];
    $currentStatus = db_get_val("SELECT status FROM school_register WHERE username = ?", [$parentId]);
    $newStatus = ($currentStatus == '1') ? '0' : '1';
    $message = ($newStatus == '1') ? "Parent account activated successfully" : "Parent account blocked successfully";
    
    db_update("school_register", ['status' => $newStatus], "username = ?", [$parentId]);
    db_update("student_guardian", ['status' => $newStatus], "parent_id = ?", [$parentId]);
    
    $_SESSION['success'] = $message;
    redirect($FileName . '?randomid=' . $randomid);
}

// ============================================================================
// DELETE PARENT (Only if no children linked? Or with warning)
// ============================================================================
if (isset($_GET['delete']) && !empty($_GET['parent_id'])) {
    $parentId = $_GET['parent_id'];
    
    // Check if parent has linked children
    $childCount = db_get_val("SELECT COUNT(*) FROM manage_student WHERE parent_id = ?", [$parentId]);
    
    if ($childCount > 0) {
        $_SESSION['error'] = "Cannot delete parent. This parent has $childCount linked child(ren). Delete the children first or reassign them.";
    } else {
        db_delete("student_guardian", "parent_id = ?", [$parentId]);
        db_delete("school_register", "username = ?", [$parentId]);
        $_SESSION['success'] = "Parent account deleted successfully!";
    }
    
    redirect($FileName);
}

// ============================================================================
// GET ALL PARENTS (UNIQUE parent_id from manage_student)
// ============================================================================
$parents = db_get_rows(
    "SELECT DISTINCT ms.parent_id, sg.*, sr.status as login_status, sr.id as login_id
     FROM manage_student ms
     LEFT JOIN student_guardian sg ON ms.parent_id = sg.parent_id
     LEFT JOIN school_register sr ON ms.parent_id = sr.username
     WHERE ms.create_by_userid = ? AND ms.parent_id IS NOT NULL AND ms.parent_id != ''
     ORDER BY sg.first_name ASC",
    [$create_by_userid]
);

$editParent = null;
$editParentChildren = [];
if (!empty($randomid)) {
    $editParent = db_get_row("SELECT * FROM student_guardian WHERE randomid = ?", [$randomid]);
    if ($editParent) {
        $editParentChildren = getLinkedChildren($editParent['parent_id']);
    }
}

$states = db_get_rows("SELECT * FROM state WHERE status = '1' ORDER BY title ASC");
$tempPassword = $_SESSION['temp_password'] ?? '';
unset($_SESSION['temp_password']);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .parents-container { display: flex; gap: 25px; padding: 20px; min-height: calc(100vh - 120px); }
        .parents-sidebar { width: 32%; background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; }
        .parents-main { width: 68%; background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid #eee; background: #f8f9fa; }
        .sidebar-header h4 { margin: 0 0 10px; color: #1B3058; }
        .sidebar-search { width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 30px; font-size: 14px; margin-bottom: 15px; }
        .sidebar-search:focus { outline: none; border-color: #1B3058; }
        .parents-list { flex: 1; overflow-y: auto; padding: 10px; max-height: calc(100vh - 200px); }
        .parent-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
            border: 1px solid #f0f0f0;
            text-decoration: none;
        }
        .parent-card:hover { background: #f8f9ff; border-color: #1B3058; transform: translateX(3px); }
        .parent-card.active { background: #1B3058; border-color: #1B3058; }
        .parent-card.active .parent-name, .parent-card.active .parent-id { color: white; }
        .parent-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        .parent-info { flex: 1; }
        .parent-name { font-weight: 600; font-size: 15px; color: #1a2a3a; margin-bottom: 4px; }
        .parent-id { font-size: 11px; color: #1B3058; }
        .parent-status {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 20px;
            margin-top: 4px;
            display: inline-block;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-blocked { background: #f8d7da; color: #721c24; }
        
        .main-header { padding: 20px 25px; border-bottom: 1px solid #eee; background: #f8f9fa; }
        .main-header h2 { margin: 0; font-size: 22px; color: #1B3058; }
        .main-content { padding: 25px; max-height: calc(100vh - 200px); overflow-y: auto; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #333; font-size: 13px; }
        .form-control, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        .btn { padding: 10px 20px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-outline { background: transparent; border: 2px solid #ddd; color: #333; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .action-buttons { display: flex; gap: 12px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; flex-wrap: wrap; }
        
        .children-section { margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .children-section h4 { margin-bottom: 15px; color: #1B3058; }
        .child-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 10px;
        }
        .child-info { flex: 1; }
        .child-name { font-weight: 600; color: #1B3058; }
        .child-details { font-size: 12px; color: #666; margin-top: 2px; }
        
        .info-box {
            background: #e8f0fe;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #1B3058;
        }
        .info-box p { margin: 5px 0; font-size: 13px; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        .empty-state i { font-size: 60px; color: #ddd; margin-bottom: 15px; display: block; }
        
        .alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b5; }
        
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
            max-width: 450px;
            width: 90%;
            padding: 25px;
            text-align: center;
        }
        .modal-password {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 16px;
            font-family: monospace;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
        }
        
        @media (max-width: 900px) {
            .parents-container { flex-direction: column; }
            .parents-sidebar, .parents-main { width: 100%; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="parents-container">
                
                <!-- LEFT SIDEBAR -->
                <div class="parents-sidebar">
                    <div class="sidebar-header">
                        <h4><i class="fa fa-users"></i> Parents (by Student ID)</h4>
                        <input type="text" id="searchParent" class="sidebar-search" placeholder="🔍 Search by name or Parent ID...">
                        <div class="info-box">
                            <p><i class="fa fa-info-circle"></i> <strong>Note:</strong> Parent ID = Student ID of first child in family</p>
                            <p><i class="fa fa-child"></i> Create new parents through <strong>Add Student</strong> page</p>
                        </div>
                    </div>
                    <div class="parents-list" id="parentsList">
                        <?php if (!empty($parents)): ?>
                            <?php foreach ($parents as $parent): ?>
                                <?php 
                                    $parentRandomId = $parent['randomid'] ?? '';
                                    $parentFirstName = $parent['first_name'] ?? '';
                                    $parentLastName = $parent['last_name'] ?? '';
                                    $parentId = $parent['parent_id'] ?? '';
                                    $loginStatus = $parent['login_status'] ?? '1';
                                    $statusClass = ($loginStatus == '1') ? 'status-active' : 'status-blocked';
                                    $statusText = ($loginStatus == '1') ? '● Active' : '● Blocked';
                                ?>
                                <a href="?randomid=<?= urlencode($parentRandomId) ?>" class="parent-card <?= ($randomid == $parentRandomId) ? 'active' : '' ?>"
                                   data-name="<?= strtolower($parentFirstName . ' ' . $parentLastName) ?>"
                                   data-id="<?= strtolower($parentId) ?>">
                                    <div class="parent-avatar">
                                        <?= strtoupper(substr($parentFirstName, 0, 1) ?: 'P') ?>
                                    </div>
                                    <div class="parent-info">
                                        <div class="parent-name"><?= hsc($parentFirstName . ' ' . $parentLastName) ?></div>
                                        <div class="parent-id">Parent ID: <?= hsc($parentId) ?></div>
                                        <span class="parent-status <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state" style="padding: 40px;">
                                <i class="fa fa-user-slash"></i>
                                No parents found. Parent accounts are created automatically when you add students.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- RIGHT MAIN PANEL -->
                <div class="parents-main">
                    <?php if ($editParent): 
                        $parentStatus = db_get_val("SELECT status FROM school_register WHERE username = ?", [$editParent['parent_id']]);
                        $statusClass = ($parentStatus == '1') ? 'status-active' : 'status-blocked';
                        $statusText = ($parentStatus == '1') ? 'Active' : 'Blocked';
                    ?>
                        <div class="main-header">
                            <h2><i class="fa fa-user"></i> <?= hsc($editParent['first_name'] . ' ' . $editParent['last_name']) ?></h2>
                            <p>Parent ID: <?= hsc($editParent['parent_id']) ?> (from Student ID)</p>
                        </div>
                        <div class="main-content">
                            <?= showMessage($stat) ?>
                            
                            <!-- Parent Info Box -->
                            <div class="info-box">
                                <p><i class="fa fa-key"></i> <strong>Login Credentials:</strong></p>
                                <p>Username/Parent ID: <strong><?= hsc($editParent['parent_id']) ?></strong></p>
                                <p>Status: <span class="parent-status <?= $statusClass ?>"><?= $statusText ?></span></p>
                                <p><i class="fa fa-info-circle"></i> Use the buttons below to reset password or change status.</p>
                            </div>
                            
                            <form method="post">
                                <div class="form-grid">
                                    <div class="form-group"><label>Title</label>
                                        <select name="title" class="form-select">
                                            <option value="">Select Title</option>
                                            <option value="Mr." <?= ($editParent['title'] ?? '') == 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                                            <option value="Mrs." <?= ($editParent['title'] ?? '') == 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                                            <option value="Miss." <?= ($editParent['title'] ?? '') == 'Miss.' ? 'selected' : '' ?>>Miss.</option>
                                            <option value="Dr." <?= ($editParent['title'] ?? '') == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                                            <option value="Prof." <?= ($editParent['title'] ?? '') == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                                            <option value="Alh." <?= ($editParent['title'] ?? '') == 'Alh.' ? 'selected' : '' ?>>Alh.</option>
                                            <option value="Hajia." <?= ($editParent['title'] ?? '') == 'Hajia.' ? 'selected' : '' ?>>Hajia.</option>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" value="<?= hsc($editParent['first_name'] ?? '') ?>" required></div>
                                    <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" class="form-control" value="<?= hsc($editParent['last_name'] ?? '') ?>" required></div>
                                    <div class="form-group"><label>Other Name</label><input type="text" name="other_name" class="form-control" value="<?= hsc($editParent['other_name'] ?? '') ?>"></div>
                                    <div class="form-group"><label>Occupation</label><input type="text" name="occupation" class="form-control" value="<?= hsc($editParent['occupation'] ?? '') ?>"></div>
                                    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= hsc($editParent['phone'] ?? '') ?>"></div>
                                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= hsc($editParent['email'] ?? '') ?>"></div>
                                </div>
                                
                                <h4 style="margin: 20px 0 15px; color: #1B3058;"><i class="fa fa-home"></i> Home Address</h4>
                                <div class="form-grid">
                                    <div class="form-group"><label>Address Line 1</label><input type="text" name="home_address1" class="form-control" value="<?= hsc($editParent['home_address_1'] ?? '') ?>"></div>
                                    <div class="form-group"><label>Address Line 2</label><input type="text" name="home_address2" class="form-control" value="<?= hsc($editParent['home_address_2'] ?? '') ?>"></div>
                                    <div class="form-group"><label>City</label><input type="text" name="home_city" class="form-control" value="<?= hsc($editParent['home_city'] ?? '') ?>"></div>
                                    <div class="form-group"><label>State</label>
                                        <select name="home_state" class="form-select">
                                            <option value="">Select State</option>
                                            <?php foreach($states as $s): ?>
                                                <option value="<?= $s['id'] ?>" <?= (($editParent['home_state'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= hsc($s['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <h4 style="margin: 20px 0 15px; color: #1B3058;"><i class="fa fa-briefcase"></i> Office Address</h4>
                                <div class="form-grid">
                                    <div class="form-group"><label>Address Line 1</label><input type="text" name="office_address1" class="form-control" value="<?= hsc($editParent['office_address_1'] ?? '') ?>"></div>
                                    <div class="form-group"><label>Address Line 2</label><input type="text" name="office_address2" class="form-control" value="<?= hsc($editParent['office_address_2'] ?? '') ?>"></div>
                                    <div class="form-group"><label>City</label><input type="text" name="office_city" class="form-control" value="<?= hsc($editParent['office_city'] ?? '') ?>"></div>
                                    <div class="form-group"><label>State</label>
                                        <select name="office_state" class="form-select">
                                            <option value="">Select State</option>
                                            <?php foreach($states as $s): ?>
                                                <option value="<?= $s['id'] ?>" <?= (($editParent['office_state'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= hsc($s['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Linked Children Section -->
                                <div class="children-section">
                                    <h4><i class="fa fa-child"></i> Linked Children (<?= count($editParentChildren) ?> children)</h4>
                                    <?php if (!empty($editParentChildren)): ?>
                                        <?php foreach ($editParentChildren as $child): ?>
                                            <div class="child-card">
                                                <div class="child-info">
                                                    <div class="child-name"><?= hsc($child['first_name'] . ' ' . $child['last_name']) ?></div>
                                                    <div class="child-details">Student ID: <?= hsc($child['student_id']) ?> | Class: <?= hsc(db_get_val("SELECT name FROM school_class WHERE id = ?", [$child['class']]) ?: 'N/A') ?></div>
                                                </div>
                                                <a href="../skool/student.php?randomid=<?= hsc($child['randomid']) ?>" class="btn btn-outline btn-sm" target="_blank">
                                                    <i class="fa fa-external-link"></i> View
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> No children linked to this parent yet.
                                            <br>To link a child, add a student and set Parent ID to: <strong><?= hsc($editParent['parent_id']) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" name="update_parent" class="btn btn-primary"><i class="fa fa-save"></i> Update Parent Info</button>
                                    <a href="<?= $FileName ?>?reset_password=1&parent_id=<?= urlencode($editParent['parent_id'] ?? '') ?>" class="btn btn-outline" onclick="return confirm('Reset password for this parent? A new password will be generated.')">
                                        <i class="fa fa-refresh"></i> Reset Password
                                    </a>
                                    <a href="<?= $FileName ?>?toggle_status=1&parent_id=<?= urlencode($editParent['parent_id'] ?? '') ?>" class="btn btn-outline" onclick="return confirm('Toggle account status for this parent?')">
                                        <i class="fa <?= ($parentStatus == '1') ? 'fa-ban' : 'fa-check-circle' ?>"></i>
                                        <?= ($parentStatus == '1') ? 'Block Account' : 'Activate Account' ?>
                                    </a>
                                    <a href="<?= $FileName ?>?delete=1&parent_id=<?= urlencode($editParent['parent_id'] ?? '') ?>" class="btn btn-danger" onclick="return confirm('Delete this parent account? This will also remove all linked student data. This action cannot be undone.')">
                                        <i class="fa fa-trash"></i> Delete Parent
                                    </a>
                                </div>
                            </form>
                        </div>
                    <?php elseif (!empty($randomid) && !$editParent): ?>
                        <div class="empty-state" style="padding: 80px;">
                            <i class="fa fa-user-slash" style="font-size: 64px; color: #ddd;"></i>
                            <h3>Parent Not Found</h3>
                            <p>The selected parent could not be found.</p>
                            <a href="<?= $FileName ?>" class="btn btn-primary">Back to Parents</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 80px;">
                            <i class="fa fa-users" style="font-size: 64px; color: #ddd;"></i>
                            <h3>Select a Parent</h3>
                            <p>Choose a parent from the left sidebar to view and edit their details.</p>
                            <p style="margin-top: 15px;"><strong>Note:</strong> Parent accounts are automatically created when you add a student.</p>
                            <a href="../skool/student.php?action=add" class="btn btn-primary"><i class="fa fa-plus"></i> Add New Student</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <h3><i class="fa fa-key"></i> Parent Credentials</h3>
        <div id="modalPasswordDisplay" class="modal-password"></div>
        <p>Please copy this password and share it securely with the parent.</p>
        <button class="btn btn-primary" onclick="copyModalPassword()"><i class="fa fa-copy"></i> Copy Password</button>
        <button class="btn btn-outline" onclick="closeModal()" style="margin-top: 10px;">Close</button>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script>
// Search functionality
document.getElementById('searchParent')?.addEventListener('keyup', function() {
    let searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.parent-card').forEach(card => {
        let name = card.getAttribute('data-name') || '';
        let id = card.getAttribute('data-id') || '';
        card.style.display = (name.includes(searchTerm) || id.includes(searchTerm)) ? 'flex' : 'none';
    });
});

// Password modal
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