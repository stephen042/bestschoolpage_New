<?php
/**
 * Change Password Page - Admin/School Users
 * Rebuilt for PHP 8.x with secure password hashing
 */

require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "Change Password";
$FileName = 'login_pass.php';

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
// PASSWORD VALIDATION HELPER
// ============================================================================
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

// ============================================================================
// UPDATE PASSWORD
// ============================================================================
if (isset($_POST['update'])) {
    $currentPass = $_POST['current_pass'] ?? '';
    $newPass = $_POST['new_pass'] ?? '';
    $confirmPass = $_POST['renew_pass'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($currentPass)) {
        $errors[] = "Current password is required";
    }
    if (empty($newPass)) {
        $errors[] = "New password is required";
    }
    if (empty($confirmPass)) {
        $errors[] = "Please confirm your new password";
    }
    
    // Password strength validation
    $strengthErrors = validatePasswordStrength($newPass);
    $errors = array_merge($errors, $strengthErrors);
    
    if (empty($errors)) {
        // Get current user's password hash from database
        $storedHash = db_get_val(
            "SELECT password FROM admin_login WHERE id = ?",
            [$adminId]
        );
        
        if (!empty($storedHash)) {
            // Verify current password (supports both old plain text and new hashed)
            $passwordValid = false;
            
            // Check if stored password is hashed (starts with $2y$)
            if (strpos($storedHash, '$2y$') === 0) {
                // New hashed password
                $passwordValid = password_verify($currentPass, $storedHash);
            } else {
                // Old plain text password (for backward compatibility)
                $passwordValid = ($storedHash === $currentPass);
            }
            
            if ($passwordValid) {
                if ($newPass === $confirmPass) {
                    // Hash the new password
                    $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);
                    
                    // Update password
                    db_update(
                        "admin_login",
                        ['password' => $hashedPassword],
                        "id = ?",
                        [$adminId]
                    );
                    
                    $_SESSION['success'] = "Password changed successfully!";
                    redirect(ADMIN_URL . $FileName);
                } else {
                    $stat['error'] = "Confirm password does not match";
                }
            } else {
                $stat['error'] = "Current password is incorrect";
            }
        } else {
            $stat['error'] = "User not found";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .password-requirements {
            background: #f9f9f9;
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 5px;
            font-size: 12px;
        }
        .password-requirements ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        .password-requirements li {
            color: #999;
            margin-bottom: 3px;
        }
        .password-requirements li.valid {
            color: #5cb85c;
            text-decoration: line-through;
        }
        .input-group {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            color: #999;
        }
        .toggle-password:hover {
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
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
                    <div class="col-md-6 col-md-offset-3">
                        <div class="card-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title text-center">
                                        <i class="fa fa-key"></i> Change Your Password
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <form role="form" action="" method="post">
                                        <div class="form-group">
                                            <label for="current_pass">Current Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control required" id="current_pass" name="current_pass" 
                                                       autocomplete="off" placeholder="Enter current password">
                                                <span class="fa fa-eye-slash toggle-password" data-target="current_pass"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="new_pass">New Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control required" id="new_pass" name="new_pass" 
                                                       autocomplete="off" placeholder="Enter new password">
                                                <span class="fa fa-eye-slash toggle-password" data-target="new_pass"></span>
                                            </div>
                                            <div class="password-requirements">
                                                <strong>Password Requirements:</strong>
                                                <ul id="passwordRequirements">
                                                    <li id="req-length">✓ At least 6 characters</li>
                                                    <li id="req-upper">✓ At least one uppercase letter (A-Z)</li>
                                                    <li id="req-lower">✓ At least one lowercase letter (a-z)</li>
                                                    <li id="req-number">✓ At least one number (0-9)</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="renew_pass">Confirm New Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control required" id="renew_pass" name="renew_pass" 
                                                       autocomplete="off" placeholder="Confirm new password">
                                                <span class="fa fa-eye-slash toggle-password" data-target="renew_pass"></span>
                                            </div>
                                            <span id="match-message" class="help-block"></span>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" name="update" class="btn btn-primary btn-block">
                                                <i class="fa fa-save"></i> Update Password
                                            </button>
                                            <a href="<?= ADMIN_URL ?>" class="btn btn-default btn-block">
                                                <i class="fa fa-arrow-left"></i> Back to Dashboard
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Security Tips:</strong>
                                <ul style="margin: 10px 0 0 20px;">
                                    <li>Never share your password with anyone</li>
                                    <li>Use a unique password for this account</li>
                                    <li>Change your password regularly</li>
                                </ul>
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
// Password strength checker
document.getElementById('new_pass').addEventListener('keyup', function() {
    var password = this.value;
    
    // Check length
    if (password.length >= 6) {
        document.getElementById('req-length').classList.add('valid');
    } else {
        document.getElementById('req-length').classList.remove('valid');
    }
    
    // Check uppercase
    if (/[A-Z]/.test(password)) {
        document.getElementById('req-upper').classList.add('valid');
    } else {
        document.getElementById('req-upper').classList.remove('valid');
    }
    
    // Check lowercase
    if (/[a-z]/.test(password)) {
        document.getElementById('req-lower').classList.add('valid');
    } else {
        document.getElementById('req-lower').classList.remove('valid');
    }
    
    // Check number
    if (/[0-9]/.test(password)) {
        document.getElementById('req-number').classList.add('valid');
    } else {
        document.getElementById('req-number').classList.remove('valid');
    }
});

// Password match checker
function checkPasswordMatch() {
    var newPass = document.getElementById('new_pass').value;
    var confirmPass = document.getElementById('renew_pass').value;
    var matchMsg = document.getElementById('match-message');
    
    if (confirmPass.length > 0) {
        if (newPass === confirmPass) {
            matchMsg.innerHTML = '<span style="color:green"><i class="fa fa-check-circle"></i> Passwords match!</span>';
            matchMsg.className = 'help-block text-success';
        } else {
            matchMsg.innerHTML = '<span style="color:red"><i class="fa fa-times-circle"></i> Passwords do not match!</span>';
            matchMsg.className = 'help-block text-danger';
        }
    } else {
        matchMsg.innerHTML = '';
    }
}

document.getElementById('new_pass').addEventListener('keyup', checkPasswordMatch);
document.getElementById('renew_pass').addEventListener('keyup', checkPasswordMatch);

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(function(element) {
    element.addEventListener('click', function() {
        var targetId = this.getAttribute('data-target');
        var input = document.getElementById(targetId);
        
        if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        } else {
            input.type = 'password';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        }
    });
});

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>
</body>
</html>