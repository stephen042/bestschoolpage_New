<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "Subject";
$FileName = 'school_subject.php';

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
// ADD SUBJECT
// ============================================================================
if (isset($_POST['submit'])) {
    $sessionId = $_POST['selectsession'] ?? 0;
    $sectionId = $_POST['selectsection'] ?? 0;
    $classId = $_POST['selectclass'] ?? 0;
    $subject = trim($_POST['subject'] ?? '');
    
    $errors = [];
    if (empty($sessionId)) $errors[] = "Session is required";
    if (empty($sectionId)) $errors[] = "Section is required";
    if (empty($classId)) $errors[] = "Class is required";
    if (empty($subject)) $errors[] = "Subject name is required";
    
    if (empty($errors)) {
        // Check for duplicate subject in same class
        $existing = db_get_val(
            "SELECT id FROM school_subject WHERE subject = ? AND class_id = ? AND userid = 0 AND sid = 0",
            [$subject, $classId]
        );
        
        if (empty($existing)) {
            $data = [
                'session_id' => $sessionId,
                'section_id' => $sectionId,
                'class_id' => $classId,
                'subject' => $subject,
                'sid' => 0,
                'userid' => 0,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("school_subject", $data);
            $_SESSION['success'] = "Subject added successfully";
            redirect($FileName);
        } else {
            $stat['error'] = "Subject already exists for this class";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// UPDATE SUBJECT
// ============================================================================
elseif (isset($_POST['update'])) {
    $subjectId = $_POST['subjectid'] ?? 0;
    $editSubject = trim($_POST['editsubject'] ?? '');
    
    if (empty($subjectId) || empty($editSubject)) {
        $stat['error'] = "Invalid data";
    } else {
        $data = ['subject' => $editSubject];
        db_update("school_subject", $data, "id = ?", [$subjectId]);
        $_SESSION['success'] = "Updated Successfully";
        redirect($FileName);
    }
}

// ============================================================================
// DELETE SUBJECT
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Check if subject is being used
    $inUse = db_get_val("SELECT id FROM subject_teacher WHERE school_subject = ? LIMIT 1", [$id]);
    if (empty($inUse)) {
        $inUse = db_get_val("SELECT id FROM score_entry_time_frame WHERE assesment_id = ? LIMIT 1", [$id]);
    }
    
    if (!empty($inUse)) {
        $_SESSION['error'] = "Cannot delete this subject as it is being used";
    } else {
        db_delete("school_subject", "id = ?", [$id]);
        $_SESSION['success'] = 'Deleted Successfully';
    }
    redirect($FileName);
}

// ============================================================================
// GET DATA FOR EDIT (AJAX response for class dropdown)
// ============================================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == 'getclass') {
    $sectionId = $_GET['section_id'] ?? 0;
    $classes = db_get_rows("SELECT id, name FROM school_class WHERE section_id = ? AND userid = 0 AND sid = 0 ORDER BY name", [$sectionId]);
    
    echo '<option value="">Select Class</option>';
    foreach ($classes as $class) {
        echo '<option value="' . e($class['id']) . '">' . e($class['name']) . '</option>';
    }
    exit;
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$sessions = db_get_rows("SELECT * FROM school_session WHERE userid = 0 AND sid = 0 ORDER BY id DESC");
$sections = db_get_rows("SELECT * FROM school_section WHERE userid = 0 AND sid = 0 ORDER BY section ASC");
$subjects = db_get_rows("SELECT * FROM school_subject WHERE userid = 0 AND sid = 0 ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .inline-edit {
            border: none;
            padding: 5px;
            width: 100%;
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
        .action-buttons {
            white-space: nowrap;
        }
        .subject-info {
            font-size: 12px;
            color: #999;
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
                                    <a href="<?= e($FileName) ?>?action=add" class="btn btn-default" style="float:right">
                                        <i class="fa fa-plus"></i> Add New Record
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ADD FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <form role="form" action="" method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="selectsession">Session <span class="text-danger">*</span></label>
                                                <select class="form-control" name="selectsession" id="selectsession">
                                                    <option value="">Select Session</option>
                                                    <?php foreach ($sessions as $session): ?>
                                                        <option value="<?= e($session['id']) ?>" <?= selected($_POST['selectsession'] ?? '', $session['id']) ?>>
                                                            <?= e($session['session']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="selectsection">Section <span class="text-danger">*</span></label>
                                                <select class="form-control" name="selectsection" id="selectsection" onchange="getClass()">
                                                    <option value="">Select Section</option>
                                                    <?php foreach ($sections as $section): ?>
                                                        <option value="<?= e($section['id']) ?>" <?= selected($_POST['selectsection'] ?? '', $section['id']) ?>>
                                                            <?= e($section['section'] == 'OTHERS' ? $section['short_name'] : $section['section']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="selectclass">Class <span class="text-danger">*</span></label>
                                                <select class="form-control" name="selectclass" id="selectclass">
                                                    <option value="">Select Class</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="subject">Subject Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="subject" name="subject" 
                                                       value="<?= e($_POST['subject'] ?? '') ?>" placeholder="Enter subject name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Submit
                                        </button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- ==================== SUBJECTS LIST ==================== -->
                        <div class="card-box">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Session</th>
                                            <th>Section</th>
                                            <th>Class</th>
                                            <th>Subject</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($subjects)): ?>
                                            <?php $i = 0; foreach ($subjects as $subject): $i++; 
                                                $sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$subject['session_id']]);
                                                $sectionName = db_get_val("SELECT section FROM school_section WHERE id = ?", [$subject['section_id']]);
                                                $className = db_get_val("SELECT name FROM school_class WHERE id = ?", [$subject['class_id']]);
                                            ?>
                                                <form action="" method="post">
                                                    <input type="hidden" name="subjectid" value="<?= e($subject['id']) ?>">
                                                    <tr>
                                                        <td><?= $i ?></td>
                                                        <td><?= e($sessionName) ?></td>
                                                        <td><?= e($sectionName == 'OTHERS' ? $subject['section_id'] : $sectionName) ?></td>
                                                        <td><?= e($className) ?></td>
                                                        <td>
                                                            <input type="text" name="editsubject" class="inline-edit" 
                                                                   value="<?= e($subject['subject']) ?>">
                                                        </td>
                                                        <td class="action-buttons">
                                                            <button type="submit" name="update" class="update-btn" title="Update">
                                                                <i class="fa fa-save"></i>
                                                            </button>
                                                            <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($subject['id']) ?>')" 
                                                               class="table-action-btn" title="Delete">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </form>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No subjects found. Please add a subject.<?= e($create_by_userid ?? '') ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>

<script>
function getClass() {
    var sectionId = document.getElementById("selectsection").value;
    
    if (sectionId) {
        $.ajax({
            url: '<?= $FileName ?>',
            type: 'GET',
            data: {
                ajax: 'getclass',
                section_id: sectionId
            },
            success: function(data) {
                document.getElementById('selectclass').innerHTML = data;
            },
            error: function() {
                document.getElementById('selectclass').innerHTML = '<option value="">Error loading classes</option>';
            }
        });
    } else {
        document.getElementById('selectclass').innerHTML = '<option value="">Select Section First</option>';
    }
}

// Preload classes if section is pre-selected (for when form reloads after error)
$(document).ready(function() {
    var selectedSection = $('#selectsection').val();
    if (selectedSection) {
        getClass();
    }
});
</script>
</body>
</html>