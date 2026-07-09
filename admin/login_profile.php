<?php  
require_once '../config.php'; 
require_once 'inc.session-create.php'; 

$PageTitle = "Update Profile";
$FileName = 'login_profile.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$adminId = $_SESSION['userid'] ?? 0;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// FILE UPLOAD HANDLER
// ============================================================================
function handleProfileImageUpload($file, $oldImage = '') {
    if (isset($file['name']) && !empty($file['name'])) {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $newFilename = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $uploadPath = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old image if it exists and is not default
                if (!empty($oldImage) && file_exists("../uploads/" . $oldImage)) {
                    unlink("../uploads/" . $oldImage);
                }
                return $newFilename;
            }
        }
    }
    return $oldImage;
}

// ============================================================================
// UPDATE PROFILE
// ============================================================================
if (isset($_POST['update'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['emailid'] ?? '');
    $mobile = trim($_POST['mobile_no'] ?? '');
    $oldImage = $_POST['company_logo_old'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    if (empty($mobile)) {
        $errors[] = "Mobile number is required";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $mobile)) {
        $errors[] = "Please enter a valid mobile number (10-15 digits)";
    }
    
    if (empty($errors)) {
        // Check if email already exists for another user
        $existingEmail = db_get_val(
            "SELECT id FROM admin_login WHERE emailid = ? AND id != ?",
            [$email, $adminId]
        );
        
        if (!empty($existingEmail)) {
            $stat['error'] = "This email address is already registered";
        } else {
            // Handle image upload
            $newImage = handleProfileImageUpload($_FILES['company_logo'] ?? [], $oldImage);
            
            // Update profile
            $data = [
                'fullname' => $fullname,
                'emailid' => $email,
                'mobile_no' => $mobile,
                'profileimage' => $newImage,
            ];
            
            db_update("admin_login", $data, "id = ?", [$adminId]);
            
            // Update session with new name
            $_SESSION['fullname'] = $fullname;
            $_SESSION['success'] = "Profile updated successfully";
            redirect(ADMIN_URL . $FileName);
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// GET CURRENT USER DATA
// ============================================================================
$userData = db_get_row("SELECT * FROM admin_login WHERE id = ?", [$adminId]);

if (empty($userData)) {
    $_SESSION['error'] = "User not found";
    redirect(ADMIN_URL);
}

// Set default values if fields are missing
$userData['fullname'] = $userData['fullname'] ?? '';
$userData['emailid'] = $userData['emailid'] ?? '';
$userData['mobile_no'] = $userData['mobile_no'] ?? '';
$userData['profileimage'] = $userData['profileimage'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .profile-image-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
            margin-top: 10px;
        }
        .current-image {
            margin-top: 10px;
        }
        .image-help {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
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
                    <div class="col-md-8 col-md-offset-2">
                        <div class="card-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title text-center">
                                        <i class="fa fa-user-circle"></i> Edit Your Profile
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <form role="form" action="" method="post" enctype="multipart/form-data">
                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="fullname">Full Name <span class="text-danger">*</span></label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control required" id="fullname" name="fullname" 
                                                       value="<?= e($userData['fullname']) ?>" placeholder="Enter your full name">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="emailid">Email ID <span class="text-danger">*</span></label>
                                            <div class="col-lg-9">
                                                <input type="email" class="form-control required" id="emailid" name="emailid" 
                                                       value="<?= e($userData['emailid']) ?>" placeholder="Enter your email address">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="mobile_no">Mobile Number <span class="text-danger">*</span></label>
                                            <div class="col-lg-9">
                                                <input type="tel" class="form-control required" name="mobile_no" id="mobile_no" 
                                                       value="<?= e($userData['mobile_no']) ?>" placeholder="Enter your mobile number">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="company_logo">Profile Image</label>
                                            <div class="col-lg-9">
                                                <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                                                <input type="hidden" name="company_logo_old" value="<?= e($userData['profileimage']) ?>">
                                                
                                                <?php if (!empty($userData['profileimage'])): ?>
                                                    <div class="current-image">
                                                        <strong>Current Image:</strong><br>
                                                        <img src="<?= SITE_URL ?>uploads/<?= e($userData['profileimage']) ?>" 
                                                             class="profile-image-preview" id="imagePreview">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="current-image">
                                                        <img src="<?= SITE_URL ?>images/default-avatar.png" 
                                                             class="profile-image-preview" id="imagePreview">
                                                    </div>
                                                <?php endif; ?>
                                                <p class="image-help">
                                                    <i class="fa fa-info-circle"></i> 
                                                    Allowed formats: JPG, PNG, JPEG, GIF. Max size: 2MB
                                                </p>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <div class="col-lg-offset-3 col-lg-9">
                                                <button type="submit" name="update" class="btn btn-primary">
                                                    <i class="fa fa-save"></i> Update Profile
                                                </button>
                                                <a href="<?= ADMIN_URL ?>" class="btn btn-default">
                                                    <i class="fa fa-arrow-left"></i> Back to Dashboard
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Note:</strong> To change your password, please go to 
                                <a href="<?= ADMIN_URL ?>login_pass.php">Change Password</a> page.
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

<script>
// Image preview on file select
document.getElementById('company_logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Phone number validation
document.getElementById('mobile_no').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
});

// Email validation on blur
document.getElementById('emailid').addEventListener('blur', function(e) {
    const email = this.value;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailPattern.test(email)) {
        this.style.borderColor = 'red';
    } else {
        this.style.borderColor = '#ddd';
    }
});
</script>

<style>
.profile-image-preview {
    max-width: 100px;
    max-height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ddd;
    margin-top: 10px;
}
.current-image {
    margin-top: 10px;
}
.image-help {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}
</style>
</body>
</html>