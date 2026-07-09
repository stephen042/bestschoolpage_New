<?php
/**
 * Broad Sheet / Board Sheet - FIXED VERSION
 * Displays students, subjects, assessments, scores, totals, averages, positions
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Broad Sheet";
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $create_by_userid);
$randomid = $_GET['randomid'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$exportMode = ($_GET['export'] ?? '') === 'excel';

// ============================================================================
// GET ALL CLASSES FOR SIDEBAR
// ============================================================================
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

$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($randomid)) {
    $classDetail = db_get_row("SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);
}

// ============================================================================
// GET ASSESSMENTS FOR THIS CLASS (FIXED - Same as input page)
// ============================================================================
$assessments = [];
if (!empty($classDetail['id'])) {
    // Get assessments for this specific class OR global assessments
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC", 
        [$classDetail['id'], $create_by_userid]
    );
}
$totalAssessments = count($assessments);

// ============================================================================
// GET SCHOOL DETAILS
// ============================================================================
$schoolDetails = db_get_row("SELECT * FROM school_register WHERE id = ?", [$create_by_userid]);
$state = db_get_row("SELECT * FROM state WHERE id = ?", [$schoolDetails['state'] ?? 0]);
$stateName = $state['title'] ?? '';

// ============================================================================
// VARIABLES
// ============================================================================
$subjects = [];
$students = [];
$studentScores = [];
$studentTotals = [];
$studentAverages = [];
$studentPositions = [];
$totalSubjects = 0;
$sessionName = '';
$termName = '';
$classTotalSum = 0;
$studentCount = 0;

if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    $sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$selectedSession]);
    $termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$selectedTerm]);
    
    // Get subjects for this class
    $subjects = db_get_rows("SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC", [$classDetail['id'], $create_by_userid]);
    $totalSubjects = count($subjects);
    
    // Get students for this class
    $students = db_get_rows(
        "SELECT * FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? ORDER BY first_name ASC",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );
    $studentCount = count($students);
    
    // DIRECT QUERY - Get ALL scores for this class, session, term
    $allScores = db_get_rows(
        "SELECT student_id, subject_id, assesment_id, score 
         FROM input_score_class_teacher 
         WHERE session_id = ? 
         AND term_id = ? 
         AND class_id = ? 
         AND create_by_userid = ?",
        [$selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid]
    );
    
    // Build scores lookup array
    $scoresLookup = [];
    foreach ($allScores as $scoreRow) {
        $scoresLookup[$scoreRow['student_id']][$scoreRow['subject_id']][$scoreRow['assesment_id']] = $scoreRow['score'];
    }

    // Average rule: total score divided by number of subjects offered.
    $subjectsOfferedCount = max(1, (int)$totalSubjects);
    
    // Calculate scores for each student
    foreach ($students as $student) {
        $studentTotal = 0;
        
        foreach ($subjects as $subject) {
            foreach ($assessments as $assessment) {
                $scoreValue = $scoresLookup[$student['id']][$subject['id']][$assessment['id']] ?? 0;
                $studentScores[$student['id']][$subject['id']][$assessment['id']] = $scoreValue;
                $studentTotal += $scoreValue;
            }
        }
        
        $studentTotals[$student['id']] = $studentTotal;
        $studentAverage = round($studentTotal / $subjectsOfferedCount, 2);
        $studentAverages[$student['id']] = $studentAverage;
        $classTotalSum += $studentAverage;
    }
    
    // Calculate positions based on averages
    arsort($studentAverages);
    $rank = 1;
    $prevScore = -1;
    $tieRank = 1;
    foreach ($studentAverages as $studentId => $avgScore) {
        if ($avgScore != $prevScore) {
            $studentPositions[$studentId] = $rank;
            $tieRank = $rank;
        } else {
            $studentPositions[$studentId] = $tieRank;
        }
        $rank++;
        $prevScore = $avgScore;
    }
}

$classAverage = ($studentCount > 0) ? round($classTotalSum / $studentCount, 2) : 0;
$allAverages = array_values($studentAverages);
$highestAverage = !empty($allAverages) ? round(max($allAverages), 2) : 0;
$lowestAverage = !empty($allAverages) ? round(min($allAverages), 2) : 0;

// Cache grade rules to avoid per-row database queries.
$gradeRules = db_get_rows(
    "SELECT minimum_number, maximum_number, grade
     FROM school_grade
     WHERE create_by_userid = ?
     ORDER BY minimum_number DESC",
    [$create_by_userid]
);

function getGrade($score) {
    global $gradeRules;

    foreach ($gradeRules as $rule) {
        $min = (float)($rule['minimum_number'] ?? 0);
        $max = (float)($rule['maximum_number'] ?? 0);
        if ($score >= $min && $score <= $max) {
            return (string)($rule['grade'] ?? '');
        }
    }

    return ($score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F')))));
}

// Fast server-side export for reliable and large downloads.
if ($exportMode) {
    if (empty($classDetail['id']) || empty($selectedSession) || empty($selectedTerm)) {
        $_SESSION['error'] = 'Select class, session and term before exporting.';
        redirect('board_sheet.php?randomid=' . urlencode((string)$randomid) . '&session=' . urlencode((string)$selectedSession) . '&term_id=' . urlencode((string)$selectedTerm));
        exit;
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $safeClass = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)($classDetail['name'] ?? 'class'));
    $fileName = 'broad_sheet_' . $safeClass . '_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    $header = ['#', 'Student ID', 'First Name', 'Last Name'];
    foreach ($subjects as $subject) {
        foreach ($assessments as $assessment) {
            $header[] = (string)$subject['subject'] . ' - ' . (string)$assessment['assesment'];
        }
    }
    $header[] = 'Total';
    $header[] = 'Avg';
    $header[] = 'Grade';
    $header[] = 'Pos';
    fputcsv($out, $header);

    $counter = 0;
    foreach ($students as $student) {
        $counter++;
        $row = [
            $counter,
            (string)($student['student_id'] ?? ''),
            (string)($student['first_name'] ?? ''),
            (string)($student['last_name'] ?? '')
        ];

        $studentTotal = 0;
        foreach ($subjects as $subject) {
            foreach ($assessments as $assessment) {
                $score = (float)($studentScores[$student['id']][$subject['id']][$assessment['id']] ?? 0);
                $studentTotal += $score;
                $row[] = number_format($score, 2, '.', '');
            }
        }

        $avg = (float)($studentAverages[$student['id']] ?? 0);
        $pos = (int)($studentPositions[$student['id']] ?? 0);
        $suffix = $pos === 1 ? 'st' : ($pos === 2 ? 'nd' : ($pos === 3 ? 'rd' : 'th'));

        $row[] = number_format($studentTotal, 2, '.', '');
        $row[] = number_format($avg, 2, '.', '');
        $row[] = getGrade($avg);
        $row[] = $pos > 0 ? ($pos . $suffix) : '-';

        fputcsv($out, $row);
    }

    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title>Broad Sheet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .broad-container { max-width: 1600px; margin: 0 auto; padding: 20px; }
        .layout { display: flex; gap: 25px; flex-wrap: wrap; }
        
        .sidebar { width: 280px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .sidebar-header { padding: 18px 20px; background: #1B3058; color: white; font-weight: 600; }
        .class-list { max-height: 600px; overflow-y: auto; }
        .class-item { padding: 15px 20px; border-bottom: 1px solid #eee; cursor: pointer; text-decoration: none; display: block; color: #333; }
        .class-item:hover { background: #f0f4ff; }
        .class-item.active { background: #1B3058; color: white; }
        
        .main-content { flex: 1; min-width: 500px; }
        
        .filter-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 20px; }
        .filter-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .filter-select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; }
        .btn { padding: 10px 24px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; background: #1B3058; color: white; }
        .btn:hover { background: #f21151; }
        
        .header-section { text-align: center; margin-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; color: #1B3058; }
        .report-title { font-size: 18px; font-weight: bold; text-decoration: underline; margin-top: 5px; }
        .info-bar { background: #f0f0f0; padding: 12px; border-radius: 8px; margin: 15px 0; text-align: center; }
        
        .table-wrapper { overflow-x: auto; background: white; border-radius: 12px; border: 1px solid #e0e0e0; }
        .broad-table { border-collapse: collapse; font-size: 12px; min-width: 800px; }
        .broad-table th, .broad-table td { border: 1px solid #ddd; padding: 10px 12px; text-align: center; vertical-align: middle; white-space: nowrap; }
        .broad-table th { background: #1B3058; color: white; font-weight: 600; position: sticky; top: 0; }
        .broad-table tr:nth-child(even) { background: #f9f9f9; }
        .broad-table tr:hover { background: #f0f4ff; }
        .student-name-cell { text-align: left !important; }
        .total-cell { font-weight: bold; background: #e8f5e9; }
        .grade-cell { font-weight: bold; background: #e3f2fd; }
        .pos-1 { background: #ffd700; font-weight: bold; }
        .pos-2 { background: #c0c0c0; font-weight: bold; }
        .pos-3 { background: #cd7f32; font-weight: bold; }
        
        .summary-card { background: white; border-radius: 16px; padding: 15px 20px; margin-top: 20px; display: flex; gap: 20px; flex-wrap: wrap; justify-content: space-between; }
        .summary-item { text-align: center; flex: 1; }
        .summary-label { font-size: 11px; color: #666; text-transform: uppercase; }
        .summary-value { font-size: 20px; font-weight: bold; color: #1B3058; }
        
        .action-buttons { margin-top: 20px; text-align: right; }
        .btn-excel { background: #28a745; margin-left: 10px; }
        .btn-excel:hover { background: #218838; }
        .btn-pdf { background: #dc3545; margin-left: 10px; }
        .btn-pdf:hover { background: #c82333; }
        
        .empty-state { text-align: center; padding: 60px; background: white; border-radius: 16px; color: #999; }
        
        @media (max-width: 900px) { .layout { flex-direction: column; } .sidebar { width: 100%; } .filter-row { flex-direction: column; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="broad-container">
                <div class="layout">
                    
                    <!-- LEFT SIDEBAR -->
                    <div class="sidebar">
                        <div class="sidebar-header"><i class="fa fa-graduation-cap"></i> Select Class</div>
                        <div class="class-list">
                            <?php foreach ($allClasses as $class): ?>
                                <a href="?randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session='.$selectedSession : '' ?><?= $selectedTerm ? '&term_id='.$selectedTerm : '' ?>" class="class-item <?= ($randomid == $class['randomid']) ? 'active' : '' ?>">
                                    <i class="fa fa-book"></i> <?= htmlspecialchars($class['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- RIGHT MAIN CONTENT -->
                    <div class="main-content">
                        <?php if (!empty($classDetail)): ?>
                            
                            <!-- FILTERS -->
                            <div class="filter-card">
                                <form method="GET" action="">
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label>Session</label>
                                            <select name="session" class="filter-select" required>
                                                <option value="">-- Select Session --</option>
                                                <?php foreach($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Term</label>
                                            <select name="term_id" class="filter-select" required>
                                                <option value="">-- Select Term --</option>
                                                <?php foreach($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <button type="submit" class="btn"><i class="fa fa-filter"></i> Load Report</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <?php if (!empty($selectedSession) && !empty($selectedTerm)): ?>
                                
                                <!-- HEADER -->
                                <div class="header-section">
                                    <div class="school-name"><?= htmlspecialchars($schoolDetails['name'] ?? 'School Name') ?></div>
                                    <div><?= htmlspecialchars($schoolDetails['location'] ?? '') ?>, <?= htmlspecialchars($stateName) ?></div>
                                    <div class="report-title">BROAD SHEET REPORT</div>
                                    <div class="info-bar">
                                        <strong>Class:</strong> <?= htmlspecialchars($classDetail['name']) ?> &nbsp;|&nbsp;
                                        <strong>Session:</strong> <?= htmlspecialchars($sessionName) ?> &nbsp;|&nbsp;
                                        <strong>Term:</strong> <?= htmlspecialchars($termName) ?>
                                    </div>
                                </div>
                                
                                <!-- TABLE WITH HORIZONTAL SCROLL -->
                                <div class="table-wrapper">
                                    <table class="broad-table" id="broadSheetTable">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">#</th>
                                                <th rowspan="2">Student ID</th>
                                                <th rowspan="2">First Name</th>
                                                <th rowspan="2">Last Name</th>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <th colspan="<?= $totalAssessments ?>"><?= htmlspecialchars($subject['subject']) ?></th>
                                                <?php endforeach; ?>
                                                <th rowspan="2">Total</th>
                                                <th rowspan="2">Avg</th>
                                                <th rowspan="2">Grade</th>
                                                <th rowspan="2">Pos</th>
                                            </tr>
                                            <tr>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <?php foreach ($assessments as $assessment): ?>
                                                        <th><?= htmlspecialchars($assessment['assesment']) ?></th>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $counter = 0; foreach ($students as $student): $counter++; ?>
                                                <tr>
                                                    <td><?= $counter ?></td>
                                                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                    <td class="student-name-cell"><?= htmlspecialchars($student['first_name']) ?></td>
                                                    <td class="student-name-cell"><?= htmlspecialchars($student['last_name']) ?></td>
                                                    
                                                    <?php 
                                                    $studentTotal = 0;
                                                    foreach ($subjects as $subject): 
                                                        foreach ($assessments as $assessment): 
                                                            $score = $studentScores[$student['id']][$subject['id']][$assessment['id']] ?? 0;
                                                            $studentTotal += $score;
                                                    ?>
                                                        <td><?= number_format($score, 2) ?></td>
                                                    <?php 
                                                        endforeach; 
                                                    endforeach; 
                                                    ?>
                                                    
                                                    <td class="total-cell"><strong><?= number_format($studentTotal, 2) ?></strong></td>
                                                    <td><?= number_format($studentAverages[$student['id']] ?? 0, 2) ?></td>
                                                    <td class="grade-cell"><?= getGrade($studentAverages[$student['id']] ?? 0) ?></td>
                                                    <td class="<?php 
                                                        $pos = $studentPositions[$student['id']] ?? 0;
                                                        echo ($pos == 1) ? 'pos-1' : (($pos == 2) ? 'pos-2' : (($pos == 3) ? 'pos-3' : ''));
                                                    ?>">
                                                        <?= $pos ? $pos . ($pos == 1 ? 'st' : ($pos == 2 ? 'nd' : ($pos == 3 ? 'rd' : 'th'))) : '-' ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($students)): ?>
                                                <tr><td colspan="<?= 4 + ($totalSubjects * $totalAssessments) + 4 ?>" style="text-align:center;">No students found for this class</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- SUMMARY -->
                                <div class="summary-card">
                                    <div class="summary-item"><div class="summary-label">No. of Students</div><div class="summary-value"><?= $studentCount ?></div></div>
                                    <div class="summary-item"><div class="summary-label">Class Average</div><div class="summary-value"><?= number_format($classAverage, 2) ?></div></div>
                                    <div class="summary-item"><div class="summary-label">Highest Average</div><div class="summary-value"><?= number_format($highestAverage, 2) ?></div></div>
                                    <div class="summary-item"><div class="summary-label">Lowest Average</div><div class="summary-value"><?= number_format($lowestAverage, 2) ?></div></div>
                                </div>
                                
                                <!-- BUTTONS -->
                                <div class="action-buttons">
                                    <button class="btn btn-excel" onclick="exportToExcel()"><i class="fa fa-file-excel-o"></i> Download Excel</button>
                                    <button class="btn btn-pdf" onclick="window.print()"><i class="fa fa-print"></i> Save as PDF</button>
                                </div>
                                
                            <?php else: ?>
                                <div class="empty-state"><i class="fa fa-filter"></i><h3>Select Session and Term</h3><p>Please select a session and term to view the Broad Sheet.</p></div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="empty-state"><i class="fa fa-graduation-cap"></i><h3>Select a Class</h3><p>Please select a class from the left sidebar.</p></div>
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
function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = 'board_sheet.php?' + params.toString();
}
</script>
</body>
</html>