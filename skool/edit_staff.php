<?php
/**
 * Edit Staff - Separate File
 * Usage: edit_staff.php?id=randomid
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Edit Staff";
$FileName = 'edit_staff.php';
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

// Process update
if (isset($_POST['update_staff'])) {
    $staffId = trim($_POST['staff_id'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $maritalStatus = $_POST['marital_status'] ?? '';
    $religion = $_POST['religion'] ?? '';
    
    // Handle picture upload
    $picture = $staff['picture'];
    if (isset($_FILES['picture']['name']) && !empty($_FILES['picture']['name'])) {
        $filename = basename($_FILES['picture']['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $newPicture = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/" . $newPicture);
            
            // Delete old picture
            if (!empty($staff['picture']) && file_exists("../uploads/" . $staff['picture'])) {
                unlink("../uploads/" . $staff['picture']);
            }
            $picture = $newPicture;
        }
    }
    
    // Update staff_manage table
    $updateData = [
        'staff_id' => $staffId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'mobile' => $mobile,
        'address_1' => $address,
        'gender' => $gender,
        'date_of_birth' => $dateOfBirth,
        'marrital_status' => $maritalStatus,
        'religion' => $religion,
        'picture' => $picture,
    ];
    
    db_update("staff_manage", $updateData, "randomid = ?", [$randomid]);
    
    // Update school_register table
    db_update("school_register", [
        'username' => $staffId,
        'name' => $firstName . ' ' . $lastName,
        'email' => $email,
        'contact_no' => $phone,
    ], "username = ?", [$staff['staff_id']]);
    
    $_SESSION['success'] = "Staff updated successfully";
    redirect('staff_simple.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff</title>
    <?php include('inc.meta.php'); ?>
    <style>
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .row { display: flex; gap: 15px; flex-wrap: wrap; }
        .col-6 { flex: 1; min-width: 250px; }
        .current-photo { margin-top: 10px; }
        .current-photo img { max-height: 80px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        h2 { margin-top: 0; color: #1B3058; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Staff</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label>Staff ID *</label>
                    <input type="text" name="staff_id" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['staff_id']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['last_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" name="mobile" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['mobile']); ?>">
                </div>
            </div>
            
            <div class="col-6">
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($staff['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($staff['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="text" name="date_of_birth" class="form-control datepicker" 
                           value="<?php echo htmlspecialchars($staff['date_of_birth']); ?>" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label>Marital Status</label>
                    <select name="marital_status" class="form-control">
                        <option value="">Select Status</option>
                        <option value="SINGLE" <?php echo ($staff['marrital_status'] == 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                        <option value="MARRIED" <?php echo ($staff['marrital_status'] == 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                        <option value="DIVORCED" <?php echo ($staff['marrital_status'] == 'DIVORCED') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="WIDOWED" <?php echo ($staff['marrital_status'] == 'WIDOWED') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Religion</label>
                    <input type="text" name="religion" class="form-control" 
                           value="<?php echo htmlspecialchars($staff['religion']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($staff['address_1']); ?></textarea>
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="picture" class="form-control" accept="image/*">
                    <?php if (!empty($staff['picture'])): ?>
                        <div class="current-photo">
                            <strong>Current:</strong><br>
                            <img src="../uploads/<?php echo htmlspecialchars($staff['picture']); ?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" name="update_staff" class="btn btn-primary">Update Staff</button>
            <a href="staff_simple.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include('inc.js.php'); ?>
<script>
$(document).ready(function() {
    $('.datepicker').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        todayHighlight: true
    });
});
</script>
</body>
</html>