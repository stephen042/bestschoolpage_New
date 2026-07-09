<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "File Name";
$FileName = "file_name.php";

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// ADD RECORD
// ============================================================================
if (isset($_POST['addnewrecord'])) {
    $title = trim($_POST['title'] ?? '');
    $fileName = trim($_POST['file_name'] ?? '');
    
    if (empty($title)) {
        $stat['error'] = "Title is required";
    } elseif (empty($fileName)) {
        $stat['error'] = "File name is required";
    } else {
        // Check for duplicate
        $existing = db_get_val(
            "SELECT id FROM school_filename WHERE title = ? AND create_by_userid = ?",
            [$title, $create_by_userid]
        );
        
        if (!empty($existing)) {
            $stat['error'] = "Title already exists";
        } else {
            $lastId = db_get_val("SELECT id FROM school_filename ORDER BY id DESC") ?? 0;
            $newId = $lastId + 1;
            $randomId = randomFix(15) . '-' . $newId;
            
            $data = [
                'usertype' => $create_by_usertype,
                'userid' => $_SESSION['userid'] ?? 0,
                'title' => $title,
                'file_name' => $fileName,
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype,
                'randomid' => $randomId,
            ];
            
            db_insert("school_filename", $data);
            $_SESSION['success'] = "Registered Successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// UPDATE RECORD
// ============================================================================
elseif (isset($_POST['updaterecord']) && !empty($id)) {
    $title = trim($_POST['title'] ?? '');
    $fileName = trim($_POST['file_name'] ?? '');
    
    if (empty($title)) {
        $stat['error'] = "Title is required";
    } elseif (empty($fileName)) {
        $stat['error'] = "File name is required";
    } else {
        // Check for duplicate (excluding current)
        $existing = db_get_val(
            "SELECT id FROM school_filename WHERE title = ? AND id != ? AND create_by_userid = ?",
            [$title, $id, $create_by_userid]
        );
        
        if (!empty($existing)) {
            $stat['error'] = "Title already exists";
        } else {
            $data = [
                'usertype' => $create_by_usertype,
                'userid' => $_SESSION['userid'] ?? 0,
                'title' => $title,
                'file_name' => $fileName,
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype,
            ];
            
            db_update("school_filename", $data, "id = ?", [$id]);
            $_SESSION['success'] = "Updated Successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// DELETE RECORD
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    db_delete("school_filename", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect($FileName);
}

// ============================================================================
// GET DATA FOR EDIT/VIEW
// ============================================================================
$editRecord = null;
if (($action == 'edit' || $action == 'view') && !empty($id)) {
    $editRecord = db_get_row("SELECT * FROM school_filename WHERE id = ?", [$id]);
    if (empty($editRecord)) {
        $_SESSION['error'] = "Record not found";
        redirect($FileName);
    }
}

// ============================================================================
// GET ALL RECORDS FOR LISTING
// ============================================================================
$allRecords = db_get_rows("SELECT * FROM school_filename ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .filename-badge {
            background: #e8f0fe;
            color: #1a73e8;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
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
                        <div class="card-box">
                            <div class="row">
                                <div class="col-sm-8"></div>
                                <div class="col-sm-4">
                                    <a href="<?= e($FileName) ?>?action=add" class="btn btn-primary" style="float:right">
                                        <i class="fa fa-plus"></i> Add New Record
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ADD FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <div class="form-container">
                                    <form action="" method="post">
                                        <div class="form-group">
                                            <label>Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control" 
                                                   value="<?= e($_POST['title'] ?? '') ?>" 
                                                   placeholder="Enter title (e.g., Admission Form)">
                                        </div>

                                        <div class="form-group">
                                            <label>File Name <span class="text-danger">*</span></label>
                                            <input type="text" name="file_name" class="form-control" 
                                                   value="<?= e($_POST['file_name'] ?? '') ?>" 
                                                   placeholder="Enter system file name (e.g., admission_form)">
                                            <small class="text-muted">This is the internal file name used in the system</small>
                                        </div>

                                        <div class="form-group text-right">
                                            <button type="submit" name="addnewrecord" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Submit
                                            </button>
                                            <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <!-- ==================== EDIT FORM ==================== -->
                        <?php elseif ($action == 'edit' && $editRecord): ?>
                            <div class="card-box">
                                <div class="form-container">
                                    <form action="" method="post">
                                        <div class="form-group">
                                            <label>Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control" 
                                                   value="<?= e($editRecord['title']) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>File Name <span class="text-danger">*</span></label>
                                            <input type="text" name="file_name" class="form-control" 
                                                   value="<?= e($editRecord['file_name']) ?>" required>
                                        </div>

                                        <div class="form-group text-right">
                                            <button type="submit" name="updaterecord" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Update
                                            </button>
                                            <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <!-- ==================== VIEW DETAILS ==================== -->
                        <?php elseif ($action == 'view' && $editRecord): ?>
                            <div class="card-box">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Title</th>
                                        <td><?= e($editRecord['title']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>File Name</th>
                                        <td><span class="filename-badge"><?= e($editRecord['file_name']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Random ID</th>
                                        <td><?= e($editRecord['randomid']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Created By</th>
                                        <td><?= e($editRecord['create_by_userid'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                                <div class="text-right">
                                    <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                </div>
                            </div>

                        <!-- ==================== LIST ALL RECORDS ==================== -->
                        <?php else: ?>
                            <div class="card-box">
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>File Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($allRecords)): ?>
                                                <?php $i = 0; foreach ($allRecords as $record): $i++; ?>
                                                    <tr>
                                                        <td><?= $i ?></td>
                                                        <td><?= e($record['title']) ?></td>
                                                        <td><span class="filename-badge"><?= e($record['file_name']) ?></span></td>
                                                        <td>
                                                            <a href="<?= e($FileName) ?>?action=view&id=<?= e($record['id']) ?>" 
                                                               class="table-action-btn" title="View">
                                                                <i class="fa fa-search"></i>
                                                            </a>
                                                            <a href="<?= e($FileName) ?>?action=edit&id=<?= e($record['id']) ?>" 
                                                               class="table-action-btn" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($record['id']) ?>')" 
                                                               class="table-action-btn" title="Delete">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">
                                                        <div class="empty-state">
                                                            <i class="fa fa-file-o"></i>
                                                            <h4>No Records Found</h4>
                                                            <p>Click the "Add New Record" button to add your first file name.</p>
                                                            <a href="<?= e($FileName) ?>?action=add" class="btn btn-primary">
                                                                <i class="fa fa-plus"></i> Add New Record
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
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