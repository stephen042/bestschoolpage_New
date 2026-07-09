<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$PageTitle = "Section";
$FileName = 'section.php';

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
// ADD SECTION
// ============================================================================
if (isset($_POST['add'])) {
    $section = trim($_POST['section'] ?? '');
    $shortName = trim($_POST['short_name'] ?? '');
    
    if (empty($section)) {
        $stat['error'] = "Section name is required";
    } else {
        // Check for duplicate section
        $existing = db_get_val(
            "SELECT id FROM school_section WHERE section = ? AND userid = 0 AND sid = 0",
            [$section]
        );
        
        if (!empty($existing)) {
            $stat['error'] = "Section already exists";
        } else {
            $data = [
                'section' => $section,
                'short_name' => ($section == 'OTHERS') ? $shortName : $section,
                'sid' => 0,
                'userid' => 0,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("school_section", $data);
            $_SESSION['success'] = "Submitted Successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// UPDATE SECTION
// ============================================================================
elseif (isset($_POST['update'])) {
    $sectionId = $_POST['section_id'] ?? 0;
    $editSection = trim($_POST['editsection'] ?? '');
    $shortName = trim($_POST['short_name'] ?? '');
    
    if (empty($editSection)) {
        $stat['error'] = "Section name is required";
    } elseif (empty($sectionId)) {
        $stat['error'] = "Invalid section ID";
    } else {
        // Check for duplicate (excluding current)
        $existing = db_get_val(
            "SELECT id FROM school_section WHERE section = ? AND id != ? AND userid = 0 AND sid = 0",
            [$editSection, $sectionId]
        );
        
        if (!empty($existing)) {
            $stat['error'] = "Section already exists";
        } else {
            $data = [
                'section' => $editSection,
                'short_name' => ($editSection == 'OTHERS') ? $shortName : $editSection,
            ];
            
            db_update("school_section", $data, "id = ?", [$sectionId]);
            $_SESSION['success'] = "Updated Successfully";
            redirect($FileName);
        }
    }
}

// ============================================================================
// DELETE SECTION
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    // Check if section is being used in other tables
    $inUse = db_get_val("SELECT id FROM school_class WHERE section_id = ? LIMIT 1", [$id]);
    if (empty($inUse)) {
        $inUse = db_get_val("SELECT id FROM school_subject WHERE section_id = ? LIMIT 1", [$id]);
    }
    
    if (!empty($inUse)) {
        $_SESSION['error'] = "Cannot delete this section as it is being used by classes or subjects";
    } else {
        db_delete("school_section", "id = ?", [$id]);
        $_SESSION['success'] = 'Deleted Successfully';
    }
    redirect($FileName);
}

// ============================================================================
// GET ALL SECTIONS FOR LISTING
// ============================================================================
$allSections = db_get_rows("SELECT * FROM school_section WHERE userid = 0 AND sid = 0 ORDER BY id DESC");
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
        .section-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: normal;
        }
        .section-creche { background: #e8f5e9; color: #2e7d32; }
        .section-nursery { background: #e3f2fd; color: #1565c0; }
        .section-primary { background: #fff3e0; color: #e65100; }
        .section-secondary { background: #fce4ec; color: #c2185b; }
        .section-others { background: #f3e5f5; color: #7b1fa2; }
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
                    <div class="col-md-6 col-md-offset-3">
                        <!-- ==================== ADD SECTION FORM ==================== -->
                        <div class="card-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title text-center">
                                        <i class="fa fa-plus-circle"></i> Add New Section
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <form role="form" action="" method="post">
                                        <div class="form-group">
                                            <label for="section">Section Name <span class="text-danger">*</span></label>
                                            <select class="form-control" id="section" name="section" onchange="showTextBox(this.value);">
                                                <option value="CRECHE" <?= selected($_POST['section'] ?? '', 'CRECHE') ?>>CRECHE</option>
                                                <option value="NURSERY" <?= selected($_POST['section'] ?? '', 'NURSERY') ?>>NURSERY</option>
                                                <option value="PRIMARY" <?= selected($_POST['section'] ?? '', 'PRIMARY') ?>>PRIMARY</option>
                                                <option value="SECONDARY" <?= selected($_POST['section'] ?? '', 'SECONDARY') ?>>SECONDARY</option>
                                                <option value="OTHERS" <?= selected($_POST['section'] ?? '', 'OTHERS') ?>>OTHERS</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="otherstext" style="display: none;">
                                            <label for="short_name">Others Name</label>
                                            <input type="text" class="form-control" id="short_name" name="short_name" 
                                                   value="<?= e($_POST['short_name'] ?? '') ?>" 
                                                   placeholder="Enter custom section name">
                                        </div>

                                        <button type="submit" name="add" class="btn btn-primary btn-block">
                                            <i class="fa fa-save"></i> Add Section
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== SECTIONS LIST WITH INLINE EDIT ==================== -->
                        <div class="card-box">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">
                                        <i class="fa fa-list"></i> Section List
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Section Name</th>
                                                    <th>Others Name</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($allSections)): ?>
                                                    <?php $i = 0; foreach ($allSections as $section): $i++; 
                                                        $sectionClass = '';
                                                        if (strpos($section['section'], 'CRECHE') !== false) $sectionClass = 'section-creche';
                                                        elseif (strpos($section['section'], 'NURSERY') !== false) $sectionClass = 'section-nursery';
                                                        elseif (strpos($section['section'], 'PRIMARY') !== false) $sectionClass = 'section-primary';
                                                        elseif (strpos($section['section'], 'SECONDARY') !== false) $sectionClass = 'section-secondary';
                                                        elseif ($section['section'] == 'OTHERS') $sectionClass = 'section-others';
                                                    ?>
                                                        <form action="" method="post">
                                                            <input type="hidden" name="section_id" value="<?= e($section['id']) ?>">
                                                            <tr>
                                                                <td><?= $i ?></td>
                                                                <td>
                                                                    <input type="text" name="editsection" class="inline-edit" 
                                                                           value="<?= e($section['section']) ?>">
                                                                 </td>
                                                                 <td>
                                                                    <?php if ($section['section'] == 'OTHERS'): ?>
                                                                        <input type="text" name="short_name" class="inline-edit" 
                                                                               value="<?= e($section['short_name']) ?>">
                                                                    <?php else: ?>
                                                                        <span class="text-muted">—</span>
                                                                        <input type="hidden" name="short_name" value="">
                                                                    <?php endif; ?>
                                                                 </td>
                                                                <td class="action-buttons">
                                                                    <button type="submit" name="update" class="update-btn" title="Update">
                                                                        <i class="fa fa-save"></i>
                                                                    </button>
                                                                    <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($section['id']) ?>')" 
                                                                       class="table-action-btn" title="Delete">
                                                                        <i class="fa fa-times"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </form>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">No sections found. Please add a section.<?= e($create_by_userid ?? '') ?></td>
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
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<script>
function showTextBox(val) {
    var otherstext = document.getElementById("otherstext");
    var shortName = document.getElementById("short_name");
    
    if (val === "OTHERS") {
        otherstext.style.display = "block";
        shortName.required = true;
    } else {
        otherstext.style.display = "none";
        shortName.required = false;
        shortName.value = "";
    }
}

// Show text box if OTHERS was previously selected (after form error)
$(document).ready(function() {
    var selectedValue = $('#section').val();
    if (selectedValue === "OTHERS") {
        $('#otherstext').show();
    }
});
</script>

<?php include('inc.js.php'); ?>
</body>
</html>