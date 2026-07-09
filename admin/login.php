<?php
/**
 * ============================================================================
 * SITE ADMIN LOGIN PAGE - FOR SUPER ADMIN / SITE OWNER ONLY
 * ============================================================================
 * This is a dedicated login page for site administrators (super admins)
 * who manage the entire platform, not individual schools.
 * ============================================================================
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../config.php');

// ============================================================================
// INITIALIZATION
// ============================================================================
$error_message = '';
$success_message = '';

// Redirect if already logged in as admin
if (!empty($_SESSION['userid']) && (int)($_SESSION['usertype'] ?? 0) === 1) {
    redirect(ADMIN_URL);
    exit;
}

// ============================================================================
// LOGIN PROCESSING
// ============================================================================
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $error_message = "Username is required";
    } elseif (empty($password)) {
        $error_message = "Password is required";
    } else {
        // Look for super admin user - check if usertype=1 AND create_by_userid is NULL or 0
        // (Super admin owns themselves, unlike school owners who own individual schools)
        if (strpos($username, '@') !== false) {
            // Login by email
            $user = db_get_row(
                "SELECT * FROM school_register WHERE email = ? AND usertype = ?",
                [$username, 1]
            );
        } else {
            // Login by username
            $user = db_get_row(
                "SELECT * FROM school_register WHERE username = ? AND usertype = ?",
                [$username, 1]
            );
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
                
                // MIGRATION: Convert plain text password to hash on successful login
                if ($passwordValid) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    db_update("school_register", ['password' => $newHash], "id = ?", [$user['id']]);
                    error_log("Migrated password for admin user: " . $user['username']);
                }
            }
            
            // Check status
            $userStatus = $user['status'] ?? '1';
            
            if ($passwordValid) {
                if ($userStatus == '0') {
                    $error_message = "Your account has been disabled. Please contact support.";
                } else {
                    // Set session for super admin
                    $_SESSION['userid'] = $user['id'];
                    $_SESSION['usertype'] = 1;  // Super Admin
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['school_name'] = $user['name'] ?? 'Site Admin';
                    $_SESSION['create_by_userid'] = $user['id'];  // Admin is their own creator
                    $_SESSION['is_super_admin'] = true;  // Flag for super admin
                    
                    // Update last login time
                    db_update("school_register", ['last_login' => date('Y-m-d H:i:s')], "id = ?", [$user['id']]);
                    
                    // Redirect to admin dashboard
                    redirect(ADMIN_URL . 'dashboard.php');
                    exit;
                }
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "This username is not authorized as a site administrator.";
        }
    }
}

$savedUsername = $_COOKIE['admin_username'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Admin Login - BestSchoolPage</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #1B3058;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #764ba2;
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
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
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .admin-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <div class="admin-badge">SITE ADMINISTRATOR</div>
        <h1>Admin Portal</h1>
        <p>Site Owner / Super Admin Login</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input 
                type="text" 
                id="username"
                name="username" 
                placeholder="Enter your admin username or email"
                value="<?php echo htmlspecialchars($_POST['username'] ?? $savedUsername); ?>"
                required 
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                placeholder="Enter your password"
                required
            >
        </div>

        <button type="submit" name="login" class="btn-login">Login to Admin Panel</button>
    </form>

    <div class="login-footer">
        <p>
            Not an admin? <a href="<?php echo SITE_URL; ?>login.php">School Login</a>
        </p>
        <p style="margin-top: 10px; font-size: 12px; color: #999;">
            For super admin access only.<br>
            Individual schools use the main login.
        </p>
    </div>
</div>
</body>
</html>
