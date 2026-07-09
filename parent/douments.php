<?php 
require_once '../config.php'; 
//require_once 'inc.session-create.php'; 

$PageTitle = "Documents";
$FileName = 'documents.php';

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
// FILE UPLOAD HANDLERS
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
                if (!empty($oldImage) && file_exists("../uploads/" . $oldImage)) {
                    unlink("../uploads/" . $oldImage);
                }
                return $newFilename;
            }
        }
    }
    return $oldImage;
}

function handleAndroidIconUpload($file, $oldIcon = '') {
    if (isset($file['name']) && !empty($file['name'])) {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg', 'ico'];
        
        if (in_array($ext, $allowed)) {
            $newFilename = md5(time() . uniqid()) . "_android_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $uploadPath = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                if (!empty($oldIcon) && file_exists("../uploads/" . $oldIcon)) {
                    unlink("../uploads/" . $oldIcon);
                }
                return $newFilename;
            }
        }
    }
    return $oldIcon;
}

// ============================================================================
// ADD DOCUMENT
// ============================================================================
if (isset($_POST['submit'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    if (empty($title)) {
        $stat['error'] = "Title is required";
    } else {
        $image = handleImageUpload($_FILES['image'] ?? []);
        $androidIcon = handleAndroidIconUpload($_FILES['android_icon'] ?? []);
        
        $data = [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'android_icon' => $androidIcon,
            'status' => $status,
            'create_by_userid' => $_SESSION['userid'] ?? 0,
            'create_by_usertype' => $_SESSION['usertype'] ?? '',
        ];
        
        db_insert("slider", $data);
        $_SESSION['success'] = "Submitted Successfully";
        redirect($FileName);
    }
}

// ============================================================================
// UPDATE DOCUMENT
// ============================================================================
elseif (isset($_POST['update']) && !empty($id)) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? '1';
    $oldImage = $_POST['image_old'] ?? '';
    $oldIcon = $_POST['android_icon_old'] ?? '';
    
    if (empty($title)) {
        $stat['error'] = "Title is required";
    } else {
        $image = handleImageUpload($_FILES['image'] ?? [], $oldImage);
        $androidIcon = handleAndroidIconUpload($_FILES['android_icon'] ?? [], $oldIcon);
        
        $data = [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'android_icon' => $androidIcon,
            'status' => $status,
        ];
        
        db_update("slider", $data, "id = ?", [$id]);
        $_SESSION['success'] = "Updated Successfully";
        redirect($FileName);
    }
}

// ============================================================================
// DELETE DOCUMENT
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Get images to delete
    $document = db_get_row("SELECT image, android_icon FROM slider WHERE id = ?", [$id]);
    if (!empty($document['image']) && file_exists("../uploads/" . $document['image'])) {
        unlink("../uploads/" . $document['image']);
    }
    if (!empty($document['android_icon']) && file_exists("../uploads/" . $document['android_icon'])) {
        unlink("../uploads/" . $document['android_icon']);
    }
    
    db_delete("slider", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect($FileName);
}

// ============================================================================
// GET DATA FOR EDIT/VIEW
// ============================================================================
$editRecord = null;
if (($action == 'edit' || $action == 'view') && !empty($id)) {
    $editRecord = db_get_row("SELECT * FROM slider WHERE id = ?", [$id]);
    if (empty($editRecord)) {
        $_SESSION['error'] = "Record not found";
        redirect($FileName);
    }
}

// ============================================================================
// GET ALL DOCUMENTS FOR LISTING
// ============================================================================
$allDocuments = db_get_rows("SELECT * FROM slider ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <link href="assets/css/pak-page.css" rel="stylesheet" type="text/css" />
    <style>
        .document-card {
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .document-card:hover {
            transform: translateY(-5px);
        }
        .document-image {
            height: 150px;
            object-fit: cover;
            width: 100%;
            border-radius: 4px 4px 0 0;
        }
        .document-icon {
            font-size: 48px;
            color: #1B3058;
            text-align: center;
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }
        .action-buttons {
            margin-top: 15px;
        }
        .action-buttons .btn {
            margin-right: 5px;
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
                        <h4 class="page-title">Document/Report Templates</h4>
                        <ol class="breadcrumb">
                            <li><a href="<?= ADMIN_URL ?>">Home</a></li>
                            <li class="active">Documents</li>
                        </ol>
                        <?= showMessage($stat) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <!-- ==================== ADD BUTTON ==================== -->
                        <div class="card-box">
                            <div class="row">
                                <div class="col-md-9"></div>
                                <div class="col-md-3">
                                    <a href="<?= e($FileName) ?>?action=add" class="btn btn-primary" style="float:right">
                                        <i class="fa fa-plus"></i> Add New Document
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ADD FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="title" 
                                                       value="<?= e($_POST['title'] ?? '') ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea class="form-control" name="description" rows="4"><?= e($_POST['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Main Image</label>
                                                <input type="file" class="form-control" name="image" accept="image/*">
                                                <small class="text-muted">Allowed: JPG, PNG, JPEG, GIF</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Android Icon</label>
                                                <input type="file" class="form-control" name="android_icon" accept="image/*">
                                                <small class="text-muted">Allowed: JPG, PNG, JPEG, GIF, ICO, SVG</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                                    <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group text-right">
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Save Document
                                        </button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Cancel</a>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== EDIT FORM ==================== -->
                        <?php elseif ($action == 'edit' && $editRecord): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="image_old" value="<?= e($editRecord['image']) ?>">
                                    <input type="hidden" name="android_icon_old" value="<?= e($editRecord['android_icon']) ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="title" 
                                                       value="<?= e($editRecord['title']) ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea class="form-control" name="description" rows="4"><?= e($editRecord['description']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Main Image</label>
                                                <input type="file" class="form-control" name="image" accept="image/*">
                                                <?php if (!empty($editRecord['image'])): ?>
                                                    <div class="current-image" style="margin-top: 10px;">
                                                        <img src="../uploads/<?= e($editRecord['image']) ?>" style="height: 50px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Android Icon</label>
                                                <input type="file" class="form-control" name="android_icon" accept="image/*">
                                                <?php if (!empty($editRecord['android_icon'])): ?>
                                                    <div class="current-image" style="margin-top: 10px;">
                                                        <img src="../uploads/<?= e($editRecord['android_icon']) ?>" style="height: 50px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="1" <?= selected($editRecord['status'], '1') ?>>Active</option>
                                                    <option value="0" <?= selected($editRecord['status'], '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group text-right">
                                        <button type="submit" name="update" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update Document
                                        </button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Cancel</a>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== VIEW DETAILS ==================== -->
                        <?php elseif ($action == 'view' && $editRecord): ?>
                            <div class="card-box">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr><th width="30%">Title</th><td><?= e($editRecord['title']) ?></td></tr>
                                            <tr><th>Description</th><td><?= nl2br(e($editRecord['description'])) ?></td></tr>
                                            <tr><th>Status</th><td>
                                                <?php if ($editRecord['status'] == '1'): ?>
                                                    <span class="status-badge status-active">Active</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-inactive">Inactive</span>
                                                <?php endif; ?>
                                             </th></tr>
                                            <tr><th>Created At</th><td><?= e($editRecord['create_at'] ?? 'N/A') ?></th></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <td><th>Main Image</th><td>
                                            <?php if (!empty($editRecord['image'])): ?>
                                                <img src="../uploads/<?= e($editRecord['image']) ?>" style="max-height: 150px;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                         </th></tr>
                                        <tr><th>Android Icon</th><td>
                                            <?php if (!empty($editRecord['android_icon'])): ?>
                                                <img src="../uploads/<?= e($editRecord['android_icon']) ?>" style="max-height: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">No icon</span>
                                            <?php endif; ?>
                                         </th></tr>
                                    </div>
                                </div>
                                <div class="form-group text-right">
                                    <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                </div>
                            </div>

                        <!-- ==================== LIST ALL DOCUMENTS (GRID VIEW) ==================== -->
                        <?php else: ?>
                            <div class="row">
                                <?php if (!empty($allDocuments)): ?>
                                    <?php foreach ($allDocuments as $document): ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="card-box document-card">
                                                <?php if (!empty($document['image'])): ?>
                                                    <img src="../uploads/<?= e($document['image']) ?>" class="document-image">
                                                <?php else: ?>
                                                    <div class="document-icon">
                                                        <i class="fa fa-file-pdf-o"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="panel-body">
                                                    <h4><?= e($document['title']) ?></h4>
                                                    <p class="text-muted">
                                                        <?= substr(e($document['description']), 0, 100) ?>
                                                        <?= strlen($document['description']) > 100 ? '...' : '' ?>
                                                    </p>
                                                    <div class="action-buttons">
                                                        <a href="<?= e($FileName) ?>?action=view&id=<?= e($document['id']) ?>" 
                                                           class="btn btn-sm btn-info" title="View">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="<?= e($FileName) ?>?action=edit&id=<?= e($document['id']) ?>" 
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                        <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($document['id']) ?>')" 
                                                           class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                        <?php if ($document['status'] == '1'): ?>
                                                            <span class="pull-right status-badge status-active">Active</span>
                                                        <?php else: ?>
                                                            <span class="pull-right status-badge status-inactive">Inactive</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-md-12">
                                        <div class="empty-state">
                                            <i class="fa fa-file-o"></i>
                                            <h4>No Documents Found</h4>
                                            <p>Click the "Add New Document" button to upload your first document.</p>
                                            <a href="<?= e($FileName) ?>?action=add" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Add New Document
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
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

<script>
$(document).ready(function() {
    $(".selLabel").click(function() {
        $('.dropdown').toggleClass('active');
    });
    
    $(".dropdown-list li").click(function() {
        $('.selLabel').text($(this).text());
        $('.dropdown').removeClass('active');
        $('.selected-item p span').text($('.selLabel').text());
    });
});
</script>
</body>
</html>