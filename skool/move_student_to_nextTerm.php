<?php
/**
 * Transfer Student To Next Term - Modern PHP 8.x
 * Move students from current session/term/class to new session/term/class
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Transfer Student To Next Term";
$FileName = 'move_student_to_nextTerm.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// GET DATA FOR DROPDOWNS
// ============================================================================
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$classes = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);

// Get all classes for sidebar (if needed)
$allClasses = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);

// ============================================================================
// SEARCH PARAMETERS
// ============================================================================
$searchSession = $_POST['current_school_session'] ?? $_GET['session'] ?? '';
$searchTerm = $_POST['current_school_term'] ?? $_GET['term_id'] ?? '';
$searchClass = $_POST['current_school_class'] ?? $_GET['class'] ?? '';
$isSearching = ($action == 'searchstudent') || (!empty($searchSession) && !empty($searchTerm) && !empty($searchClass));

// ============================================================================
// TRANSFER STUDENTS
// ============================================================================
if (isset($_POST['transfer'])) {
    $newSession = $_POST['school_session'] ?? 0;
    $newTerm = $_POST['school_term'] ?? 0;
    $newClass = $_POST['school_class'] ?? 0;
    $selectedStudents = $_POST['student_id'] ?? [];
    
    // Validation
    $errors = [];
    if (empty($newSession)) $errors[] = "Destination session is required";
    if (empty($newTerm)) $errors[] = "Destination term is required";
    if (empty($newClass)) $errors[] = "Destination class is required";
    if (empty($selectedStudents)) $errors[] = "Please select at least one student to transfer";
    
    if (empty($errors)) {
        $transferredCount = 0;
        $skippedCount = 0;
        
        foreach ($selectedStudents as $studentId) {
            // Get original student data
            $originalStudent = db_get_row(
                "SELECT * FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
                [$studentId, $create_by_userid]
            );
            
            if ($originalStudent) {
                // Check if student already exists in destination
                $existing = db_get_val(
                    "SELECT id FROM manage_student WHERE student_id = ? AND session = ? AND term_id = ? AND class = ? AND create_by_userid = ?",
                    [$studentId, $newSession, $newTerm, $newClass, $create_by_userid]
                );
                
                if (!$existing) {
                    $lastId = db_get_val("SELECT id FROM manage_student ORDER BY id DESC") ?? 0;
                    $newId = $lastId + 1;
                    $randomId = randomFix(15) . '-' . $newId;
                    
                    $transferData = [
                        'userid' => $_SESSION['userid'] ?? 0,
                        'usertype' => $_SESSION['usertype'] ?? '',
                        'student_id' => $originalStudent['student_id'],
                        'session' => $newSession,
                        'term_id' => $newTerm,
                        'class' => $newClass,
                        'last_name' => $originalStudent['last_name'],
                        'first_name' => $originalStudent['first_name'],
                        'date_of_admission' => $originalStudent['date_of_admission'],
                        'state_of_origin' => $originalStudent['state_of_origin'],
                        'other_name' => $originalStudent['other_name'],
                        'lga_of_origin' => $originalStudent['lga_of_origin'],
                        'gender' => $originalStudent['gender'],
                        'date_of_birth' => $originalStudent['date_of_birth'],
                        'religion' => $originalStudent['religion'],
                        'nationality' => $originalStudent['nationality'],
                        'number_of_sibling' => $originalStudent['number_of_sibling'],
                        'percentage' => $originalStudent['percentage'],
                        'order_of_birth' => $originalStudent['order_of_birth'],
                        'boarding' => $originalStudent['boarding'],
                        'address_1' => $originalStudent['address_1'],
                        'address_2' => $originalStudent['address_2'],
                        'state' => $originalStudent['state'],
                        'city' => $originalStudent['city'],
                        'p_o_box' => $originalStudent['p_o_box'],
                        'email' => $originalStudent['email'],
                        'phone' => $originalStudent['phone'],
                        'mobile' => $originalStudent['mobile'],
                        'picture' => $originalStudent['picture'],
                        'create_by_userid' => $create_by_userid,
                        'create_by_usertype' => $create_by_usertype,
                        'randomid' => $randomId,
                    ];
                    
                    db_insert("manage_student", $transferData);
                    $transferredCount++;
                } else {
                    $skippedCount++;
                }
            }
        }
        
        if ($transferredCount > 0) {
            $_SESSION['success'] = "$transferredCount student(s) transferred successfully!" . 
                                    ($skippedCount > 0 ? " ($skippedCount already existed)" : "");
        } else {
            $_SESSION['error'] = "No students were transferred. " . 
                                 ($skippedCount > 0 ? "$skippedCount student(s) already exist in destination." : "Please select students to transfer.");
        }
        
        redirect($FileName);
        exit;
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// GET STUDENTS FOR DISPLAY (when searching)
// ============================================================================
$searchResults = [];
if ($isSearching && !empty($searchSession) && !empty($searchTerm) && !empty($searchClass)) {
    $searchResults = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE session = ? 
         AND term_id = ? 
         AND class = ? 
         AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$searchSession, $searchTerm, $searchClass, $create_by_userid]
    );
}

// Get all students for the "Remove Student" tab
$allStudents = db_get_rows(
    "SELECT * FROM manage_student WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

// Helper function to get name by ID
function getNameById($table, $id, $field) {
    if (empty($id)) return 'N/A';
    return db_get_val("SELECT $field FROM $table WHERE id = ?", [$id]) ?: 'N/A';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .transfer-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        /* Two Column Layout */
        .two-column-layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .class-sidebar { width: 280px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .main-content { flex: 1; min-width: 500px; }
        
        /* Sidebar Styles */
        .sidebar-header { padding: 18px 20px; background: #1B3058; color: white; font-weight: 600; font-size: 16px; }
        .class-list { max-height: 600px; overflow-y: auto; }
        .class-item { padding: 15px 20px; border-bottom: 1px solid #eee; cursor: pointer; transition: all 0.2s; text-decoration: none; display: block; color: #333; }
        .class-item:hover { background: #f0f4ff; }
        .class-item.active { background: #1B3058; color: white; }
        
        /* Filter Card */
        .filter-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 20px; }
        .filter-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-select { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; }
        .filter-select:focus { outline: none; border-color: #1B3058; }
        
        /* Buttons */
        .btn { padding: 10px 24px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-block; text-decoration: none; text-align: center; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        
        /* Transfer Card */
        .transfer-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px; }
        .transfer-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 18px 25px; }
        .transfer-header h3 { margin: 0; font-size: 18px; }
        .transfer-body { padding: 25px; }
        
        /* Students Table */
        .students-table { width: 100%; border-collapse: collapse; }
        .students-table th, .students-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        .students-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .students-table tr:hover { background: #f5f5f5; }
        .student-checkbox { width: 40px; text-align: center; }
        .student-checkbox input { width: 18px; height: 18px; cursor: pointer; }
        .select-all-row { padding: 12px 15px; background: #f8f9fa; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px; }
        .select-all-row label { cursor: pointer; margin: 0; font-weight: 500; }
        
        /* Action Bar */
        .action-bar { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; display: flex; gap: 15px; justify-content: flex-end; flex-wrap: wrap; }
        
        /* Alert Messages */
        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px; color: #999; }
        .empty-state i { font-size: 64px; color: #ccc; margin-bottom: 15px; display: block; }
        
        /* Loading Spinner */
        .loading { display: inline-block; width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #1B3058; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Selected Count Badge */
        .selected-count { background: #1B3058; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-left: 10px; }
        
        @media (max-width: 900px) { 
            .two-column-layout { flex-direction: column; } 
            .class-sidebar { width: 100%; }
            .filter-row { flex-direction: column; }
            .students-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="transfer-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-exchange"></i> Transfer Student To Next Term</h2>
                    <p>Move students from current session/term/class to a new session/term/class</p>
                </div>

                <?= showMessage($stat) ?>

                <div class="two-column-layout">
                    
                    <!-- LEFT SIDEBAR - CLASS LIST -->
                    <div class="class-sidebar">
                        <div class="sidebar-header"><i class="fa fa-graduation-cap"></i> Classes</div>
                        <div class="class-list">
                            <?php foreach ($allClasses as $class): ?>
                                <a href="?action=searchstudent&session=<?= $searchSession ?>&term_id=<?= $searchTerm ?>&class=<?= $class['id'] ?>" 
                                   class="class-item <?= ($searchClass == $class['id']) ? 'active' : '' ?>">
                                    <i class="fa fa-book"></i> <?= htmlspecialchars($class['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- RIGHT MAIN CONTENT -->
                    <div class="main-content">
                        
                        <!-- SEARCH FILTERS -->
                        <div class="filter-card">
                            <form method="post" action="<?= $FileName ?>?action=searchstudent">
                                <div class="filter-row">
                                    <div class="filter-group">
                                        <label>Current Session</label>
                                        <select name="current_school_session" class="filter-select" required>
                                            <option value="">-- Select Session --</option>
                                            <?php foreach ($sessions as $s): ?>
                                                <option value="<?= $s['id'] ?>" <?= ($searchSession == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Current Term</label>
                                        <select name="current_school_term" class="filter-select" required>
                                            <option value="">-- Select Term --</option>
                                            <?php foreach ($terms as $t): ?>
                                                <option value="<?= $t['id'] ?>" <?= ($searchTerm == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Current Class</label>
                                        <select name="current_school_class" class="filter-select" required>
                                            <option value="">-- Select Class --</option>
                                            <?php foreach ($classes as $c): ?>
                                                <option value="<?= $c['id'] ?>" <?= ($searchClass == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <button type="submit" name="searchstudent" class="btn btn-primary"><i class="fa fa-search"></i> Search Students</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <?php if ($isSearching && !empty($searchResults)): ?>
                            <!-- TRANSFER FORM -->
                            <form method="post" id="transferForm" onsubmit="return confirmTransfer()">
                                <div class="transfer-card">
                                    <div class="transfer-header">
                                        <h3><i class="fa fa-arrow-right"></i> Transfer To</h3>
                                    </div>
                                    <div class="transfer-body">
                                        <div class="filter-row">
                                            <div class="filter-group">
                                                <label>New Session *</label>
                                                <select name="school_session" class="filter-select" required>
                                                    <option value="">-- Select Session --</option>
                                                    <?php foreach ($sessions as $s): ?>
                                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['session']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label>New Term *</label>
                                                <select name="school_term" class="filter-select" required>
                                                    <option value="">-- Select Term --</option>
                                                    <?php foreach ($terms as $t): ?>
                                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['term']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label>New Class *</label>
                                                <select name="school_class" class="filter-select" required>
                                                    <option value="">-- Select Class --</option>
                                                    <?php foreach ($classes as $c): ?>
                                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STUDENTS TABLE -->
                                <div class="transfer-card">
                                    <div class="transfer-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                                        <h3><i class="fa fa-users"></i> Students Found (<?= count($searchResults) ?>)</h3>
                                    </div>
                                    <div class="transfer-body">
                                        <div class="select-all-row">
                                            <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                                            <label for="selectAllCheckbox"><strong>Select All Students</strong></label>
                                            <span class="selected-count" id="selectedCount">0 selected</span>
                                        </div>
                                        <div style="overflow-x: auto;">
                                            <table class="students-table">
                                                <thead>
                                                    <tr>
                                                        <th class="student-checkbox">Select</th>
                                                        <th>Student ID</th>
                                                        <th>Student Name</th>
                                                        <th>Current Session</th>
                                                        <th>Current Term</th>
                                                        <th>Current Class</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($searchResults as $student): ?>
                                                        <tr>
                                                            <td class="student-checkbox">
                                                                <input type="checkbox" name="student_id[]" value="<?= htmlspecialchars($student['student_id']) ?>" class="student-checkbox-item" onclick="updateSelectedCount()">
                                                            </td>
                                                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                            <td><?= htmlspecialchars(getNameById('school_session', $student['session'], 'session')) ?></td>
                                                            <td><?= htmlspecialchars(getNameById('school_term', $student['term_id'], 'term')) ?></td>
                                                            <td><?= htmlspecialchars(getNameById('school_class', $student['class'], 'name')) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="action-bar">
                                            <a href="<?= $FileName ?>" class="btn btn-info"><i class="fa fa-arrow-left"></i> Back</a>
                                            <button type="submit" name="transfer" class="btn btn-success" id="transferBtn" disabled>
                                                <i class="fa fa-exchange"></i> Transfer Selected Students
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php elseif ($isSearching): ?>
                            <div class="empty-state">
                                <i class="fa fa-users"></i>
                                <h3>No Students Found</h3>
                                <p>No students found for the selected session, term, and class.</p>
                                <a href="<?= $FileName ?>" class="btn btn-primary" style="margin-top: 15px;">New Search</a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa fa-filter"></i>
                                <h3>Search for Students</h3>
                                <p>Select a session, term, and class above to find students to transfer.</p>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
                <!-- REMOVE STUDENT TAB CONTENT (Simplified) -->
<!-- REMOVE STUDENT TAB CONTENT (Fixed Table Structure) -->
<div class="transfer-card" style="margin-top: 30px;">
    <div class="transfer-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
        <h3><i class="fa fa-list"></i> All Student Records</h3>
    </div>
    <div class="transfer-body">
        <div style="overflow-x: auto;">
            <table class="students-table" id="studentsTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Session</th>
                        <th>Term</th>
                        <th>Class</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allStudents as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td><?= htmlspecialchars(getNameById('school_session', $student['session'], 'session')) ?></td>
                            <td><?= htmlspecialchars(getNameById('school_term', $student['term_id'], 'term')) ?></td>
                            <td><?= htmlspecialchars(getNameById('school_class', $student['class'], 'name')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($allStudents)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 40px;">No student records found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include('inc.js.php'); ?>
<script>
// Toggle select all checkboxes
function toggleSelectAll(source) {
    var checkboxes = document.querySelectorAll('.student-checkbox-item');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
    updateSelectedCount();
}

// Update selected count display
function updateSelectedCount() {
    var checkboxes = document.querySelectorAll('.student-checkbox-item:checked');
    var count = checkboxes.length;
    var countDisplay = document.getElementById('selectedCount');
    var transferBtn = document.getElementById('transferBtn');
    
    if (countDisplay) {
        countDisplay.innerHTML = count + ' selected';
    }
    
    if (transferBtn) {
        transferBtn.disabled = (count === 0);
    }
}

// Confirm transfer
function confirmTransfer() {
    var checkboxes = document.querySelectorAll('.student-checkbox-item:checked');
    var count = checkboxes.length;
    
    if (count === 0) {
        alert('Please select at least one student to transfer.');
        return false;
    }
    
    var destinationSession = document.querySelector('select[name="school_session"]').value;
    var destinationTerm = document.querySelector('select[name="school_term"]').value;
    var destinationClass = document.querySelector('select[name="school_class"]').value;
    
    if (!destinationSession || !destinationTerm || !destinationClass) {
        alert('Please select destination session, term, and class.');
        return false;
    }
    
    return confirm('Are you sure you want to transfer ' + count + ' student(s)?\n\nThis will create new records in the destination session/term/class.');
}

// Initialize selected count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    
    // Auto-submit when class is clicked from sidebar
    var classItems = document.querySelectorAll('.class-item');
    classItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            // Let the link work normally
            return true;
        });
    });
});
</script>
</body>
</html>