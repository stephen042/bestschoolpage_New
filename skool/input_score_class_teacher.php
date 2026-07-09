<?php
/**
 * Input Score Class Teacher - AUTO-LOAD STUDENTS
 * Features: All assessments visible at once, real-time calculation, bulk save
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Input Score";
$FileName = 'input_score_class_teacher.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$create_by_usertype = $_SESSION['usertype'] ?? '';
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $create_by_userid);
$randomid = $_GET['randomid'] ?? '';
$action = $_GET['action'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedSubject = $_GET['subject'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($randomid)) {
    $classDetail = db_get_row("SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);
}

// ============================================================================
// GET ASSESSMENTS FOR THIS CLASS
// ============================================================================
$assessments = [];
$assessmentPercentages = [];
if (!empty($classDetail['id'])) {
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC", 
        [$classDetail['id'], $create_by_userid]
    );
    foreach ($assessments as $ass) {
        $assessmentPercentages[$ass['id']] = floatval($ass['percentage'] ?? 0);
    }
}

// ============================================================================
// GET SUBJECT FOR THE SELECTED CLASS
// ============================================================================
$subjectDetail = [];
if (!empty($selectedSubject)) {
    $subjectDetail = db_get_row("SELECT * FROM school_subject WHERE randomid = ? AND create_by_userid = ?", [$selectedSubject, $create_by_userid]);
}

// ============================================================================
// GET STUDENTS - FIXED: Simplified query
// ============================================================================
$students = [];
if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    // First try: match exactly
    $students = db_get_rows(
        "SELECT id, student_id, first_name, last_name FROM manage_student 
         WHERE class = ? AND session = ? AND create_by_userid = ? AND term_id = ?
         ORDER BY first_name ASC",
        [$classDetail['id'], $selectedSession, $create_by_userid, $selectedTerm]
    );
    
    // If no students found, try without term_id (some schools don't use it)
    if (empty($students)) {
        $students = db_get_rows(
            "SELECT id, student_id, first_name, last_name FROM manage_student 
             WHERE class = ? AND session = ? AND create_by_userid = ?
             ORDER BY first_name ASC",
            [$classDetail['id'], $selectedSession, $create_by_userid]
        );
    }
    
    // If still no students, try without session (fallback)
    if (empty($students)) {
        $students = db_get_rows(
            "SELECT id, student_id, first_name, last_name FROM manage_student 
             WHERE class = ? AND create_by_userid = ?
             ORDER BY first_name ASC",
            [$classDetail['id'], $create_by_userid]
        );
    }
}

// ============================================================================
// GET EXISTING SCORES FOR ALL ASSESSMENTS
// ============================================================================
$existingScores = [];
if (!empty($students) && !empty($subjectDetail['id']) && !empty($assessments)) {
    $studentIds = array_column($students, 'id');
    $assessmentIds = array_column($assessments, 'id');

    $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
    $assessmentPlaceholders = implode(',', array_fill(0, count($assessmentIds), '?'));

    $scoreRows = db_get_rows(
        "SELECT student_id, assesment_id, score
         FROM input_score_class_teacher
         WHERE subject_id = ?
           AND session_id = ?
           AND term_id = ?
           AND class_id = ?
           AND create_by_userid = ?
           AND student_id IN ($studentPlaceholders)
           AND assesment_id IN ($assessmentPlaceholders)",
        array_merge(
            [$subjectDetail['id'], $selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid],
            $studentIds,
            $assessmentIds
        )
    );

    foreach ($scoreRows as $row) {
        $existingScores[$row['student_id']][$row['assesment_id']] = floatval($row['score']);
    }

    foreach ($studentIds as $studentId) {
        foreach ($assessmentIds as $assessmentId) {
            if (!isset($existingScores[$studentId][$assessmentId])) {
                $existingScores[$studentId][$assessmentId] = '';
            }
        }
    }
}

// ============================================================================
// SAVE ALL SCORES (BULK) WITH VALIDATION
// ============================================================================
if (isset($_POST['save_all_scores'])) {
    $studentIds = explode(',', $_POST['student_ids'] ?? '');
    $assessmentIds = explode(',', $_POST['assessment_ids'] ?? '');
    $validationErrors = [];
    $successCount = 0;
    
    foreach ($studentIds as $studentId) {
        if (empty($studentId)) continue;
        
        foreach ($assessmentIds as $assessmentId) {
            if (empty($assessmentId)) continue;
            
            $scoreKey = "score_{$studentId}_{$assessmentId}";
            $score = floatval($_POST[$scoreKey] ?? 0);
            $maxScore = $assessmentPercentages[$assessmentId] ?? 100;
            
            if ($score > $maxScore) {
                $studentName = db_get_val("SELECT first_name FROM manage_student WHERE id = ?", [$studentId]);
                $assessmentName = db_get_val("SELECT assesment FROM school_assessment WHERE id = ?", [$assessmentId]);
                $validationErrors[] = "$studentName - $assessmentName: Score ($score) exceeds maximum ($maxScore)";
                continue;
            }
            
            $existing = db_get_val(
                "SELECT id FROM input_score_class_teacher WHERE student_id = ? AND subject_id = ? AND assesment_id = ? AND session_id = ? AND term_id = ? AND class_id = ? AND create_by_userid = ?",
                [$studentId, $subjectDetail['id'], $assessmentId, $selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid]
            );
            
            $data = [
                'session_id' => $selectedSession,
                'term_id' => $selectedTerm,
                'class_id' => $classDetail['id'],
                'subject_id' => $subjectDetail['id'],
                'assesment_id' => $assessmentId,
                'student_id' => $studentId,
                'score' => $score,
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15),
            ];
            
            if ($existing) {
                db_update("input_score_class_teacher", ['score' => $score], "id = ?", [$existing]);
            } else {
                db_insert("input_score_class_teacher", $data);
            }
            $successCount++;
        }
        
        // Update total score
        $totalScore = 0;
        foreach ($assessmentIds as $assessmentId) {
            if (empty($assessmentId)) continue;
            $scoreKey = "score_{$studentId}_{$assessmentId}";
            $score = floatval($_POST[$scoreKey] ?? 0);
            $totalScore += $score;
        }
        $totalScore = round($totalScore, 2);
        
        $existingTotal = db_get_val(
            "SELECT id FROM result_total WHERE student_id = ? AND class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
            [$studentId, $classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
        );
        
        if ($existingTotal) {
            db_update("result_total", ['total' => $totalScore], "id = ?", [$existingTotal]);
        } else {
            db_insert("result_total", [
    'session_id' => $selectedSession,
    'term_id' => $selectedTerm,
    'class_id' => $classDetail['id'],
    'student_id' => $studentId,
    'total' => $totalScore,
    'usertype' => $_SESSION['usertype'] ?? '',
    'userid' => $_SESSION['userid'] ?? 0,
    'create_by_usertype' => $create_by_usertype,
    'create_by_userid' => $create_by_userid,
]);        }
    }
    
    if (!empty($validationErrors)) {
        $_SESSION['error'] = "Validation Errors:<br>" . implode("<br>", $validationErrors);
    } else {
        $_SESSION['success'] = "$successCount scores saved successfully!";
    }
    
    $redirectUrl = $FileName . "?action=input_score&randomid=" . $randomid . "&session=" . $selectedSession . "&term_id=" . $selectedTerm . "&subject=" . $selectedSubject;
    redirect($redirectUrl);
    exit;
}

// Get subjects for the class
$subjects = [];
if (!empty($classDetail['id'])) {
    $subjects = db_get_rows("SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC", [$classDetail['id'], $create_by_userid]);
}

$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

$allClasses = [];
if ($isSchoolOwnerSession) {
    $allClasses = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);
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
            $allClasses = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($assignedClassIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    }
}

$gradeScale = db_get_rows("SELECT * FROM school_grade WHERE create_by_userid = ? ORDER BY minimum_number DESC", [$create_by_userid]);

function calculateGrade($score, $gradeScale) {
    if (empty($gradeScale)) {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    }
    foreach ($gradeScale as $grade) {
        $min = floatval($grade['minimum_number'] ?? 0);
        $max = floatval($grade['maximum_number'] ?? 0);
        if ($score >= $min && $score <= $max) {
            return $grade['grade'];
        }
    }
    return 'N/A';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { box-sizing: border-box; }
        .score-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        .filter-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 25px; }
        .filter-grid { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .filter-select { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; }
        .btn { padding: 10px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-block; text-decoration: none; text-align: center; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-outline { background: transparent; border: 1px solid #ddd; color: #333; }
        .two-column-layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .class-list-panel { flex: 1; min-width: 280px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .score-panel { flex: 3; min-width: 500px; }
        .panel-header { padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 600; }
        .class-list { max-height: 600px; overflow-y: auto; }
        .class-item { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s; }
        .class-item:hover { background: #f8f9ff; }
        .class-item.active { background: #1B3058; color: white; }
        .score-table { width: 100%; border-collapse: collapse; }
        .score-table th, .score-table td { padding: 12px; text-align: center; border: 1px solid #e0e0e0; vertical-align: middle; }
        .score-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .score-table input { width: 80px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
        .score-table input:focus { outline: none; border-color: #1B3058; }
        .score-table input.invalid { border-color: #dc3545; background-color: #fff0f0; }
        .student-name-cell { text-align: left; font-weight: 500; }
        .total-cell { font-weight: bold; background: #e8f5e9; }
        .grade-cell { font-weight: bold; background: #e3f2fd; }
        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .max-hint { font-size: 10px; color: #999; display: block; }
        .auto-load-status { font-size: 12px; color: #666; margin-top: 5px; }
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-box {
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1B3058;
            font-weight: 600;
        }
        .spinner {
            width: 22px;
            height: 22px;
            border: 3px solid #d8deec;
            border-top-color: #1B3058;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @media (max-width: 900px) { .two-column-layout { flex-direction: column; } .filter-grid { flex-direction: column; } .score-table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
<div id="studentLoadingOverlay" class="loading-overlay" aria-hidden="true">
    <div class="loading-box">
        <div class="spinner" aria-hidden="true"></div>
        <span>Loading students, please wait...</span>
    </div>
</div>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="score-container">
                <div class="page-header">
                    <h2><i class="fa fa-pencil-square-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Select class, session, term, and subject to enter scores. Students load automatically.</p>
                </div>

                <?= showMessage($stat) ?>

                <div class="two-column-layout">
                    <!-- LEFT: Class List -->
                    <div class="class-list-panel">
                        <div class="panel-header"><i class="fa fa-graduation-cap"></i> Select Class</div>
                        <div class="class-list">
                            <?php foreach ($allClasses as $class): ?>
                                <a href="?action=input_score&randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session='.urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id='.urlencode($selectedTerm) : '' ?><?= $selectedSubject ? '&subject='.urlencode($selectedSubject) : '' ?>" style="text-decoration: none;">
                                    <div class="class-item <?= ($randomid == $class['randomid']) ? 'active' : '' ?>">
                                        <i class="fa fa-book"></i> <?= htmlspecialchars($class['name']) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <?php if (empty($allClasses)): ?>
                                <div class="class-item">No classes found</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT: Score Entry -->
                    <div class="score-panel">
                        <?php if (!empty($classDetail)): ?>
                            <!-- Filters - Auto Load Students -->
                            <div class="filter-card">
                                <form method="GET" action="" id="filterForm">
                                    <input type="hidden" name="action" value="input_score">
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                    <div class="filter-grid">
                                        <div class="filter-group">
                                            <label>Session</label>
                                            <select name="session" class="filter-select" onchange="this.form.submit()">
                                                <option value="">-- Select Session --</option>
                                                <?php foreach($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Term</label>
                                            <select name="term_id" class="filter-select" onchange="this.form.submit()">
                                                <option value="">-- Select Term --</option>
                                                <?php foreach($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Subject</label>
                                            <select name="subject" class="filter-select" onchange="this.form.submit()">
                                                <option value="">-- Select Subject --</option>
                                                <?php foreach($subjects as $sub): ?>
                                                    <option value="<?= $sub['randomid'] ?>" <?= ($selectedSubject == $sub['randomid']) ? 'selected' : '' ?>><?= htmlspecialchars($sub['subject']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="auto-load-status">
                                        <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedSubject)): ?>
                                            <i class="fa fa-check-circle" style="color:#28a745;"></i> 
                                            <?= count($students) ?> student(s) loaded for this selection.
                                        <?php else: ?>
                                            <i class="fa fa-info-circle"></i> Select session, term, and subject to load students automatically.
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <!-- Score Entry Table -->
                            <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedSubject) && !empty($subjectDetail) && !empty($assessments)): ?>
                                <form method="POST" id="scoreForm">
                                    <input type="hidden" name="save_all_scores" value="1">
                                    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden;">
                                        <div class="panel-header">
                                            <strong><i class="fa fa-table"></i> <?= htmlspecialchars($subjectDetail['subject']) ?> - Score Entry</strong>
                                            <span style="float:right; font-size:12px; font-weight:normal;">Enter all scores below and click Save All</span>
                                        </div>
                                        <div style="padding: 20px; overflow-x: auto;">
                                            <?php if (empty($students)): ?>
                                                <div class="alert alert-info" style="margin-bottom:0;">
                                                    <i class="fa fa-info-circle"></i> No students found for this class, session, and term.
                                                    <br><small>Please check that students are enrolled in this class for the selected session and term.</small>
                                                </div>
                                            <?php else: ?>
                                                <table class="score-table" id="scoreTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:150px;">Student Name</th>
                                                            <th>Student ID</th>
                                                            <?php foreach ($assessments as $ass): ?>
                                                                <th><?= htmlspecialchars($ass['assesment']) ?><br><small>(Max: <?= floatval($ass['percentage'] ?? 0) ?>)</small></th>
                                                            <?php endforeach; ?>
                                                            <th>Total<br><small>Score</small></th>
                                                            <th>Grade</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student): 
                                                            $totalScore = 0;
                                                            foreach ($assessments as $ass) {
                                                                $score = floatval($existingScores[$student['id']][$ass['id']] ?? 0);
                                                                $totalScore += $score;
                                                            }
                                                            $totalScore = round($totalScore, 2);
                                                            $grade = calculateGrade($totalScore, $gradeScale);
                                                        ?>
                                                            <tr>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <?php foreach ($assessments as $ass): 
                                                                    $scoreValue = $existingScores[$student['id']][$ass['id']] ?? '';
                                                                    $maxScore = floatval($ass['percentage'] ?? 0);
                                                                ?>
                                                                    <td>
                                                                        <input type="number" class="score-input" data-student="<?= $student['id'] ?>" data-assessment="<?= $ass['id'] ?>" data-max="<?= $maxScore ?>" value="<?= htmlspecialchars($scoreValue) ?>" step="any" min="0" max="<?= $maxScore ?>" placeholder="0" style="width:80px; padding:8px; text-align:center;">
                                                                        <span class="max-hint"></span>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td class="total-cell" id="total_<?= $student['id'] ?>"><?= $totalScore ?></td>
                                                                <td class="grade-cell" id="grade_<?= $student['id'] ?>"><?= htmlspecialchars($grade) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                        <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; text-align: right;">
                                            <button type="button" class="btn btn-success" onclick="validateAndSubmit()" <?= empty($students) ? 'disabled style="opacity:.6;cursor:not-allowed;"' : '' ?>><i class="fa fa-save"></i> Save All Scores</button>
                                        </div>
                                    </div>
                                </form>
                            <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedSubject) && empty($subjectDetail)): ?>
                                <div class="alert alert-danger">Subject not found. Please select a valid subject.</div>
                            <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && empty($selectedSubject)): ?>
                                <div class="alert alert-info">Please select a subject to enter scores.</div>
                            <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                <div class="alert alert-info">Please select a term to continue.</div>
                            <?php elseif (empty($selectedSession)): ?>
                                <div class="alert alert-info">Please select a session and term to continue.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Please select a class from the left panel.</div>
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
function showStudentLoadingOverlay() {
    var overlay = document.getElementById('studentLoadingOverlay');
    if (!overlay) return;
    overlay.style.display = 'flex';
    overlay.setAttribute('aria-hidden', 'false');
}

function disableFilterControls() {
    var filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    var controls = filterForm.querySelectorAll('select, button, input');
    controls.forEach(function(control) {
        control.disabled = true;
    });
}

var filterForm = document.getElementById('filterForm');
if (filterForm) {
    filterForm.addEventListener('submit', function() {
        showStudentLoadingOverlay();
        disableFilterControls();
    });
}

// Real-time calculation of total (RAW SUM)
function calculateStudentTotal(studentId) {
    var total = 0;
    var inputs = document.querySelectorAll(`.score-input[data-student="${studentId}"]`);
    inputs.forEach(function(input) {
        var score = parseFloat(input.value) || 0;
        total += score;
    });
    total = Math.round(total * 100) / 100;
    var totalCell = document.getElementById(`total_${studentId}`);
    if (totalCell) totalCell.innerText = total;
    
    var grade = getGrade(total);
    var gradeCell = document.getElementById(`grade_${studentId}`);
    if (gradeCell) gradeCell.innerText = grade;
}

function getGrade(totalScore) {
    if (totalScore >= 70) return 'A';
    if (totalScore >= 60) return 'B';
    if (totalScore >= 50) return 'C';
    if (totalScore >= 45) return 'D';
    if (totalScore >= 40) return 'E';
    return 'F';
}

// Validate a single input
function validateInput(input) {
    var value = parseFloat(input.value);
    var max = parseFloat(input.getAttribute('data-max')) || 0;
    var isValid = true;
    var errorMsg = '';
    
    if (isNaN(value)) {
        isValid = false;
        errorMsg = 'Invalid number';
    } else if (value < 0) {
        isValid = false;
        errorMsg = 'Cannot be negative';
    } else if (value > max) {
        isValid = false;
        errorMsg = 'Max ' + max;
    }
    
    if (!isValid) {
        input.classList.add('invalid');
        if (input.nextElementSibling) {
            input.nextElementSibling.innerHTML = errorMsg;
            input.nextElementSibling.style.color = '#dc3545';
            input.nextElementSibling.style.fontSize = '10px';
            input.nextElementSibling.style.display = 'block';
        }
    } else {
        input.classList.remove('invalid');
        if (input.nextElementSibling) {
            input.nextElementSibling.innerHTML = '';
        }
    }
    
    return isValid;
}

// Validate all inputs before submission
function validateAllInputs() {
    var allInputs = document.querySelectorAll('.score-input');
    var isValid = true;
    var errorMessages = [];
    
    allInputs.forEach(function(input) {
        var value = parseFloat(input.value);
        var max = parseFloat(input.getAttribute('data-max')) || 0;
        var studentName = input.closest('tr').querySelector('.student-name-cell').innerText;
        
        if (!isNaN(value) && value > max) {
            isValid = false;
            input.classList.add('invalid');
            errorMessages.push(studentName + ': Score ' + value + ' exceeds maximum ' + max);
        } else if (!isNaN(value) && value < 0) {
            isValid = false;
            input.classList.add('invalid');
            errorMessages.push(studentName + ': Score cannot be negative');
        } else {
            input.classList.remove('invalid');
        }
    });
    
    if (!isValid) {
        alert('Validation Errors:\n' + errorMessages.join('\n'));
        return false;
    }
    return true;
}

// Attach event listeners to all score inputs
document.querySelectorAll('.score-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var studentId = this.getAttribute('data-student');
        validateInput(this);
        calculateStudentTotal(studentId);
    });
    input.addEventListener('blur', function() {
        validateInput(this);
    });
});

function validateAndSubmit() {
    if (!validateAllInputs()) {
        return;
    }

    var form = document.getElementById('scoreForm');
    if (!form) return;

    form.querySelectorAll('input[data-generated="1"]').forEach(function(el) {
        el.remove();
    });
    
    var studentIds = [];
    var assessmentIds = [];
    var inputs = document.querySelectorAll('.score-input');
    
    inputs.forEach(function(input) {
        var studentId = input.getAttribute('data-student');
        var assessmentId = input.getAttribute('data-assessment');
        if (studentId && !studentIds.includes(studentId)) studentIds.push(studentId);
        if (assessmentId && !assessmentIds.includes(assessmentId)) assessmentIds.push(assessmentId);
    });
    
    var studentIdsInput = document.createElement('input');
    studentIdsInput.type = 'hidden';
    studentIdsInput.name = 'student_ids';
    studentIdsInput.value = studentIds.join(',');
    studentIdsInput.setAttribute('data-generated', '1');
    form.appendChild(studentIdsInput);
    
    var assessmentIdsInput = document.createElement('input');
    assessmentIdsInput.type = 'hidden';
    assessmentIdsInput.name = 'assessment_ids';
    assessmentIdsInput.value = assessmentIds.join(',');
    assessmentIdsInput.setAttribute('data-generated', '1');
    form.appendChild(assessmentIdsInput);
    
    inputs.forEach(function(input) {
        var studentId = input.getAttribute('data-student');
        var assessmentId = input.getAttribute('data-assessment');
        var value = input.value;
        var hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `score_${studentId}_${assessmentId}`;
        hiddenInput.value = value;
        hiddenInput.setAttribute('data-generated', '1');
        form.appendChild(hiddenInput);
    });
    
    form.submit();
}
</script>
</body>
</html>