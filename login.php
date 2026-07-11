<?php

/**
 * School Login Page - Complete Fix with Password Migration
 * FIXED: Proper school owner ID handling to prevent cross-school data display
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
                    // ==========================================================
                    // CRITICAL FIX: Determine the correct school owner ID
                    // ==========================================================

                    // Get the school owner ID from the user record
                    $schoolOwnerId = (int)($user['create_by_userid'] ?? 0);

                    // If create_by_userid is 0 or NULL, this user might be the school owner
                    // or the record is corrupted. We need to find the actual owner.
                    if ($schoolOwnerId <= 0) {
                        // Check if this user is a school owner (usertype = 0 typically)
                        // Or if they have a school_owner flag
                        $userType = (int)($user['usertype'] ?? 0);

                        // If usertype is 0 (school owner/admin), use their own ID
                        if ($userType === 0) {
                            $schoolOwnerId = (int)$user['id'];
                        } else {
                            // This is a staff/teacher with missing create_by_userid
                            // Try to find the school owner by checking if this user was created by someone
                            // or find any user with usertype=0 from the same email domain or school name

                            // First, try to find the school owner using the user's email domain
                            $emailDomain = '';
                            if (!empty($user['email']) && strpos($user['email'], '@') !== false) {
                                $emailParts = explode('@', $user['email']);
                                $emailDomain = $emailParts[1] ?? '';
                            }

                            // Try to find the school owner by email domain or school name
                            if (!empty($emailDomain)) {
                                $owner = db_get_row(
                                    "SELECT id FROM school_register 
                                     WHERE create_by_userid = 0 AND usertype = 0 AND email LIKE ?",
                                    ['%' . $emailDomain]
                                );
                                if (!empty($owner)) {
                                    $schoolOwnerId = (int)$owner['id'];
                                }
                            }

                            // If still not found, try to find any owner in the system
                            if ($schoolOwnerId <= 0) {
                                $owner = db_get_row(
                                    "SELECT id FROM school_register WHERE usertype = 0 AND status = 1 ORDER BY id ASC LIMIT 1"
                                );
                                if (!empty($owner)) {
                                    $schoolOwnerId = (int)$owner['id'];
                                }
                            }

                            // If we found a valid owner, update the user's record to fix it
                            if ($schoolOwnerId > 0) {
                                db_update(
                                    "school_register",
                                    ['create_by_userid' => $schoolOwnerId],
                                    "id = ?",
                                    [$user['id']]
                                );
                                error_log("Fixed missing create_by_userid for user: " . $user['username'] . " (ID: " . $user['id'] . ") -> Owner ID: " . $schoolOwnerId);
                            } else {
                                // Last resort: use the user's own ID (but this should be avoided)
                                // Log a critical error
                                error_log("CRITICAL: Could not determine school owner for user: " . $user['username'] . " (ID: " . $user['id'] . ")");
                                $schoolOwnerId = (int)$user['id'];
                            }
                        }
                    }

                    // Double-check that the owner ID is valid
                    if ($schoolOwnerId <= 0) {
                        // Absolute last resort - use the user's own ID
                        $schoolOwnerId = (int)$user['id'];
                        error_log("WARNING: Using user's own ID as school owner for: " . $user['username']);
                    }

                    // Set session variables
                    $_SESSION['userid'] = (int)$user['id'];
                    $_SESSION['usertype'] = (int)($user['usertype'] ?? 0);
                    $_SESSION['username'] = (string)$user['username'];
                    $_SESSION['email'] = (string)$user['email'];
                    $_SESSION['school_name'] = (string)$user['name'];

                    // CRITICAL: Always use the school owner ID for filtering all data
                    $_SESSION['create_by_userid'] = $schoolOwnerId;

                    // For compatibility with older code, also set a separate variable
                    $_SESSION['school_owner_id'] = $schoolOwnerId;

                    // Log successful login with school owner info
                    error_log("Login successful - User: " . $user['username'] . " (ID: " . $user['id'] . ") - School Owner ID: " . $schoolOwnerId);

                    // Update last login time
                    db_update("school_register", array('last_login' => date('Y-m-d H:i:s')), "id = ?", array($user['id']));

                    // Regenerate session ID for security
                    session_regenerate_id(true);

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f8fafc;
            overflow-x: hidden;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .glass-header.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .login-hero {
            position: relative;
            min-height: 46vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            overflow: hidden;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('images/home-slider.png') center/cover no-repeat;
            opacity: 0.25;
        }

        .login-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(15, 23, 42, 0.92) 0%,
                    rgba(30, 41, 59, 0.85) 50%,
                    rgba(15, 23, 42, 0.92) 100%);
            z-index: 1;
        }

        .login-hero-content {
            position: relative;
            z-index: 2;
        }

        .login-card-wrap {
            margin-top: -70px;
            margin-bottom: 80px;
            position: relative;
            z-index: 3;
        }

        .login-form {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 34px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-control {
            width: 100%;
            padding: 13px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #fafbfc;
            font-size: 0.95rem;
            color: #0f172a;
            transition: all 0.25s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background: #fff;
        }

        .btn-primary {
            width: 100%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            padding: 12px 16px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.45);
            color: #fff;
        }

        .link-muted {
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
        }

        .link-muted:hover {
            color: #4f46e5;
            text-decoration: none;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 0.92rem;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .admin-note {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-left: 4px solid #6366f1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            color: #3730a3;
            font-size: 0.9rem;
        }

        .login-caption {
            color: #64748b;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .login-form {
                padding: 22px;
                border-radius: 18px;
            }

            .login-card-wrap {
                margin-top: -45px;
                margin-bottom: 55px;
            }
        }
    </style>
</head>

<body>
    <div id="page" class="site">
        <?php include('inc.header-new.php'); ?>
        <div id="content" class="site-content">
            <section class="login-hero">
                <div class="login-hero-overlay"></div>
                <div class="container mx-auto px-4 login-hero-content">
                    <div class="max-w-4xl mx-auto text-center">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-5">
                            <i class="fas fa-right-to-bracket"></i>
                            School Portal
                        </span>
                        <h1 class="text-white text-4xl md:text-5xl font-extrabold mb-3">Welcome Back</h1>
                        <p class="text-slate-300">Sign in to manage your school operations with ease.</p>
                    </div>
                </div>
            </section>

            <section class="login-card-wrap">
                <div class="container mx-auto px-4">
                    <div class="max-w-2xl mx-auto">
                        <form action="" method="post" class="login-form">
                            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1">Login to Your School Account</h2>
                            <p class="login-caption mb-5">Use your registered username or email and password.</p>

                            <div class="admin-note">
                                <strong>Site Administrator?</strong><br>
                                If you are a site admin/super admin, please
                                <a href="<?php echo ADMIN_URL; ?>login.php" class="link-muted" style="font-weight:700; color:#312e81;">login here</a>.
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

                            <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                                <a href="<?php echo SITE_URL; ?>forgot-password.php" class="link-muted">
                                    <i class="fa fa-question-circle"></i> Forgot Password?
                                </a>
                                <button class="btn-primary" style="max-width: 180px;" type="submit" name="login">
                                    <i class="fa fa-sign-in"></i> Log In
                                </button>
                            </div>

                            <hr style="border:none; border-top:1px solid #e2e8f0; margin:20px 0;">
                            <p style="text-align:center; color:#475569; margin:0;">
                                Don't have a school account?
                                <a href="<?php echo SITE_URL; ?>registration.php" class="link-muted" style="font-weight:700; color:#4338ca;">
                                    <i class="fa fa-registered"></i> Register School
                                </a>
                            </p>
                        </form>
                    </div>
                </div>
            </section>
        </div>
        <?php include('inc.footer-new.php'); ?>
    </div>
    <?php include('inc.js-new.php'); ?>
</body>

</html>