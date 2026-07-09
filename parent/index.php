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
                $username, $username, $username,
                $username, $username, $username,
                $username, $username
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
    <title>Parent Portal - School Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="NOINDEX, NOFOLLOW">
    
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        @-moz-keyframes Gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes Gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .carousel-indicators { display: none; }
        .carousel-inner > .item > img,
        .carousel-inner > .item > a > img {
            height: 607px;
            object-fit: cover;
            width: 100%;
        }
        
        .form-group { margin-bottom: 20px !important; }
        .input-group-icon, .input-search { width: 100%; table-layout: fixed; }
        .mr-xs { margin-right: 5px; }
        .input-group {
            position: relative;
            display: table;
            border-collapse: separate;
            border: none;
            border-bottom: 1px solid #DDD;
        }
        .input-group-addon, .input-group-btn, .input-group .form-control { display: table-cell; }
        .input-group .form-control {
            position: relative;
            z-index: 2;
            float: left;
            width: 100%;
            margin-bottom: 0;
            background: none;
            font-size: 16px;
            border: none;
            box-shadow: none;
        }
        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #1f5a99;
        }
        .input-group-addon {
            padding: 6px 12px;
            font-size: 14px;
            font-weight: normal;
            line-height: 1;
            color: #555;
            text-align: center;
            border-radius: 4px;
            background: transparent;
            border: 0;
        }
        
        .btn-signin {
            background-color: #fff;
            color: #1f5a99;
            border-color: #04337d;
        }
        .btn-signin:hover {
            background-color: #04337d;
            color: #ffffff;
            border-color: #1f5a99;
        }
        .btn-lg, .btn-group-lg > .btn {
            padding: 10px 16px;
            font-size: 18px;
            line-height: 1.3333333;
            border-radius: 6px;
        }
        .btn-block { display: block; width: 100%; }
        
        .ad-nav-head nav {
            background: #1f5a99;
            margin: 0;
            border: 0;
        }
        .navbar-default .navbar-nav > li > a {
            color: white;
        }
        .navbar-default .navbar-nav > li > a:hover,
        .navbar-default .navbar-nav > li > a:focus {
            color: #fff;
            background-color: #04337d;
        }
        
        .panel-sign .panel-body {
            background: #FFF;
            border-radius: 5px 0 5px 5px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            padding: 33px 33px 15px;
        }
        .body-sign-add {
            position: absolute;
            top: 0;
            bottom: 0;
            height: 50%;
            margin: auto;
            left: 0;
            right: 0;
            vertical-align: middle;
        }
        .panel-sign .panel-title-sign .title {
            background-color: #337ab7;
            border-radius: 5px 5px 0 0;
            color: #FFF;
            display: inline-block;
            font-size: 1.2rem;
            line-height: 2rem;
            padding: 13px 17px;
            vertical-align: bottom;
            background-color: #0088cc;
            margin: 0;
            border: 0;
        }
        
        .bg-colorflow2 {
            color: #fff !important;
            background-image: linear-gradient(-45deg, #ffffff, #1f5a99, #ffffff, #1f5a99, #ffffff, #1f5a99, #ffffff, #1f5a99, #04337d) !important;
            background-size: 400% 400% !important;
            -webkit-animation: Gradient 15s ease infinite !important;
            -moz-animation: Gradient 15s ease infinite !important;
            animation: Gradient 15s ease infinite !important;
        }
        
        .center-sign {
            width: 71%;
            border: 0;
            padding: 0;
            margin: 0 auto;
        }
        .panel.panel-sign { border: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c8;
        }
        
        /* Password toggle icon positioning */
        .input-group .input-group-addon {
            padding: 6px 12px;
            cursor: pointer;
        }
        .input-group .input-group-addon a {
            color: #555;
            text-decoration: none;
        }
        .input-group .input-group-addon a:hover {
            color: #1f5a99;
        }
        
        @media (max-width: 768px) {
            .center-sign { width: 95%; }
            .carousel-inner > .item > img { height: 300px; }
            .body-sign-add { position: relative; margin-top: 20px; margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <div class="ad-nav-head">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="<?= SITE_URL ?>"><i class="fa fa-home"></i> Home</a></li>
                        <li><a href="<?= SITE_URL ?>faq.php"><i class="fa fa-question-circle"></i> FAQ</a></li>
                        <li><a href="<?= SITE_URL ?>contact-us.php"><i class="fa fa-envelope"></i> Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="fade-bannner">
        <div class="banner-inn">
            <div id="myCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <?php if (!empty($sliders)): ?>
                        <?php $i = 0; foreach ($sliders as $slider): $i++; ?>
                            <div class="item <?= $i === 1 ? 'active' : '' ?>">
                                <img src="../uploads/<?= e($slider['image']) ?>" alt="Slider Image" style="width:100%;">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="item active">
                            <img src="assets/image/sdsadfa.png" alt="Default Banner" style="width:100%;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <section class="body-sign-add">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6">
                            <div class="center-sign">
                                <div class="panel panel-sign">
                                    <div class="panel-title-sign text-center">
                                        <h4 class="title m-none bg-colorflow2" style="width:100%; font-size:18px; color:#fff;">
                                            <i class="fa fa-user mr-xs"></i> Parent Portal Login
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <?= showMessage($stat) ?>
                                        
                                        <form id="login-add" method="post" action="">
                                            <div class="form-group mb-lg">
                                                <div class="input-group input-group-icon">
                                                    <input type="text" name="username" placeholder="Parent ID (e.g., JOS/0001/2026) or Email" 
                                                           class="form-control input-lg" value="<?= e($_POST['username'] ?? '') ?>" 
                                                           autocomplete="off" required>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group mb-lg">
                                                <div class="input-group">
                                                    <input id="password-field" type="password" name="password" placeholder="Password" 
                                                           class="form-control input-lg" required>
                                                    <span class="input-group-addon" style="padding-left: 10px; padding-right: 20px;">
                                                        <a href="javascript:void(0);" id="toggle-password" class="icon icon-sm" style="color:inherit; text-decoration:none; width:100%;">
                                                            <i class="fa fa-eye" id="eye_icon"></i>
                                                        </a>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-12 text-center">
                                                    <button type="submit" name="login" class="btn-signin btn-block btn-lg">
                                                        Sign In <i class="fa fa-sign-in"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="text-center">
                                                <a href="forgot_password.php">Forgot Password?</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#myCarousel').carousel({
                interval: 5000
            });
            
            // Password toggle functionality
            $('#toggle-password').on('click', function(e) {
                e.preventDefault();
                var passwordField = $('#password-field');
                var eyeIcon = $('#eye_icon');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
</body>
</html>