<?php 
require_once '../config.php'; 
require_once 'inc.session-create.php'; 

$PageTitle = "School Class";
$FileName = 'class.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// ADD CLASS
// ============================================================================
if (isset($_POST['add'])) {
    $sectionId = $_POST['section_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $shortName = trim($_POST['short_name'] ?? '');
    
    if (empty($name)) {
        $stat['error'] = "Class name is required";
    } else {
        // Check for duplicate
        $existing = db_get_val(
            "SELECT id FROM school_class WHERE name = ? AND userid = 0 AND sid = 0",
            [$name]
        );
        
        if (empty($existing)) {
            $data = [
                'section_id' => $sectionId,
                'name' => $name,
                'short_name' => $shortName,
                'sid' => 0,
                'userid' => 0,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("school_class", $data);
            $_SESSION['success'] = "Class added successfully";
            redirect($FileName);
        } else {
            $stat['error'] = "You Cannot Add Same Classes Repeatedly!!";
        }
    }
}

// ============================================================================
// UPDATE CLASS
// ============================================================================
if (isset($_POST['update'])) {
    $classId = $_POST['classid'] ?? 0;
    $editName = trim($_POST['editname'] ?? '');
    $editShortName = trim($_POST['editsname'] ?? '');
    $sectionId = $_POST['section_id'] ?? 0;
    
    if (empty($editName)) {
        $stat['error'] = "Class name is required";
    } else {
        // Check for duplicate (excluding current)
        $existing = db_get_val(
            "SELECT id FROM school_class WHERE name = ? AND id != ? AND userid = 0 AND sid = 0",
            [$editName, $classId]
        );
        
        if (empty($existing)) {
            $data = [
                'name' => $editName,
                'short_name' => $editShortName,
                'section_id' => $sectionId,
            ];
            
            db_update("school_class", $data, "id = ?", [$classId]);
            $_SESSION['success'] = "Class updated successfully";
            redirect($FileName);
        } else {
            $stat['error'] = "You Cannot Add Same Name Repeatedly!!";
        }
    }
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$sections = db_get_rows("SELECT * FROM school_section WHERE userid = 0 AND sid = 0 ORDER BY id ASC");
$classes = db_get_rows("SELECT * FROM school_class WHERE userid = 0 AND sid = 0 ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .inline-edit {
            border: none;
            width: 100%;
            padding: 5px;
            background: transparent;
        }
        .inline-edit:focus {
            outline: none;
            background: #f9f9f9;
        }
        .update-btn {
            background: none;
            border: none;
            color: #1B3058;
            cursor: pointer;
        }
        .update-btn:hover {
            color: #f21151;
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
                                <div class="col-md-12"></div>
                            </div>
                        </div>

                        <!-- ==================== ADD CLASS FORM ==================== -->
                        <div class="card-box">
                            <form role="form" action="" method="post">
                                <div class="form-group clearfix">
                                    <label class="col-lg-2 control-label" for="section_id">Section</label>
                                    <div class="col-lg-10">
                                        <select class="form-control" id="section_id" name="section_id">
                                            <option value="">Select Section</option>
                                            <?php foreach ($sections as $section): ?>
                                                <option value="<?= e($section['id']) ?>" 
                                                    <?= selected($_POST['section_id'] ?? '', $section['id']) ?>>
                                                    <?= e($section['section']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group clearfix">
                                    <label class="col-lg-2 control-label" for="name">Name</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= e($_POST['name'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="form-group clearfix">
                                    <label class="col-lg-2 control-label" for="short_name">Short Name</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="short_name" name="short_name" 
                                               value="<?= e($_POST['short_name'] ?? '') ?>">
                                    </div>
                                </div>

                                <button type="submit" name="add" class="btn btn-default" style="float:right;">
                                    <i class="fa fa-plus"></i> Add Class
                                </button>
                            </form>
                        </div>

                        <!-- ==================== CLASS LIST WITH INLINE EDIT ==================== -->
                        <div class="card-box">
                            <form action="" method="post">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Short Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($classes)): ?>
                                            <?php foreach ($classes as $class): ?>
                                                <tr>
                                                    <td>
                                                        <input type="text" name="editname" 
                                                               value="<?= e($class['name']) ?>" 
                                                               class="inline-edit">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="editsname" 
                                                               value="<?= e($class['short_name']) ?>" 
                                                               class="inline-edit">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="classid" value="<?= e($class['id']) ?>">
                                                        <input type="hidden" name="section_id" value="<?= e($class['section_id']) ?>">
                                                        <button type="submit" name="update" class="update-btn" title="Update">
                                                            <i class="fa fa-save"></i> Save
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No classes found. Please add a class.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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