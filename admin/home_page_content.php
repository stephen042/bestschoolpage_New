<?php  
require_once '../config.php'; 
require_once 'inc.session-create.php'; 

$PageTitle = "Home Page Content";
$FileName = 'home_page_content.php';
$iClassName = ADMIN_URL;

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// SANITIZATION HELPER
// ============================================================================
function sanitizeInput($value) {
    // Remove malicious keywords
    $patterns = ['/meta/i', '/script/i', '/drop/i', '/insert/i', '/delete/i', '/update/i', '/truncate/i'];
    return preg_replace($patterns, '_', $value);
}

// ============================================================================
// UPDATE HOME PAGE CONTENT
// ============================================================================
if (isset($_POST['update'])) {
    // Get POST data with defaults
    $heading = $_POST['heading'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $link = $_POST['link'] ?? '';
    $heading2 = $_POST['heading2'] ?? '';
    $heading3 = $_POST['heading3'] ?? '';
    $heading4 = $_POST['heading4'] ?? '';
    $heading5 = $_POST['heading5'] ?? '';
    $heading6 = $_POST['heading6'] ?? '';
    $heading7 = $_POST['heading7'] ?? '';
    
    // Sanitize inputs (remove malicious content)
    $data = [
        'heading' => sanitizeInput($heading),
        'title' => sanitizeInput($title),
        'description' => sanitizeInput($description),
        'link' => sanitizeInput($link),
        'heading2' => sanitizeInput($heading2),
        'heading3' => sanitizeInput($heading3),
        'heading4' => sanitizeInput($heading4),
        'heading5' => sanitizeInput($heading5),
        'heading6' => sanitizeInput($heading6),
        'heading7' => sanitizeInput($heading7),
    ];
    
    // Check if record exists
    $existing = db_get_val("SELECT id FROM home_page_content WHERE id = 1");
    
    if (!empty($existing)) {
        // Update existing record
        db_update("home_page_content", $data, "id = 1");
        $_SESSION['success'] = "Updated Successfully";
    } else {
        // Insert new record
        $data['id'] = 1;
        db_insert("home_page_content", $data);
        $_SESSION['success'] = "Created Successfully";
    }
    
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET CURRENT DATA
// ============================================================================
$homeContent = db_get_row("SELECT * FROM home_page_content WHERE id = 1");

// Set default values if no data exists
if (empty($homeContent)) {
    $homeContent = [
        'heading' => '',
        'title' => '',
        'description' => '',
        'link' => '',
        'heading2' => '',
        'heading3' => '',
        'heading4' => '',
        'heading5' => '',
        'heading6' => '',
        'heading7' => '',
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .card-box {
            margin-bottom: 20px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
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
                        <div class="card-box aplhanewclass">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text-muted">Manage your homepage content below. All fields are optional.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-box">
                            <form role="form" action="" method="post">
                                <div class="row">
                                    <!-- Left Column -->
                                    <div class="col-md-6">
                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading">Heading 1</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading" 
                                                       id="heading" value="<?= e($homeContent['heading']) ?>"
                                                       placeholder="Enter main heading">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="title">Title</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="title" 
                                                       id="title" value="<?= e($homeContent['title']) ?>"
                                                       placeholder="Enter page title">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="description">Description</label>
                                            <div class="col-lg-9">
                                                <textarea class="form-control" name="description" 
                                                          id="description" rows="4"
                                                          placeholder="Enter description text"><?= e($homeContent['description']) ?></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="link">Link URL</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="link" 
                                                       id="link" value="<?= e($homeContent['link']) ?>"
                                                       placeholder="https://example.com/page">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading2">Heading 2</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading2" 
                                                       id="heading2" value="<?= e($homeContent['heading2']) ?>"
                                                       placeholder="Enter second heading">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="col-md-6">
                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading3">Heading 3</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading3" 
                                                       id="heading3" value="<?= e($homeContent['heading3']) ?>"
                                                       placeholder="Enter third heading">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading4">Heading 4</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading4" 
                                                       id="heading4" value="<?= e($homeContent['heading4']) ?>"
                                                       placeholder="Enter fourth heading">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading5">Heading 5</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading5" 
                                                       id="heading5" value="<?= e($homeContent['heading5']) ?>"
                                                       placeholder="Enter fifth heading">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading6">Heading 6</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading6" 
                                                       id="heading6" value="<?= e($homeContent['heading6']) ?>"
                                                       placeholder="Enter sixth heading">
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-3 control-label" for="heading7">Heading 7</label>
                                            <div class="col-lg-9">
                                                <input type="text" class="form-control" name="heading7" 
                                                       id="heading7" value="<?= e($homeContent['heading7']) ?>"
                                                       placeholder="Enter seventh heading">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <hr>
                                        <button type="submit" name="update" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update Content
                                        </button>
                                        <a href="<?= ADMIN_URL ?>" class="btn btn-default">
                                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
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