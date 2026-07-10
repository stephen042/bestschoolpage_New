<?php 
/**
 * School Registration Page - Rebuilt for PHP 8.x
 * Allows new schools to register with secure password hashing
 */

require_once('config.php');

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
        // Check for duplicates
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
            // Generate IDs
            $lastId = db_get_val("SELECT id FROM school_register ORDER BY id DESC") ?? 0;
            $newId = $lastId + 1;
            $randomId = randomFix(20) . '-' . $newId;
            $pageUrl = PageUrl($schoolName) . '-' . $newId;
            
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new school
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
            
            if ($newSchoolId) {
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
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// GET DATA FOR DROPDOWNS
// ============================================================================
$states = db_get_rows("SELECT * FROM state WHERE status = '1' ORDER BY title ASC");
$schoolTypes = db_get_rows("SELECT * FROM school_type ORDER BY school_type ASC");
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
        * { font-family: 'Inter', sans-serif; }

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

        .register-hero {
            position: relative;
            min-height: 46vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            overflow: hidden;
        }

        .register-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('images/home-slider.png') center/cover no-repeat;
            opacity: 0.25;
        }

        .register-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                rgba(15, 23, 42, 0.92) 0%,
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.92) 100%
            );
            z-index: 1;
        }

        .register-hero-content {
            position: relative;
            z-index: 2;
        }

        .register-card-wrap {
            margin-top: -70px;
            margin-bottom: 80px;
            position: relative;
            z-index: 3;
        }

        .learnpro-register-form {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 34px;
        }

        .form-group { margin-bottom: 18px; }

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

        .admin {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-left: 4px solid #6366f1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            color: #3730a3;
            font-size: 0.9rem;
            text-align: left;
            font-weight: 600;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 0.92rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
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

        .register-caption {
            color: #64748b;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .learnpro-register-form { padding: 22px; border-radius: 18px; }
            .register-card-wrap { margin-top: -45px; margin-bottom: 55px; }
        }
    </style>
</head>
<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
<div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
        <section class="register-hero">
            <div class="register-hero-overlay"></div>
            <div class="container mx-auto px-4 register-hero-content">
                <div class="max-w-4xl mx-auto text-center">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-5">
                        <i class="fas fa-school"></i>
                        School Onboarding
                    </span>
                    <h1 class="text-white text-4xl md:text-5xl font-extrabold mb-3">Register Your School</h1>
                    <p class="text-slate-300">Create your account and start managing your institution in minutes.</p>
                </div>
            </div>
        </section>

        <section class="register-card-wrap">
            <div class="container mx-auto px-4">
                <div class="max-w-3xl mx-auto">
                    <form action="" method="POST" class="learnpro-register-form">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1">Register Your School</h2>
                        <p class="register-caption mb-5">Fill in your school details and admin credentials to get started.</p>
                            
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
                            <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:6px;">
                                <p style="margin:0; color:#475569;">Already have a school account?</p>
                                <a class="link-muted" href="<?= SITE_URL ?>login.php" style="font-weight:700; color:#4338ca;">Login here <i class="fa fa-arrow-right"></i></a>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="form-group" style="margin-bottom:0;">
                                <button type="submit" name="submit" class="btn-primary">
                                    <i class="fa fa-check-circle"></i> Register School
                                </button>
                            </div>
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