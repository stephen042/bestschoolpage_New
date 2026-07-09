<?php
/**
 * ============================================================================
 * MANAGE CLASS TEACHER - STAFF ASSIGNMENT MODULE
 * ============================================================================
 * Description: Assign and remove class teachers per class (permanent until removed)
 * Version: 2.0 (PHP 8.x Compatible) - Modern UI
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// 1. PAGE CONFIGURATION
// ============================================================================
$PageTitle = "Manage Class Teacher";
$FileName = 'manage_class_teacher.php';

// Initialize Validation class
$validate = new Validation();

// Initialize $stat array for messages
$stat = [];
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';

// ============================================================================
// 2. SESSION MESSAGE HANDLING
// ============================================================================
if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (!empty($_SESSION['error'])) {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// 3. CSRF PROTECTION FUNCTIONS
// ============================================================================
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// ============================================================================
// 4. FORM SUBMISSION HANDLER - ASSIGN CLASS TEACHER
// ============================================================================
if (isset($_POST['assignCT'])) {
    // CSRF Validation
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $stat['error'] = "Security validation failed. Please try again.";
    } 
    // Form Validation using Validation class
    else {
        $validate->addRule($_POST['school_class'] ?? '', 'Num', 'School Class', true);
        
        if (!$validate->validate()) {
            $stat['error'] = $validate->errors();
        } 
        elseif (!isset($_POST['staff_id']) || empty($_POST['staff_id'])) {
            $stat['error'] = "Please select at least one Staff Member";
        } 
        else {
            $assignedCount = 0;
            $alreadyAssigned = [];
            
            foreach ($_POST['staff_id'] as $staffId) {
                $staffId = (int)$staffId;
                
                // Permanent assignment: check by staff + class only (no session restriction).
                $existingRecord = db_get_row(
                    "SELECT id, staff_id FROM class_teacher 
                     WHERE staff_id = ? AND school_class = ? 
                     AND create_by_userid = ?",
                    [$staffId, (int)$_POST['school_class'], $_SESSION['userid']]
                );
                
                if (empty($existingRecord['id'])) {
                    $insertData = [
                        'staff_id' => $staffId,
                        'school_session' => 0,
                        'school_class' => (int)$_POST['school_class'],
                        'create_by_userid' => $_SESSION['userid'],
                        'create_by_usertype' => $_SESSION['usertype']
                    ];
                    
                    if (db_insert('class_teacher', $insertData)) {
                        $assignedCount++;
                    }
                } else {
                    $alreadyAssigned[] = $staffId;
                }
            }
            
            if ($assignedCount > 0) {
                $stat['success'] = $assignedCount . " Class Teacher(s) Assigned Successfully!";
                if (!empty($alreadyAssigned)) {
                    $stat['warning'] = count($alreadyAssigned) . " staff member(s) are already assigned to this class.";
                }
            } elseif (!empty($alreadyAssigned)) {
                $stat['error'] = "Selected staff are already assigned to this class.";
            } else {
                $stat['error'] = "No staff members were assigned. Please try again.";
            }
        }
    }
}

// ============================================================================
// 5. DELETE HANDLER - CLASS TEACHER
// ============================================================================
if ($action == 'classdelete') {
    $deleteId = (int)($_GET['id'] ?? 0);
    
    if ($deleteId > 0) {
        // Check if record exists before deleting
        $exists = db_get_val("SELECT id FROM class_teacher WHERE id = ? AND create_by_userid = ?", [$deleteId, $_SESSION['userid']]);
        
        if ($exists) {
            db_delete('class_teacher', 'id = ?', [$deleteId]);
            $_SESSION['success'] = 'Class Teacher Deleted Successfully';
        } else {
            $_SESSION['error'] = 'Record not found or you do not have permission to delete it.';
        }
    } else {
        $_SESSION['error'] = 'Invalid delete request.';
    }
    
    redirect($FileName);
}

// ============================================================================
// 6. GET DATA FOR DISPLAY
// ============================================================================

// Get classes for dropdown
$classes = db_get_rows("SELECT id, name FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$_SESSION['userid']]);

// Get staff members for assignment table
$staffMembers = db_get_rows("SELECT id, staff_id, first_name, last_name FROM staff_manage WHERE create_by_userid = ? ORDER BY first_name ASC", [$_SESSION['userid']]);

// Get assigned class teachers for removal tab
$assignedTeachers = db_get_rows(
    "SELECT ct.*, sc.name as class_name, sm.first_name, sm.last_name, sm.staff_id as staff_id_number
     FROM class_teacher ct
     LEFT JOIN school_class sc ON ct.school_class = sc.id
     LEFT JOIN staff_manage sm ON ct.staff_id = sm.id
     WHERE ct.create_by_userid = ?
     ORDER BY ct.id DESC",
    [$_SESSION['userid']]
);

// Generate CSRF token for the form
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .class-teacher-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
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
        .card-header-secondary { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); }
        .card-header-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .card-header-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
        .card-body { padding: 25px; }
        
        /* Forms */
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 200px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #e0e0e0; border-radius: 12px; font-size: 14px; transition: all 0.2s; background: white; }
        .form-control:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        select.form-control { cursor: pointer; }
        
        /* Buttons */
        .btn { padding: 12px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
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
        
        /* Checkbox styling */
        .checkbox-cell { width: 40px; text-align: center; }
        .checkbox-cell input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .staff-checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; }
        
        /* Search Bar */
        .search-bar { margin-bottom: 20px; display: flex; justify-content: flex-end; }
        .search-input { padding: 10px 15px; border: 1px solid #e0e0e0; border-radius: 30px; width: 250px; font-size: 14px; }
        .search-input:focus { outline: none; border-color: #1B3058; }
        
        /* Selection toolbar */
        .selection-toolbar { margin-bottom: 15px; padding: 10px 15px; background: #f8f9fa; border-radius: 12px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .selection-toolbar .select-all-btn { background: #1B3058; color: white; border: none; padding: 6px 15px; border-radius: 20px; cursor: pointer; font-size: 12px; }
        .selection-toolbar .select-all-btn:hover { background: #f21151; }
        .selection-count { font-size: 13px; color: #666; }
        
        /* Loading Spinner */
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-loading { opacity: 0.7; pointer-events: none; }
        
        /* Badge */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #e65100; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 50px; color: #999; }
        .empty-state i { font-size: 48px; color: #ccc; margin-bottom: 15px; display: block; }
        
        /* Alert messages */
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        @media (max-width: 768px) { 
            .form-row { flex-direction: column; } 
            .search-bar { justify-content: flex-start; }
            .search-input { width: 100%; }
            .nav-tab { padding: 8px 16px; font-size: 12px; }
            .data-table th, .data-table td { padding: 10px 8px; font-size: 12px; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="class-teacher-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-chalkboard-teacher"></i> <?= $PageTitle ?></h2>
                    <p>Assign class teachers to classes permanently and manage existing assignments</p>
                </div>

                <!-- Display Messages -->
                <?php if (!empty($stat['success'])): ?>
                    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= e($stat['success']) ?></div>
                <?php endif; ?>
                <?php if (!empty($stat['error'])): ?>
                    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?= e($stat['error']) ?></div>
                <?php endif; ?>
                <?php if (!empty($stat['warning'])): ?>
                    <div class="alert alert-warning"><i class="fa fa-info-circle"></i> <?= e($stat['warning']) ?></div>
                <?php endif; ?>

                <!-- Tabs Navigation -->
                <div class="nav-tabs-custom">
                    <a href="?action=assign" class="nav-tab <?= ($action == '' || $action == 'assign') ? 'active' : '' ?>">
                        <i class="fa fa-user-plus"></i> Assign Class Teacher
                    </a>
                    <a href="?action=remove" class="nav-tab <?= ($action == 'remove') ? 'active' : '' ?>">
                        <i class="fa fa-trash"></i> Remove Class Teacher
                    </a>
                </div>

                <!-- ============================================================ -->
                <!-- ASSIGN CLASS TEACHER TAB -->
                <!-- ============================================================ -->
                <?php if ($action == '' || $action == 'assign'): ?>
                    
                    <!-- Assignment Form Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-plus-circle"></i> Assign Class Teacher</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" onsubmit="showButtonLoading(this)">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label><i class="fa fa-book"></i> Select Class <span style="color:#dc3545">*</span></label>
                                        <select name="school_class" class="form-control" required>
                                            <option value="">-- Select Class --</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= e($class['id']) ?>" <?= (isset($_POST['school_class']) && $_POST['school_class'] == $class['id']) ? 'selected' : '' ?>>
                                                    <?= e($class['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 30px;">
                                    <h4 style="margin-bottom: 15px; color: #1B3058;">
                                        <i class="fa fa-users"></i> Select Staff Members to Assign
                                    </h4>
                                    
                                    <!-- Search and Selection Toolbar -->
                                    <div class="search-bar">
                                        <input type="text" id="staffSearch" class="search-input" placeholder="🔍 Search staff by name or ID...">
                                    </div>
                                    
                                    <div class="selection-toolbar">
                                        <button type="button" class="select-all-btn" onclick="toggleAllStaff(true)">
                                            <i class="fa fa-check-square-o"></i> Select All
                                        </button>
                                        <button type="button" class="select-all-btn" onclick="toggleAllStaff(false)" style="background:#6c757d">
                                            <i class="fa fa-square-o"></i> Deselect All
                                        </button>
                                        <span class="selection-count" id="selectedCount">0 staff selected</span>
                                    </div>
                                    
                                    <div class="table-wrapper">
                                        <table class="data-table" id="staffTable">
                                            <thead>
                                                <tr>
                                                    <th class="checkbox-cell"><input type="checkbox" id="selectAllCheckbox" onclick="toggleAllStaff(this.checked)"></th>
                                                    <th>#</th>
                                                    <th>Staff ID</th>
                                                    <th>Staff Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($staffMembers)): ?>
                                                    <?php $i = 1; foreach ($staffMembers as $staff): ?>
                                                        <tr data-search="<?= strtolower(e($staff['staff_id'] . ' ' . $staff['first_name'] . ' ' . $staff['last_name'])) ?>">
                                                            <td class="checkbox-cell">
                                                                <input type="checkbox" name="staff_id[]" value="<?= e($staff['id']) ?>" class="staff-checkbox" 
                                                                    onchange="updateSelectedCount()"
                                                                    <?= (isset($_POST['staff_id']) && in_array($staff['id'], $_POST['staff_id'])) ? 'checked' : '' ?>>
                                                            </td>
                                                            <td><?= $i++ ?></td>
                                                            <td><?= e($staff['staff_id']) ?></td>
                                                            <td><?= e($staff['first_name'] . ' ' . $staff['last_name']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="empty-state">
                                                            <i class="fa fa-user-slash"></i>
                                                            No staff members found. Please add staff first.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 25px; text-align: right;">
                                    <button type="submit" name="assignCT" class="btn btn-success">
                                        <i class="fa fa-check"></i> Assign Selected Staff
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <script>
                        function updateSelectedCount() {
                            const checkboxes = document.querySelectorAll('.staff-checkbox');
                            const checked = document.querySelectorAll('.staff-checkbox:checked');
                            const countSpan = document.getElementById('selectedCount');
                            if (countSpan) {
                                countSpan.innerHTML = checked.length + ' staff selected';
                            }
                            const selectAll = document.getElementById('selectAllCheckbox');
                            if (selectAll) {
                                selectAll.checked = (checkboxes.length > 0 && checkboxes.length === checked.length);
                            }
                        }
                        
                        function toggleAllStaff(select) {
                            const checkboxes = document.querySelectorAll('.staff-checkbox');
                            checkboxes.forEach(cb => cb.checked = select);
                            updateSelectedCount();
                        }
                        
                        // Real-time search for staff table
                        const staffSearchInput = document.getElementById('staffSearch');
                        if (staffSearchInput) {
                            staffSearchInput.addEventListener('keyup', function() {
                                const searchTerm = this.value.toLowerCase();
                                const rows = document.querySelectorAll('#staffTable tbody tr');
                                rows.forEach(row => {
                                    const searchText = row.getAttribute('data-search') || '';
                                    row.style.display = searchText.includes(searchTerm) ? '' : 'none';
                                });
                            });
                        }
                        
                        // Initialize selected count on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            updateSelectedCount();
                        });
                    </script>
                    
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- REMOVE CLASS TEACHER TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'remove'): ?>
                    
                    <div class="card">
                        <div class="card-header card-header-danger">
                            <h3><i class="fa fa-trash"></i> Remove Class Teacher Assignments</h3>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="assignedSearch" class="search-input" placeholder="🔍 Search by staff name, session, or class...">
                            </div>
                            <div class="table-wrapper">
                                <table class="data-table" id="assignedTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Staff ID</th>
                                            <th>Staff Name</th>
                                            <th>Class</th>
                                            <th style="width: 100px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($assignedTeachers)): ?>
                                            <?php $i = 1; foreach ($assignedTeachers as $teacher): ?>
                                                <tr data-search="<?= strtolower(e($teacher['staff_id_number'] . ' ' . $teacher['first_name'] . ' ' . $teacher['last_name'] . ' ' . ($teacher['class_name'] ?? ''))) ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td><?= e($teacher['staff_id_number'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <?= e($teacher['first_name'] ?? '') . ' ' . e($teacher['last_name'] ?? '') ?>
                                                        <?php if (empty($teacher['first_name'])): ?>
                                                            <span class="badge badge-warning">Staff not found</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= e($teacher['class_name'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <a href="javascript:deleteItem('<?= e($FileName) ?>?action=classdelete&id=<?= e($teacher['id']) ?>', 'class teacher assignment')" 
                                                           class="btn-icon btn-delete" title="Delete">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="empty-state">
                                                    <i class="fa fa-users-slash"></i>
                                                    No class teachers assigned yet.
                                                    <?php if (empty($classes) || empty($staffMembers)): ?>
                                                        <br><small>Please ensure classes and staff are added first.</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        // Real-time search for assigned teachers table
                        const assignedSearchInput = document.getElementById('assignedSearch');
                        if (assignedSearchInput) {
                            assignedSearchInput.addEventListener('keyup', function() {
                                const searchTerm = this.value.toLowerCase();
                                const rows = document.querySelectorAll('#assignedTable tbody tr');
                                rows.forEach(row => {
                                    const searchText = row.getAttribute('data-search') || '';
                                    row.style.display = searchText.includes(searchTerm) ? '' : 'none';
                                });
                            });
                        }
                    </script>
                    
                <?php endif; ?>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>

<script>
// ============================================================================
// JAVASCRIPT FUNCTIONS - MODERN UI
// ============================================================================

// Delete confirmation function (matching manage_role.php pattern)
function deleteItem(url, itemName) {
    if (confirm(`Are you sure you want to delete this ${itemName}?\n\nThis action cannot be undone.`)) {
        showButtonLoading(null);
        window.location.href = url;
    }
}

// Show loading state on button during form submission
function showButtonLoading(form) {
    if (form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn && !btn.classList.contains('btn-loading')) {
            btn.classList.add('btn-loading');
            const originalText = btn.innerHTML;
            btn.setAttribute('data-original-text', originalText);
            btn.innerHTML = '<span class="spinner"></span> Processing...';
            
            // Reset after 5 seconds in case form doesn't submit (network issues)
            setTimeout(function() {
                if (btn.classList.contains('btn-loading')) {
                    btn.classList.remove('btn-loading');
                    btn.innerHTML = btn.getAttribute('data-original-text') || originalText;
                }
            }, 5000);
        }
    }
}

// Ensure forms show loading state on submit
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            showButtonLoading(this);
        });
    });
});
</script>

</body>
</html>