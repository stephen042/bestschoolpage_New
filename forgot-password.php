<?php 
/**
 * Forgot Password Page - Rebuilt for PHP 8.x
 * Sends new password to registered email address
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

// ============================================================================
// FUNCTION TO GENERATE RANDOM PASSWORD
// ============================================================================
function randomPassword($length = 10) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%';
    $pass = [];
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}

// ============================================================================
// SEND NEW PASSWORD EMAIL
// ============================================================================
function sendNewPasswordEmail($email, $name, $newPassword) {
    $siteName = "Best School Page";
    $siteUrl = SITE_URL;
    
    $subject = "New Password From " . $siteName;
    
    $message = "
    <html>
    <head>
        <title>New Password Request</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; background: #f4f4f4; }
            .content { background: #fff; padding: 20px; border-radius: 5px; }
            .password { font-size: 18px; font-weight: bold; color: #1B3058; background: #f0f0f0; padding: 10px; display: inline-block; }
            .btn { background: #1B3058; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
            .footer { margin-top: 20px; font-size: 12px; color: #888; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($name) . ",</h2>
                <p>We received a request to reset your password for your account at <strong>" . $siteName . "</strong>.</p>
                <p>Your new password is:</p>
                <p class='password'>" . htmlspecialchars($newPassword) . "</p>
                <p>You can change this password after logging in to your account.</p>
                <p>
                    <a href='" . $siteUrl . "login.php' class='btn'>Click here to login</a>
                </p>
                <p>If you didn't request this password reset, please ignore this email or contact support.</p>
                <hr>
                <div class='footer'>
                    <p>Thanks & Regards,<br><strong>" . $siteName . "</strong></p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: info@bestschoolpage.com.ng\r\n";
    $headers .= "Reply-To: info@bestschoolpage.com.ng\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($email, $subject, $message, $headers);
}

// ============================================================================
// PROCESS FORGOT PASSWORD REQUEST
// ============================================================================
if (isset($_POST['get_password'])) {
    $email = trim($_POST['email'] ?? '');
    
    // Validation
    if (empty($email)) {
        $stat['error'] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stat['error'] = "Please enter a valid email address.";
    } else {
        // Check if email exists in school_register table
        $user = db_get_row("SELECT * FROM school_register WHERE email = ?", [$email]);
        
        if (!empty($user)) {
            // Generate new random password
            $newPassword = randomPassword(10);
            
            // Hash the new password for storage
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password in database
            $updateResult = db_update("school_register", ['password' => $hashedPassword], "id = ?", [$user['id']]);
            
            if ($updateResult) {
                // Send email with new password
                $emailSent = sendNewPasswordEmail($email, $user['name'], $newPassword);
                
                if ($emailSent) {
                    $stat['success'] = "New password has been sent to your email address. Please check your inbox (or spam folder).";
                } else {
                    $stat['error'] = "Unable to send email. Please try again later or contact support.";
                }
            } else {
                $stat['error'] = "Unable to update password. Please try again later.";
            }
        } else {
            $stat['error'] = "Email address does not exist in our records.";
        }
    }
}
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
        .form-control:focus {
            outline: none;
            border-color: #1B3058;
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
        .frgt .row {
            display: flex;
            align-items: center;
        }
        @media (max-width: 768px) {
            .login-form {
                padding: 20px;
            }
            .frgt .row {
                flex-direction: column;
            }
            .frgt .col-md-10,
            .frgt .col-md-2 {
                width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
<div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
        <section class="login-part">
            <div class="container">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <form action="" method="post" class="login-form">
                            <h4>Reset Your Password</h4>
                            
                            <?= showMessage($stat) ?>
                            
                            <div class="form-group">
                                <input autocomplete="off" class="form-control" name="email" 
                                       value="<?= e($_POST['email'] ?? '') ?>" 
                                       placeholder="Enter your email address *" type="email" required>
                            </div>
                            
                            <div class="form-group frgt">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="here">
                                            <a href="<?= SITE_URL ?>login.php">
                                                <i class="fa fa-arrow-left"></i> Back to Login
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <button class="btn-primary" type="submit" name="get_password" style="width: 100%;">
                                            Send New Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group sclacu">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="reva">
                                            Don't have a school account? 
                                            <span class="rgstrscl">
                                                <a href="<?= SITE_URL ?>registration.php">Register School</a>
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