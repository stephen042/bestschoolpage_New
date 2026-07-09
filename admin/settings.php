<?php  
require_once '../config.php'; 
require_once 'inc.session-create.php'; 

$PageTitle = "Settings";
$FileName = 'settings.php';

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
// FILE UPLOAD HANDLER
// ============================================================================
function handleLogoUpload($file, $oldLogo = '') {
    if (isset($file['name']) && !empty($file['name'])) {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg'];
        
        if (in_array($ext, $allowed)) {
            $newFilename = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $uploadPath = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                if (!empty($oldLogo) && file_exists("../uploads/" . $oldLogo)) {
                    unlink("../uploads/" . $oldLogo);
                }
                return $newFilename;
            }
        }
    }
    return $oldLogo;
}

// ============================================================================
// SANITIZATION HELPER
// ============================================================================
function sanitizeInput($value) {
    $patterns = ['/meta/i', '/script/i', '/drop/i', '/insert/i', '/delete/i', '/update/i', '/truncate/i', '/select/i', '/union/i'];
    return preg_replace($patterns, '_', $value);
}

// ============================================================================
// UPDATE SETTINGS
// ============================================================================
if (isset($_POST['update'])) {
    // Home Content Data
    $homeData = [
        'call_number' => sanitizeInput($_POST['call_number'] ?? ''),
        'banner_description' => sanitizeInput($_POST['banner_description'] ?? ''),
        'marketing_expert' => sanitizeInput($_POST['marketing_expert'] ?? ''),
        'marketing_callno' => sanitizeInput($_POST['marketing_callno'] ?? ''),
        'request_demo_content' => sanitizeInput($_POST['request_demo_content'] ?? ''),
        'automate_1' => sanitizeInput($_POST['automate_1'] ?? ''),
        'automate_2' => sanitizeInput($_POST['automate_2'] ?? ''),
        'automate_3' => sanitizeInput($_POST['automate_3'] ?? ''),
        'institute_process_1' => sanitizeInput($_POST['institute_process_1'] ?? ''),
        'institute_process_2' => sanitizeInput($_POST['institute_process_2'] ?? ''),
        'institute_process_3' => sanitizeInput($_POST['institute_process_3'] ?? ''),
        'newsletter_content' => sanitizeInput($_POST['newsletter_content'] ?? ''),
        'contact_address' => sanitizeInput($_POST['contact_address'] ?? ''),
        'contact_phoneno' => sanitizeInput($_POST['contact_phoneno'] ?? ''),
        'contact_email' => sanitizeInput($_POST['contact_email'] ?? ''),
        'footer_content' => sanitizeInput($_POST['footer_content'] ?? ''),
        'fb_link' => sanitizeInput($_POST['fb_link'] ?? ''),
        'twitter_link' => sanitizeInput($_POST['twitter_link'] ?? ''),
        'linkedin_link' => sanitizeInput($_POST['linkedin_link'] ?? ''),
        'youtube_link' => sanitizeInput($_POST['youtube_link'] ?? ''),
        'footer_description' => sanitizeInput($_POST['footer_description'] ?? ''),
    ];
    
    // Check if home_content exists
    $homeExists = db_get_val("SELECT id FROM home_content WHERE id = 1");
    
    if (!empty($homeExists)) {
        db_update("home_content", $homeData, "id = 1");
    } else {
        $homeData['id'] = 1;
        db_insert("home_content", $homeData);
    }
    
    // Handle logo upload
    $newLogo = handleLogoUpload($_FILES['companylogo'] ?? [], $_POST['companylogo_old'] ?? '');
    
    // Settings Data
    $settingsData = [
        'headerphone' => sanitizeInput($_POST['headerphone'] ?? ''),
        'emailid' => sanitizeInput($_POST['emailid'] ?? ''),
        'headeraddress' => sanitizeInput($_POST['headeraddress'] ?? ''),
        'companylogo' => $newLogo,
        'facebook_link' => sanitizeInput($_POST['facebook_link'] ?? ''),
        'tweeter_link' => sanitizeInput($_POST['tweeter_link'] ?? ''),
        'google_link' => sanitizeInput($_POST['google_link'] ?? ''),
        'instagram_link' => sanitizeInput($_POST['instagram_link'] ?? ''),
        'customer_service_email' => sanitizeInput($_POST['customer_service_email'] ?? ''),
        'youtube' => sanitizeInput($_POST['youtube'] ?? ''),
        'linkedin' => sanitizeInput($_POST['linkedin'] ?? ''),
        'customer_service_phone' => sanitizeInput($_POST['customer_service_phone'] ?? ''),
        'customer_service_timing' => sanitizeInput($_POST['customer_service_timing'] ?? ''),
        'technical_support_email' => sanitizeInput($_POST['technical_support_email'] ?? ''),
        'technical_support_phone' => sanitizeInput($_POST['technical_support_phone'] ?? ''),
        'technical_support_timing' => sanitizeInput($_POST['technical_support_timing'] ?? ''),
        'minreturnday' => (int)($_POST['minreturnday'] ?? 0),
        'footer_timing' => sanitizeInput($_POST['footer_timing'] ?? ''),
        'footer_copyrights' => sanitizeInput($_POST['footer_copyrights'] ?? ''),
    ];
    
    // Check if settings exist
    $settingsExists = db_get_val("SELECT id FROM settings WHERE id = 1");
    
    if (!empty($settingsExists)) {
        db_update("settings", $settingsData, "id = 1");
    } else {
        $settingsData['id'] = 1;
        db_insert("settings", $settingsData);
    }
    
    $_SESSION['success'] = "Settings updated successfully";
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET CURRENT SETTINGS
// ============================================================================
$homeContent = db_get_row("SELECT * FROM home_content WHERE id = 1");
if (empty($homeContent)) {
    $homeContent = [];
}

$settings = db_get_row("SELECT * FROM settings WHERE id = 1");
if (empty($settings)) {
    $settings = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .settings-panel {
            margin-bottom: 30px;
        }
        .settings-panel .panel-heading {
            background: #f5f5f5;
            border-bottom: 2px solid #1B3058;
        }
        .settings-panel .panel-title {
            font-weight: bold;
            color: #1B3058;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .current-logo {
            margin-top: 10px;
        }
        .current-logo img {
            max-height: 60px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        textarea {
            resize: vertical;
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
                    <div class="col-md-12">
                        <form action="" method="post" enctype="multipart/form-data">
                            <!-- ==================== HOME CONTENT SECTION ==================== -->
                            <div class="panel panel-default settings-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="fa fa-home"></i> Home Content Settings</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Call Number</label>
                                                <input type="text" class="form-control" name="call_number" 
                                                       value="<?= e($homeContent['call_number'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Banner Description</label>
                                                <textarea class="form-control" name="banner_description" rows="3"><?= e($homeContent['banner_description'] ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Marketing Expert</label>
                                                <input type="text" class="form-control" name="marketing_expert" 
                                                       value="<?= e($homeContent['marketing_expert'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Marketing Call Number</label>
                                                <input type="text" class="form-control" name="marketing_callno" 
                                                       value="<?= e($homeContent['marketing_callno'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Request Demo Content</label>
                                                <input type="text" class="form-control" name="request_demo_content" 
                                                       value="<?= e($homeContent['request_demo_content'] ?? '') ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Automate 1</label>
                                                <input type="text" class="form-control" name="automate_1" 
                                                       value="<?= e($homeContent['automate_1'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Automate 2</label>
                                                <input type="text" class="form-control" name="automate_2" 
                                                       value="<?= e($homeContent['automate_2'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Automate 3</label>
                                                <input type="text" class="form-control" name="automate_3" 
                                                       value="<?= e($homeContent['automate_3'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Institute Process 1</label>
                                                <input type="text" class="form-control" name="institute_process_1" 
                                                       value="<?= e($homeContent['institute_process_1'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Institute Process 2</label>
                                                <input type="text" class="form-control" name="institute_process_2" 
                                                       value="<?= e($homeContent['institute_process_2'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Institute Process 3</label>
                                                <input type="text" class="form-control" name="institute_process_3" 
                                                       value="<?= e($homeContent['institute_process_3'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Newsletter Content</label>
                                                <input type="text" class="form-control" name="newsletter_content" 
                                                       value="<?= e($homeContent['newsletter_content'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Contact Address</label>
                                                <textarea class="form-control" name="contact_address" rows="3"><?= e($homeContent['contact_address'] ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Contact Phone Number</label>
                                                <input type="text" class="form-control" name="contact_phoneno" 
                                                       value="<?= e($homeContent['contact_phoneno'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Contact Email</label>
                                                <input type="email" class="form-control" name="contact_email" 
                                                       value="<?= e($homeContent['contact_email'] ?? '') ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Footer Content</label>
                                                <textarea class="form-control" name="footer_content" rows="3"><?= e($homeContent['footer_content'] ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Footer Description</label>
                                                <textarea class="form-control" name="footer_description" rows="3"><?= e($homeContent['footer_description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== SOCIAL LINKS SECTION ==================== -->
                            <div class="panel panel-default settings-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="fa fa-share-alt"></i> Social Links</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-facebook"></i> Facebook Link</label>
                                                <input type="url" class="form-control" name="fb_link" 
                                                       value="<?= e($homeContent['fb_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-twitter"></i> Twitter Link</label>
                                                <input type="url" class="form-control" name="twitter_link" 
                                                       value="<?= e($homeContent['twitter_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-linkedin"></i> LinkedIn Link</label>
                                                <input type="url" class="form-control" name="linkedin_link" 
                                                       value="<?= e($homeContent['linkedin_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-youtube"></i> YouTube Link</label>
                                                <input type="url" class="form-control" name="youtube_link" 
                                                       value="<?= e($homeContent['youtube_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== COMPANY SETTINGS SECTION ==================== -->
                            <div class="panel panel-default settings-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="fa fa-building"></i> Company Settings</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Header Phone</label>
                                                <input type="text" class="form-control" name="headerphone" 
                                                       value="<?= e($settings['headerphone'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Email ID</label>
                                                <input type="email" class="form-control" name="emailid" 
                                                       value="<?= e($settings['emailid'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Header Address</label>
                                                <textarea class="form-control" name="headeraddress" rows="2"><?= e($settings['headeraddress'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company Logo</label>
                                                <input type="file" class="form-control" name="companylogo" accept="image/*">
                                                <input type="hidden" name="companylogo_old" value="<?= e($settings['companylogo'] ?? '') ?>">
                                                <?php if (!empty($settings['companylogo'])): ?>
                                                    <div class="current-logo">
                                                        <img src="../uploads/<?= e($settings['companylogo']) ?>" alt="Company Logo">
                                                    </div>
                                                <?php endif; ?>
                                                <small class="text-muted">Allowed formats: JPG, PNG, JPEG, GIF, SVG</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Minimum Return Days</label>
                                                <input type="number" class="form-control" name="minreturnday" 
                                                       value="<?= e($settings['minreturnday'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== CUSTOMER SERVICE SECTION ==================== -->
                            <div class="panel panel-default settings-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="fa fa-headphones"></i> Customer Service</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer Service Email</label>
                                                <input type="email" class="form-control" name="customer_service_email" 
                                                       value="<?= e($settings['customer_service_email'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Customer Service Phone</label>
                                                <input type="text" class="form-control" name="customer_service_phone" 
                                                       value="<?= e($settings['customer_service_phone'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Customer Service Timing</label>
                                                <input type="text" class="form-control" name="customer_service_timing" 
                                                       value="<?= e($settings['customer_service_timing'] ?? '') ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Technical Support Email</label>
                                                <input type="email" class="form-control" name="technical_support_email" 
                                                       value="<?= e($settings['technical_support_email'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Technical Support Phone</label>
                                                <input type="text" class="form-control" name="technical_support_phone" 
                                                       value="<?= e($settings['technical_support_phone'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Technical Support Timing</label>
                                                <input type="text" class="form-control" name="technical_support_timing" 
                                                       value="<?= e($settings['technical_support_timing'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== FOOTER SECTION ==================== -->
                            <div class="panel panel-default settings-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="fa fa-copyright"></i> Footer Settings</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Footer Timing</label>
                                                <input type="text" class="form-control" name="footer_timing" 
                                                       value="<?= e($settings['footer_timing'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Footer Copyrights</label>
                                                <input type="text" class="form-control" name="footer_copyrights" 
                                                       value="<?= e($settings['footer_copyrights'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== ADDITIONAL SOCIAL LINKS ==================== -->
                            <div class="panel panel-default settings-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="fa fa-globe"></i> Additional Social Links</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-facebook-square"></i> Facebook</label>
                                                <input type="url" class="form-control" name="facebook_link" 
                                                       value="<?= e($settings['facebook_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-twitter-square"></i> Twitter</label>
                                                <input type="url" class="form-control" name="tweeter_link" 
                                                       value="<?= e($settings['tweeter_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-google-plus"></i> Google Plus</label>
                                                <input type="url" class="form-control" name="google_link" 
                                                       value="<?= e($settings['google_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-instagram"></i> Instagram</label>
                                                <input type="url" class="form-control" name="instagram_link" 
                                                       value="<?= e($settings['instagram_link'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-youtube-play"></i> YouTube</label>
                                                <input type="url" class="form-control" name="youtube" 
                                                       value="<?= e($settings['youtube'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-linkedin-square"></i> LinkedIn</label>
                                                <input type="url" class="form-control" name="linkedin" 
                                                       value="<?= e($settings['linkedin'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group text-center">
                                <button type="submit" name="update" class="btn btn-primary btn-lg">
                                    <i class="fa fa-save"></i> Save All Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>