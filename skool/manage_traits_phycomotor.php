<?php
/**
 * Manage Traits And Psychomotor - Modern PHP 8.x
 * Manage: Traits, Phycomotor skills, and Rating Scales
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Manage Traits And Psychomotor";
$FileName = 'manage_traits_phycomotor.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// TRAITS CRUD
// ============================================================================
if (isset($_POST['add_trait'])) {
    $trait = trim($_POST['trait'] ?? '');
    
    if (empty($trait)) {
        $stat['error'] = "Trait name is required";
    } else {
        $lastId = db_get_val("SELECT id FROM manage_traits ORDER BY id DESC") ?? 0;
        $newId = $lastId + 1;
        $randomId = randomFix(15) . '-' . $newId;

        db_insert("manage_traits", [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'trait' => $trait,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId,
        ]);
        
        $_SESSION['success'] = "Trait added successfully";
        redirect($FileName . '?action=manage_trait');
    }
}

if (isset($_POST['edit_trait']) && !empty($randomid)) {
    $trait = trim($_POST['trait'] ?? '');
    if (!empty($trait)) {
        db_update("manage_traits", ['trait' => $trait], "randomid = ?", [$randomid]);
        $_SESSION['success'] = "Trait updated successfully";
    }
    redirect($FileName . '?action=manage_trait');
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_trait' && !empty($randomid)) {
    db_delete("manage_traits", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Trait deleted successfully";
    redirect($FileName . '?action=manage_trait');
}

// ============================================================================
// PHYCOMOTOR CRUD
// ============================================================================
if (isset($_POST['add_phycomotor'])) {
    $phycomotor = trim($_POST['phycomotor'] ?? '');
    
    if (empty($phycomotor)) {
        $stat['error'] = "Phycomotor name is required";
    } else {
        db_insert("manage_phycomotor", [
            'phycomotor' => $phycomotor,
            'userid' => $_SESSION['userid'] ?? 0,
            'usertype' => $_SESSION['usertype'] ?? '',
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => randomFix(10),
        ]);
        $_SESSION['success'] = "Phycomotor added successfully";
        redirect($FileName . '?action=manage_phycomotor');
    }
}

if (isset($_POST['edit_phycomotor']) && !empty($randomid)) {
    $phycomotor = trim($_POST['phycomotor'] ?? '');
    if (!empty($phycomotor)) {
        db_update("manage_phycomotor", ['phycomotor' => $phycomotor], "randomid = ?", [$randomid]);
        $_SESSION['success'] = "Phycomotor updated successfully";
    }
    redirect($FileName . '?action=manage_phycomotor');
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_phycomotor' && !empty($randomid)) {
    db_delete("manage_phycomotor", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Phycomotor deleted successfully";
    redirect($FileName . '?action=manage_phycomotor');
}

// ============================================================================
// SCALE CRUD
// ============================================================================
if (isset($_POST['add_scale'])) {
    $rating = trim($_POST['rating'] ?? '');
    $review = trim($_POST['review'] ?? '');
    
    $errors = [];
    if (empty($rating)) $errors[] = "Rating is required";
    if (empty($review)) $errors[] = "Review is required";
    
    if (empty($errors)) {
        db_insert("school_scale", [
            'review' => $review,
            'rating' => $rating,
            'userid' => $_SESSION['userid'] ?? 0,
            'usertype' => $_SESSION['usertype'] ?? '',
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => randomFix(10),
        ]);
        $_SESSION['success'] = "Scale added successfully";
        redirect($FileName . '?action=grade');
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

if (isset($_POST['edit_scale']) && !empty($randomid)) {
    $rating = trim($_POST['rating'] ?? '');
    $review = trim($_POST['review'] ?? '');
    
    db_update("school_scale", ['review' => $review, 'rating' => $rating], "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Scale updated successfully";
    redirect($FileName . '?action=grade');
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_scale' && !empty($randomid)) {
    db_delete("school_scale", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Scale deleted successfully";
    redirect($FileName . '?action=grade');
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$traits = db_get_rows("SELECT * FROM manage_traits WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$phycomotors = db_get_rows("SELECT * FROM manage_phycomotor WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$scales = db_get_rows("SELECT * FROM school_scale WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .management-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        /* Tabs */
        .nav-tabs-custom { display: flex; gap: 5px; border-bottom: 2px solid #e0e0e0; margin-bottom: 25px; flex-wrap: wrap; }
        .nav-tab { padding: 12px 28px; background: #f5f5f5; border-radius: 30px 30px 0 0; text-decoration: none; color: #333; font-weight: 600; transition: all 0.2s; }
        .nav-tab:hover { background: #e0e0e0; }
        .nav-tab.active { background: #1B3058; color: white; }
        
        /* Cards */
        .card { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
        .card-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 18px 25px; }
        .card-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
        .card-body { padding: 25px; }
        
        /* Form */
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 200px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #e0e0e0; border-radius: 12px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        
        /* Buttons */
        .btn { padding: 12px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-icon { background: transparent; border: none; cursor: pointer; font-size: 16px; transition: all 0.2s; }
        .btn-edit { color: #17a2b8; }
        .btn-edit:hover { color: #0f6674; transform: scale(1.1); }
        .btn-delete { color: #dc3545; }
        .btn-delete:hover { color: #a71d2a; transform: scale(1.1); }
        
        /* Tables */
        .table-wrapper { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .data-table tr:hover { background: #fafafa; }
        
        /* Search Bar */
        .search-bar { margin-bottom: 20px; display: flex; justify-content: flex-end; }
        .search-input { padding: 10px 15px; border: 1px solid #e0e0e0; border-radius: 30px; width: 250px; font-size: 14px; }
        .search-input:focus { outline: none; border-color: #1B3058; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 50px; color: #999; }
        .empty-state i { font-size: 48px; color: #ccc; margin-bottom: 15px; display: block; }
        
        /* Loading Spinner */
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-loading { opacity: 0.7; pointer-events: none; }
        
        /* Toast Notification */
        .toast-notification { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; border-radius: 10px; color: white; z-index: 9999; animation: slideIn 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .toast-success { background: #28a745; }
        .toast-error { background: #dc3545; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        /* Inline Edit */
        .inline-input { padding: 6px 10px; border: 1px solid #1B3058; border-radius: 8px; width: 100%; max-width: 200px; }
        .action-cell { white-space: nowrap; }
        
        @media (max-width: 768px) { 
            .form-row { flex-direction: column; } 
            .search-bar { justify-content: flex-start; }
            .search-input { width: 100%; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="management-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-cogs"></i> <?= $PageTitle ?></h2>
                    <p>Manage traits, psychomotor skills, and rating scales</p>
                </div>

                <?= showMessage($stat) ?>

                <!-- Tabs Navigation -->
                <div class="nav-tabs-custom">
                    <a href="?action=manage_trait" class="nav-tab <?= ($action == '' || $action == 'manage_trait') ? 'active' : '' ?>">
                        <i class="fa fa-list"></i> Manage Traits
                    </a>
                    <a href="?action=manage_phycomotor" class="nav-tab <?= ($action == 'manage_phycomotor') ? 'active' : '' ?>">
                        <i class="fa fa-male"></i> Manage Psychomotor
                    </a>
                    <a href="?action=grade" class="nav-tab <?= ($action == 'grade') ? 'active' : '' ?>">
                        <i class="fa fa-star"></i> Manage Scale
                    </a>
                </div>

                <!-- ============================================================ -->
                <!-- MANAGE TRAITS TAB -->
                <!-- ============================================================ -->
                <?php if ($action == '' || $action == 'manage_trait'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-plus-circle"></i> Add New Trait</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Trait Name</label>
                                        <input type="text" name="trait" class="form-control" placeholder="e.g., PUNCTUALITY, NEATNESS" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" name="add_trait" class="btn btn-primary"><i class="fa fa-plus"></i> Add Trait</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                            <h3><i class="fa fa-list"></i> Traits List</h3>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="traitSearch" class="search-input" placeholder="🔍 Search traits...">
                            </div>
                            <div class="table-wrapper">
                                <table class="data-table" id="traitsTable">
                                    <thead>
                                        <tr><th>#</th><th>Trait Name</th><th style="width: 120px;">Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($traits)): ?>
                                            <?php $i = 1; foreach ($traits as $trait): ?>
                                                <tr data-search="<?= strtolower($trait['trait']) ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?php if ($randomid == $trait['randomid']): ?>
                                                            <form method="post" style="display: inline-block; width: 100%;" onsubmit="showButtonLoading(this)">
                                                                <input type="hidden" name="randomid" value="<?= $trait['randomid'] ?>">
                                                                <input type="text" name="trait" value="<?= htmlspecialchars($trait['trait']) ?>" class="inline-input" required>
                                                                <button type="submit" name="edit_trait" class="btn btn-success btn-sm" style="margin-top: 5px;"><i class="fa fa-check"></i> Save</button>
                                                                <a href="?action=manage_trait" class="btn btn-sm" style="background:#6c757d; color:white; padding:5px 10px; border-radius:6px; text-decoration:none;">Cancel</a>
                                                            </form>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($trait['trait']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-cell">
                                                        <?php if ($randomid != $trait['randomid']): ?>
                                                            <a href="?action=manage_trait&randomid=<?= $trait['randomid'] ?>" class="btn-icon btn-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        <?php endif; ?>
                                                        <a href="javascript:deleteItem('<?= $FileName ?>?action=delete_trait&randomid=<?= $trait['randomid'] ?>', 'trait')" class="btn-icon btn-delete" title="Delete"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="empty-state">No traits found. Add your first trait above.<?= $create_by_userid ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- MANAGE PSYCHOMOTOR TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'manage_phycomotor'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-plus-circle"></i> Add New Psychomotor Skill</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Psychomotor Name</label>
                                        <input type="text" name="phycomotor" class="form-control" placeholder="e.g., GAMES, HANDWRITING, SPORTS" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" name="add_phycomotor" class="btn btn-primary"><i class="fa fa-plus"></i> Add Psychomotor</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                            <h3><i class="fa fa-list"></i> Psychomotor Skills List</h3>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="phycomotorSearch" class="search-input" placeholder="🔍 Search psychomotor skills...">
                            </div>
                            <div class="table-wrapper">
                                <table class="data-table" id="phycomotorTable">
                                    <thead>
                                        <tr><th>#</th><th>Psychomotor Name</th><th style="width: 120px;">Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($phycomotors)): ?>
                                            <?php $i = 1; foreach ($phycomotors as $phy): ?>
                                                <tr data-search="<?= strtolower($phy['phycomotor']) ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?php if ($randomid == $phy['randomid']): ?>
                                                            <form method="post" style="display: inline-block; width: 100%;" onsubmit="showButtonLoading(this)">
                                                                <input type="hidden" name="randomid" value="<?= $phy['randomid'] ?>">
                                                                <input type="text" name="phycomotor" value="<?= htmlspecialchars($phy['phycomotor']) ?>" class="inline-input" required>
                                                                <button type="submit" name="edit_phycomotor" class="btn btn-success btn-sm" style="margin-top: 5px;"><i class="fa fa-check"></i> Save</button>
                                                                <a href="?action=manage_phycomotor" class="btn btn-sm" style="background:#6c757d; color:white; padding:5px 10px; border-radius:6px; text-decoration:none;">Cancel</a>
                                                            </form>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($phy['phycomotor']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-cell">
                                                        <?php if ($randomid != $phy['randomid']): ?>
                                                            <a href="?action=manage_phycomotor&randomid=<?= $phy['randomid'] ?>" class="btn-icon btn-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        <?php endif; ?>
                                                        <a href="javascript:deleteItem('<?= $FileName ?>?action=delete_phycomotor&randomid=<?= $phy['randomid'] ?>', 'psychomotor skill')" class="btn-icon btn-delete" title="Delete"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="empty-state">No psychomotor skills found. Add your first one above.<?= $create_by_userid ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- MANAGE SCALE TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'grade'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-plus-circle"></i> Add New Rating Scale</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Rating</label>
                                        <input type="text" name="rating" class="form-control" placeholder="e.g., 5, 4, 3, 2, 1" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Review</label>
                                        <input type="text" name="review" class="form-control" placeholder="e.g., Excellent, Good, Average, Poor" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" name="add_scale" class="btn btn-primary"><i class="fa fa-plus"></i> Add Scale</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                            <h3><i class="fa fa-list"></i> Rating Scales List</h3>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="scaleSearch" class="search-input" placeholder="🔍 Search scales...">
                            </div>
                            <div class="table-wrapper">
                                <table class="data-table" id="scalesTable">
                                    <thead>
                                        <tr><th>#</th><th>Rating</th><th>Review</th><th style="width: 120px;">Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($scales)): ?>
                                            <?php $i = 1; foreach ($scales as $scale): ?>
                                                <tr data-search="<?= strtolower($scale['rating'] . ' ' . $scale['review']) ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?php if ($randomid == $scale['randomid']): ?>
                                                            <form method="post" style="display: inline-block; width: 100%;" onsubmit="showButtonLoading(this)">
                                                                <input type="hidden" name="randomid" value="<?= $scale['randomid'] ?>">
                                                                <input type="text" name="rating" value="<?= htmlspecialchars($scale['rating']) ?>" class="inline-input" style="width: 80px;" required>
                                                                <input type="text" name="review" value="<?= htmlspecialchars($scale['review']) ?>" class="inline-input" required>
                                                                <button type="submit" name="edit_scale" class="btn btn-success btn-sm" style="margin-top: 5px;"><i class="fa fa-check"></i> Save</button>
                                                                <a href="?action=grade" class="btn btn-sm" style="background:#6c757d; color:white; padding:5px 10px; border-radius:6px; text-decoration:none;">Cancel</a>
                                                            </form>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($scale['rating']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($randomid != $scale['randomid']): ?>
                                                            <?= htmlspecialchars($scale['review']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-cell">
                                                        <?php if ($randomid != $scale['randomid']): ?>
                                                            <a href="?action=grade&randomid=<?= $scale['randomid'] ?>" class="btn-icon btn-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        <?php endif; ?>
                                                        <a href="javascript:deleteItem('<?= $FileName ?>?action=delete_scale&randomid=<?= $scale['randomid'] ?>', 'scale')" class="btn-icon btn-delete" title="Delete"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="empty-state">No rating scales found. Add your first scale above.<?= $create_by_userid ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script>
// Search functionality for Traits
const traitSearch = document.getElementById('traitSearch');
if (traitSearch) {
    traitSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#traitsTable tbody tr');
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search') || '';
            row.style.display = searchText.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Search functionality for Phycomotor
const phycomotorSearch = document.getElementById('phycomotorSearch');
if (phycomotorSearch) {
    phycomotorSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#phycomotorTable tbody tr');
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search') || '';
            row.style.display = searchText.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Search functionality for Scales
const scaleSearch = document.getElementById('scaleSearch');
if (scaleSearch) {
    scaleSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#scalesTable tbody tr');
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search') || '';
            row.style.display = searchText.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Delete confirmation
function deleteItem(url, itemName) {
    if (confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.`)) {
        showButtonLoading(null);
        window.location.href = url;
    }
}

// Show loading state on button
function showButtonLoading(form) {
    if (form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.innerHTML = '<span class="spinner"></span> Saving...';
        }
    }
}

// Show toast notification (optional helper)
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `<i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 3000);
}
</script>
</body>
</html>