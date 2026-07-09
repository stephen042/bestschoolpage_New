<?php
/**
 * School Login Page - Complete Fix with Password Migration
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = array();

// Redirect if already logged in
if (!empty($_SESSION['userid'])) {
    // If super admin (usertype=1), redirect to admin panel
    if ((int)($_SESSION['usertype'] ?? 0) === 1) {
        redirect(ADMIN_URL);
        exit;
    }
    // Otherwise, redirect to school/staff dashboard
    redirect(SKOOL_URL);
    exit;
}

// ============================================================================
// LOGIN PROCESSING
// ============================================================================
$error_message = '';
$success_message = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $error_message = "Username is required";
    } elseif (empty($password)) {
        $error_message = "Password is required";
    } else {
        // Determine if username is email or regular username
        if (strpos($username, '@') !== false) {
            // Login by email
            $user = db_get_row("SELECT * FROM school_register WHERE email = ?", array($username));
        } else {
            // Login by username
            $user = db_get_row("SELECT * FROM school_register WHERE username = ?", array($username));
        }
        
        // Check if user exists
        if (!empty($user)) {
            // Verify password
            $storedPassword = $user['password'] ?? '';
            $passwordValid = false;
            
            // Check if stored password is hashed (starts with $2y$)
            if (strpos($storedPassword, '$2y$') === 0) {
                // Already hashed - use password_verify
                $passwordValid = password_verify($password, $storedPassword);
            } else {
                // Old plain text password - direct comparison
                $passwordValid = ($storedPassword === $password);
                
                // ============================================================
                // MIGRATION: Convert plain text password to hash on successful login
                // ============================================================
                if ($passwordValid) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    db_update("school_register", ['password' => $newHash], "id = ?", array($user['id']));
                    
                    // Optional: Log the migration (for debugging)
                    error_log("Migrated password for user: " . $user['username'] . " (ID: " . $user['id'] . ")");
                }
            }
            
            // Also check status - make sure user is not blocked
            $userStatus = $user['status'] ?? '1';
            
            if ($passwordValid) {
                if ($userStatus == '0') {
                    $error_message = "Your account has been blocked. Please contact the administrator.";
                } else {
                    // Set session
                    $_SESSION['userid'] = $user['id'];
                    $_SESSION['usertype'] = $user['usertype'] ?? 0;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['school_name'] = $user['name'];
                    $sessionCreateBy = (int)($user['create_by_userid'] ?? 0);
                    if ($sessionCreateBy <= 0) {
                        $sessionCreateBy = (int)$user['id'];
                    }
                    $_SESSION['create_by_userid'] = $sessionCreateBy;
                    
                    // Update last login time
                    db_update("school_register", array('last_login' => date('Y-m-d H:i:s')), "id = ?", array($user['id']));
                    
                    redirect(SKOOL_URL);
                    exit;
                }
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "Username or email does not exist in our records.";
        }
    }
}

// Get saved username from cookie if exists
$savedUsername = $_COOKIE['school_username'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta-new.php'); ?>
    <style>
        .login-part {
            padding: 80px 0;
            min-height: 500px;
        }
        .login-form {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .login-form h4 {
            color: #1B3058;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-primary {
            background: #1B3058;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }
        .btn-primary:hover {
            background: #f21151;
        }
        .here a {
            color: #1B3058;
            text-decoration: none;
        }
        .here a:hover {
            color: #f21151;
        }
        .reva {
            text-align: center;
            margin-top: 20px;
        }
        .rgstrscl a {
            color: #f21151;
            text-decoration: none;
            font-weight: bold;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        @media (max-width: 768px) {
            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
        <section class="login-part">
            <div class="container">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <form action="" method="post" class="login-form">
                            <h4>Login to Your School Account</h4>
                            
                            <div style="background: #e7f3ff; padding: 12px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
                                <strong style="color: #1565c0;">👤 Site Administrator?</strong>
                                <p style="font-size: 13px; color: #1565c0; margin-top: 5px;">
                                    If you're a site admin/super admin, please <a href="<?php echo ADMIN_URL; ?>login.php" style="color: #0d47a1; font-weight: bold; text-decoration: none;">login here</a>.
                                </p>
                            </div>
                            
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <input autocomplete="off" class="form-control" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? $savedUsername); ?>" 
                                       placeholder="Username or Email Address *" type="text" required>
                            </div>
                            
                            <div class="form-group">
                                <input autocomplete="off" class="form-control" name="password" 
                                       placeholder="Password *" type="password" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="here">
                                            <a href="<?php echo SITE_URL; ?>forgot-password.php">
                                                <i class="fa fa-question-circle"></i> Forgot Password?
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <button class="btn-primary" type="submit" name="login">
                                            <i class="fa fa-sign-in"></i> Log In
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="reva">
                                            Don't have a school account? 
                                            <span class="rgstrscl">
                                                <a href="<?php echo SITE_URL; ?>registration.php">
                                                    <i class="fa fa-registered"></i> Register School
                                                </a>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </section>
    </div>
    <?php include('inc.footer-new.php'); ?>
</div>
<?php include('inc.js-new.php'); ?>
</body>
</html>