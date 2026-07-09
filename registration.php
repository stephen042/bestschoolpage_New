<?php

/**
 * School Registration Page - Rebuilt for PHP 8.x
 * Allows new schools to register with secure password hashing
 */

require_once('config.php');

function PageUrl()
{
    // Generate a URL-friendly version of the school name
    $schoolName = $_POST['school_name'] ?? '';
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($schoolName)));
}

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];

// Redirect if already logged in
if (!empty($_SESSION['userid'])) {
    redirect(SKOOL_URL);
    exit;
}

// Display success message from session
if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// REGISTRATION PROCESSING
// ============================================================================
if (isset($_POST['submit'])) {
    // Get form data
    $schoolName = trim($_POST['school_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $stateId = $_POST['state'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $contactNo = trim($_POST['contact_no'] ?? '');
    $schoolType = $_POST['school_type'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_pass'] ?? '';

    // Validation
    $errors = [];

    if (empty($schoolName)) $errors[] = "School name is required";
    if (empty($location)) $errors[] = "Location is required";
    if (empty($stateId)) $errors[] = "State is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($contactNo)) $errors[] = "Contact number is required";
    if (!preg_match('/^[0-9]{10,15}$/', $contactNo)) $errors[] = "Contact number must be 10-15 digits";
    if (empty($schoolType)) $errors[] = "School type is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirmPass) $errors[] = "Passwords do not match";

    if (empty($errors)) {
        try {
            // Check for duplicates using PDO from config.php
            $existingEmail = db_get_val("SELECT id FROM school_register WHERE email = ?", [$email]);
            $existingUsername = db_get_val("SELECT id FROM school_register WHERE username = ?", [$username]);
            $existingContact = db_get_val("SELECT id FROM school_register WHERE contact_no = ?", [$contactNo]);

            if (!empty($existingEmail)) {
                $stat['error'] = "This email address is already registered.";
            } elseif (!empty($existingUsername)) {
                $stat['error'] = "This username is already taken. Please choose another.";
            } elseif (!empty($existingContact)) {
                $stat['error'] = "This contact number is already registered.";
            } else {
                // Get the last ID using PDO
                $lastId = db_get_val("SELECT id FROM school_register ORDER BY id DESC");
                $newId = ($lastId ?? 0) + 1;
                $randomId = randomFix(20) . '-' . $newId;
                $pageUrl = PageUrl() . '-' . $newId;

                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new school using PDO
                $data = [
                    'usertype' => 0,
                    'name' => $schoolName,
                    'username' => $username,
                    'state' => $stateId,
                    'contact_no' => $contactNo,
                    'email' => $email,
                    'location' => $location,
                    'password' => $hashedPassword,
                    'school_type' => $schoolType,
                    'status' => 0, // Pending approval
                    'verifyid' => randomFix(15),
                    'create_at' => date('Y-m-d H:i:s'),
                    'randomid' => $randomId,
                    'pageurl' => $pageUrl,
                    'create_by_userid' => 0,
                    'create_by_usertype' => 0,
                    'walletamount' => 0,
                ];

                $newSchoolId = db_insert("school_register", $data);

                if ($newSchoolId !== false) {
                    // Set session and redirect
                    $_SESSION['userid'] = $newSchoolId;
                    $_SESSION['usertype'] = 0;
                    $_SESSION['username'] = $username;
                    $_SESSION['school_name'] = $schoolName;
                    $_SESSION['success'] = "Your school has been registered successfully! Please complete the package selection.";

                    redirect(SITE_URL . 'package.php');
                    exit;
                } else {
                    $stat['error'] = "Registration failed. Please try again later.";
                }
            }
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Registration error: " . $e->getMessage());
            $stat['error'] = "A database error occurred. Please try again later.";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// GET DATA FOR DROPDOWNS
// ============================================================================
try {
    // Using PDO from config.php
    $states = db_get_rows("SELECT * FROM state WHERE status = '1' ORDER BY title ASC");
    $schoolTypes = db_get_rows("SELECT * FROM school_type ORDER BY school_type ASC");
} catch (PDOException $e) {
    // Fallback to empty arrays if queries fail
    $states = [];
    $schoolTypes = [];
    error_log("Failed to load dropdown data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include('inc.meta-new.php'); ?>
    <style>
        .registartion-area {
            padding: 60px 0;
            background: #f9f9f9;
            min-height: 600px;
        }

        .learnpro-register-form {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .learnpro-register-form h4 {
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

        .form-control:focus {
            outline: none;
            border-color: #1B3058;
        }

        select.form-control {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
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

        .admin {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            color: #1B3058;
        }

        .reva {
            margin: 0;
            padding-top: 8px;
        }

        .here a {
            color: #f21151;
            text-decoration: none;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .learnpro-register-form {
                padding: 20px;
            }
        }
    </style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
    <div id="page" class="site">
        <?php include('inc.header-new.php'); ?>
        <div id="content" class="site-content">
            <section class="registartion-area">
                <div class="container">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <form action="" method="POST" class="learnpro-register-form">
                                <div class="center-align">
                                    <h4>Register Your School</h4>
                                </div>

                                <?= showMessage($stat) ?>

                                <!-- School Basic Information -->
                                <div class="form-group">
                                    <input autocomplete="off" class="form-control" placeholder="School Name *"
                                        value="<?= e($_POST['school_name'] ?? '') ?>" name="school_name" type="text" required>
                                </div>

                                <div class="form-group">
                                    <input autocomplete="off" class="form-control" placeholder="Location *"
                                        value="<?= e($_POST['location'] ?? '') ?>" name="location" type="text" required>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select class="required form-control" name="state" required>
                                                <option value="">Select State</option>
                                                <?php foreach ($states as $state): ?>
                                                    <option value="<?= e($state['id']) ?>" <?= selected($_POST['state'] ?? '', $state['id']) ?>>
                                                        <?= e($state['title']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <input class="required form-control" placeholder="Email *" name="email"
                                                type="email" value="<?= e($_POST['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input autocomplete="off" class="required form-control" name="contact_no"
                                                value="<?= e($_POST['contact_no'] ?? '') ?>" placeholder="Contact No. *"
                                                type="tel" required>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="required form-control" name="school_type" required>
                                                <option value="">Select School Type</option>
                                                <?php foreach ($schoolTypes as $type): ?>
                                                    <option value="<?= e($type['id']) ?>" <?= selected($_POST['school_type'] ?? '', $type['id']) ?>>
                                                        <?= e($type['school_type']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Admin Login Details -->
                                <div class="admin">
                                    <span>Admin Login Details</span>
                                </div>

                                <div class="form-group">
                                    <input class="required form-control" placeholder="Username *"
                                        value="<?= e($_POST['username'] ?? '') ?>" name="username" type="text" required>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input class="required form-control" name="password" placeholder="Password *"
                                                type="password" required>
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="required form-control" name="confirm_pass"
                                                placeholder="Confirm Password *" type="password" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Login Link -->
                                <div class="form-group sclacu">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="reva">Already have a school account?</p>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="here">
                                                <a href="<?= SITE_URL ?>login.php">Login here <i class="fa fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-group register-btn">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" name="submit" class="btn-primary">
                                                <i class="fa fa-check-circle"></i> Register School
                                            </button>
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