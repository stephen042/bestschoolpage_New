<?php 
/**
 * Contact Us Page - Rebuilt for PHP 8.x
 * Handles contact form submissions with email notifications
 */

require_once('config.php');

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];

// ============================================================================
// PROCESS CONTACT FORM
// ============================================================================
if (isset($_POST['contactus1'])) {
    $firstName = trim($_POST['fname'] ?? '');
    $lastName = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $fullName = $firstName . ' ' . $lastName;
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = "First name is required";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $firstName)) {
        $errors[] = "First name should contain only letters";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        // Save to database
        $data = [
            'name' => $fullName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'create_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'status' => 0 // 0 = unread, 1 = read
        ];
        
        $result = db_insert("contactus", $data);
        
        if ($result) {
            // Send email notification to admin (optional)
            $adminEmail = $iHomeSettingDetails['contact_email'] ?? 'admin@bestschoolpage.com.ng';
            $siteName = "Best School Page";
            
            $emailSubject = "New Contact Form Message: " . $subject;
            $emailMessage = "
            <html>
            <head><title>New Contact Message</title></head>
            <body>
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> " . htmlspecialchars($fullName) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <hr>
                <p>Submitted from: " . $siteName . "</p>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            
            // Send email notification (optional - uncomment if needed)
            // mail($adminEmail, $emailSubject, $emailMessage, $headers);
            
            $stat['success'] = "Thank you for contacting us! We will get back to you soon.";
            
            // Clear form data
            $_POST = [];
        } else {
            $stat['error'] = "Unable to submit your message. Please try again later.";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// Get contact details from settings
$contactAddress = $iHomeSettingDetails['contact_address'] ?? '123 School Street, City, Country';
$contactPhone = $iHomeSettingDetails['contact_phoneno'] ?? '+1234567890';
$contactEmail = $iHomeSettingDetails['contact_email'] ?? 'info@bestschoolpage.com';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta-new.php'); ?>
    <style>
        .contactbanner {
            position: relative;
            text-align: center;
            color: white;
        }
        .contactbanner img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .banner-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 100%;
        }
        .banner-content h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: #fff;
        }
        .banner-content h1 span {
            color: #f21151;
        }
        .breadcrumb {
            background: transparent;
            padding: 0;
            list-style: none;
        }
        .breadcrumb li {
            display: inline;
            color: #fff;
        }
        .breadcrumb li a {
            color: #fff;
            text-decoration: none;
        }
        .form-contactt {
            padding: 60px 0;
            background: #f9f9f9;
        }
        .form-contactt h3 {
            margin-bottom: 30px;
            color: #1B3058;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-control:focus {
            outline: none;
            border-color: #1B3058;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        .btn-secondary {
            background: #1B3058;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn-secondary:hover {
            background: #f21151;
        }
        .adre-detaill {
            background: #fff;
            border: 1px solid #e0e0e0;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 60px;
        }
        .adre-detaill h4 {
            color: #1B3058;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .adre-detaill p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .adre-detaill i {
            color: #f21151;
            width: 25px;
            font-size: 18px;
        }
        .adre-detaill a {
            color: #333;
            text-decoration: none;
        }
        .adre-detaill a:hover {
            color: #f21151;
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
            .banner-content h1 {
                font-size: 28px;
            }
            .form-contactt {
                padding: 30px 0;
            }
            .adre-detaill {
                margin-top: 30px;
            }
        }
    </style>
</head>
<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
<div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
        
        <!-- Banner Section -->
        <div class="home-banner contactbanner">
            <img src="images/contactus.png" alt="Contact Us" class="wow fadeIn">
            <div class="container">
                <div class="banner-content">
                    <h1><span>Contact</span> Us</h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= SITE_URL ?>">Home</a></li>
                        <li>Contact us</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Contact Form Section -->
        <div class="form-contactt">
            <div class="container">
                <div class="row">
                    <div class="col-md-5">
                        <form action="" method="post">
                            <?= showMessage($stat) ?>
                            
                            <h3>Inquiry Now</h3>
                            
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label for="fname">First Name</label>
                                        <input type="text" class="form-control" name="fname" 
                                               value="<?= e($_POST['fname'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label for="lname">Last Name</label>
                                        <input type="text" class="form-control" name="lname" 
                                               value="<?= e($_POST['lname'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?= e($_POST['email'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <input type="text" class="form-control" name="subject" 
                                               value="<?= e($_POST['subject'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea class="form-control" name="message" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <button type="submit" name="contactus1" class="btn btn-secondary">
                                        <i class="fa fa-paper-plane"></i> Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Office Address Section -->
                    <div class="col-md-6 col-md-offset-1">
                        <div class="adre-detaill">
                            <h4><i class="fa fa-building"></i> Our Office</h4>
                            <p>
                                <i class="fa fa-map-marker"></i> 
                                <?= e($contactAddress) ?>
                            </p>
                            <p>
                                <i class="fa fa-phone"></i> 
                                <a href="tel:<?= e($contactPhone) ?>"><?= e($contactPhone) ?></a>
                            </p>
                            <p>
                                <i class="fa fa-envelope-o"></i> 
                                <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
                            </p>
                            <hr>
                            <p>
                                <i class="fa fa-clock-o"></i> 
                                Monday - Friday: 9:00 AM - 6:00 PM
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('inc.footer-new.php'); ?>
</div>
<?php include('inc.js-new.php'); ?>
</body>
</html>