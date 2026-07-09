<?php
/**
 * ============================================================================
 * INPUT SCORE FOR ALL CAS SUBJECT - MODERN REDESIGN (FIXED)
 * ============================================================================
 * Description: Subject teachers can enter scores for all CA assessments at once
 * Features: Auto-load students, bulk save, edit existing scores
 * Version: 2.0 (PHP 8.x Compatible) - PDO Fixed
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Input Score For All Cas Subject";
$FileName = 'input_score_for_all_cas_subject.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$create_by_usertype = $_SESSION['usertype'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$action = $_GET['action'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedAssessment = $_GET['assesment'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

function randomFix($length = 10) {
    $characters = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

// ============================================================================
// GET SUBJECT DETAILS
// ============================================================================
$subjectDetail = [];
if (!empty($randomid)) {
    $subjectDetail = db_get_row(
        "SELECT * FROM school_subject WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($subjectDetail['class_id'])) {
    $classDetail = db_get_row(
        "SELECT * FROM school_class WHERE id = ? AND create_by_userid = ?",
        [$subjectDetail['class_id'], $create_by_userid]
    );
}

// ============================================================================
// GET SESSION AND TERM DETAILS
// ============================================================================
$sessionDetail = [];
if (!empty($selectedSession)) {
    $sessionDetail = db_get_row(
        "SELECT * FROM school_session WHERE id = ? AND create_by_userid = ?",
        [$selectedSession, $create_by_userid]
    );
}

$termDetail = [];
if (!empty($selectedTerm)) {
    $termDetail = db_get_row(
        "SELECT * FROM school_term WHERE id = ? AND create_by_userid = ?",
        [$selectedTerm, $create_by_userid]
    );
}

// ============================================================================
// GET ALL ASSESSMENTS FOR THIS CLASS
// ============================================================================
$assessments = [];
if (!empty($classDetail['id'])) {
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$classDetail['id'], $create_by_userid]
    );
}

// ============================================================================
// GET STUDENTS FOR THE CLASS
// ============================================================================
$students = [];
if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );
}

// ============================================================================
// GET EXISTING SCORES
// ============================================================================
$existingScores = [];
if (!empty($students) && !empty($subjectDetail['id']) && !empty($assessments)) {
    foreach ($students as $student) {
        foreach ($assessments as $ass) {
            $score = db_get_val(
                "SELECT score FROM input_score_class_teacher 
                 WHERE student_id = ? AND subject_id = ? AND assesment_id = ? 
                 AND session_id = ? AND term_id = ? AND class_id = ? 
                 AND create_by_userid = ?",
                [
                    $student['id'], 
                    $subjectDetail['id'], 
                    $ass['id'], 
                    $selectedSession, 
                    $selectedTerm, 
                    $classDetail['id'], 
                    $create_by_userid
                ]
            );
            $existingScores[$student['id']][$ass['id']] = $score !== false ? floatval($score) : '';
        }
    }
}

// ============================================================================
// SAVE SCORES (BULK)
// ============================================================================
if (isset($_POST['add_score']) || isset($_POST['edit_score'])) {
    $isEdit = isset($_POST['edit_score']);
    $studentIds = $_POST['student_id'] ?? [];
    $assessmentIds = $_POST['allassesment_id'] ?? [];
    $scores = $_POST['score'] ?? [];
    $offerings = $_POST['offering'] ?? [];
    
    $successCount = 0;
    
    // If editing, delete existing scores first
    if ($isEdit) {
        db_delete(
            "input_score_class_teacher",
            "class_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
            [
                $classDetail['id'],
                $subjectDetail['id'],
                $selectedSession,
                $selectedTerm,
                $create_by_userid
            ]
        );
    }
    
    foreach ($studentIds as $index => $studentId) {
        $assessmentId = $assessmentIds[$index] ?? 0;
        $score = floatval($scores[$index] ?? 0);
        $offering = $offerings[$index] ?? '';
        
        if (empty($studentId) || empty($assessmentId)) continue;
        
        // Check if score already exists (only for add, not edit)
        if (!$isEdit) {
            $existing = db_get_val(
                "SELECT id FROM input_score_class_teacher 
                 WHERE student_id = ? AND subject_id = ? AND assesment_id = ? 
                 AND session_id = ? AND term_id = ? AND class_id = ? 
                 AND create_by_userid = ?",
                [
                    $studentId,
                    $subjectDetail['id'],
                    $assessmentId,
                    $selectedSession,
                    $selectedTerm,
                    $classDetail['id'],
                    $create_by_userid
                ]
            );
            
            if ($existing) {
                continue; // Skip if already exists
            }
        }
        
        $data = [
            'session_id' => $selectedSession,
            'term_id' => $selectedTerm,
            'class_id' => $classDetail['id'],
            'subject_id' => $subjectDetail['id'],
            'offering' => $offering,
            'student_id' => $studentId,
            'assesment_id' => $assessmentId,
            'score' => $score,
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15),
        ];
        
        $result = db_insert("input_score_class_teacher", $data);
        if ($result) $successCount++;
        
        // Update total score
        $totalScore = db_get_val(
            "SELECT SUM(score) FROM input_score_class_teacher 
             WHERE session_id = ? AND term_id = ? AND class_id = ? 
             AND student_id = ? AND create_by_userid = ?",
            [
                $selectedSession,
                $selectedTerm,
                $classDetail['id'],
                $studentId,
                $create_by_userid
            ]
        );
        
        if ($totalScore === false) $totalScore = 0;
        $totalScore = round(floatval($totalScore), 2);
        
        $existingTotal = db_get_val(
            "SELECT id FROM result_total 
             WHERE session_id = ? AND term_id = ? AND class_id = ? 
             AND student_id = ? AND create_by_userid = ?",
            [
                $selectedSession,
                $selectedTerm,
                $classDetail['id'],
                $studentId,
                $create_by_userid
            ]
        );
        
        if ($existingTotal) {
            db_update(
                "result_total",
                ['total' => $totalScore],
                "id = ?",
                [$existingTotal]
            );
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
                'randomid' => randomFix(15),
            ]);
        }
    }
    
    if ($successCount > 0) {
        $_SESSION['success'] = ($isEdit ? "Updated" : "Saved") . " $successCount score(s) successfully!";
    } else {
        $_SESSION['error'] = "No scores were saved. Please check your input.";
    }
    
    redirect($FileName . "?action=input_score&randomid=" . $randomid . "&session=" . $selectedSession . "&term_id=" . $selectedTerm);
    exit;
}

// ============================================================================
// GET SUBJECTS FOR THE TEACHER
// ============================================================================
$teacherSubjects = [];
$teacherId = $_SESSION['userid'] ?? 0;

// Get subjects where this teacher is assigned as subject teacher
$teacherStaffId = db_get_val(
    "SELECT id FROM staff_manage 
     WHERE create_by_userid = ? 
     AND (staff_id = ? OR email = ? OR id = ?)
     ORDER BY id DESC LIMIT 1",
    [$create_by_userid, $_SESSION['username'] ?? '', $_SESSION['email'] ?? '', $teacherId]
);

if (!empty($teacherStaffId)) {
    $teacherSubjects = db_get_rows(
        "SELECT DISTINCT ss.* 
         FROM school_subject ss
         INNER JOIN subject_teacher st ON ss.id = st.school_subject
         WHERE st.staff_id = ? AND ss.create_by_userid = ?
         ORDER BY ss.subject ASC",
        [$teacherStaffId, $create_by_userid]
    );
}

// If no assigned subjects, get all subjects (fallback for admin)
if (empty($teacherSubjects)) {
    $teacherSubjects = db_get_rows(
        "SELECT * FROM school_subject WHERE create_by_userid = ? ORDER BY subject ASC",
        [$create_by_userid]
    );
}

// ============================================================================
// GET SESSIONS AND TERMS
// ============================================================================
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// ============================================================================
// CALCULATE TOTAL SCORE FOR EACH STUDENT
// ============================================================================
function calculateStudentTotal($studentId, $subjectId, $sessionId, $termId, $classId, $createByUserId) {
    $total = db_get_val(
        "SELECT SUM(score) FROM input_score_class_teacher 
         WHERE student_id = ? AND subject_id = ? 
         AND session_id = ? AND term_id = ? AND class_id = ? 
         AND create_by_userid = ?",
        [$studentId, $subjectId, $sessionId, $termId, $classId, $createByUserId]
    );
    return $total !== false ? round(floatval($total), 2) : 0;
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
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .two-column-layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .subject-list-panel { flex: 1; min-width: 280px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .score-panel { flex: 3; min-width: 500px; }
        .panel-header { padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 600; }
        .subject-list { max-height: 600px; overflow-y: auto; }
        .subject-item { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s; text-decoration: none; display: block; }
        .subject-item:hover { background: #f8f9ff; }
        .subject-item.active { background: #1B3058; color: white; }
        .subject-item.active small { color: rgba(255,255,255,0.7); }
        .subject-item small { display: block; font-size: 11px; color: #999; margin-top: 5px; }
        .score-table { width: 100%; border-collapse: collapse; }
        .score-table th, .score-table td { padding: 12px; text-align: center; border: 1px solid #e0e0e0; vertical-align: middle; }
        .score-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .score-table input { width: 80px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
        .score-table input:focus { outline: none; border-color: #1B3058; }
        .score-table input.invalid { border-color: #dc3545; background-color: #fff0f0; }
        .student-name-cell { text-align: left; font-weight: 500; }
        .total-cell { font-weight: bold; background: #e8f5e9; }
        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .score-input { width: 80px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
        .score-input:focus { outline: none; border-color: #1B3058; }
        @media (max-width: 900px) { .two-column-layout { flex-direction: column; } .filter-grid { flex-direction: column; } .score-table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="score-container">
                <div class="page-header">
                    <h2><i class="fa fa-pencil-square-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Enter scores for all Continuous Assessments (CAs) for each student</p>
                </div>

                <?= showMessage($stat) ?>

                <div class="two-column-layout">
                    <!-- LEFT: Subject List -->
                    <div class="subject-list-panel">
                        <div class="panel-header"><i class="fa fa-book"></i> My Subjects</div>
                        <div class="subject-list">
                            <?php if (!empty($teacherSubjects)): ?>
                                <?php foreach ($teacherSubjects as $subject): 
                                    $className = db_get_val("SELECT name FROM school_class WHERE id = ?", [$subject['class_id']]);
                                ?>
                                    <a href="<?= $FileName ?>?action=input_score&randomid=<?= urlencode($subject['randomid']) ?><?= $selectedSession ? '&session='.urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id='.urlencode($selectedTerm) : '' ?>" class="subject-item <?= ($randomid == $subject['randomid']) ? 'active' : '' ?>">
                                        <i class="fa fa-chalkboard-teacher"></i> <?= htmlspecialchars($subject['subject']) ?>
                                        <small><i class="fa fa-graduation-cap"></i> <?= htmlspecialchars($className ?: 'N/A') ?></small>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="subject-item">No subjects assigned to you</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT: Score Entry -->
                    <div class="score-panel">
                        <?php if (!empty($subjectDetail) && !empty($classDetail)): ?>
                            <!-- Filters -->
                            <div class="filter-card">
                                <form method="GET" action="<?= $FileName ?>" id="filterForm">
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
                                            <input type="text" class="filter-select" value="<?= htmlspecialchars($subjectDetail['subject']) ?>" disabled style="background:#f5f5f5;">
                                        </div>
                                        <div class="filter-group">
                                            <label>Class</label>
                                            <input type="text" class="filter-select" value="<?= htmlspecialchars($classDetail['name']) ?>" disabled style="background:#f5f5f5;">
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Score Entry Table -->
                            <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($students) && !empty($assessments)): ?>
                                <form method="POST" action="" id="scoreForm">
                                    <input type="hidden" name="<?= (isset($_POST['edit_score'])) ? 'edit_score' : 'add_score' ?>" value="1">
                                    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden;">
                                        <div class="panel-header">
                                            <strong><i class="fa fa-table"></i> <?= htmlspecialchars($subjectDetail['subject']) ?> - Score Entry</strong>
                                            <span style="float:right; font-size:12px; font-weight:normal;">Enter scores for all assessments below</span>
                                        </div>
                                        <div style="padding: 20px; overflow-x: auto;">
                                            <?php if (empty($students)): ?>
                                                <div class="alert alert-info" style="margin-bottom:0;">
                                                    <i class="fa fa-info-circle"></i> No students found for this class, session, and term.
                                                </div>
                                            <?php else: ?>
                                                <table class="score-table" id="scoreTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:120px;">Student Name</th>
                                                            <th>Student ID</th>
                                                            <?php foreach ($assessments as $ass): 
                                                                $percentage = db_get_val("SELECT percentage FROM score_entry_time_frame WHERE assesment_id = ?", [$ass['id']]);
                                                            ?>
                                                                <th><?= htmlspecialchars($ass['assesment']) ?><br><small>(Max: <?= floatval($percentage ?: $ass['percentage'] ?? 0) ?>)</small></th>
                                                            <?php endforeach; ?>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student): 
                                                            $totalScore = calculateStudentTotal($student['id'], $subjectDetail['id'], $selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid);
                                                        ?>
                                                            <tr>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <?php foreach ($assessments as $ass): 
                                                                    $scoreValue = $existingScores[$student['id']][$ass['id']] ?? '';
                                                                    $maxScore = floatval($ass['percentage'] ?? 0);
                                                                ?>
                                                                    <td>
                                                                        <input type="hidden" name="student_id[]" value="<?= $student['id'] ?>">
                                                                        <input type="hidden" name="allassesment_id[]" value="<?= $ass['id'] ?>">
                                                                        <input type="number" class="score-input" name="score[]" value="<?= htmlspecialchars($scoreValue) ?>" step="any" min="0" max="<?= $maxScore ?>" placeholder="0">
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td class="total-cell"><?= $totalScore ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                        <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; text-align: right;">
                                            <button type="submit" name="<?= (isset($_POST['edit_score'])) ? 'edit_score' : 'add_score' ?>" class="btn btn-success" <?= empty($students) ? 'disabled style="opacity:.6;cursor:not-allowed;"' : '' ?>>
                                                <i class="fa fa-save"></i> <?= (isset($_POST['edit_score'])) ? 'Update' : 'Save' ?> All Scores
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && empty($students)): ?>
                                <div class="alert alert-warning">No students found for this class. Please add students first.</div>
                            <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                <div class="alert alert-info">Please select a term to continue.</div>
                            <?php elseif (empty($selectedSession)): ?>
                                <div class="alert alert-info">Please select a session and term to continue.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Please select a subject from the left panel.</div>
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
// Auto-calculate totals as user types
document.querySelectorAll('.score-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var row = this.closest('tr');
        var inputs = row.querySelectorAll('.score-input');
        var total = 0;
        inputs.forEach(function(inp) {
            var val = parseFloat(inp.value) || 0;
            total += val;
        });
        var totalCell = row.querySelector('.total-cell');
        if (totalCell) {
            totalCell.textContent = total.toFixed(2);
        }
    });
});
</script>
</body>
</html>