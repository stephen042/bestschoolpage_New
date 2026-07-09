<?php  
/**
 * Parent/Student Profile Page - Rebuilt for PHP 8.x
 * Update profile information and change password
 */

require_once '../config.php'; 
require_once 'inc.session-create.php'; 

$PageTitle = "Update Profile";
$FileName = 'login_profile.php';
$iClassName = PARENT_URL;

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$userId = $_SESSION['userid'] ?? 0;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// FILE UPLOAD HANDLER
// ============================================================================
function handleLogoUpload($file, $oldLogo = '') {
    if (isset($file['name']) && !empty($file['name'])) {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $newFilename = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $uploadPath = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                if (!empty($oldLogo) && file_exists("../uploads/" . $oldLogo)) {
                    unlink("../uploads/" . $oldLogo);
                }
                return $newFilename;
            }
        }
    }
    return $oldLogo;
}

// ============================================================================
// UPDATE PROFILE
// ============================================================================
if (isset($_POST['update'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $oldLogo = $_POST['logo_old'] ?? '';
    
    $errors = [];
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    
    if (empty($errors)) {
        $newLogo = handleLogoUpload($_FILES['logo'] ?? [], $oldLogo);
        
        $data = [
            'first_name' => $username,
            'email' => $email,
            'phone' => $phone,
            'logo' => $newLogo,
        ];
        
        db_update("student_guardian", $data, "id = ?", [$userId]);
        $_SESSION['success'] = "Profile updated successfully";
        redirect($iClassName . $FileName);
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// GET USER DATA
// ============================================================================
$userData = db_get_row("SELECT * FROM student_guardian WHERE id = ?", [$userId]);

if (empty($userData)) {
    $_SESSION['error'] = "User not found";
    redirect(PARENT_URL);
}

// Set default values for display
$userData['logo'] = $userData['logo'] ?? '';
$userData['first_name'] = $userData['first_name'] ?? '';
$userData['email'] = $userData['email'] ?? '';
$userData['phone'] = $userData['phone'] ?? '';
$userData['last_login'] = $userData['last_login'] ?? 'Never';
$userData['create_at'] = $userData['create_at'] ?? date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Droid+Serif" />
    <?php include('inc.meta.php'); ?>
    <style>
        body, label, span, a, .gwt-Button {
            font-family: 'Droid Serif' !important;
        }
        .circle img {
            width: 150px;
            border-radius: 80px;
            height: 150px;
            margin-bottom: 20px;
            object-fit: cover;
            border: 3px solid #1565c0;
        }
        .ac h1 { color: #1565c0; }
        .up h4 { color: #1565c0; font-weight: 700; }
        .divider {
            width: 100%;
            height: 2px;
            overflow: hidden;
            background-color: #e0e0e0;
        }
        .ur {
            font-weight: bold;
            color: black;
            margin-bottom: 20px;
            padding: 10px;
        }
        .ar {
            margin-bottom: 20px;
            padding: 10px;
        }
        .hh { padding: 25px; }
        .sss .btn-flat, .fade .btn-flat .btn-large {
            letter-spacing: normal !important;
            padding: 0 1rem !important;
            line-height: 33px;
            height: 33px;
        }
        .sss .btn-flat {
            border: none;
            border-radius: 2px;
            display: inline-block;
            height: 36px;
            line-height: 36px;
            outline: 0;
            padding: 0 2rem;
            vertical-align: middle;
            background-color: transparent;
            color: #1565c0;
            cursor: pointer;
            box-shadow: none;
        }
        .sss .btn-flat .fa {
            font-size: 16px;
            margin-right: 5px;
        }
        .sss .btn {
            text-decoration: none;
            color: #fff;
            background: #1565c0;
            text-align: center;
            letter-spacing: .5px;
            transition: .2s ease-out;
            cursor: pointer;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
        }
        .sss .btn:hover {
            background-color: #0e4a8a;
            color: white !important;
        }
        .fade input[type=text], 
        .fade input[type=email], 
        .fade input[type=password] {
            background-color: transparent;
            border: none;
            border-bottom: 1px solid #9e9e9e;
            border-radius: 0;
            outline: none;
            height: 3rem;
            width: 100%;
            font-size: 15px;
            margin: 0 0 0px 0;
            padding: 0;
            box-shadow: none;
            transition: all .3s;
        }
        .fade input:focus {
            border-bottom: 1px solid #1565c0;
            box-shadow: 0 1px 0 0 #1565c0;
        }
        .fade .modal-dialog {
            width: 350px !important;
            margin: 30px auto;
        }
        .fade .modal-content {
            padding: 20px;
        }
        .modal-footer {
            border-top: 1px solid #e5e5e5;
            padding: 15px;
            text-align: right;
        }
        .btn-flat.red-text { color: #f44336 !important; }
        .btn-flat.teal-text { color: #009688 !important; }
        .edit-icon {
            cursor: pointer;
            color: #1565c0;
            margin-left: 10px;
        }
        .edit-icon:hover { color: #f21151; }
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
                        <h4 class="page-title">PROFILE ACCOUNT</h4>
                        <?= showMessage($stat) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="sectionza">
                        <div class="col-md-12 col-xm-12">
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="col-md-12 up">
                                    <h4>Update account information
                                        <i class="fa fa-edit edit-icon" onclick="displayBlock();"></i>
                                    </h4>
                                </div>
                                
                                <div class="col-md-12 circle text-center">
                                    <img src="../uploads/<?= e($userData['logo']) ?>" alt="Profile Image" id="profileImage">
                                    <input id="changelogo" style="display:none; margin-top: 10px;" type="file" name="logo" accept="image/*">
                                    <input type="hidden" name="logo_old" value="<?= e($userData['logo']) ?>">
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="divider"></div>
                                </div>
                                
                                <div class="hh">
                                    <!-- Username -->
                                    <div class="row">
                                        <div class="col-md-5 ur col-xm-12">Username</div>
                                        <div id="username1" style="display:block;" class="col-md-7 ar col-xm-12">
                                            <span><?= e($userData['first_name']) ?></span>
                                        </div>
                                        <div id="username2" style="display:none;" class="col-md-7 ar col-xm-12">
                                            <input type="text" name="username" value="<?= e($userData['first_name']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="row">
                                        <div class="col-md-5 ur col-xm-12">Email Address</div>
                                        <div id="email1" style="display:block;" class="col-md-7 ar col-xm-12">
                                            <span><?= e($userData['email']) ?></span>
                                        </div>
                                        <div id="email2" style="display:none;" class="col-md-7 ar col-xm-12">
                                            <input type="email" name="email" value="<?= e($userData['email']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <!-- Phone -->
                                    <div class="row">
                                        <div class="col-md-5 ur col-xm-12">Phone No</div>
                                        <div id="contact1" style="display:block;" class="col-md-7 ar col-xm-12">
                                            <span><?= e($userData['phone']) ?></span>
                                        </div>
                                        <div id="contact2" style="display:none;" class="col-md-7 ar col-xm-12">
                                            <input type="text" name="phone" value="<?= e($userData['phone']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <!-- Last Login -->
                                    <div id="lastlogin" style="display:block;" class="row">
                                        <div class="col-md-5 ur col-xm-12">Last Login</div>
                                        <div class="col-md-7 ar col-xm-12">
                                            <span><?= e(date('d M Y H:i:s', strtotime($userData['last_login']))) ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Date Registered -->
                                    <div id="lastregis" style="display:block;" class="row">
                                        <div class="col-md-5 ur col-xm-12">Date Registered</div>
                                        <div class="col-md-7 ar col-xm-12">
                                            <span><?= e(date('d M Y', strtotime($userData['create_at']))) ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="row sss">
                                        <div class="col-md-6">
                                            <button type="button" class="btn-flat" data-toggle="modal" data-target="#passwordModal">
                                                <i class="fa fa-shield" aria-hidden="true"></i> Change Password
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" name="update" id="updatebtn" style="display:none;" class="btn">
                                                <i class="fa fa-save"></i> Update Profile
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="passwordModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Change Password</h4>
            </div>
            <div class="modal-body">
                <div id="passwordError" class="alert" style="display:none;"></div>
                <div class="form-group">
                    <label>Old Password</label>
                    <input type="password" class="form-control" id="old_password" placeholder="Enter old password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" class="form-control" id="new_password" placeholder="Enter new password">
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" placeholder="Confirm new password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="changePassword();">Change Password</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php include('inc.js.php'); ?>

<script>
function displayBlock() {
    document.getElementById("username2").style.display = "block";
    document.getElementById("username1").style.display = "none";
    document.getElementById("email1").style.display = "none";
    document.getElementById("email2").style.display = "block";
    document.getElementById("contact1").style.display = "none";
    document.getElementById("contact2").style.display = "block";
    document.getElementById("updatebtn").style.display = "block";
    document.getElementById("lastlogin").style.display = "none";
    document.getElementById("lastregis").style.display = "none";
    document.getElementById("changelogo").style.display = "block";
}

function changePassword() {
    var oldPassword = document.getElementById("old_password").value;
    var newPassword = document.getElementById("new_password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    var errorDiv = document.getElementById("passwordError");
    
    if (!oldPassword || !newPassword || !confirmPassword) {
        errorDiv.style.display = "block";
        errorDiv.className = "alert alert-danger";
        errorDiv.innerHTML = "Please fill in all password fields";
        return;
    }
    
    if (newPassword.length < 6) {
        errorDiv.style.display = "block";
        errorDiv.className = "alert alert-danger";
        errorDiv.innerHTML = "New password must be at least 6 characters";
        return;
    }
    
    if (newPassword !== confirmPassword) {
        errorDiv.style.display = "block";
        errorDiv.className = "alert alert-danger";
        errorDiv.innerHTML = "New passwords do not match";
        return;
    }
    
    $.ajax({
        url: "ajax.php",
        type: "POST",
        data: {
            action: "Action_changepass",
            old_password: oldPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        },
        success: function(data) {
            if (data == "1") {
                errorDiv.style.display = "block";
                errorDiv.className = "alert alert-success";
                errorDiv.innerHTML = "Password changed successfully!";
                document.getElementById("passwordModal").reset();
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                errorDiv.style.display = "block";
                errorDiv.className = "alert alert-danger";
                errorDiv.innerHTML = data;
            }
        },
        error: function() {
            errorDiv.style.display = "block";
            errorDiv.className = "alert alert-danger";
            errorDiv.innerHTML = "An error occurred. Please try again.";
        }
    });
}

// Image preview on file select
document.getElementById('changelogo').addEventListener('change', function(e) {
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('profileImage').src = e.target.result;
    }
    reader.readAsDataURL(this.files[0]);
});
</script>

<?php
function showMessage($stat = []) {
    if (empty($stat)) return '';
    return msg($stat);
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
</body>
</html>