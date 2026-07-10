<?php
/**
 * Parent Portal Login Page - SUPPORTS BOTH PLAIN TEXT AND HASHED PASSWORDS
 * - Queries student_guardian table (where parent accounts are stored)
 * - Checks if password is hashed (starts with $2y$) - uses password_verify
 * - If not hashed, compares plain text (for backward compatibility)
 * - Automatically upgrades plain text passwords to hashed on successful login
 */

require_once '../config.php';

$stat = [];

// ============================================================================
// LOGIN PROCESSING
// ============================================================================
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username/Email is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        // Query student_guardian for parent login credentials
        if (strpos($username, '@') !== false) {
            // Login by email (can have duplicates across legacy data)
            $users = db_get_rows(
                "SELECT * FROM student_guardian WHERE email = ? AND type = 1 ORDER BY status DESC, id DESC",
                [$username]
            ) ?: [];
        } else {
            // Login by parent identifier with backward compatibility:
            // - current parent_id
            // - legacy student_id_str alias
            // - manage_student parent-family mappings
            // Also allow numeric guardian row id as a fallback login identifier.
            $lookupParams = [
                $username,
                $username,
                $username,
                $username,
                $username,
                $username,
                $username,
                $username
            ];
            $idClause = '';
            $normalizedUsername = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $username));

            if (ctype_digit($username)) {
                $idClause = ' OR id = ?';
                $lookupParams[] = (int)$username;
            }

            // Canonical match: ignore spaces and common separators in parent IDs.
            if ($normalizedUsername !== '') {
                $idClause .= ' OR UPPER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(parent_id), " ", ""), "-", ""), "/", ""), "_", "")) = ?';
                $idClause .= ' OR UPPER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(student_id_str), " ", ""), "-", ""), "/", ""), "_", "")) = ?';
                $lookupParams[] = $normalizedUsername;
                $lookupParams[] = $normalizedUsername;
            }

            $users = db_get_rows(
                "SELECT * FROM student_guardian
                 WHERE type = 1
                   AND (
                        parent_id = ?
                        OR TRIM(parent_id) = TRIM(?)
                                                OR LOWER(TRIM(parent_id)) = LOWER(TRIM(?))
                                                OR student_id_str = ?
                                                OR TRIM(student_id_str) = TRIM(?)
                                                OR LOWER(TRIM(student_id_str)) = LOWER(TRIM(?))
                                                OR parent_id IN (
                                                        SELECT DISTINCT ms.parent_id
                                                            FROM manage_student ms
                                                         WHERE ms.parent_id = ? OR ms.student_id = ?
                                                )" . $idClause . "
                   )
                 ORDER BY status DESC, id DESC",
                $lookupParams
            ) ?: [];
        }

        // Check if any candidate user exists
        if (!empty($users)) {
            $matchedUser = null;
            $shouldUpgrade = false;

            foreach ($users as $candidate) {
                $storedPassword = (string)($candidate['password'] ?? '');
                if ($storedPassword === '') {
                    continue;
                }

                $valid = false;
                $upgradeThis = false;

                // BCrypt hash
                if (preg_match('/^\$2[ayb]\$/', $storedPassword)) {
                    $valid = password_verify($password, $storedPassword);
                }
                // Legacy MD5 hash
                elseif (preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
                    $valid = hash_equals(strtolower($storedPassword), md5($password));
                    $upgradeThis = $valid;
                }
                // Legacy SHA1 hash
                elseif (preg_match('/^[a-f0-9]{40}$/i', $storedPassword)) {
                    $valid = hash_equals(strtolower($storedPassword), sha1($password));
                    $upgradeThis = $valid;
                }
                // Plain text fallback
                else {
                    $valid = hash_equals($storedPassword, $password);
                    $upgradeThis = $valid;
                }

                if ($valid) {
                    $matchedUser = $candidate;
                    $shouldUpgrade = $upgradeThis;
                    break;
                }
            }

            // Legacy fallback: when parent_id rows are duplicated with stale passwords,
            // retry against related rows that share the same email.
            if (empty($matchedUser) && strpos($username, '@') === false) {
                $emails = [];
                foreach ($users as $u) {
                    $emailVal = trim((string)($u['email'] ?? ''));
                    if ($emailVal !== '') {
                        $emails[$emailVal] = true;
                    }
                }

                if (count($emails) === 1) {
                    $sharedEmail = array_key_first($emails);
                    $emailUsers = db_get_rows(
                        "SELECT * FROM student_guardian WHERE email = ? AND type = 1 ORDER BY status DESC, id DESC",
                        [$sharedEmail]
                    ) ?: [];

                    foreach ($emailUsers as $candidate) {
                        $storedPassword = (string)($candidate['password'] ?? '');
                        if ($storedPassword === '') {
                            continue;
                        }

                        $valid = false;
                        $upgradeThis = false;

                        if (preg_match('/^\$2[ayb]\$/', $storedPassword)) {
                            $valid = password_verify($password, $storedPassword);
                        } elseif (preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
                            $valid = hash_equals(strtolower($storedPassword), md5($password));
                            $upgradeThis = $valid;
                        } elseif (preg_match('/^[a-f0-9]{40}$/i', $storedPassword)) {
                            $valid = hash_equals(strtolower($storedPassword), sha1($password));
                            $upgradeThis = $valid;
                        } else {
                            $valid = hash_equals($storedPassword, $password);
                            $upgradeThis = $valid;
                        }

                        if ($valid) {
                            $matchedUser = $candidate;
                            $shouldUpgrade = $upgradeThis;
                            break;
                        }
                    }
                }
            }

            if (!empty($matchedUser)) {
                // Upgrade legacy/plain password to modern hash if needed.
                if ($shouldUpgrade && !empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    db_update(
                        "student_guardian",
                        ['password' => $hashedPassword],
                        "id = ?",
                        [$matchedUser['id']]
                    );
                }

                // Check account status
                if (($matchedUser['status'] ?? '0') == '1') {
                    // Set session variables
                    $_SESSION['userid'] = $matchedUser['id'];
                    $_SESSION['usertype'] = '2'; // Parent type
                    $_SESSION['parent_id'] = $matchedUser['parent_id'];
                    $_SESSION['email'] = $matchedUser['email'] ?? '';
                    $_SESSION['fullname'] = trim(
                        ($matchedUser['title'] ?? '') . ' ' .
                            ($matchedUser['first_name'] ?? '') . ' ' .
                            ($matchedUser['last_name'] ?? '')
                    );

                    // Also store in school_register style format for compatibility
                    $_SESSION['username'] = $matchedUser['parent_id'];

                    redirect('dashboard.php');
                } else {
                    $stat['error'] = 'Your account is inactive. Please contact the administrator.';
                }
            } else {
                if (strpos($username, '@') === false) {
                    error_log('Parent login password mismatch for parent identifier: ' . $username . ' | candidates=' . count($users));
                }
                $stat['error'] = 'Invalid password. Please try again.';
            }
        } else {
            $stat['error'] = 'Parent login ID not found. Please check your credentials.';
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// GET SLIDER IMAGES
// ============================================================================
$sliders = db_get_rows("SELECT * FROM plogin_slider ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../inc.meta-new.php'); ?>
    <title>Parent Portal - School Management System</title>
    <meta name="robots" content="NOINDEX, NOFOLLOW">

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

        .parent-hero {
            position: relative;
            min-height: 46vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #2c5f21 0%, #37733b 50%, #1d4e1b 100%);
            overflow: hidden;
        }

        .parent-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('../uploads/<?= e($sliders[0]['image'] ?? '') ?>') center/cover no-repeat;
            opacity: 0.25;
        }

        .parent-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(20, 42, 15, 0.92) 0%,
                    rgba(30, 59, 38, 0.85) 50%,
                    rgba(20, 42, 15, 0.92) 100%);
            z-index: 1;
        }

        .parent-hero-content {
            position: relative;
            z-index: 2;
        }

        .parent-card-wrap {
            margin-top: -70px;
            margin-bottom: 80px;
            position: relative;
            z-index: 3;
        }

        .parent-form {
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

        .input-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-signin {
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

        .btn-signin:hover {
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

        .login-caption {
            color: #64748b;
            font-size: 0.95rem;
        }

        .tip-box {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-left: 4px solid #6366f1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            color: #3730a3;
            font-size: 0.9rem;
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

        .toggle-eye {
            border: 0;
            background: transparent;
            color: #6b7280;
            padding: 0 6px;
            cursor: pointer;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .parent-form {
                padding: 22px;
                border-radius: 18px;
            }

            .parent-card-wrap {
                margin-top: -45px;
                margin-bottom: 55px;
            }
        }
    </style>
</head>

<body>
    <div id="page" class="site">
        <?php include('../inc.header-new.php'); ?>
        <div id="content" class="site-content">
            <section class="parent-hero">
                <div class="parent-hero-overlay"></div>
                <div class="container mx-auto px-4 parent-hero-content">
                    <div class="max-w-4xl mx-auto text-center">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-5">
                            <i class="fas fa-user-group"></i>
                            Parent Portal
                        </span>
                        <h1 class="text-white text-4xl md:text-5xl font-extrabold mb-3">Welcome Parent</h1>
                        <p class="text-slate-300">Sign in to access your child's reports and school updates.</p>
                    </div>
                </div>
            </section>

            <section class="parent-card-wrap">
                <div class="container mx-auto px-4">
                    <div class="max-w-2xl mx-auto">
                        <div class="parent-form">
                            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1">Parent Portal Login</h2>
                            <p class="login-caption mb-5">Use your Parent ID or registered email and password.</p>

                            <div class="tip-box">
                                <strong>Tip:</strong> Parent ID <span style="font-weight:700;">INPUT THEM BELOW</span>.
                            </div>

                            <?= showMessage($stat) ?>

                            <form id="login-add" method="post" action="">
                                <div class="form-group">
                                    <input type="text" name="username" placeholder="Parent ID or Email"
                                        class="form-control" value="<?= e($_POST['username'] ?? '') ?>"
                                        autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <div class="input-wrap">
                                        <input id="password-field" type="password" name="password" placeholder="Password"
                                            class="form-control" required>
                                        <button type="button" id="toggle-password" class="toggle-eye" aria-label="Show or hide password">
                                            <i class="fa fa-eye" id="eye_icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                                    <a href="forgot_password.php" class="link-muted">
                                        <i class="fa fa-question-circle"></i> Forgot Password?
                                    </a>
                                    <button type="submit" name="login" class="btn-signin" style="max-width:180px;">
                                        Sign In <i class="fa fa-sign-in"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php include('../inc.footer-new.php'); ?>
    </div>

    <script>
        (function() {
            var header = document.querySelector('.glass-header');
            if (header) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                });
            }

            var toggle = document.getElementById('toggle-password');
            var passwordField = document.getElementById('password-field');
            var eyeIcon = document.getElementById('eye_icon');
            if (toggle && passwordField && eyeIcon) {
                toggle.addEventListener('click', function() {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        passwordField.type = 'password';
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }
        })();
    </script>
</body>

</html>