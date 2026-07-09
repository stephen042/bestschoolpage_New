<?php
/**
 * ============================================================================
 * CLASS TEACHER - PSYCHOMOTOR SKILLS (FIXED)
 * ============================================================================
 * - Admin sees ALL classes
 * - Class Teacher sees ONLY assigned classes
 * - Parents redirected to parent login
 * - Dropdown ratings with "Select All" button
 * - Minimal clicks workflow
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Psychomotor Skills";
$FileName = 'class_teacher_pyschomotor.php';

// ============================================================================
// PARENT REDIRECT - Parents cannot access this page
// ============================================================================
if ($_SESSION['usertype'] == '2' || $_SESSION['usertype'] == 'parent') {
    redirect(PARENT_URL);
    exit;
}

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$create_by_usertype = $_SESSION['usertype'] ?? '';
$userType = $_SESSION['usertype'] ?? '';
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $create_by_userid);

// Get selected filters
$selectedClassRandomid = $_GET['randomid'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedPsychomotor = $_GET['phycomotor'] ?? '';
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
// GET SELECTED DATA
// ============================================================================
$selectedClass = [];
if (!empty($selectedClassRandomid)) {
    $selectedClass = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$selectedClassRandomid, $create_by_userid]
    );
}

$selectedPsychomotorData = [];
if (!empty($selectedPsychomotor)) {
    $selectedPsychomotorData = db_get_row(
        "SELECT * FROM manage_phycomotor WHERE randomid = ? AND create_by_userid = ?",
        [$selectedPsychomotor, $create_by_userid]
    );
}

$sessionData = [];
if (!empty($selectedSession)) {
    $sessionData = db_get_row(
        "SELECT * FROM school_session WHERE id = ? AND create_by_userid = ?",
        [$selectedSession, $create_by_userid]
    );
}

$termData = [];
if (!empty($selectedTerm)) {
    $termData = db_get_row(
        "SELECT * FROM school_term WHERE id = ? AND create_by_userid = ?",
        [$selectedTerm, $create_by_userid]
    );
}

// ============================================================================
// GET CLASSES - BASED ON USER TYPE
// ============================================================================
$classes = [];
if ($isSchoolOwnerSession) {
    $classes = db_get_rows(
        "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
        [$create_by_userid]
    );
} else {
    $teacherStaffId = db_get_val(
        "SELECT id FROM staff_manage
         WHERE create_by_userid = ?
           AND (staff_id = ? OR email = ? OR id = ?)
         ORDER BY id DESC
         LIMIT 1",
        [$create_by_userid, $sessionUsername, $sessionEmail, $sessionUserId]
    );

    if (!empty($teacherStaffId)) {
        $assignedClassIds = db_get_val(
            "SELECT GROUP_CONCAT(school_class) FROM class_teacher WHERE staff_id = ?",
            [$teacherStaffId]
        );

        if (!empty($assignedClassIds)) {
            $classes = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($assignedClassIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    }
}

// ============================================================================
// GET FILTER DATA
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);
$terms = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);
$psychomotorSkills = db_get_rows(
    "SELECT * FROM manage_phycomotor WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

// ============================================================================
// GET STUDENTS AND EXISTING RATINGS
// ============================================================================
$students = [];
$existingRatings = [];

if (!empty($selectedClass['id']) && !empty($selectedSession) && !empty($selectedTerm) && !empty($selectedPsychomotorData['id'])) {
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$selectedClass['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );

    if (!empty($students)) {
        foreach ($students as $student) {
            $rating = db_get_val(
                "SELECT pyschmotor FROM student_pyschomotor_class_teacher 
                 WHERE session_id = ? AND term_id = ? AND class_id = ? 
                 AND pyschmotor_id = ? AND student_id = ? AND create_by_userid = ?",
                [$selectedSession, $selectedTerm, $selectedClass['id'], $selectedPsychomotorData['id'], $student['id'], $create_by_userid]
            );
            $existingRatings[$student['id']] = $rating ?: '';
        }
    }
}

// ============================================================================
// SAVE RATINGS (BULK)
// ============================================================================
if (isset($_POST['save_ratings'])) {
    $studentIds = explode(',', $_POST['student_ids'] ?? '');
    $successCount = 0;
    $errorCount = 0;
    
    // First, delete existing ratings for this combination
    db_delete(
        "student_pyschomotor_class_teacher",
        "session_id = ? AND term_id = ? AND class_id = ? AND pyschmotor_id = ? AND create_by_userid = ?",
        [$selectedSession, $selectedTerm, $selectedClass['id'], $selectedPsychomotorData['id'], $create_by_userid]
    );
    
    // Insert new ratings
    foreach ($studentIds as $studentId) {
        if (empty($studentId)) continue;
        
        $rating = $_POST["rating_{$studentId}"] ?? '';
        
        if (!empty($rating)) {
            $result = db_insert("student_pyschomotor_class_teacher", [
                'session_id' => $selectedSession,
                'term_id' => $selectedTerm,
                'class_id' => $selectedClass['id'],
                'pyschmotor_id' => $selectedPsychomotorData['id'],
                'student_id' => $studentId,
                'pyschmotor' => $rating,
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15)
            ]);
            
            if ($result !== false) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
    }
    
    if ($successCount > 0) {
        $_SESSION['success'] = "$successCount student rating(s) saved successfully!";
    }
    if ($errorCount > 0) {
        $_SESSION['error'] = "$errorCount student(s) failed to save.";
    }
    
    // Redirect to refresh the page
    $redirectUrl = $FileName . "?action=input_score&randomid=" . urlencode($selectedClassRandomid) . "&session=" . urlencode($selectedSession) . "&term_id=" . urlencode($selectedTerm) . "&phycomotor=" . urlencode($selectedPsychomotor);
    redirect($redirectUrl);
    exit;
}

// Rating options for dropdown
$ratingOptions = [
    '' => '-- Select Rating --',
    'Poor' => '🔴 Poor',
    'Satisfactory' => '🟡 Satisfactory',
    'Good' => '🟢 Good',
    'Very Good' => '🔵 Very Good',
    'Excellent' => '🟣 Excellent'
];
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .psychomotor-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .two-column-layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .classes-panel { flex: 1; min-width: 280px; background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .ratings-panel { flex: 3; min-width: 500px; }
        .panel-header { 
            padding: 18px 20px; 
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        
        .class-list { max-height: 70vh; overflow-y: auto; }
        .class-item {
            display: block;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .class-item:hover { background: #f8f9ff; }
        .class-item.active { background: #e8eef5; border-left: 4px solid #1B3058; }
        .class-name { font-size: 16px; font-weight: 600; color: #333; }
        .class-meta { font-size: 11px; color: #888; margin-top: 4px; }
        
        .filter-card { background: white; border-radius: 20px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .filter-grid { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 11px; font-weight: 700; color: #888; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-select {
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 14px; 
            font-size: 14px;
            background: white;
            transition: all 0.2s;
        }
        .filter-select:focus { outline: none; border-color: #1B3058; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 14px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        
        .students-card { background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .table-wrapper { overflow-x: auto; padding: 20px; }
        .ratings-table { width: 100%; border-collapse: collapse; }
        .ratings-table th, .ratings-table td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #eee; }
        .ratings-table th { background: #f8f9fa; font-weight: 700; color: #1B3058; font-size: 13px; }
        .ratings-table td { font-size: 14px; }
        .student-name { font-weight: 600; }
        
        .select-all-row {
            background: #f8f9fa;
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .select-all-label { font-weight: 600; color: #333; }
        .select-all-dropdown {
            padding: 8px 15px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 13px;
            min-width: 160px;
        }
        .apply-all-btn {
            background: #1B3058;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .apply-all-btn:hover { background: #f21151; }
        
        .rating-select {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 13px;
            width: 100%;
            max-width: 160px;
            cursor: pointer;
        }
        .rating-select:focus { outline: none; border-color: #1B3058; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        .empty-state i { font-size: 60px; color: #ddd; margin-bottom: 15px; display: block; }
        
        .alert { padding: 15px 20px; border-radius: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        @media (max-width: 900px) {
            .two-column-layout { flex-direction: column; }
            .filter-grid { flex-direction: column; }
            .ratings-table th, .ratings-table td { padding: 10px 8px; font-size: 12px; }
            .rating-select { max-width: 120px; font-size: 11px; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="psychomotor-container">
                
                <div class="page-header">
                    <h2><i class="fa fa-hand-paper-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Rate students on psychomotor skills (Handwriting, Drawing, Sports, etc.)</p>
                </div>

                <?= showMessage($stat) ?>

                <div class="two-column-layout">
                    
                    <!-- LEFT PANEL: Classes -->
                    <div class="classes-panel">
                        <div class="panel-header">
                            <i class="fa fa-graduation-cap"></i> 
                            <?php if ($userType == '1' || $userType == '0'): ?>
                                All Classes
                            <?php else: ?>
                                My Classes
                            <?php endif; ?>
                        </div>
                        <div class="class-list">
                            <?php if (!empty($classes)): ?>
                                <?php foreach ($classes as $class): ?>
                                    <a href="?action=input_score&randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session='.urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id='.urlencode($selectedTerm) : '' ?><?= $selectedPsychomotor ? '&phycomotor='.urlencode($selectedPsychomotor) : '' ?>" 
                                       class="class-item <?= ($selectedClassRandomid == $class['randomid']) ? 'active' : '' ?>">
                                        <div class="class-name"><?= htmlspecialchars($class['name']) ?></div>
                                        <div class="class-meta"><i class="fa fa-book"></i> <?= htmlspecialchars($class['short_name'] ?? 'Class') ?></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state" style="padding: 40px;">
                                    <i class="fa fa-folder-open"></i>
                                    <?php if ($userType == '1' || $userType == '0'): ?>
                                        No classes found. Please add classes in Configuration.
                                    <?php else: ?>
                                        No classes assigned to you. Please contact the administrator.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- RIGHT PANEL: Ratings Entry -->
                    <div class="ratings-panel">
                        
                        <?php if (!empty($selectedClass)): ?>
                            
                            <div class="filter-card">
                                <form method="GET" action="" id="filterForm">
                                    <input type="hidden" name="action" value="input_score">
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($selectedClassRandomid) ?>">
                                    <div class="filter-grid">
                                        <div class="filter-group">
                                            <label><i class="fa fa-calendar"></i> SESSION</label>
                                            <select name="session" class="filter-select" onchange="this.form.submit()">
                                                <option value="">-- Select Session --</option>
                                                <?php foreach ($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($s['session']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-tag"></i> TERM</label>
                                            <select name="term_id" class="filter-select" onchange="this.form.submit()">
                                                <option value="">-- Select Term --</option>
                                                <?php foreach ($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($t['term']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-hand-paper-o"></i> PSYCHOMOTOR SKILL</label>
                                            <select name="phycomotor" class="filter-select" onchange="this.form.submit()">
                                                <option value="">-- Select Skill --</option>
                                                <?php foreach ($psychomotorSkills as $skill): ?>
                                                    <option value="<?= $skill['randomid'] ?>" <?= ($selectedPsychomotor == $skill['randomid']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($skill['phycomotor']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedPsychomotorData)): ?>
                                
                                <?php if (!empty($students)): ?>
                                    
                                    <form method="POST" id="ratingsForm">
                                        <input type="hidden" name="student_ids" id="studentIdsHidden" value="">
                                        
                                        <div class="students-card">
                                            <div class="select-all-row">
                                                <span class="select-all-label"><i class="fa fa-magic"></i> Set all students to:</span>
                                                <select id="selectAllRating" class="select-all-dropdown">
                                                    <option value="">-- Select Rating --</option>
                                                    <?php foreach ($ratingOptions as $value => $label): ?>
                                                        <?php if ($value !== ''): ?>
                                                            <option value="<?= $value ?>"><?= $label ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="button" class="apply-all-btn" onclick="applyToAll()">
                                                    <i class="fa fa-check-circle"></i> Apply to All
                                                </button>
                                                <span style="font-size: 12px; color: #888; margin-left: auto;">
                                                    <i class="fa fa-info-circle"></i> Select rating above and click "Apply to All"
                                                </span>
                                            </div>
                                            
                                            <div class="table-wrapper">
                                                <table class="ratings-table" id="studentsTable">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Student ID</th>
                                                            <th>Student Name</th>
                                                            <th>Rating</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $studentIdList = [];
                                                        $counter = 1;
                                                        foreach ($students as $student): 
                                                            $studentIdList[] = $student['id'];
                                                            $currentRating = $existingRatings[$student['id']] ?? '';
                                                        ?>
                                                            <tr>
                                                                <td><?= $counter++ ?></td>
                                                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <td class="student-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                                <td>
                                                                    <select name="rating_<?= $student['id'] ?>" class="rating-select" data-student-id="<?= $student['id'] ?>">
                                                                        <?php foreach ($ratingOptions as $value => $label): ?>
                                                                            <option value="<?= $value ?>" <?= ($currentRating == $value) ? 'selected' : '' ?>>
                                                                                <?= $label ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </td>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; text-align: right;">
                                                <button type="submit" name="save_ratings" class="btn btn-success" onclick="prepareSubmit()">
                                                    <i class="fa fa-save"></i> Save All Ratings
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <script>
                                        var studentIds = <?= json_encode($studentIdList) ?>;
                                        
                                        function prepareSubmit() {
                                            document.getElementById('studentIdsHidden').value = studentIds.join(',');
                                        }
                                        
                                        function applyToAll() {
                                            var selectAll = document.getElementById('selectAllRating');
                                            var selectedValue = selectAll.value;
                                            
                                            if (selectedValue === '') {
                                                alert('Please select a rating to apply to all students.');
                                                return;
                                            }
                                            
                                            var allSelects = document.querySelectorAll('.rating-select');
                                            allSelects.forEach(function(select) {
                                                select.value = selectedValue;
                                            });
                                            
                                            var applyBtn = document.querySelector('.apply-all-btn');
                                            var originalText = applyBtn.innerHTML;
                                            applyBtn.innerHTML = '<i class="fa fa-check"></i> Applied!';
                                            setTimeout(function() {
                                                applyBtn.innerHTML = originalText;
                                            }, 1500);
                                        }
                                    </script>
                                    
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> No students found in this class for the selected session and term.
                                    </div>
                                <?php endif; ?>
                                
                            <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && empty($selectedPsychomotor)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-hand-paper-o"></i> Please select a psychomotor skill to continue.
                                </div>
                            <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-tag"></i> Please select a term to continue.
                                </div>
                            <?php elseif (empty($selectedSession)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-calendar"></i> Please select a session to continue.
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fa fa-folder-open"></i> Please select a class from the left panel to begin.
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