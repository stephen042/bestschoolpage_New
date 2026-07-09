<?php
/**
 * Delete Staff - Separate File
 * Usage: delete_staff.php?id=randomid
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Delete Staff";
$create_by_userid = $_SESSION['userid'] ?? 0;

// Get staff randomid from URL
$randomid = $_GET['id'] ?? $_GET['randomid'] ?? '';

if (empty($randomid)) {
    $_SESSION['error'] = "Invalid staff ID";
    redirect('staff_simple.php');
    exit;
}

// Get staff details
$staff = db_get_row("SELECT * FROM staff_manage WHERE randomid = ?", [$randomid]);

if (empty($staff)) {
    $_SESSION['error'] = "Staff not found";
    redirect('staff_simple.php');
    exit;
}

// Process deletion when confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    
    // 1. Delete profile picture from folder
    if (!empty($staff['picture']) && file_exists("../uploads/" . $staff['picture'])) {
        unlink("../uploads/" . $staff['picture']);
    }
    
    // 2. Delete signature if exists
    if (!empty($staff['signature']) && file_exists("../uploads/" . $staff['signature'])) {
        unlink("../uploads/" . $staff['signature']);
    }
    
    // 3. Delete from staff_manage table
    db_delete("staff_manage", "randomid = ?", [$randomid]);
    
    // 4. Delete from school_register (login account)
    db_delete("school_register", "username = ?", [$staff['staff_id']]);
    
    // 5. Delete Next of Kin records
    db_delete("staff_manage_kin_details", "staff_manage_id = ?", [$staff['id']]);
    
    // 6. Delete Qualifications
    db_delete("staff_qualification", "staff_manage_id = ?", [$staff['id']]);
    
    // 7. Delete Previous Employment
    db_delete("staff_previous_employment", "staff_manage_id = ?", [$staff['id']]);
    
    // 8. Delete Referees
    db_delete("staff_refree", "staff_manage_id = ?", [$staff['id']]);
    
    $_SESSION['success'] = "Staff deleted successfully";
    redirect('staff_simple.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Staff - Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .confirm-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .staff-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .warning {
            color: #dc3545;
            font-size: 48px;
            margin-bottom: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .staff-info {
            text-align: left;
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .staff-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="confirm-box">
        <div class="warning">
            <i class="fa fa-exclamation-triangle"></i>
        </div>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this staff member?</p>
        
        <div class="staff-info">
            <?php if (!empty($staff['picture'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($staff['picture']); ?>" class="staff-photo">
            <?php else: ?>
                <div style="width:100px;height:100px;background:#eee;border-radius:50%;margin:0 auto 15px;"></div>
            <?php endif; ?>
            <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($staff['staff_id']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($staff['phone']); ?></p>
        </div>
        
        <p style="color: red; font-weight: bold;">
            <i class="fa fa-warning"></i> This action cannot be undone! All staff data including references, qualifications, and next of kin will be permanently deleted.
        </p>
        
        <div>
            <a href="?id=<?php echo $randomid; ?>&confirm=yes" class="btn btn-danger">Yes, Delete Staff</a>
            <a href="staff_simple.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</body>
</html>