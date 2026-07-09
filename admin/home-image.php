<?php 
require_once '../config.php'; 
require_once 'inc.session-create.php'; 

$PageTitle = "Home Image";
$FileName = 'home-image.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// FILE UPLOAD HANDLER
// ============================================================================
function handleImageUpload($file, $oldImage = '') {
    if (isset($file['name']) && !empty($file['name'])) {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $newFilename = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $uploadPath = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old image if it exists
                if (!empty($oldImage) && file_exists("../uploads/" . $oldImage)) {
                    unlink("../uploads/" . $oldImage);
                }
                return $newFilename;
            }
        }
    }
    return $oldImage;
}

// ============================================================================
// SANITIZATION HELPER
// ============================================================================
function sanitizeInput($value) {
    $patterns = ['/meta/i', '/script/i', '/drop/i', '/insert/i', '/delete/i', '/update/i', '/truncate/i', '/select/i'];
    return preg_replace($patterns, '_', $value);
}

// ============================================================================
// ADD NEW RECORD
// ============================================================================
if (isset($_POST['addnewrecord'])) {
    $title = sanitizeInput($_POST['title'] ?? '');
    $title1 = sanitizeInput($_POST['title_1'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    if (empty($title)) {
        $stat['error'] = "Title is required";
    } else {
        $image = handleImageUpload($_FILES['picons'] ?? []);
        
        $data = [
            'title' => $title,
            'title_1' => $title1,
            'picons' => $image,
            'status' => $status,
            'create_by_userid' => $_SESSION['userid'] ?? 0,
            'create_by_usertype' => $_SESSION['usertype'] ?? '',
        ];
        
        db_insert("home_image", $data);
        $_SESSION['success'] = "Submitted Successfully";
        redirect(ADMIN_URL . $FileName);
    }
}

// ============================================================================
// UPDATE RECORD
// ============================================================================
elseif (isset($_POST['udpaterecord']) && !empty($id)) {
    $title = sanitizeInput($_POST['title'] ?? '');
    $title1 = sanitizeInput($_POST['title_1'] ?? '');
    $status = $_POST['status'] ?? '1';
    $oldImage = $_POST['picons_old'] ?? '';
    
    if (empty($title)) {
        $stat['error'] = "Title is required";
    } else {
        $image = handleImageUpload($_FILES['picons'] ?? [], $oldImage);
        
        $data = [
            'title' => $title,
            'title_1' => $title1,
            'picons' => $image,
            'status' => $status,
        ];
        
        db_update("home_image", $data, "id = ?", [$id]);
        $_SESSION['success'] = "Updated Successfully";
        redirect(ADMIN_URL . $FileName);
    }
}

// ============================================================================
// DELETE RECORD
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Get image path to delete file
    $image = db_get_val("SELECT picons FROM home_image WHERE id = ?", [$id]);
    if (!empty($image) && file_exists("../uploads/" . $image)) {
        unlink("../uploads/" . $image);
    }
    
    db_delete("home_image", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect(ADMIN_URL . $FileName);
}

// ============================================================================
// GET DATA FOR EDIT
// ============================================================================
$editRecord = null;
if ($action == 'edit' && !empty($id)) {
    $editRecord = db_get_row("SELECT * FROM home_image WHERE id = ?", [$id]);
    if (empty($editRecord)) {
        $_SESSION['error'] = "Record not found";
        redirect(ADMIN_URL . $FileName);
    }
}

// ============================================================================
// GET ALL RECORDS FOR LISTING
// ============================================================================
$allRecords = db_get_rows("SELECT * FROM home_image ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .image-preview {
            max-width: 100px;
            max-height: 60px;
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 2px;
        }
        .current-image {
            margin-top: 10px;
        }
        .current-image img {
            max-height: 50px;
            border-radius: 4px;
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
                                <div class="col-md-9"></div>
                                <div class="col-md-3">
                                    <a href="<?= ADMIN_URL . $FileName ?>?action=add" class="btn btn-default" style="float:right">
                                        <i class="fa fa-plus"></i> Add New Record
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ADD FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="title">Title <span class="text-danger">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control required" name="title" id="title" 
                                                   value="<?= e($_POST['title'] ?? '') ?>" placeholder="Enter title">
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="title_1">Short Description</label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control" name="title_1" id="title_1" 
                                                   value="<?= e($_POST['title_1'] ?? '') ?>" placeholder="Enter short description">
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="picons">Icon Image</label>
                                        <div class="col-lg-10">
                                            <input type="file" class="form-control" name="picons" id="picons" accept="image/*">
                                            <p class="help-block">Allowed formats: JPG, PNG, JPEG, GIF. Max size: 2MB</p>
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="status">Status</label>
                                        <div class="col-lg-10">
                                            <select name="status" class="form-control" id="status">
                                                <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                                <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button type="submit" name="addnewrecord" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Submit
                                            </button>
                                            <a href="<?= ADMIN_URL . $FileName ?>" class="btn btn-default">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== EDIT FORM ==================== -->
                        <?php elseif ($action == 'edit' && $editRecord): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="picons_old" value="<?= e($editRecord['picons']) ?>">

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="title">Title <span class="text-danger">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control required" name="title" id="title" 
                                                   value="<?= e($editRecord['title']) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="title_1">Short Description</label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control" name="title_1" id="title_1" 
                                                   value="<?= e($editRecord['title_1']) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="picons">Icon Image</label>
                                        <div class="col-lg-10">
                                            <input type="file" class="form-control" name="picons" id="picons" accept="image/*">
                                            <?php if (!empty($editRecord['picons'])): ?>
                                                <div class="current-image">
                                                    <strong>Current Image:</strong><br>
                                                    <img src="<?= SITE_URL ?>uploads/<?= e($editRecord['picons']) ?>" class="image-preview">
                                                </div>
                                            <?php endif; ?>
                                            <p class="help-block">Leave empty to keep current image. Allowed: JPG, PNG, JPEG, GIF</p>
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label" for="status">Status</label>
                                        <div class="col-lg-10">
                                            <select name="status" class="form-control" id="status">
                                                <option value="1" <?= selected($editRecord['status'], '1') ?>>Active</option>
                                                <option value="0" <?= selected($editRecord['status'], '0') ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group clearfix">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button type="submit" name="udpaterecord" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Update
                                            </button>
                                            <a href="<?= ADMIN_URL . $FileName ?>" class="btn btn-default">Back</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== LIST ALL RECORDS ==================== -->
                        <?php else: ?>
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Short Description</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($allRecords)): ?>
                                            <?php $i = 0; foreach ($allRecords as $record): $i++; ?>
                                                <tr>
                                                    <td><?= $i ?></td>
                                                    <td><?= e($record['title']) ?></td>
                                                    <td><?= e($record['title_1']) ?></td>
                                                    <td>
                                                        <?php if (!empty($record['picons'])): ?>
                                                            <img src="<?= SITE_URL ?>uploads/<?= e($record['picons']) ?>" class="image-preview">
                                                        <?php else: ?>
                                                            <span class="text-muted">No image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($record['status'] == '1'): ?>
                                                            <span class="label label-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="label label-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= ADMIN_URL . $FileName ?>?action=edit&id=<?= e($record['id']) ?>" class="table-action-btn" title="Edit">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                        <a href="javascript:del('<?= ADMIN_URL . $FileName ?>?action=delete&id=<?= e($record['id']) ?>')" class="table-action-btn" title="Delete">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No records found. Click "Add New Record" to create one.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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